<?php

namespace App\Services;

use App\Models\Vehicle;
use Shuchkin\SimpleXLSX;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class VehicleImportService
{
    /**
     * Import vehicles from Excel or CSV file
     *
     * @param string $filePath
     * @return array
     */
    public function importFromExcel($filePath)
    {
        $result = [
            'success' => false,
            'message' => '',
            'data' => [
                'total_rows' => 0,
                'imported' => 0,
                'failed' => 0,
                'errors' => []
            ]
        ];

        try {
            // Determine file type and parse accordingly
            $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $rows = [];

            if ($fileExtension === 'csv') {
                // Parse CSV file
                $rows = $this->parseCsvFile($filePath);
            } else {
                // Parse Excel file
                if (!$xlsx = SimpleXLSX::parse($filePath)) {
                    $result['message'] = 'Failed to parse Excel file: ' . SimpleXLSX::parseError();
                    return $result;
                }
                $rows = $xlsx->rows();
            }

            $totalRows = count($rows);

            if ($totalRows <= 1) {
                $result['message'] = 'File is empty or contains only headers';
                return $result;
            }

            $result['data']['total_rows'] = $totalRows - 1; // Exclude header row

            // Expected headers: vehical_number, company, vehical_series, fuel_type
            $expectedHeaders = ['vehical_number', 'company', 'vehical_series', 'fuel_type'];
            $rawHeaders = $rows[0] ?? [];

            // Clean and normalize headers
            $headers = [];
            foreach ($rawHeaders as $header) {
                // Clean the header: lowercase, trim, replace spaces with underscores
                $cleanHeader = strtolower(trim(str_replace(' ', '_', $header)));
                $headers[] = $cleanHeader;
            }

            // Debug: Log the headers for troubleshooting
            Log::info('Raw headers: ' . json_encode($rawHeaders));
            Log::info('Cleaned headers: ' . json_encode($headers));
            Log::info('Expected headers: ' . json_encode($expectedHeaders));

            // Create header mapping with flexible matching
            $headerMapping = [];
            foreach ($expectedHeaders as $expected) {
                $found = false;
                foreach ($headers as $index => $actual) {
                    // Try exact match first
                    if ($actual === $expected) {
                        $headerMapping[$expected] = $index;
                        $found = true;
                        break;
                    }
                    // Try partial match (for cases like "vehicle number" matching "vehical_number")
                    if (
                        strpos($actual, str_replace('_', ' ', $expected)) !== false ||
                        strpos(str_replace('_', ' ', $expected), $actual) !== false
                    ) {
                        $headerMapping[$expected] = $index;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $result['message'] = 'Missing required column: ' . $expected .
                        '. Found headers: ' . implode(', ', $headers) .
                        '. Raw headers: ' . implode(', ', $rawHeaders);
                    return $result;
                }
            }

            // Process each row (skip header)
            for ($i = 1; $i < $totalRows; $i++) {
                $row = $rows[$i];
                $rowNumber = $i + 1;

                try {
                    // Extract data based on header positions
                    $vehicleData = [
                        'vehical_number' => isset($row[$headerMapping['vehical_number']]) ? trim($row[$headerMapping['vehical_number']]) : '',
                        'company' => isset($row[$headerMapping['company']]) ? trim($row[$headerMapping['company']]) : '',
                        'vehical_series' => isset($row[$headerMapping['vehical_series']]) ? trim($row[$headerMapping['vehical_series']]) : '',
                        'fuel_type' => isset($row[$headerMapping['fuel_type']]) ? trim($row[$headerMapping['fuel_type']]) : '',
                        'status' => '1' // Default active status
                    ];

                    // Validate data
                    $validator = $this->validateVehicleData($vehicleData, $rowNumber);

                    if ($validator->fails()) {
                        $result['data']['failed']++;
                        $result['data']['errors'][] = [
                            'row' => $rowNumber,
                            'errors' => $validator->errors()->all()
                        ];
                        continue;
                    }

                    // Check if vehicle already exists (by vehical_number)
                    $existingVehicle = Vehicle::where('vehical_number', $vehicleData['vehical_number'])->first();

                    if ($existingVehicle) {
                        $result['data']['failed']++;
                        $result['data']['errors'][] = [
                            'row' => $rowNumber,
                            'errors' => ['Vehicle with this vehicle number already exists']
                        ];
                        continue;
                    }

                    // Create vehicle
                    Vehicle::create($vehicleData);
                    $result['data']['imported']++;

                } catch (\Exception $e) {
                    $result['data']['failed']++;
                    $result['data']['errors'][] = [
                        'row' => $rowNumber,
                        'errors' => ['Error processing row: ' . $e->getMessage()]
                    ];
                    Log::error('Vehicle import error for row ' . $rowNumber . ': ' . $e->getMessage());
                }
            }

            $result['success'] = true;
            $result['message'] = "Import completed. {$result['data']['imported']} vehicles imported, {$result['data']['failed']} failed.";

        } catch (\Exception $e) {
            $result['message'] = 'Import failed: ' . $e->getMessage();
            Log::error('Vehicle import error: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Validate vehicle data
     *
     * @param array $data
     * @param int $rowNumber
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validateVehicleData($data, $rowNumber)
    {
        $rules = [
            'vehical_number' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'vehical_series' => 'nullable|string|max:50',
            'fuel_type' => 'required|string|max:100',
        ];

        $messages = [
            'vehical_number.required' => "Vehicle number is required in row {$rowNumber}",
            'company.required' => "Company is required in row {$rowNumber}",
            'fuel_type.required' => "Fuel type is required in row {$rowNumber}",
        ];

        return Validator::make($data, $rules, $messages);
    }

    /**
     * Generate sample Excel template
     *
     * @return array
     */
    public function generateSampleData()
    {
        return [
            ['vehical_number', 'company', 'vehical_series', 'fuel_type'],
            ['MH43BP8938', 'RRCLPL', '1', 'Diesel'],
            ['MH43BP8939', 'RRCLPL', '1', 'Diesel'],
            ['GJ12BW7502', 'RRCLPL', '2', 'Diesel'],
            ['GJ12BW7378', 'RRC', '3', 'Diesel'],
        ];
    }

    /**
     * Parse CSV file and return rows array
     *
     * @param string $filePath
     * @return array
     */
    private function parseCsvFile($filePath)
    {
        $rows = [];

        if (($handle = fopen($filePath, "r")) !== FALSE) {
            $firstRow = true;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Remove BOM from first cell of first row
                if ($firstRow && isset($data[0])) {
                    $data[0] = $this->removeBOM($data[0]);
                    $firstRow = false;
                }
                $rows[] = $data;
            }
            fclose($handle);
        }

        return $rows;
    }

    /**
     * Remove BOM (Byte Order Mark) from string
     *
     * @param string $text
     * @return string
     */
    private function removeBOM($text)
    {
        // Remove UTF-8 BOM
        if (substr($text, 0, 3) === "\xEF\xBB\xBF") {
            $text = substr($text, 3);
        }

        // Remove UTF-16 LE BOM
        if (substr($text, 0, 2) === "\xFF\xFE") {
            $text = substr($text, 2);
        }

        // Remove UTF-16 BE BOM
        if (substr($text, 0, 2) === "\xFE\xFF") {
            $text = substr($text, 2);
        }

        return $text;
    }
}
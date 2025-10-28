<?php

namespace App\Services;

use App\Models\Distance;
use Shuchkin\SimpleXLSX;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class DistanceImportService
{
    /**
     * Import distances from Excel or CSV file
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

            // Expected headers: from, to, trip, distance
            $expectedHeaders = ['from', 'to', 'trip', 'distance'];
            $rawHeaders = $rows[0] ?? [];

            // Clean and normalize headers
            $headers = [];
            foreach ($rawHeaders as $header) {
                // Clean the header: lowercase, trim
                $cleanHeader = strtolower(trim($header));
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
                    // Try partial match
                    if (strpos($actual, $expected) !== false || strpos($expected, $actual) !== false) {
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
                    $from = isset($row[$headerMapping['from']]) ? trim(strtoupper($row[$headerMapping['from']])) : '';
                    $to = isset($row[$headerMapping['to']]) ? trim(strtoupper($row[$headerMapping['to']])) : '';
                    $trip = isset($row[$headerMapping['trip']]) ? trim(strtoupper($row[$headerMapping['trip']])) : '';
                    $distance = isset($row[$headerMapping['distance']]) ? trim($row[$headerMapping['distance']]) : '';

                    $distanceData = [
                        'from_location' => $from,
                        'to_location' => $to,
                        'trip_name' => $trip,
                        'distance' => (float) $distance,
                        'status' => '1' // Default active status
                    ];

                    // Validate data
                    $validator = $this->validateDistanceData($distanceData, $rowNumber);

                    if ($validator->fails()) {
                        $result['data']['failed']++;
                        $result['data']['errors'][] = [
                            'row' => $rowNumber,
                            'errors' => $validator->errors()->all()
                        ];
                        continue;
                    }

                    // Check if distance already exists (by from_location and to_location)
                    $existingDistance = Distance::where('from_location', $distanceData['from_location'])
                        ->where('to_location', $distanceData['to_location'])
                        ->first();

                    if ($existingDistance) {
                        $result['data']['failed']++;
                        $result['data']['errors'][] = [
                            'row' => $rowNumber,
                            'errors' => ['Distance route from ' . $from . ' to ' . $to . ' already exists']
                        ];
                        continue;
                    }

                    // Create distance
                    Distance::create($distanceData);
                    $result['data']['imported']++;

                } catch (\Exception $e) {
                    $result['data']['failed']++;
                    $result['data']['errors'][] = [
                        'row' => $rowNumber,
                        'errors' => ['Error processing row: ' . $e->getMessage()]
                    ];
                    Log::error('Distance import error for row ' . $rowNumber . ': ' . $e->getMessage());
                }
            }

            $result['success'] = true;
            $result['message'] = "Import completed. {$result['data']['imported']} distances imported, {$result['data']['failed']} failed.";

        } catch (\Exception $e) {
            $result['message'] = 'Import failed: ' . $e->getMessage();
            Log::error('Distance import error: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Validate distance data
     *
     * @param array $data
     * @param int $rowNumber
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validateDistanceData($data, $rowNumber)
    {
        $rules = [
            'from_location' => 'required|string|max:255',
            'to_location' => 'required|string|max:255',
            'trip_name' => 'required|string|max:255',
            'distance' => 'required|numeric|min:0',
        ];

        $messages = [
            'from_location.required' => "From location is required in row {$rowNumber}",
            'to_location.required' => "To location is required in row {$rowNumber}",
            'trip_name.required' => "Trip name is required in row {$rowNumber}",
            'distance.required' => "Distance is required in row {$rowNumber}",
            'distance.numeric' => "Distance must be a number in row {$rowNumber}",
            'distance.min' => "Distance must be greater than 0 in row {$rowNumber}",
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
            ['from', 'to', 'trip', 'distance'],
            ['MANGAON', 'PARADEEP', 'MANGAON-PARADEEP', '1900'],
            ['DANKUNI', 'KHURDA', 'DANKUNI-KHURDA', '475'],
            ['AHMEDABAD', 'MALIA', 'AHMEDABAD-MALIA', '160'],
            ['KHIRASARA', 'CHHATRAL', 'KHIRASARA-CHHATRAL', '250'],
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
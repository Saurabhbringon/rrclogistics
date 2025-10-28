<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\MileageRate;
use App\Services\VehicleImportService;
use Illuminate\Support\Facades\Validator;
use Exception;

class VehicleController extends Controller
{
    /**
     * Display a listing of vehicles.
     */
    public function index()
    {
        try {
            $vehicles = Vehicle::orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Vehicles retrieved successfully',
                'data' => [
                    'vehicles' => $vehicles,
                    'total' => $vehicles->count()
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to retrieve vehicles',
                'data' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Store a newly created vehicle.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'vehical_number' => 'required|string|max:255|unique:vehicles,vehical_number',
                'company' => 'required|string|max:255',
                'vehical_series' => 'required|string|max:50',
                'fuel_type' => 'required|string|max:100',
                'status' => 'nullable|in:0,1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Validation failed',
                    'data' => ['errors' => $validator->errors()]
                ], 400);
            }

            $vehicle = Vehicle::create($request->all());

            return response()->json([
                'status' => 201,
                'success' => true,
                'message' => 'Vehicle created successfully',
                'data' => ['vehicle' => $vehicle]
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to create vehicle',
                'data' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Display the specified vehicle.
     */
    public function show($id)
    {
        try {
            $vehicle = Vehicle::find($id);

            if (!$vehicle) {
                return response()->json([
                    'status' => 404,
                    'success' => false,
                    'message' => 'Vehicle not found',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Vehicle retrieved successfully',
                'data' => ['vehicle' => $vehicle]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to retrieve vehicle',
                'data' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Update the specified vehicle.
     */
    public function update(Request $request, $id)
    {
        try {
            $vehicle = Vehicle::find($id);

            if (!$vehicle) {
                return response()->json([
                    'status' => 404,
                    'success' => false,
                    'message' => 'Vehicle not found',
                    'data' => []
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'vehical_number' => 'required|string|max:255|unique:vehicles,vehical_number,' . $id,
                'company' => 'required|string|max:255',
                'vehical_series' => 'nullable|string|max:50',
                'fuel_type' => 'required|string|max:100',
                'status' => 'nullable|in:0,1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Validation failed',
                    'data' => ['errors' => $validator->errors()]
                ], 400);
            }

            $vehicle->update($request->all());

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Vehicle updated successfully',
                'data' => ['vehicle' => $vehicle->fresh()]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to update vehicle',
                'data' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Remove the specified vehicle.
     */
    public function destroy($id)
    {
        try {
            $vehicle = Vehicle::find($id);

            if (!$vehicle) {
                return response()->json([
                    'status' => 404,
                    'success' => false,
                    'message' => 'Vehicle not found',
                    'data' => []
                ], 404);
            }

            $vehicle->delete();

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Vehicle deleted successfully',
                'data' => []
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to delete vehicle',
                'data' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Get active vehicles.
     */
    public function getActiveVehicles()
    {
        try {
            $vehicles = Vehicle::active()->orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Active vehicles retrieved successfully',
                'data' => [
                    'vehicles' => $vehicles,
                    'total' => $vehicles->count()
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to retrieve active vehicles',
                'data' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Get inactive vehicles.
     */
    public function getInactiveVehicles()
    {
        try {
            $vehicles = Vehicle::inactive()->orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Inactive vehicles retrieved successfully',
                'data' => [
                    'vehicles' => $vehicles,
                    'total' => $vehicles->count()
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to retrieve inactive vehicles',
                'data' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Update vehicle status.
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $vehicle = Vehicle::find($id);

            if (!$vehicle) {
                return response()->json([
                    'status' => 404,
                    'success' => false,
                    'message' => 'Vehicle not found',
                    'data' => []
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:0,1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Validation failed',
                    'data' => ['errors' => $validator->errors()]
                ], 400);
            }

            $vehicle->update(['status' => $request->status]);

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Vehicle status updated successfully',
                'data' => ['vehicle' => $vehicle->fresh()]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to update vehicle status',
                'data' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Search vehicles.
     */
    public function search(Request $request)
    {
        try {
            $query = Vehicle::query();

            if ($request->has('vehical_number') && $request->vehical_number) {
                $query->where('vehical_number', 'like', '%' . $request->vehical_number . '%');
            }

            if ($request->has('company') && $request->company) {
                $query->where('company', 'like', '%' . $request->company . '%');
            }

            if ($request->has('fuel_type') && $request->fuel_type) {
                $query->where('fuel_type', 'like', '%' . $request->fuel_type . '%');
            }

            if ($request->has('status') && $request->status !== null) {
                $query->where('status', $request->status);
            }

            $vehicles = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Vehicle search completed successfully',
                'data' => [
                    'vehicles' => $vehicles,
                    'total' => $vehicles->count()
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to search vehicles',
                'data' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Upload and import vehicles from Excel/CSV file
     */
    public function uploadVehicleDataExcel(Request $request)
    {
        try {
            // Validate the uploaded file
            $validator = Validator::make($request->all(), [
                'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240' // Max 10MB
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Validation failed',
                    'data' => ['errors' => $validator->errors()]
                ], 400);
            }

            $file = $request->file('excel_file');

            // Store the file temporarily
            $tempPath = $file->store('temp', 'local');
            $fullPath = storage_path('app/' . $tempPath);

            // Import vehicles using the service
            $importService = new VehicleImportService();
            $result = $importService->importFromExcel($fullPath);

            // Clean up the temporary file
            unlink($fullPath);

            if ($result['success']) {
                return response()->json([
                    'status' => 200,
                    'success' => true,
                    'message' => $result['message'],
                    'data' => $result['data']
                ], 200);
            } else {
                return response()->json([
                    'status' => 400,
                    'success' => false,
                    'message' => $result['message'],
                    'data' => $result['data']
                ], 400);
            }

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to upload and import Excel file',
                'data' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Download sample Excel template for vehicle import
     */
    public function downloadTemplate()
    {
        try {
            $importService = new VehicleImportService();
            $sampleData = $importService->generateSampleData();

            // Create CSV content (Excel-compatible)
            $filename = 'vehicle_import_template.csv';
            $handle = fopen('php://temp', 'w+');

            // Add BOM for UTF-8
            fwrite($handle, "\xEF\xBB\xBF");

            // Write sample data
            foreach ($sampleData as $row) {
                fputcsv($handle, $row);
            }

            rewind($handle);
            $csvContent = stream_get_contents($handle);
            fclose($handle);

            return response($csvContent)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-cache, must-revalidate');

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to generate template',
                'data' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    public function milage(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'vehicle_number' => 'required|string',
                'load_category' => 'required|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Validation failed',
                    'data' => ['errors' => $validator->errors()]
                ], 400);
            }

            $vehicleNumber = $request->vehicle_number;
            $loadCategory = (float) $request->load_category;

            // Find vehicle by vehicle number
            $vehicle = Vehicle::where('vehical_number', $vehicleNumber)->first();

            if (!$vehicle) {
                return response()->json([
                    'status' => 404,
                    'success' => false,
                    'message' => 'Vehicle not found',
                    'data' => []
                ], 404);
            }

            $vehicalSeries = (int) $vehicle->vehical_series;
            $mileage = $this->calculateMileage($vehicalSeries, $loadCategory);

            if ($mileage === null) {
                return response()->json([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Unable to calculate mileage for the given vehicle series',
                    'data' => [
                        'vehicle_number' => $vehicleNumber,
                        'vehical_series' => $vehicalSeries,
                        'load_category' => $loadCategory
                    ]
                ], 400);
            }

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Mileage calculated successfully',
                'data' => [
                    'vehicle_number' => $vehicleNumber,
                    'vehical_series' => $vehicalSeries,
                    'load_category' => $loadCategory,
                    'mileage' => $mileage,
                    'unit' => 'km/lt'
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to calculate mileage',
                'data' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Calculate mileage based on vehicle series and load category
     *
     * @param int $vehicalSeries
     * @param float $loadCategory
     * @return float|null
     */
    private function calculateMileage($vehicalSeries, $loadCategory)
    {
        // Get rate from database
        $mileageRate = MileageRate::findRateForLoad($vehicalSeries, $loadCategory);

        if ($mileageRate) {
            return $mileageRate->mileage_rate;
        }

        // Return null if no rate found in database
        return null;
    }
}
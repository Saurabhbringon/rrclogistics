<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Distance;
use App\Services\DistanceImportService;
use Illuminate\Support\Facades\Validator;
use Exception;

class DistanceController extends Controller
{
    /**
     * Display a listing of distances.
     */
    public function index()
    {
        try {
            $distances = Distance::orderBy('from_location', 'asc')
                ->orderBy('to_location', 'asc')
                ->get();

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Distances retrieved successfully',
                'data' => [
                    'distances' => $distances,
                    'total' => $distances->count()
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to retrieve distances',
                'data' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Store a newly created distance.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'from_location' => 'required|string|max:255',
                'to_location' => 'required|string|max:255',
                'trip_name' => 'required|string|max:255',
                'distance' => 'required|numeric|min:0',
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

            // Check if route already exists
            $existingDistance = Distance::where('from_location', strtoupper(trim($request->from_location)))
                ->where('to_location', strtoupper(trim($request->to_location)))
                ->first();

            if ($existingDistance) {
                return response()->json([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Distance route already exists',
                    'data' => []
                ], 400);
            }

            $data = $request->all();
            $data['from_location'] = strtoupper(trim($data['from_location']));
            $data['to_location'] = strtoupper(trim($data['to_location']));
            $data['trip_name'] = strtoupper(trim($data['trip_name']));

            $distance = Distance::create($data);

            return response()->json([
                'status' => 201,
                'success' => true,
                'message' => 'Distance created successfully',
                'data' => ['distance' => $distance]
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to create distance',
                'data' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Display the specified distance.
     */
    public function show($id)
    {
        try {
            $distance = Distance::find($id);

            if (!$distance) {
                return response()->json([
                    'status' => 404,
                    'success' => false,
                    'message' => 'Distance not found',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Distance retrieved successfully',
                'data' => ['distance' => $distance]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to retrieve distance',
                'data' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Update the specified distance.
     */
    public function update(Request $request, $id)
    {
        try {
            $distance = Distance::find($id);

            if (!$distance) {
                return response()->json([
                    'status' => 404,
                    'success' => false,
                    'message' => 'Distance not found',
                    'data' => []
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'from_location' => 'required|string|max:255',
                'to_location' => 'required|string|max:255',
                'trip_name' => 'required|string|max:255',
                'distance' => 'required|numeric|min:0',
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

            // Check if route already exists (excluding current record)
            $existingDistance = Distance::where('from_location', strtoupper(trim($request->from_location)))
                ->where('to_location', strtoupper(trim($request->to_location)))
                ->where('id', '!=', $id)
                ->first();

            if ($existingDistance) {
                return response()->json([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Distance route already exists',
                    'data' => []
                ], 400);
            }

            $data = $request->all();
            $data['from_location'] = strtoupper(trim($data['from_location']));
            $data['to_location'] = strtoupper(trim($data['to_location']));
            $data['trip_name'] = strtoupper(trim($data['trip_name']));

            $distance->update($data);

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Distance updated successfully',
                'data' => ['distance' => $distance->fresh()]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to update distance',
                'data' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Remove the specified distance.
     */
    public function destroy($id)
    {
        try {
            $distance = Distance::find($id);

            if (!$distance) {
                return response()->json([
                    'status' => 404,
                    'success' => false,
                    'message' => 'Distance not found',
                    'data' => []
                ], 404);
            }

            $distance->delete();

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Distance deleted successfully',
                'data' => []
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to delete distance',
                'data' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Search distances.
     */
    public function search(Request $request)
    {
        try {
            $query = Distance::query();

            if ($request->has('from_location') && $request->from_location) {
                $query->fromLocation($request->from_location);
            }

            if ($request->has('to_location') && $request->to_location) {
                $query->toLocation($request->to_location);
            }

            if ($request->has('trip_name') && $request->trip_name) {
                $query->byTrip($request->trip_name);
            }

            if ($request->has('status') && $request->status !== null) {
                $query->where('status', $request->status);
            }

            $distances = $query->orderBy('from_location', 'asc')
                ->orderBy('to_location', 'asc')
                ->get();

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Distance search completed successfully',
                'data' => [
                    'distances' => $distances,
                    'total' => $distances->count()
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to search distances',
                'data' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Get distance between two specific locations
     */
    public function getRouteDistance(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'from_location' => 'required|string',
                'to_location' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Validation failed',
                    'data' => ['errors' => $validator->errors()]
                ], 400);
            }

            $from = strtoupper(trim($request->from_location));
            $to = strtoupper(trim($request->to_location));

            // Try to find direct route
            $distance = Distance::findDistance($from, $to);

            // If not found, try reverse route
            if (!$distance) {
                $distance = Distance::findReverseDistance($from, $to);
            }

            if (!$distance) {
                return response()->json([
                    'status' => 404,
                    'success' => false,
                    'message' => "No route found from {$from} to {$to}",
                    'data' => []
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Route distance found successfully',
                'data' => [
                    'from_location' => $from,
                    'to_location' => $to,
                    'distance' => $distance->distance,
                    'formatted_distance' => $distance->formatted_distance,
                    'trip_name' => $distance->trip_name
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to get route distance',
                'data' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Upload and import distances from Excel/CSV file
     */
    public function uploadDistanceDataExcel(Request $request)
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

            // Import distances using the service
            $importService = new DistanceImportService();
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
     * Download sample Excel template for distance import
     */
    public function downloadTemplate()
    {
        try {
            $importService = new DistanceImportService();
            $sampleData = $importService->generateSampleData();

            // Create CSV content (Excel-compatible)
            $filename = 'distance_import_template.csv';
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
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MileageRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MileageRateController extends Controller
{
    /**
     * Display a listing of mileage rates
     */
    public function index(Request $request)
    {
        try {
            $query = MileageRate::query();

            // Filter by vehicle series if provided
            if ($request->has('vehicle_series')) {
                $query->where('vehicle_series', $request->vehicle_series);
            }

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Get all or paginated
            if ($request->has('per_page')) {
                $rates = $query->orderBy('vehicle_series')
                    ->orderBy('load_from')
                    ->paginate($request->per_page);
            } else {
                $rates = $query->orderBy('vehicle_series')
                    ->orderBy('load_from')
                    ->get();
            }

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Mileage rates retrieved successfully',
                'data' => $rates
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to retrieve mileage rates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created mileage rate
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'vehicle_series' => 'required|integer',
                'load_from' => 'required|numeric|min:0',
                'load_to' => 'nullable|numeric|gt:load_from',
                'mileage_rate' => 'required|numeric|min:0',
                'status' => 'nullable|in:0,1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check for overlapping ranges
            $overlap = MileageRate::where('vehicle_series', $request->vehicle_series)
                ->where(function ($query) use ($request) {
                    $query->where(function ($q) use ($request) {
                        // Check if new range overlaps with existing ranges
                        $q->where('load_from', '<=', $request->load_from)
                            ->where(function ($subQ) use ($request) {
                            $subQ->whereNull('load_to')
                                ->orWhere('load_to', '>=', $request->load_from);
                        });
                    })
                        ->orWhere(function ($q) use ($request) {
                            if ($request->load_to !== null) {
                                $q->where('load_from', '<=', $request->load_to)
                                    ->where(function ($subQ) use ($request) {
                                        $subQ->whereNull('load_to')
                                            ->orWhere('load_to', '>=', $request->load_to);
                                    });
                            }
                        });
                })
                ->exists();

            if ($overlap) {
                return response()->json([
                    'status' => 422,
                    'success' => false,
                    'message' => 'Load range overlaps with existing mileage rate for this vehicle series'
                ], 422);
            }

            $mileageRate = MileageRate::create([
                'vehicle_series' => $request->vehicle_series,
                'load_from' => $request->load_from,
                'load_to' => $request->load_to,
                'mileage_rate' => $request->mileage_rate,
                'status' => $request->status ?? 1
            ]);

            return response()->json([
                'status' => 201,
                'success' => true,
                'message' => 'Mileage rate created successfully',
                'data' => $mileageRate
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to create mileage rate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified mileage rate
     */
    public function show($id)
    {
        try {
            $mileageRate = MileageRate::find($id);

            if (!$mileageRate) {
                return response()->json([
                    'status' => 404,
                    'success' => false,
                    'message' => 'Mileage rate not found'
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Mileage rate retrieved successfully',
                'data' => $mileageRate
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to retrieve mileage rate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified mileage rate
     */
    public function update(Request $request, $id)
    {
        try {
            $mileageRate = MileageRate::find($id);

            if (!$mileageRate) {
                return response()->json([
                    'status' => 404,
                    'success' => false,
                    'message' => 'Mileage rate not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'vehicle_series' => 'sometimes|required|integer',
                'load_from' => 'sometimes|required|numeric|min:0',
                'load_to' => 'nullable|numeric|gt:load_from',
                'mileage_rate' => 'sometimes|required|numeric|min:0',
                'status' => 'nullable|in:0,1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check for overlapping ranges (excluding current record)
            $vehicleSeries = $request->vehicle_series ?? $mileageRate->vehicle_series;
            $loadFrom = $request->load_from ?? $mileageRate->load_from;
            $loadTo = $request->load_to ?? $mileageRate->load_to;

            $overlap = MileageRate::where('vehicle_series', $vehicleSeries)
                ->where('id', '!=', $id)
                ->where(function ($query) use ($loadFrom, $loadTo) {
                    $query->where(function ($q) use ($loadFrom) {
                        $q->where('load_from', '<=', $loadFrom)
                            ->where(function ($subQ) use ($loadFrom) {
                                $subQ->whereNull('load_to')
                                    ->orWhere('load_to', '>=', $loadFrom);
                            });
                    })
                        ->orWhere(function ($q) use ($loadTo) {
                            if ($loadTo !== null) {
                                $q->where('load_from', '<=', $loadTo)
                                    ->where(function ($subQ) use ($loadTo) {
                                        $subQ->whereNull('load_to')
                                            ->orWhere('load_to', '>=', $loadTo);
                                    });
                            }
                        });
                })
                ->exists();

            if ($overlap) {
                return response()->json([
                    'status' => 422,
                    'success' => false,
                    'message' => 'Load range overlaps with existing mileage rate for this vehicle series'
                ], 422);
            }

            $mileageRate->update($request->only([
                'vehicle_series',
                'load_from',
                'load_to',
                'mileage_rate',
                'status'
            ]));

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Mileage rate updated successfully',
                'data' => $mileageRate
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to update mileage rate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified mileage rate
     */
    public function destroy($id)
    {
        try {
            $mileageRate = MileageRate::find($id);

            if (!$mileageRate) {
                return response()->json([
                    'status' => 404,
                    'success' => false,
                    'message' => 'Mileage rate not found'
                ], 404);
            }

            $mileageRate->delete();

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Mileage rate deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to delete mileage rate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get mileage rate for specific vehicle series and load
     */
    public function getRateForLoad(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'vehicle_series' => 'required|integer|min:1|max:7',
                'load_category' => 'required|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $rate = MileageRate::findRateForLoad(
                $request->vehicle_series,
                $request->load_category
            );

            if (!$rate) {
                return response()->json([
                    'status' => 404,
                    'success' => false,
                    'message' => 'No mileage rate found for this vehicle series and load category'
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Mileage rate retrieved successfully',
                'data' => [
                    'mileage_rate' => $rate->mileage_rate,
                    'load_range' => [
                        'from' => $rate->load_from,
                        'to' => $rate->load_to ?? 'unlimited'
                    ],
                    'vehicle_series' => $rate->vehicle_series
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to retrieve mileage rate',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
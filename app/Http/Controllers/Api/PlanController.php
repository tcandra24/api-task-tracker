<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $token = request()->user()->currentAccessToken();

            $plans = Plan::where('user_id', $token->tokenable->id)->get();
            return response()->json([
                'success' => true,
                'data' => $plans
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve plans'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePlanRequest $request)
    {
        try {
            $token = request()->user()->currentAccessToken();

            DB::transaction(function() use ($request, $token){
                $plan = Plan::create([
                    'name' => $request->name,
                    'date_start' => $request->date_start,
                    'date_end' => $request->date_end,
                    'user_id' => $token->tokenable->id
                ]);

                $plan->details()->createMany($request->details);
            });

            return response()->json([
                'success' => true,
                'message' => 'Plan created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Plan $plan)
    {
        try {
            $plan->load(['details']);

            return response()->json([
                'success' => true,
                'data' => $plan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve plan'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlanRequest $request, Plan $plan)
    {
        try {
            $token = request()->user()->currentAccessToken();

            DB::transaction(function() use ($request, $plan, $token){
                $plan->where('user_id', $token->tokenable->id)->update([
                    'name' => $request->name,
                    'date_start' => $request->date_start,
                    'date_end' => $request->date_end
                ]);

                // Check if data have new detail plan not in the database
                $existingDetail = $plan->details()->pluck('id')->toArray();
                $newDetails = collect($request->details)->whereNotIn('id', $existingDetail);

                // Check if data remove in details & still exist on database
                $requestDetails = collect($request->details)->pluck('id')->toArray();
                $removedDetails = $plan->details()->whereNotIn('id', $requestDetails);

                // Only insert new data or update old data & delete data not exist in details array for data in the table plans
                $newDetails->each(function($detail) use ($plan) {
                    $plan->details()->create([
                        'name' => $detail['name']
                    ]);
                });

                $removedDetails->each(function($detail) use ($plan) {
                    $plan->details()->where('id', $detail['id'])->delete();
                });
            });

            return response()->json([
                'success' => true,
                'message' => 'Plan updated successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plan $plan)
    {
        try {
            $token = request()->user()->currentAccessToken();

            $plan->where('user_id', $token->tokenable->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Plan deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

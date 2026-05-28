<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Models\Plan;
use App\Models\PlanDetail;

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
    public function store(Request $request)
    {
        try {
            $validator = Validator($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'date_start' => ['required', 'date'],
                'date_end' => ['required', 'date'],
                'details' => ['required', 'array'],
                'details.*.name' => ['required', 'string']
            ]);

            if($validator->fails()){
                return response()->json(['message' => $validator->errors()]);
            }

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
    public function update(Request $request, Plan $plan)
    {
        try {
            $validator = Validator($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'date_start' => ['required', 'date'],
                'date_end' => ['required', 'date'],
                'details' => ['required', 'array'],
                'details.*.name' => ['required', 'string']
            ]);

            if($validator->fails()){
                return response()->json(['message' => $validator->errors()]);
            }

            DB::transaction(function() use ($request, $plan){
                $plan->update([
                    'name' => $request->name,
                    'date_start' => $request->date_start,
                    'date_end' => $request->date_end
                ]);

                $incomingIds = collect($request->details)->pluck('id')->filter();
                PlanDetail::whereNotIn('id', $incomingIds)->delete();

                $plan->details()->upsert($request->details, ['id'], ['name']);
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
            $plan->delete();

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

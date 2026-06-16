<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Realization;
use Illuminate\Support\Facades\DB;

class RealizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $token = request()->user()->currentAccessToken();

            $realizations = Realization::with(['plan', 'planDetail'])->where('user_id', $token->tokenable->id)->get();
            return response()->json([
                'success' => true,
                'message' => 'Realizations retrieved successfully',
                'data' => $realizations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve realizations'
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
                'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
                'description' => ['required', 'string'],
                'attachments.*.file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048']
            ]);

            if($validator->fails()){
                throw new \Exception($validator->errors());
            }

            $token = request()->user()->currentAccessToken();

            DB::transaction(function() use ($request, $token){
                $realization = Realization::create([
                    'plan_id' => $request->plan_id,
                    'plan_detail_id' => $request->plan_detail_id,
                    'description' => $request->description,
                    'user_id' => $token->tokenable->id,
                    'progress' => $request->plan_id && $request->plan_detail_id ? $request->progress : 100
                ]);

                foreach($request->file('attachments', []) as $image) {
                    $image->storeAs('attachments', $image->hashName(), 'public');
                    $realization->attachments()->create([
                        'image' => $image->hashName(),
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Realization created successfully'
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
    public function show(Realization $realization)
    {
        try {
            $realization->load(['plan', 'planDetail', 'attachments']);

            return response()->json([
                'success' => true,
                'data' => $realization
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve realization'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Realization $realization)
    {
        try {
            $validator = Validator($request->all(), [
                'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
                'description' => ['required', 'string'],
                'attachments.*.file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048']
            ]);

            if($validator->fails()){
                throw new \Exception($validator->errors());
            }

            DB::transaction(function() use ($request, $realization){
                $realization->update([
                    'plan_id' => $request->plan_id,
                    'plan_detail_id' => $request->plan_detail_id,
                    'description' => $request->description,
                    'progress' => $request->plan_id && $request->plan_detail_id ? $request->progress : 100
                ]);

                if($request->file('attachments') && count($request->file('attachments')) > 0) {
                    if ($realization->attachments()->count() > 0) {
                        foreach ($realization->attachments as $image) {
                            Storage::disk('public')->delete('attachments/'.basename($image->image));
                            $image->delete();
                        }
                    }

                    foreach($request->file('attachments', []) as $image) {
                        $image->storeAs('attachments', $image->hashName(), 'public');
                        $realization->attachments()->create([
                            'image' => $image->hashName(),
                        ]);
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Realization updated successfully'
            ], 200);
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
    public function destroy(Realization $realization)
    {
        try {
            DB::transaction(function () use ($realization) {
                if ($realization->attachments()->count() > 0) {
                    foreach ($realization->attachments as $image) {
                        Storage::disk('public')->delete('attachments/'.basename($image->image));
                        $image->delete();
                    }
                }
                $realization->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Realization deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

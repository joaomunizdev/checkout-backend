<?php

namespace App\Http\Controllers;

use App\Models\Plan;

class PlansController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $plans = Plan::all();

        return response()->json($plans);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $plan = Plan::findOrFail($id);

        return response()->json($plan);
    }
}

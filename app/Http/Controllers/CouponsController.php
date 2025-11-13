<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $coupons = Coupon::all();

        return response()->json($coupons);
    }

    /**
     * Display the specified resource.
     */
    public function showByPlanId(int $id)
    {
        $coupon = Coupon::where('plan_id', '=', $id)->orWhereNull('plan_id')->get();

        return response()->json($coupon);
    }
}

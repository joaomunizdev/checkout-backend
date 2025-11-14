<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\CouponService;
use Carbon\Carbon;
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

    public function validateCoupon(Request $request, CouponService $couponService)
    {
        $validatedData = $request->validate([
            'coupon' => 'required|string|max:255|exists:coupons,name',
            'plan_id' => 'integer|exists:plans,id',
        ]);

        try {
            $couponService->validate($validatedData["coupon"], $validatedData["plan_id"] ?? null);

            return response()->json([
                'valid' => true,
                'message' => 'Valid coupon!',
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'valid' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}

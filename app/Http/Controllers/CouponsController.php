<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Subscription;
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

    public function validateCoupon(Request $request)
    {
        $validatedData = $request->validate([
            'coupon' => 'required|string|max:255|exists:coupons,name',
            'plan_id' => 'integer|exists:plans,id',
        ]);

        $planId = $validatedData['plan_id'] ?? null;

        $couponQuery = Coupon::where('name', $validatedData['coupon']);
        $coupon = $couponQuery->when($planId, function ($query, $planId) {
            $query->where(function ($q) use ($planId) {
                $q->where('plan_id', $planId)
                    ->orWhereNull('plan_id');
            });
        }, function ($query) {
            $query->whereNull('plan_id');
        })->first();

        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid Coupon.'
            ], 422);
        }

        if ($coupon->expiration_days !== null) {
            $expirationDate = $coupon->getAttribute("created_at")->addDays($coupon->expiration_days);

            if (Carbon::now()->isAfter($expirationDate)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Invalid Coupon.'
                ], 422);
            }
        }

        if ($coupon->amount_of_uses) {
            $usageCount = Subscription::where('coupon_id', $coupon->getKey())->count();

            if ($usageCount >= $coupon->amount_of_uses) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Invalid Coupon.'
                ], 422);
            }
        }


        return response()->json([
            'valid' => true,
            'message' => 'Valid coupon!',
        ]);
    }
}

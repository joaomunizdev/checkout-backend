<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Subscription;
use Carbon\Carbon;

class CouponService
{
    public function validate(string $couponName, ?int $planId): Coupon
    {
        $couponQuery = Coupon::where('name', $couponName);

        $coupon = $couponQuery->when($planId, function ($query, $planId) {
            $query->where(function ($q) use ($planId) {
                $q->where('plan_id', $planId)
                    ->orWhereNull('plan_id');
            });
        }, function ($query) {
            $query->whereNull('plan_id');
        })->first();

        if (!$coupon) {
            throw new \Exception('Invalid coupon!');
        }

        if ($coupon->expiration_days !== null) {
            $expirationDate = $coupon->getAttribute('created_at')->addDays($coupon->expiration_days);
            if (Carbon::now()->isAfter($expirationDate)) {
                throw new \Exception('Expired coupon!');
            }
        }

        if ($coupon->amount_of_uses) {
            $usageCount = Subscription::where('coupon_id', $coupon->getKey())->count();
            if ($usageCount >= $coupon->amount_of_uses) {
                throw new \Exception('Coupon usage limit exceeded!');
            }
        }

        return $coupon;
    }
}

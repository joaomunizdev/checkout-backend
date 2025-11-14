<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\CouponService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use OA;

/**
 * @OA\Tag(
 * name="Coupons",
 * description="Endpoints for managing and validating discount coupons"
 * )
 *
 * @OA\Schema(
 * schema="CouponValidationPayload",
 * title="Coupon Validation Payload",
 * description="Payload required to validate a coupon",
 * required={"coupon", "plan_id"},
 * @OA\Property(property="coupon", type="string", description="The coupon code to validate", example="SAVE30"),
 * @OA\Property(property="plan_id", type="integer", description="The ID of the plan this coupon will be applied to", example=1)
 * )
 *
 * @OA\Schema(
 * schema="CouponValidationSuccess",
 * title="Coupon Validation Success",
 * description="Successful response for a valid coupon",
 * @OA\Property(property="valid", type="boolean", example=true),
 * @OA\Property(property="message", type="string", example="Valid coupon!")
 * )
 */
class CouponsController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/coupons",
     * tags={"Coupons"},
     * summary="List all coupons",
     * description="Retrieves a complete list of all available coupons in the system.",
     * @OA\Response(
     * response=200,
     * description="A list of all coupons",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/Coupon")
     * )
     * )
     * )
     */
    public function index()
    {
        $coupons = Coupon::all();

        return response()->json($coupons);
    }

    /**
     * @OA\Get(
     * path="/api/coupons/{id}",
     * tags={"Coupons"},
     * summary="Get coupons applicable to a specific plan",
     * description="Finds coupons that are either specific to the given Plan ID or are global (plan_id = null). Note: The {id} in the path refers to the Plan ID.",
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="The ID of the Plan to check for coupons",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="A list of applicable coupons",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/Coupon")
     * )
     * )
     * )
     */
    public function showByPlanId(int $id)
    {
        $coupon = Coupon::where('plan_id', '=', $id)->orWhereNull('plan_id')->get();

        return response()->json($coupon);
    }

    /**
     * @OA\Post(
     * path="/api/coupons-validate",
     * tags={"Coupons"},
     * summary="Validate a coupon",
     * description="Checks if a coupon is valid based on its code, associated plan, expiration, and usage limits.",
     * @OA\RequestBody(
     * description="Coupon and Plan ID to validate",
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/CouponValidationPayload")
     * ),
     * @OA\Response(
     * response=200,
     * description="Coupon is valid",
     * @OA\JsonContent(ref="#/components/schemas/CouponValidationSuccess")
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation error or coupon is invalid (expired, limit reached, etc.)",
     * @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     * )
     * )
     */
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

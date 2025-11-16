<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OA;
use Exception;

/**
 * @OA\Tag(
 * name="Subscriptions",
 * description="Endpoints for creating and managing subscriptions and payments."
 * )
 *
 *
 *
 * @OA\Schema(
 * schema="CardResponse",
 * title="Card",
 * description="Card details (partial, no sensitive data)",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="client_name", type="string", example="JOAO DA SILVA"),
 * @OA\Property(property="last_4_digits", type="string", example="4123"),
 * @OA\Property(property="expire_date", type="string", format="date", example="2028-12-31"),
 * @OA\Property(property="card_flag_id", type="integer", example=1)
 * )
 *
 *
 *
 * @OA\Schema(
 * schema="SubscriptionWithRelations",
 * title="Subscription",
 * description="Subscription with all related details (plan, coupon, transaction)",
 * allOf={@OA\Schema(ref="#/components/schemas/Subscription")},
 * @OA\Property(property="plan", ref="#/components/schemas/Plan"),
 * @OA\Property(property="coupon", ref="#/components/schemas/Coupon"),
 * @OA\Property(property="transaction", ref="#/components/schemas/Transaction")
 * )
 *
 * @OA\Schema(
 * schema="ErrorResponse",
 * title="Error Response",
 * description="Standard error response for 422",
 * @OA\Property(property="message", type="string", example="Payment declined.")
 * )
 *
 * @OA\Schema(
 * schema="StoreSubscriptionPayload",
 * title="Subscription Creation Payload",
 * description="Full payload for the checkout endpoint.",
 * required={"plan_id", "email"},
 * @OA\Property(property="coupon", type="string", nullable=true, example="OFF10"),
 * @OA\Property(property="plan_id", type="integer", example=1),
 * @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 * )
 */
class SubscriptionsController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/subscriptions",
     * tags={"Subscriptions"},
     * summary="List all subscriptions",
     * description="Returns a list of all subscriptions with their relationships.",
     * @OA\Response(
     * response=200,
     * description="Subscription list",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/SubscriptionWithRelations")
     * )
     * )
     * )
     */
    public function index()
    {
        $subscription = Subscription::with([
            'transaction' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'transaction.card.cardFlag',
            'plan',
            'coupon'
        ])->get();

        return response()->json($subscription);
    }

    /**
     * @OA\Get(
     * path="/api/subscriptions/create",
     * tags={"Subscriptions"},
     * summary="Shows the payload format for creating a signature.",
     * description="Returns a sample JSON showing the fields required for the endpoint. 'store'.",
     * @OA\Response(
     * response=200,
     * description="Payload example",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="payload_format", type="object",
     * @OA\Property(property="coupon", type="string", example="SAVE30"),
     * @OA\Property(property="plan_id", type="integer", example=1),
     * @OA\Property(property="email", type="string", example="email@example.com"),
     * )
     * )
     * )
     * )
     */
    public function create()
    {
        $payload = [
            'coupon' => '(Optional) Coupon code, e.g., "SAVE30"',
            'plan_id' => 'ID (int) of the desired plan',
            'email' => 'email@example.com',
        ];

        return response()->json([
            'payload_format' => $payload
        ]);
    }

    /**
     * @OA\Post(
     * path="/api/subscriptions",
     * tags={"Subscriptions"},
     * summary="Create a new subscription (Checkout)",
     * description="It receives the plan, customer, and card details; validates the coupon; processes the payment; and creates the subscription, card, and transaction.",
     * @OA\Parameter(
     * name="Idempotency-Key",
     * in="header",
     * required=true,
     * description="A unique key (UUID) is used to ensure that the request is processed only once (prevents duplicate charges).",
     * @OA\Schema(
     * type="string",
     * format="uuid",
     * example="a1b2c3d4-5678-90ab-cdef-1234567890ab"
     * )
     * ),
     *
     * @OA\RequestBody(
     * description="Subscription and payment details",
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/StoreSubscriptionPayload")
     * ),
     * @OA\Response(
     * response=201,
     * description="Signature created successfully.",
     * @OA\JsonContent(ref="#/components/schemas/Subscription")
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation error, payment failure, missing Idempotency-Key, duplicate Idempotency-Key validation, and coupon verification.",
     * @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     * )
     * )
     */
    public function store(Request $request, CouponService $couponService)
    {
        $rules = [
            'coupon' => 'nullable|string|max:50|exists:coupons,name',
            'plan_id' => 'required|integer|exists:plans,id',
            'email' => 'required|email|unique:subscriptions,email',
        ];

        $messages = [
            'email.unique' => 'Este e-mail já possui uma assinatura. Por favor, utilize outro e-mail.',
        ];

        $validatedData = $request->validate($rules, $messages);

        DB::beginTransaction();

        try {
            if (!empty($validatedData['coupon'])) {
                $coupon = $couponService->validate(
                    $validatedData['coupon'],
                    $validatedData['plan_id']
                );

                $couponId = $coupon->getKey();
                $validatedData["coupon_id"] = $couponId;
            }

            $subscription = Subscription::create($validatedData);

            DB::commit();

            return response()->json($subscription, 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * @OA\Get(
     * path="/api/subscriptions/{id}",
     * tags={"Subscriptions"},
     * summary="Search for a specific subscription",
     * description="Returns the data for a subscription and its relationships..",
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="Subscription ID",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Subscription details",
     * @OA\JsonContent(ref="#/components/schemas/SubscriptionWithRelations")
     * ),
     * @OA\Response(
     * response=404,
     * description="Signature not found (returns standard Laravel JSON 404)"
     * )
     * )
     */
    public function show(int $id)
    {
        $subscription = Subscription::with([
            'transaction' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'transaction.card.cardFlag',
            'plan',
            'coupon'
        ])->findOrFail($id);

        return response()->json($subscription);
    }
}

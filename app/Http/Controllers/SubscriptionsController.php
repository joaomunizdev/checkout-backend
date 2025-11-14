<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Services\CouponService;
use App\Services\PaymentGatewayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subscription = Subscription::with([
            'transaction' => [
                'card'
            ],
            'plan',
            'coupon'
        ])->get();

        return response()->json($subscription);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $payload = [
            'coupon' => '(Optional) Coupon code, e.g., "SAVE30"',
            'plan_id' => 'ID (int) of the desired plan',
            'email' => 'email@example.com',
            'card_number' => '1111222233334444',
            'client_name' => 'NAME AS IT APPEARS ON THE CARD',
            'expiration_date' => 'MM/YY (e.g., "28/12")',
            'cvc' => '123',
            'card_flag_id' => 'ID (int) of the card brand (e.g., 1 for Visa)',
        ];

        return response()->json([
            'payload_format' => $payload
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, CouponService $couponService, PaymentGatewayService $paymentGatewayService)
    {
        $validatedData = $request->validate([
            'coupon' => 'string|max:50|:coupons,name',
            'plan_id' => 'required|integer|exists:plans,id',
            'email' => 'required|email',
            'card_number' => 'required|numeric|digits_between:12,19',
            'client_name' => 'required|string|max:255',
            'expire_date' => 'required|string|date_format:m/y',
            'cvc' => 'required|numeric|digits_between:3,4',
            'card_flag_id' => 'required|integer|exists:card_flags,id',
        ]);

        DB::beginTransaction();

        try {
            $coupon = null;

            if (!empty($validatedData['coupon'])) {
                $coupon = $couponService->validate(
                    $validatedData['coupon'],
                    $validatedData['plan_id']
                );

                if ($coupon) {
                    $validatedData['coupon_id'] = $coupon->getKey();
                }
            }

            $expireDate = Carbon::createFromFormat('m/y', $validatedData['expire_date'])
                ->endOfMonth()
                ->format('Y-m-d');

            $validatedData['expire_date'] = $expireDate;

            $paymentGatewayService->processPayment(
                $validatedData['card_number'],
            );

            $subscription = Subscription::create($validatedData);

            $card = Card::create($validatedData);

            $validatedData['card_id'] = $card->getKey();
            $validatedData['subscription_id'] = $subscription->getKey();

            Transaction::create($validatedData);

            DB::commit();

            return response()->json($subscription, 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $subscription = Subscription::with([
            'transaction' => [
                'card'
            ],
            'plan',
            'coupon'
        ])->findOrFail($id);

        return response()->json($subscription);
    }
}

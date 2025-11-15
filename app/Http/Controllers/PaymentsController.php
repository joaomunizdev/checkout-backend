<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Services\PaymentGatewayService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OA; // <-- Importado

/**
 * @OA\Tag(
 * name="Payments",
 * description="Endpoints for processing subsequent payments on existing subscriptions"
 * )
 *
 * @OA\Schema(
 * schema="PaymentPayload",
 * title="Payment Payload",
 * description="Payload required to process a new payment",
 * required={"subscription_id", "card_number", "client_name", "expire_date", "cvc", "card_flag_id"},
 * @OA\Property(property="subscription_id", type="integer", description="ID of the subscription being paid for", example=1),
 * @OA\Property(property="card_number", type="string", description="Credit card number (12-19 digits)", example="5555444433332222"),
 * @OA\Property(property="client_name", type="string", description="Name as it appears on the card", example="JOAO DA SILVA"),
 * @OA\Property(property="expire_date", type="string", description="MM/YY", example="12/28"),
 * @OA\Property(property="cvc", type="string", description="CVC/CVV (3-4 digits)", example="123"),
 * @OA\Property(property="card_flag_id", type="integer", example=1)
 * )
 *
 */
class PaymentsController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/payments/create",
     * tags={"Payments"},
     * summary="Shows the payload format for creating a payment",
     * description="Returns an example JSON payload showing the fields required for the 'store' endpoint.",
     * @OA\Response(
     * response=200,
     * description="Example payload format",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="payload_format", type="object",
     * @OA\Property(property="subscription_id", type="string", example="Subscription ID"),
     * @OA\Property(property="card_number", type="string", example="5555444433332222"),
     * @OA\Property(property="client_name", type="string", example="NAME AS IT APPEARS ON THE CARD"),
     * @OA\Property(property="expire_date", type="string", example="12/28"),
     * @OA\Property(property="cvc", type="string", example="123"),
     * @OA\Property(property="card_flag_id", type="integer", example=1)
     * )
     * )
     * )
     * )
     */
    public function create()
    {
        $payload = [
            'subscription_id' => 'Subscription ID',
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
     * @OA\Post(
     * path="/api/payments",
     * tags={"Payments"},
     * summary="Processes a new payment for a subscription",
     * description="Receives payment details, processes it through the simulated gateway, and creates a card and transaction record.",
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
     * @OA\RequestBody(
     * description="Payment details",
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/PaymentPayload")
     * ),
     * @OA\Response(
     * response=201,
     * description="Payment processed successfully, transaction created",
     * @OA\JsonContent(ref="#/components/schemas/Transaction")
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation error or payment denied by gateway",
     * @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     * ),
     * @OA\Response(
     * response=404,
     * description="Subscription not found",
     * @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     * )
     * )
     */
    public function store(Request $request, PaymentGatewayService $paymentGatewayService)
    {
        $validatedData = $request->validate([
            'subscription_id' => 'required|integer|exists:subscriptions,id',
            'card_number' => 'required|numeric|digits_between:12,19',
            'client_name' => 'required|string|max:255',
            'expire_date' => 'required|string|date_format:m/y',
            'cvc' => 'required|numeric|digits_between:3,4',
            'card_flag_id' => 'required|integer|exists:card_flags,id',
        ]);

        DB::beginTransaction();

        try {

            $subscription = Subscription::findOrFail($validatedData['subscription_id']);

            if ($subscription->active) {
                throw new Exception('Esta assinatura já está ativa.');
            }

            $expireDate = Carbon::createFromFormat('m/y', $validatedData['expire_date'])
                ->endOfMonth()
                ->format('Y-m-d');

            $validatedData['expire_date'] = $expireDate;


            $existingCard = Card::where('card_number', '=', $validatedData["card_number"])->first();

            if (!$existingCard) {
                $existingCard = Card::create($validatedData);
            }

            $validatedData['card_id'] = $existingCard->getKey();

            $payment = $paymentGatewayService->processPayment(
                $validatedData['card_number'],
            );

            if ($payment["status"]) {
                $validatedData["status"] = true;
            }

            $subscription->update(['active' => true]);

            $transaction = Transaction::create($validatedData);

            DB::commit();

            return response()->json($transaction, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}

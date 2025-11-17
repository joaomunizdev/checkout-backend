<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Services\PaymentGatewayService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

/**
 * @OA\Tag(
 * name="Payments",
 * description="Endpoints para processar pagamentos subsequentes em assinaturas existentes"
 * )
 *
 * @OA\Schema(
 * schema="PaymentPayload",
 * title="Payment Payload",
 * description="Payload necessário para processar um novo pagamento",
 * required={"subscription_id", "card_number", "client_name", "expire_date", "cvc", "card_flag_id"},
 * @OA\Property(property="subscription_id", type="integer", description="ID da assinatura que está sendo paga", example=1),
 * @OA\Property(property="card_number", type="string", description="Número do cartão de crédito (12-19 dígitos)", example="5555444433332222"),
 * @OA\Property(property="client_name", type="string", description="Nome como aparece no cartão", example="JOAO DA SILVA"),
 * @OA\Property(property="expire_date", type="string", description="MM/AA", example="12/28"),
 * @OA\Property(property="cvc", type="string", description="CVC/CVV (3-4 dígitos)", example="123"),
 * @OA\Property(property="card_flag_id", type="integer", example=1)
 * )
 *
 * @OA\Schema(
 * schema="ValidationErrorResponse",
 * title="Resposta de Erro de Validação",
 * required={"message", "errors"},
 * @OA\Property(property="message", type="string", example="Os dados fornecidos são inválidos."),
 * @OA\Property(
 * property="errors",
 * type="object",
 * description="Objeto onde as chaves são os nomes dos campos e os valores são arrays de mensagens de erro.",
 * @OA\AdditionalProperties(
 * type="array",
 * @OA\Items(type="string")
 * )
 * )
 * )
 */
class PaymentsController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/payments/create",
     * tags={"Payments"},
     * summary="Mostra o formato do payload para criar um pagamento",
     * description="Retorna um payload JSON de exemplo mostrando os campos necessários para o endpoint 'store'.",
     * @OA\Response(
     * response=200,
     * description="Formato do payload de exemplo",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="payload_format", type="object",
     * @OA\Property(property="subscription_id", type="string", example="Subscription ID"),
     * @OA\Property(property="card_number", type="string", example="5555444433332222"),
     * @OA\Property(property="client_name", type="string", example="NOME COMO APARECE NO CARTÃO"),
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
            'client_name' => 'NOME COMO APARECE NO CARTÃO',
            'expiration_date' => 'MM/AA (ex: 12/28)',
            'cvc' => '123',
            'card_flag_id' => 'ID (int) da bandeira (ex: 1 para Visa)',
        ];

        return response()->json([
            'payload_format' => $payload
        ]);
    }

    /**
     * @OA\Post(
     * path="/api/payments",
     * tags={"Payments"},
     * summary="Processa um novo pagamento para uma assinatura",
     * description="Recebe os detalhes do pagamento, processa através do gateway simulado e cria um registro de cartão e transação.",
     * @OA\Parameter(
     * name="Idempotency-Key",
     * in="header",
     * required=true,
     * description="Uma chave única (UUID) usada para garantir que a requisição seja processada apenas uma vez.",
     * @OA\Schema(type="string", format="uuid", example="a1b2c3d4-5678-90ab-cdef-1234567890ab")
     * ),
     * @OA\RequestBody(
     * description="Detalhes do pagamento",
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/PaymentPayload")
     * ),
     * @OA\Response(
     * response=201,
     * description="Pagamento processado com sucesso, transação criada",
     * @OA\JsonContent(ref="#/components/schemas/Transaction")
     * ),
     * @OA\Response(
     * response=422,
     * description="Erro de validação (Exemplo 1) ou Erro de processamento/pagamento negado (Exemplo 2).",
     * @OA\JsonContent(
     * oneOf={
     * @OA\Schema(ref="#/components/schemas/ValidationErrorResponse"),
     * @OA\Schema(ref="#/components/schemas/SimpleErrorResponse")
     * },
     * @OA\Examples(
     * example="validationError",
     * summary="Erro de Validação (Laravel $validate)",
     * value={
     * "message": "Os dados fornecidos são inválidos.",
     * "errors": {
     * "card_number": {"O campo card number deve ter entre 12 e 19 dígitos."},
     * "expire_date": {"O campo expire date não corresponde ao formato m/y."}
     * }
     * }
     * ),
     * @OA\Examples(
     * example="processingError",
     * summary="Erro de Processamento",
     * value={
     * "message": "Esta assinatura já está ativa."
     * }
     * )
     * )
     * ),
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
            $validatedData["last_4_digits"] = substr($validatedData["card_number"], -4);

            $existingCard = Card::where('card_number', '=', $validatedData["card_number"])->first();

            if (!$existingCard) {
                $existingCard = Card::create($validatedData);
            }

            $validatedData['card_id'] = $existingCard->getKey();

            $payment = $paymentGatewayService->processPayment(
                $validatedData['card_number'],
            );

            $validatedData['status'] = $payment['status'];

            if ($payment["status"]) {
                $subscription->update(['active' => true]);
            }

            $plan = Plan::findOrFail($subscription->getAttribute('plan_id'));
            $planPrice = $plan->getAttribute('price');
            $pricePaid = $planPrice;

            if ($subscription->getAttribute('coupon_id')) {
                $coupon = Coupon::findOrFail($subscription->getAttribute('coupon_id'));

                $discount = 0;

                if ($coupon->discount_percent) {
                    $discount = $plan->price * $coupon->discount_percent / 100;
                } elseif ($coupon->discount_amount) {
                    $discount = min($coupon->discount_amount, $plan->price);
                }

                $pricePaid = $planPrice - $discount;
            }

            $validatedData["price_paid"] = $pricePaid;

            $transaction = Transaction::create($validatedData);

            DB::commit();

            return response()->json($transaction, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}

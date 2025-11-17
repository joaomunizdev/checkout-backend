<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;
use Exception;

/**
 * @OA\Tag(
 * name="Subscriptions",
 * description="Endpoints para criar e gerenciar assinaturas."
 * )
 *
 * @OA\Schema(
 * schema="SubscriptionWithRelations",
 * title="Assinatura com Relações",
 * description="Assinatura com todos os detalhes relacionados (plano, cupom, transação)",
 * allOf={@OA\Schema(ref="#/components/schemas/Subscription")},
 * @OA\Property(property="plan", ref="#/components/schemas/Plan"),
 * @OA\Property(property="coupon", ref="#/components/schemas/Coupon", nullable=true),
 * @OA\Property(
 * property="transaction",
 * type="array",
 * @OA\Items(ref="#/components/schemas/Transaction")
 * )
 * )
 *
 * @OA\Schema(
 * schema="SimpleErrorResponse",
 * title="Resposta de Erro Simples",
 * description="Resposta de erro simples",
 * @OA\Property(property="message", type="string", example="Mensagem de erro.")
 * )
 *

 * @OA\Schema(
 * schema="StoreSubscriptionPayload",
 * title="Payload de Criação de Assinatura",
 * description="Payload completo para o endpoint de checkout.",
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
     * summary="Listar todas as assinaturas",
     * description="Retorna uma lista de todas as assinaturas com seus relacionamentos.",
     * @OA\Response(
     * response=200,
     * description="Lista de assinaturas",
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
     * summary="Mostra o formato do payload para criar uma assinatura.",
     * description="Retorna um JSON de exemplo mostrando os campos necessários para o endpoint 'store'.",
     * @OA\Response(
     * response=200,
     * description="Exemplo de payload",
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
            'coupon' => '(Opcional) Código do cupom, ex: "SAVE30"',
            'plan_id' => 'ID (int) do plano desejado',
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
     * summary="Criar uma nova assinatura (Checkout - Passo 1)",
     * description="Recebe o plano, cliente e cupom. Valida o cupom e cria a assinatura inicial (ainda inativa).",
     * @OA\Parameter(
     * name="Idempotency-Key",
     * in="header",
     * required=true,
     * description="Uma chave única (UUID) usada para garantir que a requisição seja processada apenas uma vez.",
     * @OA\Schema(
     * type="string",
     * format="uuid",
     * example="a1b2c3d4-5678-90ab-cdef-1234567890ab"
     * )
     * ),
     *
     * @OA\RequestBody(
     * description="Detalhes da assinatura",
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/StoreSubscriptionPayload")
     * ),
     * @OA\Response(
     * response=201,
     * description="Assinatura criada com sucesso.",
     * @OA\JsonContent(ref="#/components/schemas/Subscription")
     * ),
     * @OA\Response(
     * response=422,
     * description="Erro de validação (Ex: email duplicado) ou erro de regra (Ex: cupom inválido).",
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
     * "email": {"Este e-mail já possui uma assinatura. Por favor, utilize outro e-mail."}
     * }
     * }
     * ),
     * @OA\Examples(
     * example="couponError",
     * summary="Erro de Regra (Serviço/Exception)",
     * value={
     * "message": "Este cupom já expirou."
     * }
     * )
     * )
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
     * summary="Buscar uma assinatura específica",
     * description="Retorna os dados de uma assinatura e seus relacionamentos.",
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="ID da Assinatura",
     * @OA\Schema(type="integer", example=1)
     * ),
     * @OA\Response(
     * response=200,
     * description="Detalhes da assinatura",
     * @OA\JsonContent(ref="#/components/schemas/SubscriptionWithRelations")
     * ),
     * @OA\Response(
     * response=404,
     * description="Assinatura não encontrada.",
     * @OA\JsonContent(ref="#/components/schemas/SimpleErrorResponse")
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

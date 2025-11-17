<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * @OA\Tag(
 * name="Coupons",
 * description="Endpoints para gerenciar e validar cupons de desconto"
 * )
 *
 * @OA\Schema(
 * schema="CouponValidationPayload",
 * title="Coupon Validation Payload",
 * description="Payload necessário para validar um cupom",
 * required={"coupon"},
 * @OA\Property(property="coupon", type="string", description="O código do cupom para validar", example="OFF10"),
 * @OA\Property(property="plan_id", type="integer", description="O ID do plano ao qual este cupom será aplicado", example=1)
 * )
 *
 * @OA\Schema(
 * schema="CouponValidationSuccess",
 * title="Sucesso na Validação do Cupom",
 * description="Resposta de sucesso para um cupom válido",
 * @OA\Property(property="valid", type="boolean", example=true),
 * @OA\Property(property="message", type="string", example="Valid Coupon!")
 * )
 *
 * @OA\Schema(
 * schema="CouponValidationFailure",
 * title="Falha na Validação do Cupom",
 * description="Resposta de falha para um cupom inválido (regra de negócio)",
 * @OA\Property(property="valid", type="boolean", example=false),
 * @OA\Property(property="message", type="string", example="Expired coupon.")
 * )
 *
 * )
 */
class CouponsController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/coupons",
     * tags={"Coupons"},
     * summary="Listar todos os cupons",
     * description="Recupera uma lista completa de todos os cupons disponíveis no sistema.",
     * @OA\Response(
     * response=200,
     * description="Uma lista de todos os cupons",
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
     * path="/api/coupons/{value}",
     * tags={"Coupons"},
     * summary="Obter cupons pelo nome",
     * description="Encontra cupom pelo nome",
     * @OA\Parameter(
     * name="value",
     * in="path",
     * required=true,
     * description="O nome do cupom para verificar dados do cupom",
     * @OA\Schema(type="string", example="OFF10")
     * ),
     * @OA\Response(
     * response=200,
     * description="Detalhes do cupom",
     * @OA\JsonContent(ref="#/components/schemas/Coupon")
     * ),
     * )
     * )
     */
    public function showByPlanId(string $value)
    {
        $coupon = Coupon::where('name', 'like', $value)->get();

        return response()->json($coupon);
    }

    /**
     * @OA\Post(
     * path="/api/coupons-validate",
     * tags={"Coupons"},
     * summary="Validar um cupom",
     * description="Verifica se um cupom é válido com base em seu código, plano associado, expiração e limites de uso.",
     * @OA\RequestBody(
     * description="Cupom e ID do Plano para validar",
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/CouponValidationPayload")
     * ),
     * @OA\Response(
     * response=200,
     * description="Cupom é válido",
     * @OA\JsonContent(ref="#/components/schemas/CouponValidationSuccess")
     * ),
     * @OA\Response(
     * response=422,
     * description="Erro de validação (Ex: cupom não existe) ou cupom inválido (Ex: expirado).",
     * @OA\JsonContent(
     * oneOf={
     * @OA\Schema(ref="#/components/schemas/ValidationErrorResponse"),
     * @OA\Schema(ref="#/components/schemas/CouponValidationFailure")
     * },
     * @OA\Examples(
     * example="validationError",
     * summary="Erro de Validação (Laravel $validate)",
     * value={
     * "message": "The data provided is invalid.",
     * "errors": {
     * "coupon": {"Invalid coupon!"}
     * }
     * }
     * ),
     * @OA\Examples(
     * example="couponInvalid",
     * summary="Erro de Regra (Serviço)",
     * value={
     * "valid": false,
     * "message": "Expired coupon."
     * }
     * )
     * )
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

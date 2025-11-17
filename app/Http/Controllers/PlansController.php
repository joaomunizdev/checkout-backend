<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use OpenApi\Attributes as OA;

/**
 * @OA\Tag(
 * name="Plans",
 * description="Endpoints para buscar planos de assinatura"
 * )
 *
 */
class PlansController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/plans",
     * tags={"Plans"},
     * summary="Listar todos os planos",
     * description="Recupera uma lista completa de todos os planos de assinatura disponíveis.",
     * @OA\Response(
     * response=200,
     * description="Uma lista de planos",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/Plan")
     * )
     * )
     * )
     */
    public function index()
    {
        $plans = Plan::all();

        return response()->json($plans);
    }

    /**
     * @OA\Get(
     * path="/api/plans/{id}",
     * tags={"Plans"},
     * summary="Obter um plano específico por ID",
     * description="Recupera os detalhes de um único plano por seu ID.",
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="O ID do plano",
     * @OA\Schema(type="integer", example=1)
     * ),
     * @OA\Response(
     * response=200,
     * description="Detalhes do plano",
     * @OA\JsonContent(ref="#/components/schemas/Plan")
     * ),
     * @OA\Response(
     * response=404,
     * description="Plano não encontrado.",
     * @OA\JsonContent(ref="#/components/schemas/SimpleErrorResponse")
     * )
     * )
     */
    public function show(string $id)
    {
        $plan = Plan::findOrFail($id);

        return response()->json($plan);
    }
}

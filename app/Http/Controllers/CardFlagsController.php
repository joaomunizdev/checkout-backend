<?php

namespace App\Http\Controllers;

use App\Models\CardFlag;
use OpenApi\Attributes as OA;

/**
 * @OA\Tag(
 * name="CardFlags",
 * description="Endpoints para buscar bandeiras de cartão (ex: Visa, Mastercard)"
 * )
 *
 */
class CardFlagsController extends Controller
{

    /**
     * @OA\Get(
     * path="/api/card-flags",
     * tags={"CardFlags"},
     * summary="Listar todas as bandeiras de cartão",
     * description="Recupera uma lista completa de todas as bandeiras de cartão disponíveis.",
     * @OA\Response(
     * response=200,
     * description="Uma lista de bandeiras de cartão",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/CardFlag")
     * )
     * )
     * )
     */
    public function index()
    {
        $cardFlags = CardFlag::all();

        return response()->json($cardFlags);
    }

    /**
     * @OA\Get(
     * path="/api/card-flags/{id}",
     * tags={"CardFlags"},
     * summary="Obter uma bandeira de cartão específica por ID",
     * description="Recupera os detalhes de uma única bandeira de cartão pelo seu ID.",
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="O ID da bandeira do cartão",
     * @OA\Schema(type="integer", example=1)
     * ),
     * @OA\Response(
     * response=200,
     * description="Detalhes da bandeira do cartão",
     * @OA\JsonContent(ref="#/components/schemas/CardFlag")
     * ),
     * @OA\Response(
     * response=404,
     * description="Bandeira do cartão não encontrada.",
     * @OA\JsonContent(ref="#/components/schemas/SimpleErrorResponse")
     * )
     * )
     */
    public function show(string $id)
    {
        $cardFlag = CardFlag::findOrFail($id);

        return response()->json($cardFlag);
    }
}

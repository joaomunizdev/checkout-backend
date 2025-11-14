<?php

namespace App\Http\Controllers;

use App\Models\CardFlag;
use OA;

/**
 * @OA\Tag(
 * name="CardFlags",
 * description="Endpoints for retrieving card brands (e.g., Visa, Mastercard)"
 * )
 */
class CardFlagsController extends Controller
{

    /**
     * @OA\Get(
     * path="/api/card-flags",
     * tags={"CardFlags"},
     * summary="List all card brands",
     * description="Retrieves a complete list of all available card brands.",
     * @OA\Response(
     * response=200,
     * description="A list of card brands",
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
     * summary="Get a specific card brand by ID",
     * description="Retrieves the details of a single card brand by its unique ID.",
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="The ID of the card brand",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="The card brand details",
     * @OA\JsonContent(ref="#/components/schemas/CardFlag")
     * ),
     * @OA\Response(
     * response=404,
     * description="Signature not found (returns standard Laravel JSON 404)"
     * )
     * )
     */
    public function show(string $id)
    {
        $cardFlag = CardFlag::findOrFail($id);

        return response()->json($cardFlag);
    }
}

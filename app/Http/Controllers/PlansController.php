<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use OA;

/**
 * @OA\Tag(
 * name="Plans",
 * description="Endpoints for retrieving subscription plans"
 * )
 *
 */
class PlansController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/plans",
     * tags={"Plans"},
     * summary="List all plans",
     * description="Retrieves a complete list of all available subscription plans.",
     * @OA\Response(
     * response=200,
     * description="A list of plans",
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
     * summary="Get a specific plan by ID",
     * description="Retrieves the details of a single plan by its unique ID.",
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="The ID of the plan",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="The plan details",
     * @OA\JsonContent(ref="#/components/schemas/Plan")
     * ),
     * @OA\Response(
     * response=404,
     * description="Signature not found (returns standard Laravel JSON 404)"
     * )
     * )
     */
    public function show(string $id)
    {
        $plan = Plan::findOrFail($id);

        return response()->json($plan);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CardFlag;

class CardFlagsController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cardFlags = CardFlag::all();

        return response()->json($cardFlags);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cardFlag = CardFlag::findOrFail($id);

        return response()->json($cardFlag);
    }
}

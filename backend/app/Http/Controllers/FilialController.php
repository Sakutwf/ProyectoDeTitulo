<?php

namespace App\Http\Controllers;

use App\Models\Filial;

class FilialController extends Controller
{
    public function index()
    {
        return response()->json(
            Filial::query()->orderBy('nombre')->get(),
            200
        );
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AvisoResource;
use App\Models\Aviso;

class AvisoController extends Controller
{
    public function index()
    {
        $avisos = Aviso::whereNotNull('publicado_em')
            ->where('publicado_em', '<=', now())
            ->latest('publicado_em')
            ->get();

        return AvisoResource::collection($avisos);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Universite;
use App\Support\ApiData;
use Illuminate\Http\JsonResponse;

class UniversiteController extends Controller
{
    public function index(): JsonResponse
    {
        $universites = Universite::where('actif', true)
            ->orderBy('ordre')
            ->get();

        return response()->json([
            'data' => $universites->map(fn (Universite $universite) => ApiData::universite($universite))->values(),
        ]);
    }
}

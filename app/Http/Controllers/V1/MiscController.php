<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Facultad;
use App\Models\Carrera;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MiscController extends Controller
{

    public function getFacultades(Request $request)
    {
        // $facultades = Facultad::with('carrera')->get();
        // $facultades = Facultad::all();
        
        $facultades = Facultad::with('carreras')->get();
        // $facultades = Facultad::all();
        // dd($facultades);

        return response()->json($facultades);
    }

    public function getCarreras(Request $request){
        $carreras = Carrera::with('facultad')->get();
        return response()->json($carreras);
    }
}
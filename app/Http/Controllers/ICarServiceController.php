<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ICarServiceController extends Controller
{

    public function index(Request $request)
    {

        return response()->json([
            'message' =>
            'ICarService up and running'
        ]);
    }

    public function testAuth()
    {
        return response()->json([
            'user' =>
            auth()->user()->name
        ]);
    }

    public function spisakKomisija()
    {
        return collect(DB::select("SELECT ImePrezime, BrojMagacina, BrojDokumenta from Komisija"));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

    public function getIdent(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'dokument' => 'required|integer',
            'ident' => 'required',
            'magacin' => 'required|integer',
        ]);
        if ($validator->fails()) {
            // return response()->json(["errors" => implode(" ", $validator->errors()->all())], 422);
            return response()->json(implode(" ", $validator->errors()->all()), 422);
        }
        $idents =  collect(DB::select(
            "SELECT ltrim(rtrim(cast(ta.NumInterno as char))) as idArtikla, ta.Referencia as katBroj, ta.Descrip as opis,isnull(taa.Ubicacion1,'') as lokacija,
        ltrim(rtrim(cast(taa.CdadStock as char))) as stanjeKnjige, ltrim(rtrim(cast(isnull(q.unesenaKolicina,0) as char))) as unesenaKolicina , 
        ltrim(rtrim(cast(taa.CdadStock  - isnull(q.unesenaKolicina,0) as char)))  as novaKolicina
        FROM taArticulo ta 
        INNER JOIN taArticuloAlma taa ON ta.NumInterno = taa.NumInterno 
        outer apply (select idArtikla, sum(Stanje) as unesenaKolicina from Popis2012Test p where 1=1 and p.IdArtikla = ta.NumInterno 
        and Dokument = ?	 group by IdArtikla)q
        WHERE ta.Referencia LIKE RTRIM(?)+'%' AND taa.Almacen = cast(? as int)  AND taa.Emp = '001' 
        ORDER BY len(ta.Referencia),lokacija desc, ta.Referencia,CdadStock DESC, ta.Marca DESC",
            [$request->input('dokument'), $request->input('ident'), $request->input('magacin')]
        ));
        return response()->json($idents);
    }
}

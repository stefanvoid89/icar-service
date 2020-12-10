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
        case when isnull(q.unesenaKolicina,0) >= taa.CdadStock then '0' else ltrim(rtrim(cast(taa.CdadStock  - isnull(q.unesenaKolicina,0) as char))) end  as novaKolicina,marca
        FROM taArticulo ta 
        INNER JOIN taArticuloAlma taa ON ta.NumInterno = taa.NumInterno 
        outer apply (select idArtikla, sum(Stanje) as unesenaKolicina from Popis2012Test p where 1=1 and p.IdArtikla = ta.NumInterno 
        and Dokument = ?	 group by IdArtikla)q
        WHERE ta.Referencia = TRIM(?) AND taa.Almacen = cast(? as int)  AND taa.Emp = '001' 
        ORDER BY len(ta.Referencia),lokacija desc, ta.Referencia,CdadStock DESC, ta.Marca DESC",
            [$request->input('dokument'), $request->input('ident'), $request->input('magacin')]
        ));
        return response()->json($idents);
    }


    public function storeItem(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'katBroj' => 'required',
            //    'marca' => 'required',
            //    'opis' => 'required',
            //    'lokacija' => 'required',
            'stanjeKnjige' => 'required',
            'novoStanje' => 'required',
            'popisivac' => 'required',
            'dokument' => 'required|integer',
        ]);
        if ($validator->fails()) {
            // return response()->json(["errors" => implode(" ", $validator->errors()->all())], 422);
            return response()->json(implode(" ", $validator->errors()->all()), 422);
        }

        try {
            DB::insert(
                "INSERT INTO Popis2012Test (IdArtikla, KatBroj, Marka, Opis, Lokacija, StanjeKnjige, Stanje, Popisivac, Dokument ,Datum) 
            VALUES (?,?,?,?,?,?,?,?,?,getdate())",
                [
                    $request->input('id'), $request->input('katBroj'), $request->input('marca'), $request->input('opis'), $request->input('lokacija'),
                    $request->input('stanjeKnjige'), $request->input('novoStanje'), $request->input('popisivac'), $request->input('dokument')
                ]
            );
        } catch (\Exception $ex) {
            return response()->json($ex->getMessage(), 500);
        }
        return response()->json("Success");
    }


    public function getListItems(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'dokument' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json(implode(" ", $validator->errors()->all()), 422);
        }
        $items =  collect(DB::select(
            "SELECT IdTabele as idTabela,ltrim(rtrim(cast(IdArtikla as char)))  as idArtikla  ,katBroj,opis ,lokacija 
            ,'' as stanjeKnjige ,ltrim(rtrim(cast(stanje as char)))  as unesenaKolicina ,'' as novaKolicina ,Marka as marca
            FROM ICARDMS.dbo.Popis2012Test where Dokument	 = ?     
            order by katBroj,marca desc",
            [$request->input('dokument')]
        ));
        return response()->json($items);
    }

    public function deleteItem(Request $request, $id)
    {
        DB::delete("DELETE from ICARDMS.dbo.Popis2012Test where idTabele = ?", [$id]);
        return response()->json($id);
    }
}

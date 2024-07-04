<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ConsultaDocenteRequest;
use Uspdev\Replicado\DB;

class DocenteController extends Controller
{
    public function consulta(ConsultaDocenteRequest $request)
    {
        $validated = $request->validated();
        
        if($request->expectsJson()){
            if(array_key_exists('codpes', $validated)){
                $codpes = $validated['codpes'];

                $query = " SELECT P.codpes, P.nompes";
                $query .= " FROM PESSOA AS P, VINCULOPESSOAUSP AS VP";
                $query .= " WHERE P.codpes = :codpes";
                $query .= " AND VP.codpes = :codpes";
                $query .= " AND VP.tipfnc = :tipfnc";
                $param = [
                    'codpes' => $codpes,
                    'tipfnc' => 'Docente',
                ];
        
                $docentes = array_unique(DB::fetchAll($query, $param),SORT_REGULAR);

                if($docentes){
                    return response()->json($docentes[0]["nompes"]);
                }
            }elseif(array_key_exists('nompes', $validated)){
                $nompes = $validated['nompes'];
                
                $query = " SELECT P.codpes, P.nompes";
                $query .= " FROM PESSOA AS P, VINCULOPESSOAUSP AS VP";
                $query .= " WHERE P.nompes LIKE :nompes";
                $query .= " AND VP.codpes = P.codpes";
                $query .= " AND VP.tipfnc = :tipfnc";
                $param = [
                    'nompes' => "%".$nompes."%",
                    'tipfnc' => 'Docente',
                ];

                $res = array_unique(DB::fetchAll($query, $param),SORT_REGULAR);
                if($docentes){
                    return response()->json($docentes);
                }
            }

            return response()->json("");
        }
    }
}

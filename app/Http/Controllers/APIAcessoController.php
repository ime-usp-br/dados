<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Uspdev\Replicado\DB;

class APIAcessoController extends Controller
{
    public function individual(Request $request)
    {
        $token = $request->header('Authorization');
        if($token != "Bearer ".env("API_ACESSO_TOKEN")){
            return response()->json([
                "status"=>"403",
                "message"=>"Token não confere!"
            ]);
        }

        if(!$request->has("codpes")){
            return response()->json([
                "status"=>400,
                "message"=>"Não foi encontrado o codpes."
            ]);
        }

        $codpes = $request->get("codpes");

        $query = " select P.nompes,  CC.numserchi, CC.tipvinaux";
        $query .= " from PESSOA as P, CATR_CRACHA as CC";
        $query .= " where codpes = :codpes";
        $query .= " and codpescra = :codpes";
        $param = [
            'codpes' => $codpes,
        ];

        $respostas = DB::fetchAll($query,$param);

        return response()->json([
            "status"=>200,
            "message"=>$respostas
        ]);
    }

    public function loteGrad(Request $request)
    {
        $token = $request->header('Authorization');
        if($token != "Bearer ".env("API_ACESSO_TOKEN")){
            return response()->json([
                "status"=>"403",
                "message"=>"Token não confere!"
            ]);
        }

        if(!$request->has("codcur")){
            return response()->json([
                "status"=>400,
                "message"=>"Não foi encontrado o codcur."
            ]);
        }

        if(!$request->has("anoing")){
            return response()->json([
                "status"=>400,
                "message"=>"Não foi encontrado o anoing."
            ]);
        }

        $codcur = $request->get("codcur");
        $anoing = $request->get("anoing");

        $query = " select distinct VP.codpes, CC.numserchi";
        $query .= " from PROGRAMAGR as PGR, HABILPROGGR as HGR, CARTAOUSPSOLICITACAO as CUSP, CATR_CRACHA as CC, VINCULOPESSOAUSP as VP";
        $query .= " where HGR.codcur = :codcur";
        $query .= " and PGR.codpes = HGR.codpes";
        $query .= " and YEAR(PGR.dtaing) = :anoing";
        $query .= " and CUSP.codpes = PGR.codpes";
        $query .= " and CUSP.numserchi is not null";
        $query .= " and CC.numserchi = CUSP.numserchi";
        $query .= " and VP.codpes = PGR.codpes";
        $query .= " and VP.sitatl = 'A'";
        $param = [
            'codcur' => $codcur,
            'anoing' => $anoing,
        ];

        $respostas = DB::fetchAll($query,$param);

        return response()->json([
            "status"=>200,
            "message"=>$respostas
        ]);
    }

    public function lotePos(Request $request)
    {
        $token = $request->header('Authorization');
        if($token != "Bearer ".env("API_ACESSO_TOKEN")){
            return response()->json([
                "status"=>"403",
                "message"=>"Token não confere!"
            ]);
        }

        if(!$request->has("codare")){
            return response()->json([
                "status"=>400,
                "message"=>"Não foi encontrado o codare."
            ]);
        }

        $codare = $request->get("codare");

        $query = " select distinct VP.codpes, CC.numserchi";
        $query .= " from VINCULOPESSOAUSP as VP, CATR_CRACHA as CC, AGPROGRAMA as AGP";
        $query .= " where VP.sitatl = 'A'";
        $query .= " and VP.codclg = '45'";
        $query .= " and VP.tipvin = 'ALUNOPOS'";
        $query .= " and CC.codpescra = VP.codpes";
        $query .= " and AGP.codpes = VP.codpes";
        $query .= " and AGP.codare = :codare";
        $param = [
            'codare' => $codare,
        ];

        $respostas = DB::fetchAll($query,$param);

        return response()->json([
            "status"=>200,
            "message"=>$respostas
        ]);
    }

    public function loteDoc(Request $request)
    {
        $token = $request->header('Authorization');
        if($token != "Bearer ".env("API_ACESSO_TOKEN")){
            return response()->json([
                "status"=>"403",
                "message"=>"Token não confere!"
            ]);
        }

        if(!$request->has("codset")){
            return response()->json([
                "status"=>400,
                "message"=>"Não foi encontrado o codset."
            ]);
        }

        $codset = $request->get("codset");

        $query = " select distinct VP.codpes, CC.numserchi";
        $query .= " from VINCULOPESSOAUSP as VP, CARTAOUSPSOLICITACAO as CUSP, CATR_CRACHA as CC";
        $query .= " where VP.codund = 45";
        $query .= " and VP.tipvin = 'SERVIDOR'";
        $query .= " and VP.tipfnc = 'Docente'";
        $query .= " and VP.codset = :codset";
        $query .= " and VP.sitatl != 'D'";
        $query .= " and CUSP.codpes = VP.codpes";
        $query .= " and CUSP.numserchi is not null";
        $query .= " and CC.numserchi = CUSP.numserchi";
        $param = [
            'codset' => $codset,
        ];

        $respostas = DB::fetchAll($query,$param);

        return response()->json([
            "status"=>200,
            "message"=>$respostas
        ]);
    }

    public function loteFunc(Request $request)
    {
        $token = $request->header('Authorization');
        if($token != "Bearer ".env("API_ACESSO_TOKEN")){
            return response()->json([
                "status"=>"403",
                "message"=>"Token não confere!"
            ]);
        }

        $query = " select distinct VP.codpes, CC.numserchi";
        $query .= " from VINCULOPESSOAUSP as VP, CARTAOUSPSOLICITACAO as CUSP, CATR_CRACHA as CC";
        $query .= " where VP.codund = 45";
        $query .= " and VP.tipvin = 'SERVIDOR'";
        $query .= " and VP.sitatl != 'D'";
        $query .= " and VP.tipfnc != :tipfnc";
        $query .= " and CUSP.codpes = VP.codpes";
        $query .= " and CUSP.numserchi is not null";
        $query .= " and CC.numserchi = CUSP.numserchi";
        $param = [
            'tipfnc' => 'Docente',
        ];

        $respostas = DB::fetchAll($query,$param);

        return response()->json([
            "status"=>200,
            "message"=>$respostas
        ]);
    }

    public function ativo(Request $request)
    {
        $token = $request->header('Authorization');
        if($token != "Bearer ".env("API_ACESSO_TOKEN")){
            return response()->json([
                "status"=>"403",
                "message"=>"Token não confere!"
            ]);
        }

        if(!$request->has("numserchi")){
            return response()->json([
                "status"=>400,
                "message"=>"Não foi encontrado o numserchi."
            ]);
        }

        $numserchi = $request->get("numserchi");

        $query = " select CC.sitpescra";
        $query .= " from CATR_CRACHA as CC";
        $query .= " where CC.numserchi = :numserchi";
        $param = [
            'numserchi' => $numserchi,
        ];

        $respostas = DB::fetchAll($query,$param);

        $resposta = false;
        foreach($respostas as $r){            
            foreach($r as $k=>$a){
                if($k=="sitpescra" and $a=="A"){
                    $resposta = true;;
                }
            }
        }

        return response()->json([
            "status"=>200,
            "message"=>$resposta
        ]);

    }
}

<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests\CargaDidaticaRequest;
use Illuminate\Support\Facades\Auth;
use Uspdev\Replicado\DB;

class RelatoriosController extends Controller
{
    public function cargaDidatica(CargaDidaticaRequest $request)
    {
        if(!Auth::check()){
            return redirect(route("login"));
        }

        $validated = $request->validated();
        
        if(isset($validated["departamento"]) and isset($validated["ano"]) and isset($validated["semestre"])){
            $codigoSetores = ["MAC"=>1664, "MAE"=>1665, "MAP"=>1666, "MAT"=>1667];
            $query = " select distinct P.nompes, V.codpes, M.coddis, D.nomdis, M.codtur, M.dtainiaul, M.dtafimaul, D.creaul, D.cretrb, (T.nummtr+T.nummtrturcpl+T.nummtropt+T.nummtrecr) as nummtr,M.diasmnocp, H.horent, H.horsai";
            $query .= " from VINCULOPESSOAUSP as V, PESSOA as P, MINISTRANTE as M, DISCIPLINAGR as D, TURMAGR as T, PERIODOHORARIO as H";
            $query .= " where V.codund = :codund";
            $query .= " and V.codset = :codset";
            $query .= " and V.tipfnc = 'Docente'";
            $query .= " and P.codpes = V.codpes";
            $query .= " and M.codpes = V.codpes";
            $query .= " and M.codtur like :codtur";
            $query .= " and D.coddis = M.coddis";
            $query .= " and D.verdis = M.verdis";
            $query .= " and T.coddis = M.coddis";
            $query .= " and T.verdis = M.verdis";
            $query .= " and T.codtur = M.codtur";
            $query .= " and H.codperhor = M.codperhor";
            $query .= " order by nompes, coddis, codtur asc";
            $param = [
                'codund' => '45',
                'codtur' => $validated["ano"].$validated["semestre"]."%",
                'codset' => $codigoSetores[$validated["departamento"]],
            ];

            $dados = array();
            $respostas = DB::fetchAll($query, $param);
            foreach($respostas as $resposta){
                $dados[$resposta["codpes"]]["nompes"] = $resposta["nompes"];
                $dados[$resposta["codpes"]]["disciplinas"][$resposta["coddis"]]["nomdis"] = $resposta["nomdis"];
                $dados[$resposta["codpes"]]["disciplinas"][$resposta["coddis"]]["nivel"] = "Graduação";
                $dados[$resposta["codpes"]]["disciplinas"][$resposta["coddis"]]["creaul"] = $resposta["creaul"];
                $dados[$resposta["codpes"]]["disciplinas"][$resposta["coddis"]]["cretrb"] = $resposta["cretrb"];
                $dados[$resposta["codpes"]]["disciplinas"][$resposta["coddis"]]["dtainiaul"] = explode(" ",$resposta["dtainiaul"])[0];
                $dados[$resposta["codpes"]]["disciplinas"][$resposta["coddis"]]["dtafimaul"] = explode(" ",$resposta["dtafimaul"])[0];
                $dados[$resposta["codpes"]]["disciplinas"][$resposta["coddis"]]["turmas"][$resposta["codtur"]]["nummtr"] = $resposta["nummtr"];
                $dados[$resposta["codpes"]]["disciplinas"][$resposta["coddis"]]["turmas"][$resposta["codtur"]]["horarios"][] = [
                    "diasmnocp"=>$resposta["diasmnocp"],
                    "horent"=>$resposta["horent"],
                    "horsai"=>$resposta["horsai"],
                ];
            }

            $departamento = $validated["departamento"];
            $ano = $validated["ano"];
            $semestre = $validated["semestre"];

            return view("relatorios.cargaDidatica", compact([
                "dados",
                "departamento",
                "ano",
                "semestre"
            ]));
        }

        return view("relatorios.createCargaDidatica");
    }
}

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

            $dadosGraduação = array();
            $respostas = DB::fetchAll($query, $param);
            foreach($respostas as $resposta){
                $dadosGraduação[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["nomdis"] = $resposta["nomdis"];
                $dadosGraduação[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["nivel"] = "Graduação";
                $dadosGraduação[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["creaul"] = $resposta["creaul"];
                $dadosGraduação[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["cretrb"] = $resposta["cretrb"];
                $dadosGraduação[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["dtainiaul"] = explode(" ",$resposta["dtainiaul"])[0];
                $dadosGraduação[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["dtafimaul"] = explode(" ",$resposta["dtafimaul"])[0];
                $dadosGraduação[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["turmas"][$resposta["codtur"]]["nummtr"] = $resposta["nummtr"];
                $dadosGraduação[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["turmas"][$resposta["codtur"]]["horarios"][] = [
                    "diasmnocp"=>$resposta["diasmnocp"],
                    "horent"=>$resposta["horent"],
                    "horsai"=>$resposta["horsai"],
                ];
            }

            $query = " select P.nompes, VP.codpes, R32.sgldis as coddis, D.nomdis, O.dtainiofe as dtainiaul, O.dtafimofe as dtafimaul, D.cgahorteodis, D.cgahorpradis, D.cgahoresddis, D.cgahordis, D.numcretotdis, ET.diasmnofe as diasmnocp, ET.horiniofe as horent, ET.horfimofe as horsai, count(*) as nummtr";
            $query .= " from VINCULOPESSOAUSP as VP, PESSOA as P, DISCIPLINA as D, R32TURMINDOC as R32, OFERECIMENTO as O, R41PGMMATTUR as R41, ESPACOTURMA as ET";
            $query .= " where VP.codund = :codund";
            $query .= " and VP.tipfnc = 'Docente'";
            $query .= " and VP.codset = :codset";
            $query .= " and P.codpes = VP.codpes";
            $query .= " and R32.codpes = VP.codpes";
            $query .= " and O.sgldis = R32.sgldis";
            $query .= " and O.numseqdis = R32.numseqdis";
            $query .= " and O.numofe = R32.numofe";
            $query .= " and YEAR(O.dtainiofe) in (:ano)";
            $query .= " and MONTH(O.dtainiofe) >= :mesmin";
            $query .= " and MONTH(O.dtainiofe) <= :mesmax";
            $query .= " and D.sgldis = R32.sgldis";
            $query .= " and D.numseqdis = R32.numseqdis";
            $query .= " and R41.sgldis = R32.sgldis";
            $query .= " and R41.numseqdis = R32.numseqdis";
            $query .= " and R41.numofe = R32.numofe";
            $query .= " and ET.sgldis = R32.sgldis";
            $query .= " and ET.numseqdis = R32.numseqdis";
            $query .= " and ET.numofe = R32.numofe";
            $query .= " group by P.nompes, VP.codpes, D.nomdis, R32.sgldis, O.dtainiofe, O.dtafimofe, D.cgahorteodis, D.cgahorpradis, D.cgahoresddis, D.cgahordis, D.numcretotdis, ET.diasmnofe, ET.horiniofe, ET.horfimofe";
            $param = [
                'codund' => '45',
                'ano' => $validated["ano"],
                'mesmin' => $validated["semestre"] == 1 ? 1 : 7,
                'mesmax' => $validated["semestre"] == 1 ? 6 : 12,
                'codset' => $codigoSetores[$validated["departamento"]],
            ];

            $dadosPos = array();
            $respostas = DB::fetchAll($query, $param);
            $dias = ["2SG"=>"seg","3TR"=>"ter","4QA"=>"qua","5QI"=>"qui","6SX"=>"sex"];
            foreach($respostas as $resposta){
                $dadosPos[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["nomdis"] = $resposta["nomdis"];
                $dadosPos[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["nivel"] = "Pós Graduação";
                $dadosPos[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["cgahorteodis"] = $resposta["cgahorteodis"];
                $dadosPos[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["cgahorpradis"] = $resposta["cgahorpradis"];
                $dadosPos[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["cgahoresddis"] = $resposta["cgahoresddis"];
                $dadosPos[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["cgahordis"] = $resposta["cgahordis"];
                $dadosPos[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["numcretotdis"] = $resposta["numcretotdis"];
                $dadosPos[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["dtainiaul"] = explode(" ",$resposta["dtainiaul"])[0];
                $dadosPos[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["dtafimaul"] = explode(" ",$resposta["dtafimaul"])[0];
                $dadosPos[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["nummtr"] = $resposta["nummtr"];
                $dadosPos[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["horarios"][] = [
                    "diasmnocp"=>$dias[$resposta["diasmnocp"]],
                    "horent"=>substr_replace($resposta["horent"], ":", 2, 0),
                    "horsai"=>substr_replace($resposta["horsai"], ":", 2, 0),
                ];
            }

            $departamento = $validated["departamento"];
            $ano = $validated["ano"];
            $semestre = $validated["semestre"];
            $dados = array_merge_recursive($dadosGraduação, $dadosPos);

            ksort($dados);
            
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

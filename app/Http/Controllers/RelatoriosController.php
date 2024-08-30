<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests\CargaDidaticaDisciplinaRequest;
use App\Http\Requests\CargaDidaticaDocenteRequest;
use App\Http\Requests\AnaliseDeBolsasMonitoriaRequest;
use App\Http\Requests\DiscentesIngressantesRequest;
use App\Http\Requests\DiscentesEstabilidadeRequest;
use App\Http\Requests\DocenteSemCargaDidaticaRequest;
use App\Http\Requests\CargaDidaticaPorDocenteRequest;
use Illuminate\Support\Facades\Auth;
use Uspdev\Replicado\DB;
use Illuminate\Support\Facades\DB as DBFacade;
use App\Models\Turma;
use App\Models\Semestre;
use App\Models\Docente;
use App\Models\Horario;
use App\Models\Log;
use GuzzleHttp\Client;

class RelatoriosController extends Controller
{
    public function cargaDidaticaDocentes(CargaDidaticaDocenteRequest $request)
    {
        if(!Auth::check()){
            return redirect(route("login"));
        }elseif(!Auth::user()->hasPermissionTo("RPT_CD_DOCENTE")){
            Log::create([
                "operacao"=>"RPT_CD_DOCENTE",
                "status"=>"NEGADO",
                "usuario_id"=>Auth::user()->id,
                "descricao"=>$request->getClientIp()
            ]);
            return abort(403);
        }

        $validated = $request->validated();
        
        if(isset($validated["departamento"]) and isset($validated["ano"]) and isset($validated["semestre"])){
            $codigoSetores = ["MAC"=>1664, "MAE"=>1665, "MAP"=>1666, "MAT"=>1667];
            $semestre = Semestre::firstOrCreate(["ano"=>$validated["ano"],"periodo"=>$validated["semestre"]]);

            $query = " select distinct P.nompes, V.codpes, V.codset, M.coddis, D.nomdis, M.codtur, FORMAT(M.dtainiaul, 'dd/MM/yyyy') as dtainiaul, FORMAT(M.dtafimaul, 'dd/MM/yyyy') as dtafimaul, D.creaul, D.cretrb, (T.nummtr+T.nummtrturcpl+T.nummtropt+T.nummtrecr) as nummtr,M.diasmnocp, H.horent, H.horsai";
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
                'codtur' => $semestre->ano.$semestre->periodo."%",
                'codset' => $codigoSetores[$validated["departamento"]],
            ];

            $respostas = DB::fetchAll($query, $param);

            $dadosGraduação = array();
            foreach($respostas as $resposta){
                $dadosGraduação[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["nomdis"] = $resposta["nomdis"];
                $dadosGraduação[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["nivel"] = "Graduação";
                $dadosGraduação[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["creaul"] = $resposta["creaul"];
                $dadosGraduação[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["cretrb"] = $resposta["cretrb"];
                $dadosGraduação[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["dtainiaul"] = $resposta["dtainiaul"];
                $dadosGraduação[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["dtafimaul"] = $resposta["dtafimaul"];
                $dadosGraduação[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["turmas"][$resposta["codtur"]]["nummtr"] = $resposta["nummtr"];
                $dadosGraduação[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["turmas"][$resposta["codtur"]]["horarios"][] = [
                    "diasmnocp"=>$resposta["diasmnocp"],
                    "horent"=>$resposta["horent"],
                    "horsai"=>$resposta["horsai"],
                ];
            }

            $query = " select P.nompes, V.codpes, V.codset, R32.sgldis as coddis, D.nomdis, FORMAT(O.dtainiofe, 'dd/MM/yyyy') as dtainiaul, FORMAT(O.dtafimofe, 'dd/MM/yyyy') as dtafimaul, D.cgahorteodis as creaul, D.cgahorpradis as cretrb, D.cgahordis, D.numcretotdis, ET.diasmnofe as diasmnocp, ET.horiniofe as horent, ET.horfimofe as horsai, count(*) as nummtr";
            $query .= " from VINCULOPESSOAUSP as V, PESSOA as P, DISCIPLINA as D, R32TURMINDOC as R32, OFERECIMENTO as O, R41PGMMATTUR as R41, ESPACOTURMA as ET";
            $query .= " where V.codund = :codund";
            $query .= " and V.tipfnc = 'Docente'";
            $query .= " and V.codset = :codset";
            $query .= " and P.codpes = V.codpes";
            $query .= " and R32.codpes = V.codpes";
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
            $query .= " group by P.nompes, V.codpes, V.codset, D.nomdis, R32.sgldis, O.dtainiofe, O.dtafimofe, D.cgahorteodis, D.cgahorpradis, D.cgahordis, D.numcretotdis, ET.diasmnofe, ET.horiniofe, ET.horfimofe";
            $param = [
                'codund' => '45',
                'codset' => $codigoSetores[$validated["departamento"]],
                'ano' => $semestre->ano,
                'mesmin' => $semestre->periodo == 1 ? 1 : 7,
                'mesmax' => $semestre->periodo == 1 ? 6 : 12,
            ];

            $respostas = DB::fetchAll($query, $param);

            $dadosPos = array();
            $dias = ["2SG"=>"seg","3TR"=>"ter","4QA"=>"qua","5QI"=>"qui","6SX"=>"sex"];
            foreach($respostas as $resposta){
                $dadosPos[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["nomdis"] = $resposta["nomdis"];
                $dadosPos[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["nivel"] = "Pós Graduação";
                $dadosPos[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["cgahorteodis"] = $resposta["creaul"];
                $dadosPos[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["cgahorpradis"] = $resposta["cretrb"];
                $dadosPos[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["cgahordis"] = $resposta["cgahordis"];
                $dadosPos[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["numcretotdis"] = $resposta["numcretotdis"];
                $dadosPos[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["dtainiaul"] = $resposta["dtainiaul"];
                $dadosPos[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["dtafimaul"] = $resposta["dtafimaul"];
                $dadosPos[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["nummtr"] = $resposta["nummtr"];
                $dadosPos[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]]["horarios"][] = [
                    "diasmnocp"=>$dias[$resposta["diasmnocp"]],
                    "horent"=>substr_replace($resposta["horent"], ":", 2, 0),
                    "horsai"=>substr_replace($resposta["horsai"], ":", 2, 0),
                ];
            }

            $query = " select P.nompes, V.codpes, V.codset, O.codofeatvceu as coddis, A.nomatvceu as nomdis, FORMAT(O.dtainiofeatv, 'dd/MM/yyyy') as dtainiaul, FORMAT(O.dtafimofeatv, 'dd/MM/yyyy') as dtafimaul, M.cgahormis,D.idcdiasmn as diasmnocp, D.horent, D.horsai, COUNT(MC.codcurceu) as nummtr";
            $query .= " from PESSOA as P, VINCULOPESSOAUSP as V, OFERECIMENTOATIVIDADECEU as O, DIAOFERECIMENTOCEU as D, CURSOCEU as C, MINISTRANTECEU as M, ATIVIDADECEU as A, MATRICULACURSOCEU as MC";
            $query .= " where V.codund = :codund";
            $query .= " and V.tipfnc = :tipfnc";
            $query .= " and V.codset = :codset";
            $query .= " and P.codpes = V.codpes";
            $query .= " and YEAR(O.dtainiofeatv) in (:ano)";
            $query .= " and MONTH(O.dtainiofeatv) >= :mesmin";
            $query .= " and MONTH(O.dtainiofeatv) <= :mesmax";
            $query .= " and M.codpes = V.codpes";
            $query .= " and O.codofeatvceu = M.codofeatvceu";
            $query .= " and A.codatvceu = O.codatvceu";
            $query .= " and A.codund = :codund";
            $query .= " and C.codcurceu = O.codcurceu";
            $query .= " and C.codcurceu != :remcodcurceu";
            $query .= " and MC.codcurceu = O.codcurceu";
            $query .= " and MC.codedicurceu = O.codedicurceu";
            $query .= " and D.codofeatvceu = O.codofeatvceu";
            $query .= " group by P.nompes, V.codpes, V.codset,O.codofeatvceu, A.nomatvceu, FORMAT(O.dtainiofeatv, 'dd/MM/yyyy'), FORMAT(O.dtafimofeatv, 'dd/MM/yyyy'), M.cgahormis,D.idcdiasmn, D.horent, D.horsai";
            $query .= " order by P.nompes asc;";
            $param = [
                'codund' => '45',
                'tipfnc' => 'Docente',
                'codset' => $codigoSetores[$validated["departamento"]],
                'ano' => $semestre->ano,
                'mesmin' => $semestre->periodo == 1 ? 1 : 7,
                'mesmax' => $semestre->periodo == 1 ? 6 : 12,
                'remcodcurceu' => '450200005',
            ];
            
            $respostas = DB::fetchAll($query, $param);

            $dadosCeu = array();
            $dias = ["1"=>"dom","2"=>"seg","3"=>"ter","4"=>"qua","5"=>"qui","6"=>"sex","7"=>"sab"];
            foreach($respostas as $resposta){
                $dadosCeu[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]. "\0"]["nomdis"] = $resposta["nomdis"];
                $dadosCeu[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]. "\0"]["nivel"] = "Cultura e Extensão";
                $dadosCeu[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]. "\0"]["cgahormis"] = $resposta["cgahormis"];
                $dadosCeu[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]. "\0"]["dtainiaul"] = $resposta["dtainiaul"];
                $dadosCeu[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]. "\0"]["dtafimaul"] = $resposta["dtafimaul"];
                $dadosCeu[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]. "\0"]["nummtr"] = $resposta["nummtr"];
                $dadosCeu[$resposta["nompes"]." (".$resposta["codpes"].")"]["disciplinas"][$resposta["coddis"]. "\0"]["horarios"][] = [
                    "diasmnocp"=>$dias[$resposta["diasmnocp"]],
                    "horent"=>str_pad($resposta["horent"], 5, "0", STR_PAD_LEFT),
                    "horsai"=>str_pad($resposta["horsai"], 5, "0", STR_PAD_LEFT),
                ];
            }

            $departamento = $validated["departamento"];
            $ano = $validated["ano"];
            $semestre = $validated["semestre"];
            $dados = array_merge_recursive($dadosGraduação, $dadosPos, $dadosCeu);

            ksort($dados);

            $dias = ["seg"=>1,"ter"=>2,"qua"=>3,"qui"=>4,"sex"=>5,"sab"=>6];
            foreach($dados as $key=>$values){
                foreach($dados[$key]["disciplinas"] as $key2=>$values2){
                    if($dados[$key]["disciplinas"][$key2]["nivel"] == "Graduação"){
                        foreach($dados[$key]["disciplinas"][$key2]["turmas"] as $key3=>$values3){
                            $horarios = $dados[$key]["disciplinas"][$key2]["turmas"][$key3]["horarios"];
                            usort($horarios, function($a,$b)use($dias){
                                if($a["diasmnocp"] == $b["diasmnocp"]){
                                    return $a["horent"] <=> $b["horent"];
                                }
                                return $dias[$a["diasmnocp"]] <=> $dias[$b["diasmnocp"]];
                            });
                            $dados[$key]["disciplinas"][$key2]["turmas"][$key3]["horarios"] = $horarios;
                        }
                    }elseif($dados[$key]["disciplinas"][$key2]["nivel"] == "Pós Graduação"){
                        $horarios = $dados[$key]["disciplinas"][$key2]["horarios"];
                        usort($horarios, function($a,$b)use($dias){
                            if($a["diasmnocp"] == $b["diasmnocp"]){
                                return $a["horent"] <=> $b["horent"];
                            }
                            return $dias[$a["diasmnocp"]] <=> $dias[$b["diasmnocp"]];
                        });
                        $dados[$key]["disciplinas"][$key2]["horarios"] = $horarios;
                    }elseif($dados[$key]["disciplinas"][$key2]["nivel"] == "Cultura e Extensão"){
                        $horarios = $dados[$key]["disciplinas"][$key2]["horarios"];
                        usort($horarios, function($a,$b)use($dias){
                            if($a["diasmnocp"] == $b["diasmnocp"]){
                                return $a["horent"] <=> $b["horent"];
                            }
                            return $dias[$a["diasmnocp"]] <=> $dias[$b["diasmnocp"]];
                        });
                        $dados[$key]["disciplinas"][$key2]["horarios"] = $horarios;
                    }
                }
            }
            

            Log::create([
                "operacao"=>"RPT_CD_DOCENTE",
                "status"=>"OK",
                "usuario_id"=>Auth::user()->id,
                "descricao"=>$request->getClientIp()
            ]);

            return view("relatorios.cargaDidatica.docentes.index", compact([
                "dados",
                "departamento",
                "ano",
                "semestre"
            ]));
        }

        return view("relatorios.cargaDidatica.docentes.create");
    }

    public function cargaDidaticaDisciplinas(CargaDidaticaDisciplinaRequest $request)
    {
        if(!Auth::check()){
            return redirect(route("login"));
        }elseif(!Auth::user()->hasPermissionTo("RPT_CD_DISCIPLINA")){
            Log::create([
                "operacao"=>"RPT_CD_DISCIPLINA",
                "status"=>"NEGADO",
                "usuario_id"=>Auth::user()->id,
                "descricao"=>$request->getClientIp()
            ]);
            return abort(403);
        }

        $validated = $request->validated();
        
        if(isset($validated["departamento"]) and isset($validated["periodo_inicial"])){
            $codigoSetores = ["MAC"=>1664, "MAE"=>1665, "MAP"=>1666, "MAT"=>1667];

            $departamento = $validated["departamento"];

            $semestres_id = [];
            $pi = substr($validated["periodo_inicial"], 4, 1);
            $ai = substr($validated["periodo_inicial"], 0, 4);
            $pf = ((isset($validated["periodo_final"])) ? substr($validated["periodo_final"], 4, 1) : $pi);
            $af = ((isset($validated["periodo_final"])) ? substr($validated["periodo_final"], 0, 4) : $ai);

            foreach(range($ai,$af) as $ano){
                foreach([1,2] as $periodo){
                    $semestre = null;
                    if($ano == $ai){
                        if($periodo >= $pi){
                            $semestre = Semestre::where(["ano"=>$ano,"periodo"=>$periodo])->first();
                        }
                    }elseif($ano == $af){
                        if($periodo <= $pf){
                            $semestre = Semestre::where(["ano"=>$ano,"periodo"=>$periodo])->first();
                        }
                    }else{
                        $semestre = Semestre::where(["ano"=>$ano,"periodo"=>$periodo])->first();
                    }
                    if($semestre){
                        $semestres_id[] = $semestre->id;
                    }
                }
            }

            $turmas = Turma::whereIn("semestre_id", $semestres_id)->where("coddis", "like", $departamento."%")->get(); //supondo que as turmas ja existam(talvez n seja o caso)
            
            $turmas = $turmas->sortBy(['nivel','coddis','codtur']);

            Log::create([
                "operacao"=>"RPT_CD_DISCIPLINA",
                "status"=>"OK",
                "usuario_id"=>Auth::user()->id,
                "descricao"=>$request->getClientIp()
            ]);

            return view("relatorios.cargaDidatica.disciplinas.index", compact([
                "turmas",
                "departamento"
            ]));
        }

        return view("relatorios.cargaDidatica.disciplinas.create");
    }

    public function analiseDeBolsasMonitoria(AnaliseDeBolsasMonitoriaRequest $request)
    {
        if(!Auth::check()){
            return redirect(route("login"));
        }elseif(!Auth::user()->hasPermissionTo("RPT_MONITORIA")){
            Log::create([
                "operacao"=>"RPT_MONITORIA",
                "status"=>"NEGADO",
                "usuario_id"=>Auth::user()->id,
                "descricao"=>$request->getClientIp()
            ]);
            return abort(403);
        }

        $validated = $request->validated();
        
        if(isset($validated["ano"]) and isset($validated["semestre"])){
            $semestre = Semestre::firstOrCreate(["ano"=>$validated["ano"],"periodo"=>$validated["semestre"]]);
            $turmas = Turma::whereBelongsTo($semestre)->get(); //supondo que as turmas ja existam(talvez n seja o caso)
            
            $turmas = $turmas->sortBy(['nivel','coddis','codtur']);

            Log::create([
                "operacao"=>"RPT_MONITORIA",
                "status"=>"OK",
                "usuario_id"=>Auth::user()->id,
                "descricao"=>$request->getClientIp()
            ]);

            return view("relatorios.analiseDeBolsas.monitoria.index", compact([
                "turmas",
                "semestre"
            ]));
        }

        return view("relatorios.analiseDeBolsas.monitoria.create");

    }

    public function discentesIngressantes(DiscentesIngressantesRequest $request)
    {
        if(!Auth::check()){
            return redirect(route("login"));
        }elseif(!Auth::user()->hasPermissionTo("RPT_DIS_ING")){
            Log::create([
                "operacao"=>"RPT_DIS_ING",
                "status"=>"NEGADO",
                "usuario_id"=>Auth::user()->id,
                "descricao"=>$request->getClientIp()
            ]);
            return abort(403);
        }

        $validated = $request->validated();
        
        if(isset($validated["ano"]) and isset($validated["codcurhab"])){
            $codcur = explode("-", $validated["codcurhab"])[0];
            $codhab = explode("-", $validated["codcurhab"])[1];

            $query = " SELECT H.codcur, H.codhab, PS.codpes, PS.nompes, P.stapgm, P.tipencpgm, FORMAT(P.dtaing, 'dd/MM/yyyy') as dtaing, FORMAT(H.dtafim, 'dd/MM/yyyy') as dtafim, PS.sexpes, P.numopcing, P.tiping, P.clsing, P.codpgm, 'ALUNOGR', FORMAT(PS.dtanas, 'dd/MM/yyyy') as dtanas, R.raccor";
            $query .= " FROM PESSOA AS PS";
            $query .= " INNER JOIN HABILPROGGR AS H ON H.codpes = PS.codpes";
            $query .= " INNER JOIN PROGRAMAGR AS P ON P.codpes = PS.codpes AND P.codpgm = H.codpgm";
            $query .= " INNER JOIN COMPLPESSOA AS CP ON PS.codpes = CP.codpes";
            $query .= " LEFT JOIN RACACOR AS R ON CP.codraccor = R.codraccor";
            $query .= " WHERE H.codcur = :codcur";
            $query .= " AND H.codhab = :codhab";
            $query .= " AND YEAR(P.dtaing) = :ano";
            $query .= " ORDER BY PS.nompes ASC";
            $param = [
                'codcur' => $codcur,
                'codhab' => $codhab,
                'ano' => $validated["ano"],
            ];

            $alunos = DB::fetchAll($query,$param);

            $query = " select C.codcur, C.nomcur, H.codhab, H.nomhab, H.perhab";
            $query .= " from CURSOGR as C, HABILITACAOGR as H";
            $query .= " where C.codcur = :codcur";
            $query .= " and C.dtadtvcur is null";
            $query .= " and H.codcur = :codcur";
            $query .= " and H.codhab = :codhab";
            #$query .= " and H.dtadtvhab is null";
            $param = [
                'codcur' => $codcur,
                'codhab' => $codhab,
            ];
    
            $curso = DB::fetchAll($query,$param);

            if($curso){
                $curso = $curso[0];
            }

            $ano = $validated["ano"];

            Log::create([
                "operacao"=>"RPT_DIS_ING",
                "status"=>"OK",
                "usuario_id"=>Auth::user()->id,
                "descricao"=>$request->getClientIp()
            ]);

            return view("relatorios.discentes.ingressantes.index", compact([
                'alunos',
                'curso',
                'ano'
            ]));
        }

        $query = " select C.codcur, C.nomcur, H.codhab, H.nomhab";
        $query .= " from CURSOGR as C, HABILITACAOGR as H";
        $query .= " where C.codclg = :codclg";
        $query .= " and C.dtadtvcur is null";
        $query .= " and H.codcur = C.codcur";
        #$query .= " and H.dtadtvhab is null";
        $query .= " order by C.codcur, H.codhab asc";
        $param = [
            'codclg' => '45'
        ];

        $cursos = DB::fetchAll($query,$param);

        return view("relatorios.discentes.ingressantes.create", compact([
            'cursos'
        ]));

    }

    public function discentesEstabilidade(DiscentesEstabilidadeRequest $request)
    {
        if(!Auth::check()){
            return redirect(route("login"));
        }elseif(!Auth::user()->hasPermissionTo("RPT_DIS_EST")){
            Log::create([
                "operacao"=>"RPT_DIS_ING",
                "status"=>"NEGADO",
                "usuario_id"=>Auth::user()->id,
                "descricao"=>$request->getClientIp()
            ]);
            return abort(403);
        }

        $validated = $request->validated();
        
        if(isset($validated["anoini"]) and isset($validated["anofim"]) and isset($validated["codcurhab"])){
            $codcur = explode("-", $validated["codcurhab"])[0];
            $codhab = explode("-", $validated["codcurhab"])[1];
            $dados = array();

            foreach(range($validated["anoini"],$validated["anofim"]) as $ano){
                $query = " SELECT PS.sexpes, P.tipencpgm";
                $query .= " FROM PESSOA AS PS";
                $query .= " INNER JOIN HABILPROGGR AS H ON (PS.codpes = H.codpes)";
                $query .= " INNER JOIN PROGRAMAGR AS P ON (PS.codpes = P.codpes AND H.codpgm = P.codpgm)";
                $query .= " WHERE H.codcur = :codcur AND H.codhab = :codhab";
                $query .= " AND P.stapgm = :stapgm";
                $query .= " AND YEAR(H.dtafim) = :ano_encerramento_programa";
                $param = [
                    'codcur' => $codcur,
                    'codhab' => $codhab,
                    'stapgm' => 'E',
                    'ano_encerramento_programa' => $ano,
                ];

                $respostas = DB::fetchAll($query,$param);

                $dados[$ano]["Formadas"] = count(array_filter($respostas, function($var){
                    return $var['sexpes'] == "F" and str_contains($var['tipencpgm'], "Conclusão");
                }));

                $dados[$ano]["Formados"] = count(array_filter($respostas, function($var){
                    return $var['sexpes'] == "M" and str_contains($var['tipencpgm'], "Conclusão");
                }));

                $dados[$ano]["Transferidas"] = count(array_filter($respostas, function($var){
                    return $var['sexpes'] == "F" and str_contains($var['tipencpgm'], "Transferência");
                }));

                $dados[$ano]["Transferidos"] = count(array_filter($respostas, function($var){
                    return $var['sexpes'] == "M" and str_contains($var['tipencpgm'], "Transferência");
                }));

                $dados[$ano]["Outros Feminino"] = count(array_filter($respostas, function($var){
                    return $var['sexpes'] == "F" and !str_contains($var['tipencpgm'], "Transferência") and !str_contains($var['tipencpgm'], "Conclusão");
                }));

                $dados[$ano]["Outros Masculino"] = count(array_filter($respostas, function($var){
                    return $var['sexpes'] == "M" and !str_contains($var['tipencpgm'], "Transferência") and !str_contains($var['tipencpgm'], "Conclusão");
                }));

                $dados[$ano]["Total"] = count($respostas);
            }

            $query = " select C.codcur, C.nomcur, H.codhab, H.nomhab, H.perhab";
            $query .= " from CURSOGR as C, HABILITACAOGR as H";
            $query .= " where C.codcur = :codcur";
            $query .= " and C.dtadtvcur is null";
            $query .= " and H.codcur = :codcur";
            $query .= " and H.codhab = :codhab";
            $query .= " and H.dtadtvhab is null";
            $param = [
                'codcur' => $codcur,
                'codhab' => $codhab,
            ];
    
            $curso = DB::fetchAll($query,$param);

            if($curso){
                $curso = $curso[0];
            }

            Log::create([
                "operacao"=>"RPT_DIS_EST",
                "status"=>"OK",
                "usuario_id"=>Auth::user()->id,
                "descricao"=>$request->getClientIp()
            ]);

            return view("relatorios.discentes.estabilidade.index", compact([
                'dados',
                'curso'
            ]));
        }

        $query = " select C.codcur, C.nomcur, H.codhab, H.nomhab";
        $query .= " from CURSOGR as C, HABILITACAOGR as H";
        $query .= " where C.codclg = :codclg";
        $query .= " and C.dtadtvcur is null";
        $query .= " and H.codcur = C.codcur";
        $query .= " and H.dtadtvhab is null";
        $query .= " order by C.codcur, H.codhab asc";
        $param = [
            'codclg' => '45'
        ];

        $cursos = DB::fetchAll($query,$param);

        return view("relatorios.discentes.estabilidade.create", compact([
            'cursos'
        ]));
    }

    public function downloadRelatorioSistemaMonitoria(AnaliseDeBolsasMonitoriaRequest $request)
    {
        if(!Auth::check()){
            return redirect(route("login"));
        }elseif(!Auth::user()->hasPermissionTo("RPT_MONITORIA")){
            Log::create([
                "operacao"=>"RPT_MONITORIA_PDF",
                "status"=>"NEGADO",
                "usuario_id"=>Auth::user()->id,
                "descricao"=>$request->getClientIp()
            ]);
            return abort(403);
        }

        $validated = $request->validated();

        $client = new Client();

        $resposta = $client->request('GET', env("EXTERNAL_REPORT_URL"), 
            [
                "query" => [
                    'token' => env("EXTERNAL_REPORT_TOKEN"), 
                    "ano" => $validated["ano"],
                    "periodo" => $validated["semestre"]
                ]
            ]);

        $resposta = (string) $resposta->getBody();

        $resposta = json_decode($resposta, true);

        $bin = base64_decode($resposta["report"], true);

        return response($bin)
        ->header('Content-Type', 'application/pdf');
    }

    public function semCargaDidaticaDocentes(DocenteSemCargaDidaticaRequest $request)
    {
        if(!Auth::check()){
            return redirect(route("login"));
        }elseif(!Auth::user()->hasPermissionTo("RPT_SCD_DOCENTES")){
            Log::create([
                "operacao"=>"RPT_SCD_DOCENTES",
                "status"=>"NEGADO",
                "usuario_id"=>Auth::user()->id,
                "descricao"=>$request->getClientIp()
            ]);
            return abort(403);
        }

        $validated = $request->validated();

        if(isset($validated["ano"]) and isset($validated["periodo"])){

            $semestre = Semestre::where('ano', $validated["ano"])->where('periodo', $validated["periodo"])->first();

            $turmasSemestreIds = $semestre->turmas()->pluck('id');

            $docentesComTurmasIds = DBFacade::table('docente_turma')
                                        ->whereIn('turma_id', $turmasSemestreIds)
                                        ->pluck('docente_id');

            $docentes = Docente::whereNotIn('id', $docentesComTurmasIds)->get();

            $query = " SELECT V.codpes, V.dtainivin, V.dtafimvin, V.sitatl, V.dtainisitatl FROM VINCULOPESSOAUSP as V";

            $totaldocentes = $docentes->count();

            $query .= " where V.codpes in (";
            foreach($docentes as $index=>$docente){
                $query .= $docente->codpes;
                if($index != $totaldocentes - 1){
                    $query .= ",";
                }
            }
            $query .= ")";
            $query .= " and V.tipfnc = :tipfnc";
            $query .= " and V.codund = :codund";
            $param = [
                'codund' => '45',
                'tipfnc' => 'Docente'
            ];
    
            $respostas = DB::fetchAll($query,$param);
            
            $ativos = [];
            $dtaref = $validated["ano"] . ($validated["periodo"] == 1 ? '-04-01' : '-09-01');
            foreach($respostas as $docente){
                $dtainivin = explode(" ",$docente['dtainivin'])[0];
                if($docente['dtafimvin']){
                    $dtafimvin = explode(" ",$docente['dtafimvin'])[0];
                }else{
                    $dtafimvin = explode(" ",$docente['dtainisitatl'])[0];
                }
                if($docente["sitatl"]=="A"){
                    $ativos[] = $docente["codpes"];
                }elseif($dtainivin <= $dtaref and $dtafimvin >= $dtaref){
                    $ativos[] = $docente["codpes"];
                }
            }
            
            $docentes = $docentes->filter(function($docente)use($ativos){
                return in_array($docente->codpes, $ativos);
            });

            return view("relatorios.semCargaDidatica.docentes.index", compact([
                "docentes",
                "semestre"
            ]));    
        }

        Log::create([
            "operacao"=>"RPT_SCD_DOCENTES",
            "status"=>"OK",
            "usuario_id"=>Auth::user()->id,
            "descricao"=>$request->getClientIp()
        ]);

        return view("relatorios.semCargaDidatica.docentes.create");
    }


    public function cargaDidaticaPorDocente(CargaDidaticaPorDocenteRequest $request)
    {
        if(!Auth::check()){
            return redirect(route("login"));
        }elseif(!Auth::user()->hasPermissionTo("RPT_CDP_DOCENTE")){
            Log::create([
                "operacao"=>"RPT_CDP_DOCENTE",
                "status"=>"NEGADO",
                "usuario_id"=>Auth::user()->id,
                "descricao"=>$request->getClientIp()
            ]);
            return abort(403);
        }

        $validated = $request->validated();
        
        if(isset($validated["codpes"]) and isset($validated["periodoInicial"]) and isset($validated["periodoFinal"])){
            $docente = Docente::where("codpes",$validated["codpes"])->first();

            $turmas = $docente->turmas;

            $periodo1 = substr($validated["periodoInicial"], 4, 1);
            $ano1 = substr($validated["periodoInicial"], 0, 4);
            $periodo2 = ((isset($validated["periodoFinal"])) ? substr($validated["periodoFinal"], 4, 1) : $pi);
            $ano2 = ((isset($validated["periodoFinal"])) ? substr($validated["periodoFinal"], 0, 4) : $ai);
            
            $semestres = Semestre::where(function ($query) use ($ano1, $periodo1, $ano2, $periodo2) {
                $query->where(function ($subQuery) use ($ano1, $periodo1) {
                    $subQuery->where('ano', '>', $ano1)
                                ->orWhere(function ($innerQuery) use ($ano1, $periodo1) {
                                    $innerQuery->where('ano', $ano1)->where('periodo', '>=', $periodo1);
                                });
                })->where(function ($subQuery) use ($ano2, $periodo2) {
                    $subQuery->where('ano', '<', $ano2)
                                ->orWhere(function ($innerQuery) use ($ano2, $periodo2) {
                                    $innerQuery->where('ano', $ano2)->where('periodo', '<=', $periodo2);
                                });
                });
            })->pluck('id')->toArray();

            $turmas = $turmas->filter(function($turma)use($semestres){
                return in_array($turma->semestre_id, $semestres);
            });           

            $turmas = $turmas->sortByDesc(function ($turma) {
                return sprintf('%04d-%02d', $turma->semestre->ano, $turma->semestre->periodo);
            });

            Log::create([
                "operacao"=>"RPT_CDP_DOCENTE",
                "status"=>"OK",
                "usuario_id"=>Auth::user()->id,
                "descricao"=>$request->getClientIp()
            ]);

            return view("relatorios.cargaDidatica.porDocente.index", compact([
                "turmas",
                "docente"
            ]));
        }

        return view("relatorios.cargaDidatica.porDocente.create");
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Turma;
use App\Models\Docente;
use App\Models\Semestre;
use App\Models\Horario;
use Uspdev\Replicado\DB;

class TurmaSeeder extends Seeder
{
    public function run(): void
    {
        foreach(Semestre::all() as $semestre){
            $query = " select distinct M.coddis, M.codtur, M.verdis";
            $query .= " from VINCULOPESSOAUSP as V, PESSOA as P, MINISTRANTE as M";
            $query .= " where V.codund = :codund";
            $query .= " and V.tipfnc = :tipfnc";
            $query .= " and P.codpes = V.codpes";
            $query .= " and M.codpes = V.codpes";
            $query .= " and M.codtur like :codtur";
            $query .= " and M.coddis like :ccm";
            $query .= " order by M.coddis asc, M.codtur asc, M.verdis asc";
            $param = [
                'codund' => '45',
                'tipfnc' => 'Docente',
                'codtur' => $semestre->ano.$semestre->periodo."%",
                'ccm' => 'CCM%',
            ];

            $ccm = DB::fetchAll($query, $param);
            
            $query = " select distinct T.coddis, T.codtur, T.verdis";
            $query .= " from DISCIPGRCODIGO as DC, TURMAGR AS T";
            $query .= " where DC.codclg = :codclg";
            $query .= " and DC.sglclg = :sglclg";
            $query .= " and T.coddis = DC.coddis";
            $query .= " and T.codtur like :codtur";
            $query .= " and T.verdis = (select max(T2.verdis) from TURMAGR as T2 where T2.coddis = DC.coddis and T2.codtur like T.codtur)";
            $query .= " order by T.coddis asc, T.codtur asc, T.verdis asc";
            $param = [
                'codclg' => '45',
                'sglclg' => 'CG',
                'codtur' => $semestre->ano.$semestre->periodo."%",
            ];

            $turmasBruto = array_merge($ccm, DB::fetchAll($query, $param));
            
            $query = " select T.coddis, D.nomdis, T.codtur, FORMAT(T.dtainitur, 'dd/MM/yyyy') as dtainiaul, FORMAT(T.dtafimtur , 'dd/MM/yyyy') as dtafimaul, D.creaul, D.cretrb, (T.nummtr+T.nummtrturcpl+T.nummtropt+T.nummtrecr+T.nummtroptlre) as nummtr";
            $query .= " from TURMAGR as T";
            $query .= " JOIN DISCIPLINAGR AS D ON T.coddis = D.coddis AND T.verdis = D.verdis";
            $query .= " JOIN ( VALUES ";
            foreach(range(0, count($turmasBruto)-1) as $i){
                $query .= "('".$turmasBruto[$i]["coddis"]."','".$turmasBruto[$i]["codtur"]."','".$turmasBruto[$i]["verdis"]."')";
                if($i != count($turmasBruto)-1){
                    $query .= ",";
                }
            }
            $query .= " ) AS TEMP(coddis, codtur, verdis)";
            $query .= " ON T.coddis = TEMP.coddis";
            $query .= " AND T.codtur = TEMP.codtur";
            $query .= " AND T.verdis = TEMP.verdis";            
            
            $respostas = DB::fetchAll($query);

            foreach($respostas as $t){
                $turma = Turma::firstOrCreate(
                    [
                        "coddis" => $t["coddis"],
                        "codtur" => $t["codtur"],
                    ],
                    [
                        "nomdis" => $t["nomdis"],
                        "dtainiaul" => $t["dtainiaul"],
                        "dtafimaul" => $t["dtafimaul"],
                        "creaul" => $t["creaul"],
                        "cretrb" => $t["cretrb"],
                        "nummtr" => $t["nummtr"],
                        "nivel" => "Graduação",
                        "semestre_id"=>$semestre->id,
                ]);

                $query = " select distinct M.codpes, P.nompes, V.codset, OT.diasmnocp, PH.horent, PH.horsai";
                $query .= " from MINISTRANTE as M, VINCULOPESSOAUSP as V, PESSOA as P, OCUPTURMA as OT, PERIODOHORARIO as PH";
                $query .= " where M.coddis = :coddis";
                $query .= " and M.codtur = :codtur";
                $query .= " and M.verdis = (select max(M2.verdis) from MINISTRANTE as M2 where M2.coddis = M.coddis and M2.codtur = M.codtur)";
                $query .= " and V.codpes = M.codpes";
                $query .= " and P.codpes = M.codpes";
                $query .= " and OT.coddis = M.coddis";
                $query .= " and OT.codtur = M.codtur";
                $query .= " and OT.verdis = M.verdis";
                $query .= " and PH.codperhor = OT.codperhor";
                $param = [
                    'coddis' => $turma->coddis,
                    'codtur' => $turma->codtur,
                ];

                $docentesHorarios = DB::fetchAll($query, $param);

                foreach($docentesHorarios as $dh){
                    $docente = $turma->docentes()->where("codpes",$dh["codpes"])->first();

                    if(!$docente and $dh["codset"]){
                        $docente = Docente::where(["codpes"=>$dh["codpes"],"codset"=>$dh["codset"]])->first();
                        if($docente){
                            $turma->docentes()->attach($docente);
                        }else{
                            if(in_array($dh["codset"],[1664,1665,1666,1666])){
                                $docente = Docente::firstOrCreate([
                                    "nompes" => $dh["nompes"],
                                    "codpes" => $dh["codpes"],
                                    "codset" => $dh["codset"],
                                ]);
                                $turma->docentes()->attach($docente);
                            }else{
                                $docente = Docente::where(["codpes"=>$dh["codpes"]])->first();

                                if($docente){
                                    $turma->docentes()->attach($docente);
                                }else{
                                    $docente = Docente::firstOrCreate([
                                        "nompes" => $dh["nompes"],
                                        "codpes" => $dh["codpes"],
                                        "codset" => $dh["codset"],
                                    ]);
                                    $turma->docentes()->attach($docente);
                                }
                            }
                        }
                    }

                    $horario = Horario::firstOrCreate([
                        "diasmnocp" => $dh["diasmnocp"],
                        "horent" => $dh["horent"],
                        "horsai" => $dh["horsai"],
                    ]);

                    if(!$turma->horarios()->where("id",$horario->id)->first()){
                        $turma->horarios()->attach($horario);
                    }
                }
            }

            $query = " select distinct R32.sgldis, R32.numseqdis, R32.numofe";
            $query .= " from VINCULOPESSOAUSP as V, PESSOA as P, R32TURMINDOC as R32, OFERECIMENTO as O";
            $query .= " where V.codund = :codund";
            $query .= " and V.tipfnc = :tipfnc";
            $query .= " and P.codpes = V.codpes";
            $query .= " and R32.codpes = V.codpes";
            $query .= " and R32.sgldis like :ibi";
            $query .= " and O.sgldis = R32.sgldis";
            $query .= " and O.numseqdis = R32.numseqdis";
            $query .= " and O.numofe = R32.numofe";
            $query .= " and YEAR(O.dtainiofe) in (:ano)";
            $query .= " and MONTH(O.dtainiofe) >= :mesmin";
            $query .= " and MONTH(O.dtainiofe) <= :mesmax";
            $query .= " order by R32.sgldis asc, R32.numseqdis asc, R32.numofe asc";
            $param = [
                'codund' => '45',
                'tipfnc' => 'Docente',
                'ibi' => 'IBI%',
                'ano' => $semestre->ano,
                'mesmin' => $semestre->periodo == 1 ? 1 : 7,
                'mesmax' => $semestre->periodo == 1 ? 6 : 12,
            ];

            $ibi = DB::fetchAll($query, $param);

            $query = " select distinct O.sgldis, O.numseqdis, O.numofe";
            $query .= " from DISCIPLINA as D, OFERECIMENTO as O";
            $query .= " where D.codare like :codare";
            $query .= " and O.sgldis = D.sgldis";
            $query .= " and O.numseqdis = D.numseqdis";
            $query .= " and YEAR(O.dtainiofe) in (:ano)";
            $query .= " and MONTH(O.dtainiofe) >= :mesmin";
            $query .= " and MONTH(O.dtainiofe) <= :mesmax";
            $param = [
                'codare' => '45%',
                'ano' => $semestre->ano,
                'mesmin' => $semestre->periodo == 1 ? 1 : 7,
                'mesmax' => $semestre->periodo == 1 ? 6 : 12,
            ];

            $turmasBruto = array_merge($ibi, DB::fetchAll($query, $param));

            foreach($turmasBruto as $tb){
                $query = " select P.nompes, V.codpes, V.codset, O.sgldis as coddis, D.nomdis, O.numofe, FORMAT(O.dtainiofe, 'dd/MM/yyyy') as dtainiaul, FORMAT(O.dtafimofe, 'dd/MM/yyyy') as dtafimaul, D.cgahorteodis as creaul, D.cgahorpradis as cretrb, D.cgahordis, D.numcretotdis, ET.diasmnofe as diasmnocp, ET.horiniofe as horent, ET.horfimofe as horsai, count(*) as nummtr";
                $query .= " from OFERECIMENTO as O, DISCIPLINA as D, R41PGMMATTUR as R41, ESPACOTURMA as ET, VINCULOPESSOAUSP as V, PESSOA as P, R32TURMINDOC as R32";
                $query .= " where O.sgldis = :sgldis";
                $query .= " and O.numseqdis = :numseqdis";
                $query .= " and O.numofe = :numofe";
                $query .= " and YEAR(O.dtainiofe) in (:ano)";
                $query .= " and MONTH(O.dtainiofe) >= :mesmin";
                $query .= " and MONTH(O.dtainiofe) <= :mesmax";
                $query .= " and D.sgldis = O.sgldis";
                $query .= " and D.numseqdis = O.numseqdis";
                $query .= " and R41.sgldis = O.sgldis";
                $query .= " and R41.numseqdis = O.numseqdis";
                $query .= " and R41.numofe = O.numofe";
                $query .= " and ET.sgldis = O.sgldis";
                $query .= " and ET.numseqdis = O.numseqdis";
                $query .= " and ET.numofe = O.numofe";
                $query .= " and R32.sgldis = O.sgldis";
                $query .= " and R32.numseqdis = O.numseqdis";
                $query .= " and R32.numofe = O.numofe";
                $query .= " and V.codpes = R32.codpes";
                $query .= " and V.tipfnc = :tipfnc";
                $query .= " and P.codpes = V.codpes";
                $query .= " group by P.nompes, V.codpes, V.codset, D.nomdis, O.sgldis, O.numofe, O.dtainiofe, O.dtafimofe, D.cgahorteodis, D.cgahorpradis, D.cgahordis, D.numcretotdis, ET.diasmnofe, ET.horiniofe, ET.horfimofe";
                $param = [
                    'sgldis' => $tb['sgldis'],
                    'numseqdis' => $tb['numseqdis'],
                    'numofe' => $tb['numofe'],
                    'ano' => $semestre->ano,
                    'mesmin' => $semestre->periodo == 1 ? 1 : 7,
                    'mesmax' => $semestre->periodo == 1 ? 6 : 12,
                    'tipfnc' => 'Docente',
                ];    
    
                $respostas = array_unique(DB::fetchAll($query, $param),SORT_REGULAR);

                $dias = ["2SG"=>"seg","3TR"=>"ter","4QA"=>"qua","5QI"=>"qui","6SX"=>"sex","7SB"=>"sab"];
                foreach($respostas as $r){
                    $turma = Turma::firstOrCreate(
                        [
                            "coddis" => $r["coddis"],
                            "codtur" => $semestre->ano.$semestre->periodo.str_pad($r["numofe"],2,'0', STR_PAD_LEFT),
                        ],
                        [
                            "nomdis" => $r["nomdis"],
                            "dtainiaul" => $r["dtainiaul"],
                            "dtafimaul" => $r["dtafimaul"],
                            "creaul" => $r["creaul"],
                            "cretrb" => $r["cretrb"],
                            "nummtr" => $r["nummtr"],
                            "nivel" => "Pós Graduação",
                            "semestre_id"=>$semestre->id,
                    ]);
    
                    $docente = $turma->docentes()->where("codpes",$r["codpes"])->first();

                    if(!$docente and $r["codset"]){
                        $docente = Docente::where(["codpes"=>$r["codpes"],"codset"=>$r["codset"]])->first();
                        if($docente){
                            $turma->docentes()->attach($docente);
                        }else{
                            if(in_array($r["codset"],[1664,1665,1666,1666])){
                                $docente = Docente::firstOrCreate([
                                    "nompes" => $r["nompes"],
                                    "codpes" => $r["codpes"],
                                    "codset" => $r["codset"],
                                ]);
                                $turma->docentes()->attach($docente);
                            }else{
                                $docente = Docente::where(["codpes"=>$r["codpes"]])->first();

                                if($docente){
                                    $turma->docentes()->attach($docente);
                                }else{
                                    $docente = Docente::firstOrCreate([
                                        "nompes" => $r["nompes"],
                                        "codpes" => $r["codpes"],
                                        "codset" => $r["codset"],
                                    ]);
                                    $turma->docentes()->attach($docente);
                                }
                            }
                        }
                    }

                    $horario = Horario::firstOrCreate([
                        "diasmnocp" => $dias[$r["diasmnocp"]],
                        "horent" => substr_replace($r["horent"], ":", 2, 0),
                        "horsai" => substr_replace($r["horsai"], ":", 2, 0),
                    ]);

                    if(!$turma->horarios()->where("id",$horario->id)->first()){
                        $turma->horarios()->attach($horario);
                    }

                }
            }
        }
    }
}

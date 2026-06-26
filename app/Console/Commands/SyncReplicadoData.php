<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB as DBFacade;
use App\Models\Turma;
use App\Models\Docente;
use App\Models\Semestre;
use App\Models\Horario;
use Uspdev\Replicado\DB;

class SyncReplicadoData extends Command
{
    /**
     * A assinatura do comando.
     * Use {start?} e {end?} para argumentos opcionais.
     */
    protected $signature = 'app:sync-replicado-data 
                            {start? : O semestre inicial no formato YYYYP (ex: 20241)}
                            {end? : O semestre final no formato YYYYP (ex: 20242)}';

    /**
     * A descrição do comando.
     */
    protected $description = 'Sincroniza os dados de turmas do Replicado para o banco local, com opção de filtrar por período.';

    /**
     * Executa o comando.
     */
    public function handle(): int
    {
        $this->info('Iniciando a sincronização com o Replicado USP...');

        $this->syncSemestres();

        $start = $this->argument('start');
        $end = $this->argument('end') ?? $start;

        if ($start) {
            $this->line("<fg=yellow>Sincronizando o intervalo de semestres de {$start} a {$end}.</>");
            // Usamos CONCAT para tratar o ano e período como um número único para a comparação
            $semestresToProcess = Semestre::where(DBFacade::raw("CONCAT(ano, periodo)"), '>=', $start)
                                          ->where(DBFacade::raw("CONCAT(ano, periodo)"), '<=', $end)
                                          ->orderBy('ano')->orderBy('periodo')->get();
        } else {
            $this->line('<fg=yellow>Nenhum período especificado. Sincronizando todos os semestres.</>');
            $semestresToProcess = Semestre::orderBy('ano')->orderBy('periodo')->get();
        }
        
        if ($semestresToProcess->isEmpty()) {
            $this->warn('Nenhum semestre encontrado para o intervalo especificado. Verifique os parâmetros.');
            return Command::FAILURE;
        }

        $bar = $this->output->createProgressBar($semestresToProcess->count());
        $bar->start();

        foreach ($semestresToProcess as $semestre) {
            $bar->setMessage("Processando semestre {$semestre->ano}/{$semestre->periodo}");
            $this->syncGraduacao($semestre);
            $this->syncPosGraduacao($semestre);
            $bar->advance();
        }

        $bar->finish();
        $this->info("\n\nSincronização concluída com sucesso!");
        return Command::SUCCESS;
    }

    /**
     * Garante que a tabela de semestres esteja populada.
     */
    private function syncSemestres(): void
    {
        $this->line('Verificando e sincronizando a lista de semestres...');
        $anoAtual = now()->format("Y");
        $mes = now()->format("m");
        $periodoAtual = ($mes >= 1 && $mes <= 5) ? 1 : 2;

        for ($ano = 2000; $ano <= $anoAtual; $ano++) {
            $periodos = [1];
            if (($ano < $anoAtual) || ($ano == $anoAtual && $periodoAtual == 2)) {
                $periodos[] = 2;
            }
            foreach ($periodos as $periodo) {
                Semestre::firstOrCreate(['ano' => $ano, 'periodo' => $periodo]);
            }
        }
        $this->info('Lista de semestres está atualizada.');
    }

    /**
     * Sincroniza dados das turmas de Graduação para um dado semestre.
     */
    private function syncGraduacao(Semestre $semestre): void
    {
        $turmasBruto = $this->getGraduacaoTurmas($semestre);

        if (empty($turmasBruto)) return;

        $query = "SELECT T.coddis, D.nomdis, T.codtur, FORMAT(T.dtainitur, 'dd/MM/yyyy') as dtainiaul, FORMAT(T.dtafimtur , 'dd/MM/yyyy') as dtafimaul, D.creaul, D.cretrb, (T.nummtr+T.nummtrturcpl+T.nummtropt+T.nummtrecr+T.nummtroptlre) as nummtr";
        $query .= " FROM TURMAGR as T";
        $query .= " JOIN DISCIPLINAGR AS D ON T.coddis = D.coddis AND T.verdis = D.verdis";
        $query .= " JOIN ( VALUES " . $this->buildValuesClause($turmasBruto) . " ) AS TEMP(coddis, codtur, verdis)";
        $query .= " ON T.coddis = TEMP.coddis AND T.codtur = TEMP.codtur AND T.verdis = TEMP.verdis";

        $turmasData = DB::fetchAll($query);

        foreach ($turmasData as $data) {
            $turma = Turma::updateOrCreate(
                ['coddis' => $data['coddis'], 'codtur' => $data['codtur']],
                [
                    'nomdis' => $data['nomdis'], 'dtainiaul' => $data['dtainiaul'], 'dtafimaul' => $data['dtafimaul'],
                    'creaul' => $data['creaul'], 'cretrb' => $data['cretrb'], 'nummtr' => $data['nummtr'],
                    'nivel' => 'Graduação', 'semestre_id' => $semestre->id,
                ]
            );
            $this->syncDocentesEHorariosGrad($turma);
        }
    }

    /**
     * Sincroniza dados das turmas de Pós-Graduação para um dado semestre.
     */
    private function syncPosGraduacao(Semestre $semestre): void
    {
        $turmasBruto = $this->getPosGraduacaoTurmas($semestre);

        foreach ($turmasBruto as $tb) {
            $query = "SELECT P.nompes, V.codpes, V.codset, O.sgldis as coddis, D.nomdis, O.numofe, FORMAT(O.dtainiofe, 'dd/MM/yyyy') as dtainiaul, FORMAT(O.dtafimofe, 'dd/MM/yyyy') as dtafimaul, D.cgahorteodis as creaul, D.cgahorpradis as cretrb, D.cgahordis, D.numcretotdis, ET.diasmnofe as diasmnocp, ET.horiniofe as horent, ET.horfimofe as horsai, count(*) as nummtr
                      FROM OFERECIMENTO as O, DISCIPLINA as D, R41PGMMATTUR as R41, ESPACOTURMA as ET, VINCULOPESSOAUSP as V, PESSOA as P, R32TURMINDOC as R32
                      WHERE O.sgldis = :sgldis AND O.numseqdis = :numseqdis AND O.numofe = :numofe AND YEAR(O.dtainiofe) = :ano AND MONTH(O.dtainiofe) BETWEEN :mesmin AND :mesmax
                      AND D.sgldis = O.sgldis AND D.numseqdis = O.numseqdis
                      AND R41.sgldis = O.sgldis AND R41.numseqdis = O.numseqdis AND R41.numofe = O.numofe
                      AND ET.sgldis = O.sgldis AND ET.numseqdis = O.numseqdis AND ET.numofe = O.numofe
                      AND R32.sgldis = O.sgldis AND R32.numseqdis = O.numseqdis AND R32.numofe = O.numofe
                      AND V.codpes = R32.codpes AND V.tipfnc = 'Docente'
                      AND P.codpes = V.codpes
                      GROUP BY P.nompes, V.codpes, V.codset, D.nomdis, O.sgldis, O.numofe, O.dtainiofe, O.dtafimofe, D.cgahorteodis, D.cgahorpradis, D.cgahordis, D.numcretotdis, ET.diasmnofe, ET.horiniofe, ET.horfimofe";
            
            $param = [
                'sgldis' => $tb['sgldis'], 'numseqdis' => $tb['numseqdis'], 'numofe' => $tb['numofe'],
                'ano' => $semestre->ano, 'mesmin' => $semestre->periodo == 1 ? 1 : 7, 'mesmax' => $semestre->periodo == 1 ? 6 : 12,
            ];

            $respostas = DB::fetchAll($query, $param);
            $dias = ["2SG"=>"seg","3TR"=>"ter","4QA"=>"qua","5QI"=>"qui","6SX"=>"sex","7SB"=>"sab"];

            // Agrupa os resultados por turma, já que a query retorna uma linha por docente/horário
            $turmasAgrupadas = [];
            foreach ($respostas as $r) {
                $turmaKey = $r['coddis'] . '-' . $r['numofe'];
                if (!isset($turmasAgrupadas[$turmaKey])) {
                    $turmasAgrupadas[$turmaKey] = [
                        'data' => $r,
                        'docentes' => [],
                        'horarios' => [],
                    ];
                }
                $turmasAgrupadas[$turmaKey]['docentes'][] = ['codpes' => $r['codpes'], 'nompes' => $r['nompes'], 'codset' => $r['codset']];
                if (isset($dias[$r["diasmnocp"]])) {
                    $turmasAgrupadas[$turmaKey]['horarios'][] = [
                        'diasmnocp' => $dias[$r["diasmnocp"]],
                        'horent' => substr_replace($r["horent"], ":", 2, 0),
                        'horsai' => substr_replace($r["horsai"], ":", 2, 0)
                    ];
                }
            }

            foreach($turmasAgrupadas as $turmaAgrupada) {
                $r = $turmaAgrupada['data'];
                $turma = Turma::updateOrCreate(
                    [
                        "coddis" => $r["coddis"],
                        "codtur" => $semestre->ano.$semestre->periodo.str_pad($r["numofe"],2,'0', STR_PAD_LEFT),
                    ],
                    [
                        "nomdis" => $r["nomdis"], "dtainiaul" => $r["dtainiaul"], "dtafimaul" => $r["dtafimaul"],
                        "creaul" => $r["creaul"], "cretrb" => $r["cretrb"], "nummtr" => $r["nummtr"],
                        "nivel" => "Pós Graduação", "semestre_id"=>$semestre->id,
                    ]
                );

                $docenteIds = [];
                foreach (array_unique($turmaAgrupada['docentes'], SORT_REGULAR) as $d) {
                    if ($d['codset']) {
                        $docente = Docente::updateOrCreate(['codpes' => $d['codpes']], ['nompes' => $d['nompes'], 'codset' => $d['codset']]);
                        $docenteIds[] = $docente->id;
                    }
                }
                $turma->docentes()->sync($docenteIds);

                $horarioIds = [];
                foreach (array_unique($turmaAgrupada['horarios'], SORT_REGULAR) as $h) {
                    $horario = Horario::firstOrCreate($h);
                    $horarioIds[] = $horario->id;
                }
                $turma->horarios()->sync($horarioIds);
            }
        }
    }

    private function getGraduacaoTurmas(Semestre $semestre): array
    {
        $query = "SELECT DISTINCT M.coddis, M.codtur, M.verdis FROM VINCULOPESSOAUSP V, PESSOA P, MINISTRANTE M WHERE V.codund = :codund AND V.tipfnc = 'Docente' AND P.codpes = V.codpes AND M.codpes = V.codpes AND M.codtur LIKE :codtur AND M.coddis LIKE 'CCM%'";
        $param = ['codund' => '45', 'codtur' => $semestre->ano . $semestre->periodo . "%"];
        $ccm = DB::fetchAll($query, $param);

        $query = "SELECT DISTINCT T.coddis, T.codtur, T.verdis FROM DISCIPGRCODIGO DC, TURMAGR T WHERE DC.codclg = :codclg AND DC.sglclg = 'CG' AND T.coddis = DC.coddis AND T.codtur LIKE :codtur AND T.verdis = (SELECT max(T2.verdis) FROM TURMAGR T2 WHERE T2.coddis = DC.coddis AND T2.codtur LIKE T.codtur)";
        $param = ['codclg' => '45', 'codtur' => $semestre->ano . $semestre->periodo . "%"];
        $turmasCG = DB::fetchAll($query, $param);

        return array_merge($ccm, $turmasCG);
    }
    
    private function syncDocentesEHorariosGrad(Turma $turma): void
    {
        $query = "SELECT DISTINCT M.codpes, P.nompes, V.codset, OT.diasmnocp, PH.horent, PH.horsai
                  FROM MINISTRANTE M, VINCULOPESSOAUSP V, PESSOA P, OCUPTURMA OT, PERIODOHORARIO PH
                  WHERE M.coddis = :coddis AND M.codtur = :codtur AND M.verdis = (SELECT max(M2.verdis) FROM MINISTRANTE M2 WHERE M2.coddis = M.coddis AND M2.codtur = M.codtur)
                  AND V.codpes = M.codpes AND P.codpes = M.codpes AND OT.coddis = M.coddis AND OT.codtur = M.codtur
                  AND OT.verdis = M.verdis AND PH.codperhor = OT.codperhor";
        
        $docentesHorarios = DB::fetchAll($query, ['coddis' => $turma->coddis, 'codtur' => $turma->codtur]);
        
        $docenteIds = [];
        $horarioIds = [];

        foreach ($docentesHorarios as $dh) {
            if ($dh['codset']) {
                $docente = Docente::updateOrCreate(['codpes' => $dh['codpes']], ['nompes' => $dh['nompes'], 'codset' => $dh['codset']]);
                $docenteIds[] = $docente->id;
            }

            $horario = Horario::firstOrCreate(['diasmnocp' => $dh['diasmnocp'], 'horent' => $dh['horent'], 'horsai' => $dh['horsai']]);
            $horarioIds[] = $horario->id;
        }

        $turma->docentes()->sync(array_unique($docenteIds));
        $turma->horarios()->sync(array_unique($horarioIds));
    }

    private function getPosGraduacaoTurmas(Semestre $semestre): array
    {
        $params = ['codund' => '45', 'tipfnc' => 'Docente', 'ibi' => 'IBI%', 'ano' => $semestre->ano, 'mesmin' => $semestre->periodo == 1 ? 1 : 7, 'mesmax' => $semestre->periodo == 1 ? 6 : 12];
        
        $queryIbi = "SELECT DISTINCT R32.sgldis, R32.numseqdis, R32.numofe FROM VINCULOPESSOAUSP V, PESSOA P, R32TURMINDOC R32, OFERECIMENTO O
                     WHERE V.codund = :codund AND V.tipfnc = :tipfnc AND P.codpes = V.codpes AND R32.codpes = V.codpes AND R32.sgldis LIKE :ibi
                     AND O.sgldis = R32.sgldis AND O.numseqdis = R32.numseqdis AND O.numofe = R32.numofe
                     AND YEAR(O.dtainiofe) = :ano AND MONTH(O.dtainiofe) BETWEEN :mesmin AND :mesmax";
        $ibi = DB::fetchAll($queryIbi, $params);

        $queryAre = "SELECT DISTINCT O.sgldis, O.numseqdis, O.numofe FROM DISCIPLINA D, OFERECIMENTO O
                     WHERE D.codare LIKE :codare AND O.sgldis = D.sgldis AND O.numseqdis = D.numseqdis
                     AND YEAR(O.dtainiofe) = :ano AND MONTH(O.dtainiofe) BETWEEN :mesmin AND :mesmax";
        $are = DB::fetchAll($queryAre, ['codare' => '45%', 'ano' => $semestre->ano, 'mesmin' => $params['mesmin'], 'mesmax' => $params['mesmax']]);
        
        return array_merge($ibi, $are);
    }
    
    private function buildValuesClause(array $data): string
    {
        $values = [];
        foreach ($data as $row) {
            $values[] = "('" . implode("','", array_values($row)) . "')";
        }
        return implode(',', $values);
    }
}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB as DBFacade;
use App\Models\Turma;
use App\Models\Semestre;

class UpdateNumMatriculados extends Command
{
    /**
     * A assinatura do comando.
     * Use {start?} e {end?} para filtrar o intervalo de semestres, no formato YYYYP (ex: 20241).
     */
    protected $signature = 'app:update-num-matriculados
                            {start? : O semestre inicial no formato YYYYP (ex: 20241)}
                            {end? : O semestre final no formato YYYYP (ex: 20242)}
                            {--nivel= : Filtra turmas pelo nivel (ex: "Graduação" ou "Pós Graduação")}';

    /**
     * A descrição do comando.
     */
    protected $description = 'Atualiza o número de alunos matriculados (nummtr) das turmas a partir do Replicado.';

    /**
     * Executa o comando.
     */
    public function handle(): int
    {
        $this->info('Iniciando a atualização do número de matriculados...');

        $start = $this->argument('start');
        $end = $this->argument('end') ?? $start;
        $nivel = $this->option('nivel');

        if ($start) {
            $this->line("<fg=yellow>Atualizando o intervalo de semestres de {$start} a {$end}.</>");
            $semestresToProcess = Semestre::where(DBFacade::raw("CONCAT(ano, periodo)"), '>=', $start)
                                          ->where(DBFacade::raw("CONCAT(ano, periodo)"), '<=', $end)
                                          ->orderBy('ano')->orderBy('periodo')->get();
        } else {
            $this->line('<fg=yellow>Nenhum período especificado. Atualizando apenas o semestre mais recente.</>');
            $semestresToProcess = Semestre::orderBy('ano', 'desc')->orderBy('periodo', 'desc')->limit(1)->get();
        }

        if ($semestresToProcess->isEmpty()) {
            $this->error('Nenhum semestre encontrado. Execute o app:sync-replicado-data antes.');
            return Command::FAILURE;
        }

        $query = Turma::whereIn('semestre_id', $semestresToProcess->pluck('id'));
        if ($nivel) {
            $query->where('nivel', $nivel);
        }
        $turmas = $query->get();

        if ($turmas->isEmpty()) {
            $this->warn('Nenhuma turma encontrada para os critérios informados. Nada a fazer.');
            return Command::SUCCESS;
        }

        $termInfo = $start
            ? 'semestres de ' . $start . ' a ' . $end
            : $semestresToProcess->first()->periodo . '/' . $semestresToProcess->first()->ano;
        $nivelInfo = $nivel ? " (nivel: {$nivel})" : '';
        $this->info("Período(s): {$termInfo}{$nivelInfo}");
        $this->info($turmas->count() . ' turma(s) serão processadas.');

        $bar = $this->output->createProgressBar($turmas->count());
        $bar->start();

        $falhas = 0;
        foreach ($turmas as $turma) {
            try {
                $turma->calcNumMatriculados();
                $turma->save();
            } catch (\Exception $e) {
                $falhas++;
                $bar->clear();
                $this->error("Falha ao atualizar a turma {$turma->codtur} da disciplina {$turma->coddis}: " . $e->getMessage());
                $bar->display();
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($falhas > 0) {
            $this->warn("Concluído com {$falhas} falha(s).");
        } else {
            $this->info('Atualização do número de matriculados concluída com sucesso para ' . $turmas->count() . ' turma(s).');
        }

        return Command::SUCCESS;
    }
}
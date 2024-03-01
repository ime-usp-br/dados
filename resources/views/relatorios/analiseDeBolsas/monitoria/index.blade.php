@extends('main')

@section('title', 'Relatório de Analise de Bolsas')

@section('content')
@parent
<div id="layout_conteudo">
    <div class="justify-content-center">
        <div class="col-md-12">
            <h1 class='text-center'>Relatório de Análise de Bolsas de Monitoria</h1>
            <h1 class='text-center mb-5'>{{$semestre->periodo}}° semestre de {{$semestre->ano}}</h1>

            <?php 
                $disciplinas = App\Models\Turma::whereBelongsTo($semestre)->get(['nomdis','coddis']);

                
                $disciplinasFiltradas = collect([]);

                foreach ($disciplinas as $disciplina) {
                    if (!$disciplinasFiltradas->has($disciplina->coddis)) {
                        $disciplinasFiltradas->put($disciplina->coddis, $disciplina->nomdis);
                    }
                }

                $disciplinas = $disciplinasFiltradas->sortKeys()->toArray();

            ?>
            @if(count($disciplinas) > 0)
                @foreach($disciplinas as $coddis=>$nomdis)
                    <table id="table" class="table table-bordered" style="font-size:12px;">
                        <thead>
                            <tr>
                                <th class="text-left" colspan="8">{{ $coddis." - ".$nomdis}}</th>
                            </tr>

                            <tr>
                                <th class="text-center" rowspan="2" style="vertical-align: middle;">Turma</th>
                                <th class="text-center" colspan="2" style="vertical-align: middle;">Créditos</th>
                                <th class="text-center" rowspan="2" style="vertical-align: middle;">Alunos matriculados</th>
                                <th class="text-center" rowspan="2" style="vertical-align: middle;">Horário</th>
                                <th class="text-center" rowspan="2" style="vertical-align: middle;">Nível</th>
                                <th class="text-center" rowspan="2" style="vertical-align: middle;">Histórico de alunos <br>matriculados nos três<br> períodos anteriores</th>
                                <th class="text-center" rowspan="2" style="vertical-align: middle;">Observações</th>
                            </tr>
                            <tr class="text-center">
                                <th style="vertical-align: middle;">Teórica</th>
                                <th style="vertical-align: middle;">Prática/Trab</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(App\Models\Turma::whereBelongsTo($semestre)
                                                    ->where("coddis",$coddis)
                                                    ->get()->sortBy("codtur") as $turma)
                                
                                                    <tr>
                                    <td class="text-center" style="vertical-align: middle;">{{ $turma->codtur }}</td>
                                    <td class="text-center" style="vertical-align: middle;">{{ $turma->creaul }}</td>
                                    <td class="text-center" style="vertical-align: middle;">{{ $turma->cretrb }}</td>
                                    <td class="text-center" style="vertical-align: middle;">{{ $turma->nummtr }}</td>
                                    <td class="text-center" style="vertical-align: middle;white-space: nowrap;">
                                        @foreach($turma->horarios->sortBy([
                                            function ($a, $b) {
                                                $dias = ["seg"=>1,"ter"=>2,"qua"=>3,"qui"=>4,"sex"=>5,"sab"=>6];
                                                if($a["diasmnocp"] == $b["diasmnocp"]){
                                                    return $a["horent"] <=> $b["horent"];
                                                }
                                                return $dias[$a["diasmnocp"]] <=> $dias[$b["diasmnocp"]];
                                            }
                                        ]) as $horario)
                                            {{ $horario["diasmnocp"]." ".$horario["horent"]." ".$horario["horsai"] }}<br>
                                        @endforeach
                                    </td>
                                    <td class="text-center" style="vertical-align: middle;">{{ $turma->nivel }}</td>
                                    <?php
                                        $query = App\Models\Turma::where("coddis",$turma->coddis)
                                                    ->where("id", "!=", $turma->id)
                                                    ->whereHas("semestre", function($query)use($turma){
                                                        $query->where("ano", "<",$turma->semestre->ano);
                                                        if($turma->semestre->periodo == 2){
                                                            $query->orWhere(function($query2)use($turma){
                                                                $query2->where("ano",$turma->semestre->ano)
                                                                        ->where("periodo",1);
                                                            });
                                                        }
                                                    });
                                        if($turma->nivel == "Graduação"){
                                            $query = $query->where("codtur", "like", "%".substr($turma->codtur,4,3));
                                        }

                                        $ts = $query->get()->sortByDesc("codtur")->slice(0,3);
                                    ?>
                                    <td class="text-center" style="vertical-align: middle;">
                                        @if(count($ts) > 0)
                                            @foreach($ts as $t)
                                                {{ $t->semestre->periodo."/".$t->semestre->ano.": ".$t->nummtr }}<br>
                                            @endforeach
                                        @else
                                            Indisponível
                                        @endif
                                    </td>
                                    <td class="text-center" style="vertical-align: middle;">
                                    @if($turma->dobradinha()->exists())
                                        Provável dobradinha com 
                                        @foreach($turma->dobradinha->turmas()->where("id","!=",$turma->id)->get() as $t)
                                            @if($t->nivel == "Graduação")
                                                {{ $t->coddis."(".$t->codtur.") "}}
                                            @elseif($t->nivel == "Pós Graduação")
                                                {{ $t->coddis." "}}
                                            @endif
                                        @endforeach
                                    @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endsection
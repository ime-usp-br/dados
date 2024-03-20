@extends('main')

@section('title', 'Relatório Carga Didática')

@section('content')
@parent
<div id="layout_conteudo">
    <div class="justify-content-center">
        <div class="col-md-12">
            <h1 class='text-center'>Relatório Carga Didática do {{$departamento}} por Disciplina</h1>
            
            @if(count($turmas) > 0)

                <table id="table_cdp" class="table table-bordered" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th class="text-center" rowspan="2" style="vertical-align: middle;">Nº USP</th>
                            <th class="text-center" rowspan="2" style="vertical-align: middle;">Docente</th>
                            <th class="text-center" rowspan="2" style="vertical-align: middle;">Sigla</th>
                            <th class="text-center" rowspan="2" style="vertical-align: middle;">Nome da disciplina</th>
                            <th class="text-center" rowspan="2" style="vertical-align: middle;">Turma</th>
                            <th class="text-center" rowspan="2" style="vertical-align: middle;">Ano</th>
                            <th class="text-center" rowspan="2" style="vertical-align: middle;">Semestre</th>
                            <th class="text-center" colspan="2" style="vertical-align: middle;">Créditos</th>
                            <th class="text-center" rowspan="2" style="vertical-align: middle;">Alunos matriculados</th>
                            <th class="text-center" rowspan="2" style="vertical-align: middle;">Horário</th>
                            <th class="text-center" rowspan="2" style="vertical-align: middle;">Nível</th>
                            <th class="text-center" rowspan="2" style="vertical-align: middle;">Histórico de alunos <br>matriculados nos cinco<br> períodos anteriores</th>
                            <th class="text-center" rowspan="2" style="vertical-align: middle;">Observações</th>
                        </tr>
                        <tr class="text-center">
                            <th style="vertical-align: middle;">Teórica</th>
                            <th style="vertical-align: middle;">Prática/Trab</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($turmas as $turma)
                            @foreach($turma->docentes as $docente)
                                <tr>
                                    <td class="text-center" style="vertical-align: middle;">{{ $docente->codpes }}</td>
                                    <td class="text-center" style="vertical-align: middle;">{{ $docente->nompes }}</td>
                                    <td class="text-center" style="vertical-align: middle;">{{ $turma->coddis }}</td>
                                    <td class="text-center" style="vertical-align: middle;">{{ $turma->nomdis }}</td>
                                    <td class="text-center" style="vertical-align: middle;">{{ $turma->codtur }}</td>
                                    <td class="text-center" style="vertical-align: middle;">{{ $turma->semestre->ano }}</td>
                                    <td class="text-center" style="vertical-align: middle;">{{ $turma->semestre->periodo }}</td>
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

                                        $ts = $query->get()->sortByDesc("codtur")->slice(0,5);
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
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection

@section('javascripts_bottom')
  @parent
  <script>
    $(document).ready(function() {
        $('#table_cdp').DataTable({
            dom: 'B',
            buttons: {
                buttons:[{
                    extend: 'excel',
                    text:'Exportar para Excel',
                    className: 'btn-outline-dark',
                    exportOptions: {
                        format: {
                            body: function (data, row, column, node) {
                                let newData = data.replace(/<br\s*\/?>/gi, '; ');
                                while (newData.indexOf('  ') !== -1) {
                                    newData = newData.replace(/  /g, ' ');
                                }
                                return newData;
                            }
                        }
                    }
                }],
                dom:{
                    button:{
                        className:'btn'
                    }
                }
            },
            paging:false,
            order:[]
        });
    });
  </script>
@endsection
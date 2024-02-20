@extends('main')

@section('title', 'Relatório Carga Didática Docente')

@section('content')
@parent
<div id="layout_conteudo">
    <div class="justify-content-center">
        <div class="col-md-12">
            <h1 class='text-center'>Relatório Carga Didática dos Docentes do {{$departamento}}</h1>
            <h1 class='text-center mb-5'>{{$semestre}}° semestre de {{$ano}}</h1>

            @if (count($dados) > 0)
                @foreach($dados as $codpes=>$docente)
                
                <h4 class='text-center mt-5'>{{$docente["nompes"]}} ({{$codpes}}) </h1>
                <table id="table_id" class="table table-bordered" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th class="text-center" rowspan="2" style="vertical-align: middle;">Sigla da Disciplina</th>
                            <th class="text-center" rowspan="2" style="vertical-align: middle;">Nome da Disciplina</th>
                            <th class="text-center" colspan="2" style="vertical-align: middle;">Carga horária</th>
                            <th class="text-center" rowspan="2" style="vertical-align: middle;">Nível</th>
                            <th class="text-center" rowspan="2" style="vertical-align: middle;">Inicio</th>
                            <th class="text-center" rowspan="2" style="vertical-align: middle;">Fim</th>
                            <th class="text-center" rowspan="2" style="vertical-align: middle;">Turma</th>
                            <th class="text-center" rowspan="2" style="vertical-align: middle;">Alunos matriculados</th>
                            <th class="text-center" rowspan="2" style="vertical-align: middle;">Horário</th>
                        </tr>
                        <tr class="text-center">
                            <th>Teórica</th>
                            <th>Prática</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($docente["disciplinas"] as $coddis=>$disciplina)
                            <tr>
                                <?php $primeiraTurma = true;?>
                                <td class="text-center" rowspan="{{ count($disciplina['turmas']) }}" style="vertical-align: middle;">{{ $coddis }}</td>
                                <td class="text-center" rowspan="{{ count($disciplina['turmas']) }}" style="vertical-align: middle;width:500px;">{{ $disciplina["nomdis"] }}</td>
                                <td class="text-center" rowspan="{{ count($disciplina['turmas']) }}" style="vertical-align: middle;">{{ $disciplina["creaul"] }}</td>
                                <td class="text-center" rowspan="{{ count($disciplina['turmas']) }}" style="vertical-align: middle;">{{ $disciplina["cretrb"] }}</td>
                                <td class="text-center" rowspan="{{ count($disciplina['turmas']) }}" style="vertical-align: middle;">{{ $disciplina["nivel"] }}</td>
                                <td class="text-center" rowspan="{{ count($disciplina['turmas']) }}" style="vertical-align: middle;">{{ $disciplina["dtainiaul"] }}</td>
                                <td class="text-center" rowspan="{{ count($disciplina['turmas']) }}" style="vertical-align: middle;">{{ $disciplina["dtafimaul"] }}</td>
                                @foreach($disciplina['turmas'] as $codtur=>$turma)
                                    @if(!$primeiraTurma)
                                        <tr>
                                    @endif
                                        <td class="text-center" style="vertical-align: middle;">{{ $codtur }}</td>
                                        <td class="text-center" style="vertical-align: middle;">{{ $turma["nummtr"] }}</td>
                                        <td class="text-center" style="vertical-align: middle;">
                                            @foreach($turma["horarios"] as $horario)
                                                {{ $horario["diasmnocp"]." ".$horario["horent"]." ".$horario["horsai"] }}<br>
                                            @endforeach
                                        </td>
                                    </tr>
                                    <?php $primeiraTurma = false;?>
                                @endforeach
                        @endforeach
                    </tbody>
                </table>

                @endforeach
            @endif
        </div>
    </div>
</div>
@endsection
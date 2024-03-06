@extends('main')

@section('title', 'Relatório de Discentes Ingressantes')

@section('content')
@parent
<div id="layout_conteudo">
    <div class="justify-content-center">
        <div class="col-md-12">
            <h1 class='text-center'>Relatório de Discentes Ingressantes</h1>
            
            @if(count($alunos) > 0)
                <table id="table_alunos" class="table table-bordered" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th class="text-center" style="vertical-align: middle;">Nº USP</th>
                            <th class="text-center" style="vertical-align: middle;">Nome</th>
                            <th class="text-center" style="vertical-align: middle;">Data de nascimento</th>
                            <th class="text-center" style="vertical-align: middle;">Raça/Cor</th>
                            <th class="text-center" style="vertical-align: middle;">Sexo</th>
                            <th class="text-center" style="vertical-align: middle;">Status</th>
                            <th class="text-center" style="vertical-align: middle;">Tipo do ingresso</th>
                            <th class="text-center" style="vertical-align: middle;">Classificação no ingresso</th>
                            <th class="text-center" style="vertical-align: middle;">Motivo do encerramento</th>
                            <th class="text-center" style="vertical-align: middle;">Data do ingresso</th>
                            <th class="text-center" style="vertical-align: middle;">Data do encerramento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $status = [
                                'A' => 'Ativo',
                                'E' => 'Encerrado',
                                'T' => 'Trancado',
                                'R' => 'Reativado',
                                'S' => 'Suspenso',
                                'P' => 'Pendente',
                                'H' => 'Histórico',
                                'EH' => 'Encerramento de Habilitação'
                            ];
                        ?>
                        @foreach($alunos as $aluno)
                            <tr>
                                <td>{{$aluno['codpes']}}</td>
                                <td>{{$aluno['nompes']}}</td>
                                <td>{{$aluno['dtanas']}}</td>
                                <td>{{$aluno['raccor']}}</td>
                                <td>{{$aluno['sexpes']}}</td>
                                <td>{{$status[$aluno['stapgm']]}}</td>
                                <td>{{$aluno['tiping']}}</td>
                                <td>{{$aluno['clsing']}}</td>
                                <td>{{$aluno['tipencpgm']}}</td>
                                <td>{{$aluno['dtaing']}}</td>
                                <td>{{$aluno['dtafim']}}</td>
                            </tr>
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
        $('#table_alunos').DataTable({
            dom: 'B',
            buttons: {
                buttons:[{
                    extend: 'excel',
                    text:'Exportar para Excel',
                    className: 'btn-outline-dark'
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
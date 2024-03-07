@extends('main')

@section('title', 'Relatório de Estabilidade Discente')

@section('content')
@parent
<div id="layout_conteudo">
    <div class="justify-content-center">
        <div class="col-md-12">
            <h1 class='text-center'>Relatório de Estabilidade Discente</h1>
            <h2 class='text-center'>{{ $curso['nomcur'] }}</h2>
            <h3 class='text-center'>
                @if($curso['codhab']>4)
                    {{ $curso['nomhab'].' - ' }}
                @endif
                {{ ucfirst($curso['perhab']) }}
            </h3>
            
            <table id="table_alunos" class="table table-bordered" style="font-size:12px;">
                <thead>
                    <tr>
                        <th class="text-center" style="vertical-align: middle;">Curso</th>
                        <th class="text-center" style="vertical-align: middle;">Ano</th>
                        <th class="text-center" style="vertical-align: middle;">Formadas</th>
                        <th class="text-center" style="vertical-align: middle;">Formados</th>
                        <th class="text-center" style="vertical-align: middle;">Transferidas</th>
                        <th class="text-center" style="vertical-align: middle;">Transferidos</th>
                        <th class="text-center" style="vertical-align: middle;">Outros Feminino</th>
                        <th class="text-center" style="vertical-align: middle;">Outros Masculino</th>
                        <th class="text-center" style="vertical-align: middle;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dados as $ano=>$dado)
                        <tr>
                            <td>{{ $curso['codcur']." - ".$curso['codhab'].": ".$curso['nomcur'] }}</td>
                            <td>{{ $ano }}</td>
                            <td>{{ $dado['Formadas'] }}</td>
                            <td>{{ $dado['Formados'] }}</td>
                            <td>{{ $dado['Transferidas'] }}</td>
                            <td>{{ $dado['Transferidos'] }}</td>
                            <td>{{ $dado['Outros Feminino'] }}</td>
                            <td>{{ $dado['Outros Masculino'] }}</td>
                            <td>{{ $dado['Total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
@extends('main')

@section('title', 'Logs')

@section('content')
@parent
<div id="layout_conteudo">
    <div class="justify-content-center">
        <div class="col-md-12">
            <h1 class='text-center'>Logs do Sistema</h1>
            
            <table id="table_logs" class="table table-bordered" style="font-size:12px;">
                <thead>
                    <tr>
                        <th class="text-center" style="vertical-align: middle;">ID</th>
                        <th class="text-center" style="vertical-align: middle;">Operação</th>
                        <th class="text-center" style="vertical-align: middle;">Status</th>
                        <th class="text-center" style="vertical-align: middle;">Timestamp</th>
                        <th class="text-center" style="vertical-align: middle;">Usuário</th>
                        <th class="text-center" style="vertical-align: middle;">Descrição</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>{{ $log->operacao }}</td>
                            <td>{{ $log->status }}</td>
                            <td>{{ $log->created_at }}</td>
                            <td>{{ $log->usuario->codpes." - ".$log->usuario->name }}</td>
                            <td>{{ $log->descricao }}</td>
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
        $('#table_logs').DataTable({
            dom: 'Btp',
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
            paging:true,
            pagingType: 'simple_numbers',
            order:[]
        });
    });
  </script>
@endsection

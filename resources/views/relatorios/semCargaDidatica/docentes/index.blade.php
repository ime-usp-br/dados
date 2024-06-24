@extends('main')

@section('title', 'Relatório Docentes sem Carga Didática')

@section('content')
@parent
<div id="layout_conteudo">
    <div class="justify-content-center">
        <div class="col-md-12">
            <h1 class='text-center'>Relatório Docentes sem Carga Didática</h1>
            <h1 class='text-center mb-5'>{{$semestre->periodo}}° semestre de {{$semestre->ano}}</h1>
            @if(count($docentes) > 0)
                <table id="table_id" class="table table-bordered" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th class="text-center" style="vertical-align: middle;">N° USP</th>
                            <th class="text-center" style="vertical-align: middle;">Nome</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($docentes as $docente)
                            <tr>
                                <td class="text-center" style="vertical-align: middle;">{{ $docente->codpes }}</td>
                                <td class="text-center" style="vertical-align: middle;">{{ $docente->nompes }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
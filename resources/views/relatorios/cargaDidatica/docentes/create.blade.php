@extends('main')

@section('title', 'Relatório Carga Didática Docente')

@section('content')
@parent
<div id="layout_conteudo">
    <div class="justify-content-center">
        <div class="col-md-12">
            <h1 class='text-center mb-5'>Relatório Carga Didática dos Docentes</h1>

            <form method="GET" action="{{ route('relatorios.cargaDidaticaDocentes') }}" enctype='multipart/form-data'>
                @include('relatorios.cargaDidatica.docentes.partials.form')
            </form>
        </div>
    </div>
</div>
@endsection
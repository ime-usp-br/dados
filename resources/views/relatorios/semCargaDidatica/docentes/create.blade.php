@extends('main')

@section('title', 'Relatório Docentes sem Carga Didática')

@section('content')
@parent
<div id="layout_conteudo">
    <div class="justify-content-center">
        <div class="col-md-12">
            <h1 class='text-center mb-5'>Relatório Docentes sem Carga Didática</h1>

            <form method="GET" action="{{ route('relatorios.semCargaDidaticaDocentes') }}" enctype='multipart/form-data'>
                @include('relatorios.semCargaDidatica.docentes.partials.form')
            </form>
        </div>
    </div>
</div>
@endsection
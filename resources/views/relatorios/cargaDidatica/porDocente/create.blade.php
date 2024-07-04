@extends('main')

@section('title', 'Relatório Carga Didática Por Docente')

@section('content')
@parent
<div id="layout_conteudo">
    <div class="justify-content-center">
        <div class="col-md-12">
            <h1 class='text-center mb-5'>Relatório Carga Didática por Docente</h1>

                @include('relatorios.cargaDidatica.porDocente.modals.addInstructor')

            <form method="GET" action="{{ route('relatorios.cargaDidaticaPorDocente') }}" enctype='multipart/form-data'>
                @include('relatorios.cargaDidatica.porDocente.partials.form')
            </form>
        </div>
    </div>
</div>
@endsection
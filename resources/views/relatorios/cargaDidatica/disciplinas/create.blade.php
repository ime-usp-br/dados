@extends('main')

@section('title', 'Relatório Carga Didática por Disciplina')

@section('content')
@parent
<div id="layout_conteudo">
    <div class="justify-content-center">
        <div class="col-md-12">
            <h1 class='text-center mb-5'>Relatório Carga Didática por Disciplina</h1>

            <form method="GET" action="{{ route('relatorios.cargaDidaticaDisciplinas') }}" enctype='multipart/form-data'>
                @include('relatorios.cargaDidatica.disciplinas.partials.form')
            </form>
        </div>
    </div>
</div>
@endsection
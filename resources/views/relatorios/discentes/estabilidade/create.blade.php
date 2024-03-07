@extends('main')

@section('title', 'Relatório de Estabilidade Discente')

@section('content')
@parent
<div id="layout_conteudo">
    <div class="justify-content-center">
        <div class="col-md-12">
            <h1 class='text-center mb-5'>Relatório de Estabilidade Discente</h1>

            <form method="GET" action="{{ route('relatorios.discentesEstabilidade') }}" enctype='multipart/form-data'>
                @include('relatorios.discentes.estabilidade.partials.form')
            </form>
        </div>
    </div>
</div>
@endsection
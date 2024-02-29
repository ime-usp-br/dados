@extends('main')

@section('title', 'Relatório de Analise de Bolsas')

@section('content')
@parent
<div id="layout_conteudo">
    <div class="justify-content-center">
        <div class="col-md-12">
            <h1 class='text-center mb-5'>Relatório de Análise de Bolsas de Monitoria</h1>

            <form method="GET" action="{{ route('relatorios.analiseDeBolsasMonitoria') }}" enctype='multipart/form-data'>
                @include('relatorios.analiseDeBolsas.monitoria.partials.form')
            </form>
        </div>
    </div>
</div>
@endsection
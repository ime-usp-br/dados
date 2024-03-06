@extends('main')

@section('title', 'Relatório de Discentes Ingressantes')

@section('content')
@parent
<div id="layout_conteudo">
    <div class="justify-content-center">
        <div class="col-md-12">
            <h1 class='text-center mb-5'>Relatório de Discentes Ingressantes</h1>

            <form method="GET" action="{{ route('relatorios.discentesIngressantes') }}" enctype='multipart/form-data'>
                @include('relatorios.discentes.ingressantes.partials.form')
            </form>
        </div>
    </div>
</div>
@endsection
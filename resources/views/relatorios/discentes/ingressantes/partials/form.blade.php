<div class="row custom-form-group justify-content-center">
    <div class="col-sm-2  text-sm-right" style="min-width:150px">
        <label for="departamento">Cursos *</label>
    </div>
    <div class="col-sm-2" style="min-width:100px">
        <select class="custom-form-control" type="text" name="codcurhab"
            id="codcurhab" style="width:400px"
        >
            <option value="" selected></option>

            @foreach ($cursos as $curso)
                <option value="{{ $curso['codcur'].'-'.$curso['codhab'] }}">
                    @if($curso['codhab']<=4)    
                        {{ $curso['codcur']." - ".$curso['codhab'].' - '.$curso['nomcur'] }}
                    @else
                        {{ $curso['codcur']." - ".$curso['codhab'].' - '.$curso['nomcur'].' - '.$curso['nomhab'] }}
                    @endif
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row custom-form-group justify-content-center">
    <div class="col-sm-2  text-sm-right" style="min-width:150px">
        <label for="ano">Ano *</label>
    </div>
    <div class="col-sm-2" style="min-width:100px">
        <select class="custom-form-control" type="text" name="ano"
            id="ano" style="width:150px"
        >
            <option value="" selected></option>

            @foreach (range(date("Y"), 2000) as $ano)
                <option value="{{ $ano }}">{{ $ano }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row custom-form-group justify-content-center">
    <div class="text-center mt-3">
        <button type="submit" class="btn btn-outline-dark">
            Consultar
        </button>
    </div>
</div>
<div class="row custom-form-group justify-content-center">
    <div class="col-sm-2  text-sm-right" style="min-width:150px">
        <label for="departamento">Departamento *</label>
    </div>
    <div class="col-sm-2" style="min-width:100px">
        <select class="custom-form-control" type="text" name="departamento"
            id="departamento" style="width:150px"
        >
            <option value="" selected></option>
            <option value="TODOS">Todos os departamentos</option>

            @foreach ([
                'MAT',
                'MAC',
                'MAP',
                'MAE'
                ] as $departamento)
                <option value="{{ $departamento }}">{{ $departamento }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row custom-form-group justify-content-center">
    <div class="col-sm-2  text-sm-right" style="min-width:150px">
        <label for="periodo_inicial">Período Inicial *</label>
    </div>
    <div class="col-sm-2" style="min-width:100px">
        <select class="custom-form-control" type="text" name="periodo_inicial"
            id="ano" style="width:150px"
        >
            <option value="" selected></option>

            @foreach (range(date("Y"), 2000) as $ano)
                @if(!($ano == date("Y") and date("m") < 6))
                    <option value="{{ $ano.'2' }}">{{ $ano }} - 2° Semestre</option>
                @endif
                <option value="{{ $ano.'1' }}">{{ $ano }} - 1° Semestre</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row custom-form-group justify-content-center">
    <div class="col-sm-2  text-sm-right" style="min-width:150px">
        <label for="periodo_final">Período Final</label>
    </div>
    <div class="col-sm-2" style="min-width:100px">
        <select class="custom-form-control" type="text" name="periodo_final"
            id="semestre" style="width:150px"
        >
            <option value="" selected></option>

            @foreach (range(date("Y"), 2000) as $ano)
                @if(!($ano == date("Y") and date("m") < 6))
                    <option value="{{ $ano.'2' }}">{{ $ano }} - 2° Semestre</option>
                @endif
                <option value="{{ $ano.'1' }}">{{ $ano }} - 1° Semestre</option>
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
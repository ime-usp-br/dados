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
    <div class="col-sm-2  text-sm-right" style="min-width:150px">
        <label for="semestre">Período *</label>
    </div>
    <div class="col-sm-2" style="min-width:100px">
        <select class="custom-form-control" type="text" name="semestre"
            id="semestre" style="width:150px"
        >
            <option value="" selected></option>

            @foreach ([
                        '1° Semestre'=>1,
                        '2° Semestre'=>2,
                     ] as $period=>$value)
                <option value="{{ $value }}">{{ $period }}</option>
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
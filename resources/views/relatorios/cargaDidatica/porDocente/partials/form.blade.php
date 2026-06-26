

<div class="row custom-form-group justify-content-center">
    <div class="col-sm-2  text-sm-right" style="min-width:150px">
        <label for="departamento">Docente *</label>
    </div>
    <div class="col-sm-2" style="min-width:100px">

    <div id="novos-instrutores"></div>
        <input id="count-new-instructor" value=0 type="hidden" disabled>
        <a class="btn btn-outline-dark" id="btn-addDocente" 
            data-toggle="modal" data-target="#addInstructorModal"
            title="Adicionar Docente">
            Adicionar Docente
        </a>

        <script>
            function removeInstrutor(){
                document.getElementById("instrutor").remove();
                document.getElementById("btn-addDocente").disabled = false;
            }
        </script>
    </div>
    </div>
</div>

<div class="row custom-form-group justify-content-center">
    <div class="col-sm-2  text-sm-right" style="min-width:150px">
        <label for="periodoInicial">Período Inicial *</label>
    </div>
    <div class="col-sm-2" style="min-width:100px">
        <select class="custom-form-control" type="text" name="periodoInicial"
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
        <label for="periodoFinal">Período Final</label>
    </div>
    <div class="col-sm-2" style="min-width:100px">
        <select class="custom-form-control" type="text" name="periodoFinal"
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
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Turma;
use App\Models\Docente;
use App\Models\Horario;
use App\Models\Dobradinha;
use Uspdev\Replicado\DB;
use Carbon\Carbon;

class Turma extends Model
{
    use HasFactory;

    protected $fillable = [
        'codtur',
        'nivel',
        'nomdis',
        'coddis',
        'nummtr',
        'creaul',
        'cretrb',
        'dtainiaul',
        'dtafimaul',
        'semestre_id',
        'dobradinha_id',
    ];

    protected $casts = [
        'dtainitur' => 'date:d/m/Y',
        'dtafimtur' => 'date:d/m/Y',
    ];

    public function setDtainiaulAttribute($value)
    {
        $this->attributes['dtainiaul'] = Carbon::createFromFormat('d/m/Y', $value)->startOfDay();
    }

    public function setDtafimaulAttribute($value)
    {
        $this->attributes['dtafimaul'] = Carbon::createFromFormat('d/m/Y', $value)->endOfDay();
    }

    public function getDtainiaulAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d/m/Y') : '';
    }

    public function getDtafimaulAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d/m/Y') : '';
    }

    public function semestre()
    {
        return $this->belongsTo(Semestre::class, "semestre_id");
    }

    public function docentes()
    {
        return $this->belongsToMany(Docente::class);
    }

    public function horarios()
    {
        return $this->belongsToMany(Horario::class);
    }

    public function dobradinha()
    {
        return $this->belongsTo(Dobradinha::class, "dobradinha_id");
    }

    public function verificaColisaoHorario($turma)
    {
        $t1di = Carbon::createFromFormat('d/m/Y', $this->dtainiaul);
        $t1df = Carbon::createFromFormat('d/m/Y', $this->dtafimaul);
        $t2di = Carbon::createFromFormat('d/m/Y', $turma->dtainiaul);
        $t2df = Carbon::createFromFormat('d/m/Y', $turma->dtafimaul);

        foreach($this->horarios as $cs1){
            foreach($turma->horarios as $cs2){
                if($cs1->diasmnocp == $cs2->diasmnocp){
                    if(!($cs1->horsai <= $cs2->horent or $cs1->horent >= $cs2->horsai) and !($t1df <= $t2di or $t1di >= $t2df)){
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function calcNumMatriculados()
    {
        $semestre = $this->semestre;

        if (!$semestre) {
            $this->nummtr = 0;
            return;
        }

        if ($this->nivel === 'Pós Graduação') {
            // O codtur das turmas de pós é montado como ano + periodo + numofe (zero-padded à direita).
            $numofe = ltrim(substr((string) $this->codtur, 5), '0') ?: '0';
            $mesmin = $semestre->periodo == 1 ? 1 : 7;
            $mesmax = $semestre->periodo == 1 ? 6 : 12;

            $query  = " SELECT COUNT(DISTINCT M.codpes) AS TOTAL";
            $query .= " FROM R41PGMMATTUR AS M";
            $query .= " JOIN OFERECIMENTO AS O ON O.sgldis = M.sgldis AND O.numseqdis = M.numseqdis AND O.numofe = M.numofe";
            $query .= " WHERE M.sgldis = :sgldis";
            $query .= " AND M.numofe = :numofe";
            $query .= " AND M.stamtrpgmofe IN ('P', 'A', 'D')";
            $query .= " AND YEAR(O.dtainiofe) = :ano";
            $query .= " AND MONTH(O.dtainiofe) BETWEEN :mesmin AND :mesmax";
            $param = [
                'sgldis'  => $this->coddis,
                'numofe'  => $numofe,
                'ano'     => $semestre->ano,
                'mesmin'  => $mesmin,
                'mesmax'  => $mesmax,
            ];
        } else {
            $query  = " SELECT (T.nummtr + T.nummtrturcpl + T.nummtropt + T.nummtrecr + T.nummtroptlre) AS TOTAL";
            $query .= " FROM TURMAGR AS T";
            $query .= " WHERE T.coddis = :coddis";
            $query .= " AND T.codtur = :codtur";
            $query .= " AND T.verdis = (SELECT MAX(T2.verdis) FROM TURMAGR T2 WHERE T2.coddis = T.coddis AND T2.codtur = T.codtur)";
            $param = [
                'coddis' => $this->coddis,
                'codtur' => $this->codtur,
            ];
        }

        $res = DB::fetchAll($query, $param);

        $total = $res && isset($res[0]['TOTAL']) ? $res[0]['TOTAL'] : null;
        $this->nummtr = $total !== null ? (int) $total : 0;
    }

    public function rastrearCriarDobradinhas()
    {
        $conflitos = [];
        $dobradinha = false;
        $turmas = Turma::whereBelongsTo($this->semestre)->whereHas("docentes", function($query){
            $query->whereIn("id",$this->docentes()->pluck("id")->toArray());
        })->where("id","!=",$this->id)->get();

        foreach($turmas as $sc2){
            if($this->verificaColisaoHorario($sc2) and $this->docentes->diff($sc2->docentes)->isEmpty() and $sc2->docentes->diff($this->docentes)->isEmpty()){
                array_push($conflitos, $sc2->id);
                if($sc2->dobradinha()->exists()){
                    $dobradinha = $sc2->dobradinha;
                }
            }
        }

        if($conflitos){
            if(!$dobradinha){
                $dobradinha = Dobradinha::create();
                foreach($conflitos as $id){
                    $sc2 = Turma::find($id); 
                    $sc2->dobradinha()->associate($dobradinha); 
                    $sc2->save();
                }
            }
            $this->dobradinha()->associate($dobradinha);
            $this->save();
        }
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Turma;

class Docente extends Model
{
    use HasFactory;

    protected $fillable = [
        'codpes',
        'nompes',
        'codset',
    ];

    public function turmas()
    {
        return $this->belongsToMany(Turma::class);
    }

    public function getNomeAbreviado()
    {
        $pattern = '/ de | do | dos | da | das | e /i';
        $nome = preg_replace($pattern,' ',$this->nompes);
        $nome = explode(' ', $nome);
        
        $nomes_meio = ' ';
        
        if(count($nome) > 2){
            for($x=1;$x<count($nome)-1;$x++){
                $nomes_meio .= $nome[$x][0].". ";
            }
        }
        
        $nomeabreviado = array_shift($nome).$nomes_meio.array_pop($nome);
        
        return $nomeabreviado;
    }
}

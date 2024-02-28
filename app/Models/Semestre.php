<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Turma;

class Semestre extends Model
{
    use HasFactory;

    protected $fillable = [
        'ano',
        'periodo',
    ];
    public function turmas()
    {
        return $this->hasMany(Turma::class);
    }

}

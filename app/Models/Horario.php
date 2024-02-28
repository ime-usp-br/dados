<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Turma;

class Horario extends Model
{
    use HasFactory;

    protected $fillable = [
        'diasmnocp',
        'horent',
        'horsai',
    ];

    public function turmas()
    {
        return $this->belongsToMany(Turma::class);
    }
}

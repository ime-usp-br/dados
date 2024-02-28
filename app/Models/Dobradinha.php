<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Turma;

class Dobradinha extends Model
{
    use HasFactory;

    public function turmas()
    {
        return $this->hasMany(Turma::class);
    }
}

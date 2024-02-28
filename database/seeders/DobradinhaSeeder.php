<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Semestre;
use App\Models\Turma;
use App\Models\Dobradinha;

class DobradinhaSeeder extends Seeder
{
    public function run(): void
    {
        foreach(Semestre::all() as $semestre){
            foreach(Turma::whereBelongsTo($semestre)->get() as $turma){
                $turma = $turma->fresh();
                if(!$turma->dobradinha){
                    $turma->rastrearCriarDobradinhas();
                }
            }
        }
    }
}

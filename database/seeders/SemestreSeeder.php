<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Semestre;

class SemestreSeeder extends Seeder
{
    public function run(): void
    {        
        $mes = now()->format("m");
        $anoAtual = now()->format("Y");
        $periodoAtual = ($mes >= 1 and $mes) <= 6 ? 1 : 2;

        foreach(range($anoAtual, 2000) as $ano){
            $periodos = [1];

            if(($anoAtual == $ano and $periodoAtual == 2) or ($ano < $anoAtual)){
                $periodos[] = 2;
            }

            foreach($periodos as $periodo){
                Semestre::create([
                    "ano" => $ano,
                    "periodo" => $periodo
                ]);
            }
        }
    }
}

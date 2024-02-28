<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Docente;
use Uspdev\Replicado\DB;

class DocenteSeeder extends Seeder
{
    public function run(): void
    {
        $query = " select distinct P.nompes, V.codpes, V.codset";
        $query .= " from VINCULOPESSOAUSP as V, PESSOA as P";
        $query .= " where V.codund = :codund";
        $query .= " and V.codset in (1664,1665,1666,1667)";
        $query .= " and V.tipfnc = :tipfnc";
        $query .= " and P.codpes = V.codpes";
        $query .= " order by nompes asc";
        $param = [
            'codund' => '45',
            'tipfnc' => 'Docente'
        ];

        $respostas = DB::fetchAll($query, $param);

        foreach($respostas as $r){
            Docente::create($r);
        }
    }
}

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RelatoriosController;
use App\Http\Controllers\LogsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('main');
})->name("home");

Route::get("/relatorios/cargadidatica/disciplinas", [RelatoriosController::class, "cargaDidaticaDisciplinas"])->name("relatorios.cargaDidaticaDisciplinas");
Route::get("/relatorios/cargadidatica/docentes", [RelatoriosController::class, "cargaDidaticaDocentes"])->name("relatorios.cargaDidaticaDocentes");
Route::get("/relatorios/analisedebolsas/monitoria", [RelatoriosController::class, "analiseDeBolsasMonitoria"])->name("relatorios.analiseDeBolsasMonitoria");
Route::get("/relatorios/sistemamonitoriapdf/download", [RelatoriosController::class, "downloadRelatorioSistemaMonitoria"])->name("relatorios.monitoriaPDF");
Route::get("/relatorios/discentes/ingressantes", [RelatoriosController::class, "discentesIngressantes"])->name("relatorios.discentesIngressantes");
Route::get("/relatorios/discentes/estabilidade", [RelatoriosController::class, "discentesEstabilidade"])->name("relatorios.discentesEstabilidade");
Route::get("/logs", [LogsController::class, "index"])->name("logs.index");

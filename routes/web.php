<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RelatoriosController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\APIAcessoController;
use App\Http\Controllers\NotaController;
use App\Http\Controllers\DocenteController;

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

Route::get("/relatorios/semcargadidatica/docentes", [RelatoriosController::class, "semCargaDidaticaDocentes"])->name("relatorios.semCargaDidaticaDocentes");
Route::get("/relatorios/cargadidatica/disciplinas", [RelatoriosController::class, "cargaDidaticaDisciplinas"])->name("relatorios.cargaDidaticaDisciplinas");
Route::get("/relatorios/cargadidatica/docentes", [RelatoriosController::class, "cargaDidaticaDocentes"])->name("relatorios.cargaDidaticaDocentes");
Route::get("/relatorios/cargadidatica/pordocente", [RelatoriosController::class, "cargaDidaticaPorDocente"])->name("relatorios.cargaDidaticaPorDocente");
Route::get("/relatorios/analisedebolsas/monitoria", [RelatoriosController::class, "analiseDeBolsasMonitoria"])->name("relatorios.analiseDeBolsasMonitoria");
Route::get("/relatorios/sistemamonitoriapdf/download", [RelatoriosController::class, "downloadRelatorioSistemaMonitoria"])->name("relatorios.monitoriaPDF");
Route::get("/relatorios/discentes/ingressantes", [RelatoriosController::class, "discentesIngressantes"])->name("relatorios.discentesIngressantes");
Route::get("/relatorios/discentes/estabilidade", [RelatoriosController::class, "discentesEstabilidade"])->name("relatorios.discentesEstabilidade");

Route::get("/logs", [LogsController::class, "index"])->name("logs.index");

Route::get("/api/acesso/individual", [APIAcessoController::class, "individual"])->name("acesso.individual");
Route::get("/api/acesso/ativo", [APIAcessoController::class, "ativo"])->name("acesso.ativo");
Route::get("/api/acesso/lote/pos", [APIAcessoController::class, "lotePos"])->name("acesso.lote.pos");
Route::get("/api/acesso/lote/grad", [APIAcessoController::class, "loteGrad"])->name("acesso.lote.grad");
Route::get("/api/acesso/lote/func", [APIAcessoController::class, "loteFunc"])->name("acesso.lote.func");
Route::get("/api/acesso/lote/doc", [APIAcessoController::class, "loteDoc"])->name("acesso.lote.doc");
Route::get("/api/acesso/lote/pdoc", [APIAcessoController::class, "lotePdoc"])->name("acesso.lote.pdoc");


Route::get('/notas/importar', [NotaController::class, 'importar']);
Route::post('/notas/importar/csv', [NotaController::class, 'importar_csv']);

Route::get('/docente/consulta', [DocenteController::class, 'consulta'])->name("docente.consulta");

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('turmas', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('codtur')->nullable();
            $table->string('coddis');
            $table->string('nivel');
            $table->string('nomdis');
            $table->integer('nummtr')->unsigned()->default(0);
            $table->integer('creaul')->unsigned()->default(0);
            $table->integer('cretrb')->unsigned()->default(0);
            $table->timestamp('dtainiaul')->nullable();
            $table->timestamp('dtafimaul')->nullable();
            $table->unsignedBigInteger('semestre_id');
            $table->unsignedBigInteger('dobradinha_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turma');
    }
};

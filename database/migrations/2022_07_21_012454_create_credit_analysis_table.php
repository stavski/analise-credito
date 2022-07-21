<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_analysis', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 75);
            $table->string('cpf', 14);
            $table->boolean('negativado');
            $table->float('salario', 7, 2);
            $table->string('limite_cartao', 7, 2);
            $table->string('valor_aluguel', 7, 2);
            $table->string('rua', 120);
            $table->integer('numero');
            $table->string('municipio', 75);
            $table->string('unidade_federativa', 2);
            $table->string('cep', 9);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_analysis');
    }
};

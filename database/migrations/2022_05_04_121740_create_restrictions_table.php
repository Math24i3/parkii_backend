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
        Schema::create('restrictions', function (Blueprint $table) {
            $table->string('FID')->primary()->index();
            $table->integer('vejkode')->nullable();
            $table->string('vejnavn')->nullable();
            $table->integer('antal_pladser')->nullable();
            $table->string('restriktion')->nullable();
            $table->string('vejstatus')->nullable();
            $table->string('vejside')->nullable();
            $table->string('bydel')->nullable();
            $table->string('p_ordning')->nullable();
            $table->string('p_type')->nullable();
            $table->string('p_status')->nullable();
            $table->string('rettelsedato')->nullable();
            $table->string('oprettelsesdato')->nullable();
            $table->string('bemaerkning')->nullable();
            $table->integer('id')->nullable();
            $table->string('restriktionstype')->nullable();
            $table->string('restriktionstekst')->nullable();


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('restrictions');
    }
};

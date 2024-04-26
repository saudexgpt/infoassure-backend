<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id');
            $table->integer('ra_id');
            $table->integer('asset_type_id');
            $table->string('asset');
            $table->string('risk_owner');
            $table->string('threat_impact_description');
            $table->string('vunerability_description');
            $table->string('existing_controls');
            $table->string('likelihood_justification');
            $table->integer('risk_likelihood_id');
            $table->integer('confidentiality');
            $table->integer('integrity');
            $table->integer('availability');
            $table->integer('impact_value');
            $table->integer('risk_value');
            $table->string('risk_category_id');
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
        Schema::dropIfExists('risk_assessments');
    }
};

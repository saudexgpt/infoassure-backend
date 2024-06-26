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
        // Schema::create('risk_assessments', function (Blueprint $table) {
        //     $table->id();
        //     $table->integer('client_id');
        //     $table->integer('ra_id');
        //     $table->integer('asset_type_id');
        //     $table->string('asset');
        //     $table->string('risk_owner');
        //     $table->string('threat_impact_description');
        //     $table->string('vunerability_description');
        //     $table->string('existing_controls');
        //     $table->string('likelihood_justification');
        //     $table->integer('risk_likelihood_id');
        //     $table->integer('confidentiality');
        //     $table->integer('integrity');
        //     $table->integer('availability');
        //     $table->integer('impact_value');
        //     $table->integer('risk_value');
        //     $table->string('risk_category_id');
        //     $table->timestamps();
        // });

        Schema::create('risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->integer('risk_register_id');
            $table->integer('client_id');
            $table->integer('business_unit_id')->nullable();
            $table->integer('business_process_id')->nullable();
            $table->integer('standard_id')->nullable();
            $table->integer('asset_type_id')->nullable();
            $table->string('asset')->nullable();

            $table->json('impact_data')->nullable();

            $table->string('likelihood_rationale')->nullable();
            $table->integer('likelihood_of_occurence')->nullable();
            $table->integer('impact_of_occurence')->nullable();
            $table->integer('overall_risk_rating')->nullable();
            $table->string('risk_category')->nullable();

            $table->string('control_effectiveness_level')->nullable();

            $table->json('revised_impact_data')->nullable();

            $table->string('revised_likelihood_rationale')->nullable();
            $table->integer('revised_likelihood_of_occurence')->nullable();
            $table->integer('revised_impact_of_occurence')->nullable();
            $table->integer('revised_overall_risk_rating')->nullable();
            $table->string('revised_risk_category')->nullable();
            $table->string('key_risk_indicator')->nullable();
            $table->text('comments')->nullable();
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

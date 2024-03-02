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
        Schema::create('business_impact_analyses', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id');
            $table->integer('business_unit_id');
            $table->integer('business_process_id');
            $table->string('product_or_service_delivered');
            $table->string('legal_obligation');
            $table->string('priority');
            $table->integer('minimum_service_level');
            $table->string('maximum_allowable_outage');
            $table->string('recovery_time_objective');
            $table->string('recovery_point_objective');
            $table->string('application_used_by_process');
            $table->string('business_units_depended_on');
            $table->string('business_processes_depended_on');
            $table->string('key_vendors');
            $table->string('vital_non_electronic_records');
            $table->string('vital_electronic_records');
            $table->string('alternative_workarounds_during_system_failure');
            $table->string('key_individuals_process_depends_on');
            $table->string('peak_periods');
            $table->string('remote_workings');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_impact_analyses');
    }
};

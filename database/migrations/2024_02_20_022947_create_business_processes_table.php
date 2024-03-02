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
        Schema::create('business_processes', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id');
            $table->integer('business_unit_id');
            $table->integer('name');
            $table->text('description');
            $table->string('roles_responsible');
            $table->integer('no_of_people_involved');
            $table->integer('minimum_no_of_people_involved');
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
        Schema::dropIfExists('business_processes');
    }
};

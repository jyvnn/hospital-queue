<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->integer('age')->nullable();
            $table->string('gender')->nullable();
            $table->string('contact')->nullable();
            $table->text('symptoms')->nullable();
            $table->string('priority')->nullable();
            $table->string('service_type')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('check_in_time')->nullable();
            $table->timestamp('completion_time')->nullable();

            $table->foreignId('assigned_doctor_id')
                ->nullable()
                ->constrained('doctors')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['assigned_doctor_id']);
        });
        Schema::dropIfExists('patients');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('specialty')->nullable();
            $table->string('availability')->nullable();
            $table->integer('current_patients')->default(0);
            $table->integer('max_patients_per_day')->default(20);
            $table->text('expertise')->nullable();
            // keep created_at column nullable since models set timestamps = false
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('doctors');
    }
};

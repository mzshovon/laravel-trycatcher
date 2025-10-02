<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level')->nullable();
            $table->string('message');
            $table->text('trace')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('error_logs');
    }
};

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
        Schema::create('games', function (Blueprint $table) {
            $table->id();

            $table->string('username', 45);
            $table->string('age', 2);
            $table->string('secret', 4);
            $table->json('combinations');
            $table->integer('attempts');
            $table->date('expires');
            $table->decimal('evaluation');
            $table->boolean('win')->default(false);
            $table->boolean('game_over')->default(false);
            $table->string('auth_key');
            $table->integer('ranking')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};

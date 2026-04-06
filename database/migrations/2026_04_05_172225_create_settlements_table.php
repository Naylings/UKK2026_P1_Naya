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
        Schema::disableForeignKeyConstraints();

        Schema::create('settlements', function (Blueprint $table) {
            $table->integer('id')->primary()->autoIncrement();
            $table->integer('violation_id')->unique()->comment('FK ke violations  ,   1 pelanggaran hanya bisa dilunasi 1 kali');
            $table->foreign('violation_id')->references('id')->on('violations');
            $table->integer('employee_id')->comment('FK ke users   (  Employee  )   yang mencatat pelunasan');
            $table->foreign('employee_id')->references('id')->on('users');
            $table->text('description')->comment('Penjelasan pelunasan: bayar denda / ganti alat / kesepakatan lain');
            $table->timestamp('settled_at')->comment('Waktu pelunasan dicatat. Setelah ini violations.status = settled dan users.is_restricted = 0');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};

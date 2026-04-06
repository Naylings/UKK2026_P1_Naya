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

        Schema::create('violations', function (Blueprint $table) {
            $table->integer('id')->primary()->autoIncrement();
            $table->integer('loan_id')->comment('FK ke loans yang menghasilkan pelanggaran');
            $table->foreign('loan_id')->references('id')->on('loans');
            $table->integer('user_id')->comment('FK ke users yang dikenakan pelanggaran');
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('return_id')->nullable()->comment('FK ke returns. NULL jika type = lost karena tidak ada pengembalian');
            $table->foreign('return_id')->references('id')->on('returns');
            $table->enum('type', ["late","damaged","lost"]);
            $table->integer('total_score')->comment('total kredit user yang berkurang');
            $table->float('fine', 53)->comment('Jumlah Denda');
            $table->text('description')->comment('Penjelasan detail pelanggaran');
            $table->enum('status', ["active","settled"]);
            $table->timestamp('created_at');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violations');
    }
};

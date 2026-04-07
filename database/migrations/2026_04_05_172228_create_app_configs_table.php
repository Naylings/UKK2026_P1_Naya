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

        Schema::create('app_configs', function (Blueprint $table) {
            $table->integer('id')->primary()->autoIncrement();
            $table->string('name')->comment('nama app/instansi');
            $table->integer('late_point')->comment('poin penalty yang bertambah jika pengembalian alat terlambat');
            $table->integer('broken_point')->comment('poin penalty yang bertambah jika alat rusak');
            $table->integer('lost_point')->comment('poin penalty yang bertambah jika alat hilang');
            $table->integer('late_fine')->comment('persenan default denda dari harga alat');
            $table->integer('broken_fine')->comment('persenan default denda dari harga alat');
            $table->integer('lost_fine')->comment('persenan default denda dari harga alat');
            $table->timestamp('updated_at')->nullable()->comment('Terakhir diubah oleh Admin');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_configs');
    }
};

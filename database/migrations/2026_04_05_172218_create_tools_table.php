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

        Schema::create('tools', function (Blueprint $table) {
            $table->integer('id')->primary()->autoIncrement();
            $table->integer('category_id')->comment('FK ke categories');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->string('name', 255)->comment('Nama template/jenis alat');
            $table->enum('item_type', ["single","bundle","bundle_tool"]);
            $table->bigInteger('price')->comment('harga tool ,  sebagai patokan untuk perhitungan ,  misalkan pada config fine untuk telas 15 atau 15% dari harga alat\\ maka denda secara default akan berjumlah 15% dari harga alat');
            $table->integer('min_credit_score')->nullable()->comment('min credit user yang dibutuhkan untuk pinjam alat');
            $table->text('description')->comment('Deskripsi umum alat atau bundle');
            $table->bigInteger('code_slug')->nullable();
            $table->string('photo_path', 255)->comment('Path foto representatif alat');
            $table->timestamp('created_at');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};

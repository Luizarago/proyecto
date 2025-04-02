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
        
        Schema::table('personas', function (Blueprint $table) {
            $table->dropForeign(['documento_id']);
            $table->dropColumn('documento_id');
        });

        
        Schema::table('personas', function (Blueprint $table) {
            $table->foreignId('documento_id')->after('estado')->constrained('documentos')->onDelete('cascade');
        });

        
        Schema::table('personas', function (Blueprint $table) {
            $table->string('numero_documento',20)->after('documento_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        Schema::table('personas', function (Blueprint $table) {
            $table->dropForeign(['documento_id']);
            $table->dropColumn('documento_id');
        });

       
        Schema::table('personas', function (Blueprint $table) {
            $table->foreignId('documento_id')->after('estado')->unique()->constrained('documentos')->onDelete('cascade');
        });

        
        Schema::table('personas', function (Blueprint $table) {
            $table->dropColumn('numero_documento');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('konsultasi_chat', function (Blueprint $table) {
            // Make id_klasifikasi nullable
            $table->uuid('id_klasifikasi')->nullable()->change();
            
            // Add id_pohon column
            $table->uuid('id_pohon')->nullable();
            
            // Define foreign key relationship
            $table->foreign('id_pohon')->references('id_pohon')->on('pohon')->onDelete('cascade');
        });

        // Add CHECK constraint using raw SQL
        DB::statement('
            ALTER TABLE konsultasi_chat 
            ADD CONSTRAINT chk_chat_scope CHECK (
                (id_klasifikasi IS NOT NULL AND id_pohon IS NULL) OR
                (id_klasifikasi IS NULL AND id_pohon IS NOT NULL)
            )
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop CHECK constraint
        DB::statement('ALTER TABLE konsultasi_chat DROP CONSTRAINT IF EXISTS chk_chat_scope');

        Schema::table('konsultasi_chat', function (Blueprint $table) {
            $table->dropForeign(['id_pohon']);
            $table->dropColumn('id_pohon');
        });

        // Delete records where id_klasifikasi is null to allow making it non-nullable again
        DB::table('konsultasi_chat')->whereNull('id_klasifikasi')->delete();

        Schema::table('konsultasi_chat', function (Blueprint $table) {
            $table->uuid('id_klasifikasi')->nullable(false)->change();
        });
    }
};

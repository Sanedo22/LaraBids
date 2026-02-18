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
        Schema::table('auctions', function (Blueprint $table) {
            // Add indexes for frequently searched/filtered columns
            $table->index('status', 'idx_auctions_status');
            $table->index('current_price', 'idx_auctions_current_price');
            $table->index('end_time', 'idx_auctions_end_time');
            $table->index('category_id', 'idx_auctions_category_id');
            $table->index('created_at', 'idx_auctions_created_at');
            
            // Composite index for common query patterns
            $table->index(['status', 'end_time'], 'idx_auctions_status_end_time');
            $table->index(['category_id', 'status'], 'idx_auctions_category_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            // Drop indexes in reverse order
            $table->dropIndex('idx_auctions_category_status');
            $table->dropIndex('idx_auctions_status_end_time');
            $table->dropIndex('idx_auctions_created_at');
            $table->dropIndex('idx_auctions_category_id');
            $table->dropIndex('idx_auctions_end_time');
            $table->dropIndex('idx_auctions_current_price');
            $table->dropIndex('idx_auctions_status');
        });
    }
};

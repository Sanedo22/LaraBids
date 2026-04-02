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
        Schema::table('user_strikes', function (Blueprint $table) {
            $table->enum('status', ['active', 'appealed', 'dismissed', 'resolved'])->default('active')->after('reason');
            $table->string('type')->default('buyer_non_payment')->after('status');
            $table->text('appeal_reason')->nullable()->after('type');
            $table->timestamp('appeal_at')->nullable()->after('appeal_reason');
            $table->text('admin_note')->nullable()->after('appeal_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_strikes', function (Blueprint $table) {
            $table->dropColumn(['status', 'type', 'appeal_reason', 'appeal_at', 'admin_note']);
        });
    }
};

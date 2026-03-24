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
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('payout_amount', 15, 2)->nullable()->after('commission_percentage');
            $table->string('payout_status')->default('pending')->after('payout_amount'); // pending, paid
            $table->timestamp('payout_at')->nullable()->after('payout_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payout_amount', 'payout_status', 'payout_at']);
        });
    }
};

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
            $table->string('payment_method')->default('online')->after('amount'); // online, cash
            $table->decimal('commission_amount', 15, 2)->nullable()->after('payment_method');
            $table->decimal('commission_percentage', 5, 2)->default(5.00)->after('commission_amount');
            $table->string('commission_status')->default('unpaid')->after('commission_percentage'); // unpaid, paid
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'commission_amount', 'commission_percentage', 'commission_status']);
        });
    }
};

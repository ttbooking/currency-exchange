<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->char('base', 3);
            $table->char('quote', 3);
            $table->date('date');
            $table->string('service');
            $table->unsignedDecimal('rate', 14, 8);
            $table->primary(['base', 'quote', 'date', 'service']);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};

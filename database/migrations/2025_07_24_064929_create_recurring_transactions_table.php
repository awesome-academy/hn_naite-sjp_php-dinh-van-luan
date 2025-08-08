<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecurringTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');

            $table->decimal('amount', 15, 2);
            $table->text('note')->nullable();

            $table->enum('recurring_type', ['daily', 'weekly', 'monthly', 'yearly']);
            $table->integer('interval_value')->default(1);

            $table->date('start_date');

            $table->boolean('is_forever')->default(true);
            $table->date('end_date')->nullable(); // Nếu không mãi mãi
            $table->integer('max_occurrences')->nullable(); // Nếu có giới hạn số lần
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recurring_transactions');
    }
}

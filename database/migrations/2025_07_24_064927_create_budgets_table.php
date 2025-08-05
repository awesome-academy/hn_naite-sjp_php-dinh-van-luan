<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBudgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('limit_amount', 15, 2);
            $table->decimal('spent_amount', 15, 2)->default(0);
            $table->enum('wallet_use_scope', ['total', 'wallet']);
            $table->foreignId('wallet_id')->nullable()->constrained('wallets')->onDelete('cascade');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurring_type', ['weekly', 'monthly', 'quarterly', 'yearly', 'option']);
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
        Schema::dropIfExists('budgets');
    }
}

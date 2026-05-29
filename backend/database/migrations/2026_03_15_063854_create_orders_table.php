<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('status', ['new', 'pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'])->default('new');
            $table->decimal('total_amount', 10, 2);
            $table->string('delivery_method');
            $table->decimal('delivery_price', 10, 2)->default(0);
            $table->text('delivery_address');
            $table->date('delivery_date')->nullable();
            $table->string('payment_method');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            
            // Данные получателя
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            
            $table->text('comment')->nullable();
            $table->text('admin_comment')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
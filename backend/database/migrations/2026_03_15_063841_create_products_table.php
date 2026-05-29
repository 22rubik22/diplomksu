<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            $table->text('description')->nullable();
            $table->year('publication_year')->nullable();
            $table->string('contry')->default('Россия');
            $table->string('consist')->nullable();
            $table->decimal('weight', 8, 2)->nullable(); // в граммах
            $table->decimal('price', 10, 2);
            $table->decimal('old_price', 10, 2)->nullable();
            $table->integer('quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
            
            // Индексы
            $table->index('price');
            $table->index('is_active');
            $table->index('created_at');
            $table->index('brand_id'); // Добавляем индекс для внешнего ключа
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
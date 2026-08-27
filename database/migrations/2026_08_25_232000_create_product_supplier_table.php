<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'supplier_id']);
        });

        // Migrate existing single supplier_id from products table to product_supplier pivot table
        $existingProducts = DB::table('products')->whereNotNull('supplier_id')->get(['id', 'supplier_id']);
        foreach ($existingProducts as $p) {
            DB::table('product_supplier')->insertOrIgnore([
                'product_id' => $p->id,
                'supplier_id' => $p->supplier_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_supplier');
    }
};

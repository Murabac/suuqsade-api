<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('product_platform')->nullable()->after('product_note');
            $table->string('product_title')->nullable()->after('product_platform');
            $table->text('product_description')->nullable()->after('product_title');
            $table->json('product_images')->nullable()->after('product_description');
            $table->string('product_metadata_status')->default('pending')->after('product_images');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'product_platform',
                'product_title',
                'product_description',
                'product_images',
                'product_metadata_status',
            ]);
        });
    }
};

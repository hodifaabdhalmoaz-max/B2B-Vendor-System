<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'storefront_contexts')) {
                $table->json('storefront_contexts')->nullable()->after('parent_id');
            }

            if (! Schema::hasColumn('categories', 'storefront_order')) {
                $table->unsignedSmallInteger('storefront_order')->default(0)->after('storefront_contexts');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'storefront_sections')) {
                $table->json('storefront_sections')->nullable()->after('description');
            }

            if (! Schema::hasColumn('products', 'storefront_order')) {
                $table->unsignedSmallInteger('storefront_order')->default(0)->after('storefront_sections');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'storefront_order')) {
                $table->dropColumn('storefront_order');
            }

            if (Schema::hasColumn('products', 'storefront_sections')) {
                $table->dropColumn('storefront_sections');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'storefront_order')) {
                $table->dropColumn('storefront_order');
            }

            if (Schema::hasColumn('categories', 'storefront_contexts')) {
                $table->dropColumn('storefront_contexts');
            }
        });
    }
};

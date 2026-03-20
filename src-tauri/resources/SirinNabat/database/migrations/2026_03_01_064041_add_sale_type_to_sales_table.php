<?php

// файл: database/migrations/xxxx_xx_xx_add_sale_type_to_sales_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            // Тип продажи: 'box' (коробка) или 'unit' (пластинка)
            $table->string('sale_type')->default('box')->after('quantity');
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('sale_type');
        });
    }
};

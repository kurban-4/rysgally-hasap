<?php

// файл: database/migrations/xxxx_xx_xx_add_unit_fields_to_medicines_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('medicines', function (Blueprint $table) {
            // Сколько пластинок в одной коробке (по умолчанию 1, чтобы старые товары не сломались)
            $table->integer('units_per_box')->default(1)->after('price'); 
            
            // Цена за целую коробку
            $table->decimal('price_box', 10, 2)->nullable()->after('units_per_box');
            
            // Цена за 1 пластинку
            $table->decimal('price_unit', 10, 2)->nullable()->after('price_box');
            
            // Наш новый "умный" остаток (всё храним в пластинках)
            $table->integer('total_quantity_units')->default(0)->after('price_unit');
        });
    }

    public function down()
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn(['units_per_box', 'price_box', 'price_unit', 'total_quantity_units']);
        });
    }
};

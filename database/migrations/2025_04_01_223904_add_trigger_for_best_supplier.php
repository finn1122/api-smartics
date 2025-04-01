<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Solo ejecutar si usas MySQL
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::unprepared('
                DROP TRIGGER IF EXISTS after_external_product_data_insert;
                CREATE TRIGGER after_external_product_data_insert
                AFTER INSERT ON external_product_data
                FOR EACH ROW
                BEGIN
                    UPDATE products
                    SET has_best_supplier = (
                        SELECT EXISTS (
                            SELECT 1 FROM external_product_data
                            WHERE product_id = NEW.product_id
                            AND price > 0 AND sale_price > 0 AND new_sale_price > 0
                            ORDER BY quantity > 0 DESC, price ASC
                            LIMIT 1
                        )
                    )
                    WHERE id = NEW.product_id;
                END;

                DROP TRIGGER IF EXISTS after_external_product_data_update;
                CREATE TRIGGER after_external_product_data_update
                AFTER UPDATE ON external_product_data
                FOR EACH ROW
                BEGIN
                    UPDATE products
                    SET has_best_supplier = (
                        SELECT EXISTS (
                            SELECT 1 FROM external_product_data
                            WHERE product_id = NEW.product_id
                            AND price > 0 AND sale_price > 0 AND new_sale_price > 0
                            ORDER BY quantity > 0 DESC, price ASC
                            LIMIT 1
                        )
                    )
                    WHERE id = NEW.product_id;
                END;

                DROP TRIGGER IF EXISTS after_external_product_data_delete;
                CREATE TRIGGER after_external_product_data_delete
                AFTER DELETE ON external_product_data
                FOR EACH ROW
                BEGIN
                    UPDATE products
                    SET has_best_supplier = (
                        SELECT EXISTS (
                            SELECT 1 FROM external_product_data
                            WHERE product_id = OLD.product_id
                            AND price > 0 AND sale_price > 0 AND new_sale_price > 0
                            ORDER BY quantity > 0 DESC, price ASC
                            LIMIT 1
                        )
                    )
                    WHERE id = OLD.product_id;
                END;
            ');
        }
    }

    public function down()
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::unprepared('
                DROP TRIGGER IF EXISTS after_external_product_data_insert;
                DROP TRIGGER IF EXISTS after_external_product_data_update;
                DROP TRIGGER IF EXISTS after_external_product_data_delete;
            ');
        }
    }
};

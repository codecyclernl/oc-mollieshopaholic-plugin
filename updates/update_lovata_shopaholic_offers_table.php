<?php namespace Codecycler\MollieShopaholic\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class updatelovatashopaholicofferstable extends Migration
{
    public function up()
    {
        Schema::table('lovata_shopaholic_offers', function($obTable) {
            $obTable->integer('quantity')->unsigned(false)->change();
        });
    }

    public function down()
    {
        Schema::table('lovata_shopaholic_offers', function ($obTable) {
            $obTable->integer('quantity')->unsigned()->change();
        });
    }
}
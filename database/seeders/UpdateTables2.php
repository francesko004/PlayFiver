<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class UpdateTables2 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::table('custom_layouts', function ($table) {
            $table->string('pixel_deposito')->nullable();
            $table->string('pixel_cadastro')->nullable();
            $table->string('link_suporte')->nullable();
        });
    }
}

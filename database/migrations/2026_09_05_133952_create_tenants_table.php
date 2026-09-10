<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('tenants', function (Blueprint $table) {
        $table->id();
        $table->string('no_reg')->unique();
        $table->string('room_no');
        $table->string('name');
        $table->string('phone');
        $table->integer('base_rent');
        $table->float('last_water')->default(0);
        $table->float('last_elec')->default(0);
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }

    
};

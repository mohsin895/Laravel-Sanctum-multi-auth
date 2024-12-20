<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('tree_space')->nullable();
            $table->string('cut_type')->nullable();
            $table->string('stack_number')->nullable();
            $table->string('log_length')->nullable();
            $table->string('average_diameter')->nullable();
            $table->string('log_count')->nullable();
            $table->longText('stack_placement')->nullable();
            $table->string('property_name')->nullable();
            $table->string('volume')->nullable();
            $table->json('geo_location')->nullable();
            $table->enum('isOnline',['yes','no'])->default('yes');
            $table->enum('status',['yes','no'])->default('yes');
            $table->enum('buying_status',['sold','available'])->default('available');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

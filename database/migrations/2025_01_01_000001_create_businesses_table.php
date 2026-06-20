<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->string('id')->primary(); // cuid2 / custom string
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status')->default('pending');
            $table->string('admin_email')->nullable();
            $table->string('created_by')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};

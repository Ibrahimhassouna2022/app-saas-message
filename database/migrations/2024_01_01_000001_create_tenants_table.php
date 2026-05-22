<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    public function up()
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary(); // company_slug
            $table->string('plan')->default('basic'); // basic, premium, enterprise
            $table->string('company_name');
            $table->string('admin_email');
            $table->date('subscription_ends_at')->nullable();
            $table->integer('max_messages')->default(1000);
            $table->json('settings')->nullable();
            $table->json('data')->nullable(); // مطلوب من حزمة stancl/tenancy
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tenants');
    }
}

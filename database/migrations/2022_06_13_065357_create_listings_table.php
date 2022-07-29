<?php

use App\Enums\EmploymentType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->string('nanoid')->unique();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id');

            $table->tinyInteger('employment_type')->default(EmploymentType::FullTime);

            $table->string('position');
            $table->string('location');
            $table->text('description')->nullable();

            $table->boolean('is_highlighted')->default(false);
            $table->boolean('is_pinned')->default(false);

            $table->integer('min_annual_salary')->default(0);
            $table->integer('max_annual_salary')->default(0);

            $table->string('apply_url');

            $table->boolean('is_closed')->default(false);

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('listings');
    }
};

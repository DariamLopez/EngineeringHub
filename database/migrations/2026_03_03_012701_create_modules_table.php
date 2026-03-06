<?php

use App\Enums\ModuleStatusEnum;
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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('status', ModuleStatusEnum::values())->default(ModuleStatusEnum::DRAFT->value);

            $table->text('objective')->nullable();
            $table->json('inputs')->nullable();
            $table->json('data_structure')->nullable();
            $table->text('logic_rules')->nullable();
            $table->json('outputs')->nullable();
            $table->text('responsability')->nullable();
            $table->text('failure_scenarios')->nullable();
            $table->text('audit_trail_requirements')->nullable();
            $table->json('dependencies')->nullable(); //json array of module_ids
            $table->string('version_note')->nullable();

            $table->foreignId('domain_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};

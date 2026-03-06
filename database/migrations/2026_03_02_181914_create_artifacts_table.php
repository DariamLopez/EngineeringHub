<?php

use App\Enums\ArtifactStatusEnum;
use App\Enums\ArtifactTypeEnum;
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
        Schema::create('artifacts', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ArtifactTypeEnum::values())->default(ArtifactTypeEnum::STRATEGIC_ALIGNMENT->value);
            $table->json('content_json');
            $table->enum('status', ArtifactStatusEnum::values())->default(ArtifactStatusEnum::NOT_STARTED->value);

            $table->foreignId('owner_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');

            $table->date('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artifacts');
    }
};

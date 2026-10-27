
<?php

use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->morphs('reportable');

            $table->string('reason', 50);
            $table->text('description')->nullable();

            $table->string('status', 30)
                ->default(ReportStatus::Pending->value);

            $table->timestamps();

            $table->unique(
                ['user_id', 'reportable_type', 'reportable_id'],
                'reports_user_reportable_unique'
            );

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};

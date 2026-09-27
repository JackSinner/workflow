<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Workflow\Support\WorkflowMigration;

return new class() extends WorkflowMigration {
    public function up(): void
    {
        if (Schema::connection($this->getConnection())->hasColumn('workflow_history_events', 'recorded_at_utc')) {
            return;
        }

        Schema::connection($this->getConnection())->table(
            'workflow_history_events',
            static function (Blueprint $table): void {
                $table->dateTime('recorded_at_utc', 6)
                    ->nullable();
            },
        );
    }

    public function down(): void
    {
        // Dropping this column would discard the only unambiguous timestamp
        // for events written during a repeated local hour.
    }
};

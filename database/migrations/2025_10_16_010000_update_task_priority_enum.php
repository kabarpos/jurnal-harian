<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected function usingSqlite(): bool
    {
        return Schema::getConnection()->getDriverName() === 'sqlite';
    }

    public function up(): void
    {
        if (! Schema::hasTable('tasks')) {
            return;
        }

        DB::transaction(function () {
            DB::statement("UPDATE tasks SET priority = 'urgent' WHERE priority IN ('p1')");
            DB::statement("UPDATE tasks SET priority = 'important' WHERE priority IN ('p2')");
            DB::statement("UPDATE tasks SET priority = 'normal' WHERE priority IN ('p3','p4','')");

            if (! $this->usingSqlite()) {
                DB::statement("ALTER TABLE tasks MODIFY COLUMN priority ENUM('normal','important','urgent') NOT NULL DEFAULT 'normal'");
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tasks')) {
            return;
        }

        DB::transaction(function () {
            DB::statement("UPDATE tasks SET priority = 'p1' WHERE priority = 'urgent'");
            DB::statement("UPDATE tasks SET priority = 'p2' WHERE priority = 'important'");
            DB::statement("UPDATE tasks SET priority = 'p3' WHERE priority = 'normal'");

            if (! $this->usingSqlite()) {
                DB::statement("ALTER TABLE tasks MODIFY COLUMN priority ENUM('p1','p2','p3','p4') NOT NULL DEFAULT 'p3'");
            }
        });
    }
};

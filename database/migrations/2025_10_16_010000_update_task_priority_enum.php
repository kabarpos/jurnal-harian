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

        if ($this->usingSqlite()) {
            DB::transaction(function () {
                DB::statement("UPDATE tasks SET priority = 'urgent' WHERE priority IN ('p1','P1')");
                DB::statement("UPDATE tasks SET priority = 'important' WHERE priority IN ('p2','P2')");
                DB::statement("UPDATE tasks SET priority = 'normal' WHERE priority IN ('p3','P3','p4','P4','')");
            });

            return;
        }

        DB::statement("ALTER TABLE tasks MODIFY COLUMN priority ENUM('p1','p2','p3','p4','normal','important','urgent') NOT NULL DEFAULT 'p3'");
        DB::statement("UPDATE tasks SET priority = 'urgent' WHERE priority IN ('p1','P1')");
        DB::statement("UPDATE tasks SET priority = 'important' WHERE priority IN ('p2','P2')");
        DB::statement("UPDATE tasks SET priority = 'normal' WHERE priority IN ('p3','P3','p4','P4','')");
        DB::statement("ALTER TABLE tasks MODIFY COLUMN priority ENUM('normal','important','urgent') NOT NULL DEFAULT 'normal'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('tasks')) {
            return;
        }

        if ($this->usingSqlite()) {
            DB::transaction(function () {
                DB::statement("UPDATE tasks SET priority = 'p1' WHERE priority = 'urgent'");
                DB::statement("UPDATE tasks SET priority = 'p2' WHERE priority = 'important'");
                DB::statement("UPDATE tasks SET priority = 'p3' WHERE priority = 'normal'");
            });

            return;
        }

        DB::statement("ALTER TABLE tasks MODIFY COLUMN priority ENUM('normal','important','urgent','p1','p2','p3','p4') NOT NULL DEFAULT 'normal'");
        DB::statement("UPDATE tasks SET priority = 'p1' WHERE priority = 'urgent'");
        DB::statement("UPDATE tasks SET priority = 'p2' WHERE priority = 'important'");
        DB::statement("UPDATE tasks SET priority = 'p3' WHERE priority = 'normal'");
        DB::statement("ALTER TABLE tasks MODIFY COLUMN priority ENUM('p1','p2','p3','p4') NOT NULL DEFAULT 'p3'");
    }
};

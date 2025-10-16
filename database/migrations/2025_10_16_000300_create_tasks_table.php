<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['p1', 'p2', 'p3', 'p4'])->default('p3');
            $table->enum('status', ['planned', 'in_progress', 'done', 'canceled'])->default('planned');
            $table->date('planned_date')->nullable();
            $table->date('due_date')->nullable();
            $table->unsignedInteger('estimate_minutes')->default(0);
            $table->unsignedInteger('actual_minutes')->default(0);
            $table->string('context')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_rule')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'planned_date']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};

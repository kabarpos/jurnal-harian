<?php

namespace Tests\Feature\Planner;

use App\Livewire\Planner\DailyPlannerPage;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class DailyPlannerPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_backlog_task(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(DailyPlannerPage::class)
            ->set('newBacklogTaskTitle', 'Write daily reflection')
            ->call('createBacklogTask')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Write daily reflection',
            'planned_date' => null,
            'status' => Task::STATUS_PLANNED,
            'priority' => Task::PRIORITY_NORMAL,
        ]);
    }

    public function test_user_can_plan_backlog_task(): void
    {
        $user = User::factory()->create();

        $task = $user->tasks()->create([
            'title' => 'Prepare sprint review',
            'status' => Task::STATUS_PLANNED,
            'priority' => Task::PRIORITY_IMPORTANT,
            'order' => 1,
        ]);

        $targetDate = Carbon::today()->addDay()->toDateString();

        Livewire::actingAs($user)
            ->test(DailyPlannerPage::class)
            ->set('selectedDate', $targetDate)
            ->call('planTask', $task->id);

        $this->assertSame(
            $targetDate,
            $task->fresh()->planned_date?->toDateString()
        );
    }
}

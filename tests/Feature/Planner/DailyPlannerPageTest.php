<?php

namespace Tests\Feature\Planner;

use App\Livewire\Planner\DailyPlannerPage;
use App\Models\Task;
use App\Models\TimeBlock;
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
        ]);
    }

    public function test_user_can_plan_backlog_task(): void
    {
        $user = User::factory()->create();

        $task = $user->tasks()->create([
            'title' => 'Prepare sprint review',
            'status' => Task::STATUS_PLANNED,
            'priority' => Task::PRIORITY_P2,
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

    public function test_time_block_creation_prevents_overlap(): void
    {
        $user = User::factory()->create();

        TimeBlock::create([
            'user_id' => $user->id,
            'start_at' => Carbon::parse('2025-05-03 09:00', config('app.timezone')),
            'end_at' => Carbon::parse('2025-05-03 10:00', config('app.timezone')),
        ]);

        Livewire::actingAs($user)
            ->test(DailyPlannerPage::class)
            ->set('selectedDate', '2025-05-03')
            ->set('newTimeBlockStart', '09:30')
            ->set('newTimeBlockEnd', '10:30')
            ->call('createTimeBlock')
            ->assertHasErrors(['start_at']);
    }
}

<?php

namespace Tests\Feature\Planner;

use App\Livewire\Planner\TimeBlocksPage;
use App\Models\TimeBlock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class TimeBlocksPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_time_block_creation_prevents_overlap(): void
    {
        $user = User::factory()->create();

        TimeBlock::create([
            'user_id' => $user->id,
            'start_at' => Carbon::parse('2025-05-03 09:00', config('app.timezone')),
            'end_at' => Carbon::parse('2025-05-03 10:00', config('app.timezone')),
        ]);

        Livewire::actingAs($user)
            ->test(TimeBlocksPage::class)
            ->set('selectedDate', '2025-05-03')
            ->set('newTimeBlockStart', '09:30')
            ->set('newTimeBlockEnd', '10:30')
            ->call('createTimeBlock')
            ->assertHasErrors(['start_at']);
    }
}

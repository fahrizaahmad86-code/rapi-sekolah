<?php

namespace Tests\Unit;

use App\Models\Complaint;
use Carbon\Carbon;
use Tests\TestCase;

class ComplaintOverdueTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_whatsapp_reminder_is_due_after_three_days_without_progress(): void
    {
        Carbon::setTestNow('2026-11-09 12:00:00');
        $complaint = new Complaint([
            'handler_id' => 12,
            'status_repair' => 'Belum Diperbaiki',
            'last_progress_at' => Carbon::parse('2026-11-06 12:00:00'),
        ]);

        $this->assertTrue($complaint->isOverdue());
    }

    public function test_whatsapp_reminder_is_not_due_before_three_days(): void
    {
        Carbon::setTestNow('2026-11-09 12:00:00');
        $complaint = new Complaint([
            'handler_id' => 12,
            'status_repair' => 'Belum Diperbaiki',
            'last_progress_at' => Carbon::parse('2026-11-06 12:00:01'),
        ]);

        $this->assertFalse($complaint->isOverdue());
    }

    public function test_whatsapp_reminder_requires_an_assigned_pic_and_unresolved_status(): void
    {
        Carbon::setTestNow('2026-11-09 12:00:00');
        $oldProgress = Carbon::parse('2026-11-05 12:00:00');
        $unassigned = new Complaint([
            'status_repair' => 'Belum Diperbaiki',
            'last_progress_at' => $oldProgress,
        ]);
        $otherStatus = new Complaint([
            'handler_id' => 12,
            'status_repair' => 'Menunggu Spare Part',
            'last_progress_at' => $oldProgress,
        ]);

        $this->assertFalse($unassigned->isOverdue());
        $this->assertFalse($otherStatus->isOverdue());
    }
}

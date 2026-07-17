<?php

namespace Tests\Unit;

use App\Support\ActivityLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    public function test_write_does_not_throw_when_fallback_logger_fails(): void
    {
        Schema::shouldReceive('hasTable')
            ->once()
            ->with('activity_logs')
            ->andReturn(false);

        Log::shouldReceive('channel')
            ->once()
            ->with('activity')
            ->andReturn(new class
            {
                public function info(string $message, array $context = []): void
                {
                    throw new \RuntimeException('logger unavailable');
                }
            });

        ActivityLog::write('switch', 'Tenant switched');

        $this->assertTrue(true);
    }
}

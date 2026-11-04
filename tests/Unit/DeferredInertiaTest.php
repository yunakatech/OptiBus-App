<?php

namespace Tests\Unit;

use App\Support\DeferredInertia;
use PHPUnit\Framework\TestCase;

class DeferredInertiaTest extends TestCase
{
    public function test_missing_and_invalid_overrides_follow_the_hosting_default(): void
    {
        foreach ([null, '', '  ', 'invalid'] as $value) {
            $this->assertTrue(DeferredInertia::resolve($value, false));
            $this->assertFalse(DeferredInertia::resolve($value, true));
        }
    }

    public function test_explicit_overrides_apply_on_both_hosts(): void
    {
        foreach ([false, true] as $onVercel) {
            foreach ([true, 'true', ' 1 ', 'yes', 'on'] as $value) {
                $this->assertTrue(DeferredInertia::resolve($value, $onVercel));
            }
            foreach ([false, 'false', '0', 'no', 'off'] as $value) {
                $this->assertFalse(DeferredInertia::resolve($value, $onVercel));
            }
        }
    }
}

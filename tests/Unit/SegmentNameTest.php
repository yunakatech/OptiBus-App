<?php

namespace Tests\Unit;

use App\Support\SegmentName;
use PHPUnit\Framework\TestCase;

class SegmentNameTest extends TestCase
{
    public function test_it_builds_segment_name_from_origin_and_destination(): void
    {
        $this->assertSame(
            'Pinrang - Mamuju',
            SegmentName::display('Pinrang', 'Mamuju', 'Fallback'),
        );
    }

    public function test_it_normalizes_broken_fallback_separator(): void
    {
        $this->assertSame(
            'Pinrang - Mamuju',
            SegmentName::display('', '', 'Pinrang ? Mamuju'),
        );
    }

    public function test_it_normalizes_arrow_fallback_separator(): void
    {
        $this->assertSame(
            'Pinrang - Mamuju',
            SegmentName::display('', '', "Pinrang \u{2192} Mamuju"),
        );
    }
}

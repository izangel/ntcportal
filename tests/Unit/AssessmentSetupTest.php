<?php

namespace Tests\Unit;

use App\Livewire\Admin\AssessmentSetup;
use PHPUnit\Framework\TestCase;

class AssessmentSetupTest extends TestCase
{
    public function test_batch_effectiveness_is_checked_across_year_ranges(): void
    {
        $component = new AssessmentSetup();
        $method = new \ReflectionMethod($component, 'isEffectiveForBatch');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($component, '2024', '2024'));
        $this->assertTrue($method->invoke($component, '2024-2025', '2024'));
        $this->assertFalse($method->invoke($component, '2023', '2024'));
        $this->assertFalse($method->invoke($component, '2025', '2024'));
        $this->assertFalse($method->invoke($component, null, '2024'));
        $this->assertFalse($method->invoke($component, '', '2024'));
    }
}

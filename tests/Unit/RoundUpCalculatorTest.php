<?php

namespace Tests\Unit;

use App\Helpers\RoundUpCalculator;
use PHPUnit\Framework\TestCase;

class RoundUpCalculatorTest extends TestCase
{
    public function test_calculates_round_up_correctly(): void
    {
        $this->assertEquals(0.63, RoundUpCalculator::calculate(4.37));
        $this->assertEquals(0.01, RoundUpCalculator::calculate(12.99));
        $this->assertEquals(0.00, RoundUpCalculator::calculate(20.00));
        $this->assertEquals(0.50, RoundUpCalculator::calculate(7.50));
    }
}

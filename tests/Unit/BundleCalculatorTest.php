<?php

namespace Tests\Unit;

use App\Services\BundleCalculator;
use PHPUnit\Framework\TestCase;

class BundleCalculatorTest extends TestCase
{
    public function test_complete_bundles_and_normal_remainders(): void
    {
        foreach ([1 => 35000, 2 => 50000, 3 => 85000, 4 => 100000] as $quantity => $total) {
            $quote = (new BundleCalculator)->allocate([1 => $quantity], 35000, 2, 50000);
            $this->assertSame($total, 35000 * $quantity - $quote['discount']);
        }
    }

    public function test_cent_allocation_is_exact_and_independent_of_input_order(): void
    {
        $calculator = new BundleCalculator;
        for ($quantity = 1; $quantity <= 25; $quantity++) {
            $a = $calculator->allocate([8 => $quantity, 2 => 1, 4 => 2], 35000, 2, 49999);
            $b = $calculator->allocate([4 => 2, 2 => 1, 8 => $quantity], 35000, 2, 49999);
            $this->assertSame($a, $b);
            $this->assertSame($a['discount'], array_sum($a['allocation']));
        }
    }

    public function test_invalid_quantity_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new BundleCalculator)->allocate([1 => -1], 35000, 2, 50000);
    }
}

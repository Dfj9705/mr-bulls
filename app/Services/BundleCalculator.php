<?php

namespace App\Services;

/** Integer-only calculation, shared by quotes and persisted orders. */
class BundleCalculator
{
    public function allocate(array $quantities, int $unitCents, int $size, int $bundleCents): array
    {
        if ($size < 2 || $unitCents < 0 || $bundleCents < 0 || $bundleCents >= $size * $unitCents) {
            throw new \InvalidArgumentException('Configuración de paquete inválida.');
        }
        foreach ($quantities as $quantity) {
            if (!is_int($quantity) || $quantity < 1) {
                throw new \InvalidArgumentException('Cantidad inválida.');
            }
        }
        ksort($quantities, SORT_NUMERIC);
        $units = array_sum($quantities);
        $bundles = intdiv($units, $size);
        $discount = $bundles * ($size * $unitCents - $bundleCents);
        $allocation = [];
        $remaining = $discount;
        $remainders = [];
        foreach ($quantities as $id => $quantity) {
            $allocation[$id] = intdiv($discount * $quantity, $units);
            $remainders[$id] = ($discount * $quantity) % $units;
            $remaining -= $allocation[$id];
        }
        // Largest remainder; product ID breaks ties deterministically.
        arsort($remainders, SORT_NUMERIC);
        foreach ($remainders as $id => $_) {
            if ($remaining-- <= 0) {
                break;
            }
            $allocation[$id]++;
        }
        return ['bundles' => $bundles, 'discount' => $discount, 'allocation' => $allocation];
    }
}

<?php

namespace App\Services;

use App\Models\BusinessProfile;

class JewelleryPricingService
{
    /**
     * Compute comprehensive item prices and taxes.
     */
    public function computeItemPricing(array $itemData, int|string|null $buyerStateCode = null, ?bool $isGstApplicable = null): array
    {
        $grossWeight = (float) ($itemData['gross_weight'] ?? 0);
        $stoneWeight = (float) ($itemData['stone_weight'] ?? 0);
        $netWeight   = max(0, $grossWeight - $stoneWeight);

        $purityPercentage = (float) ($itemData['purity_percentage'] ?? 91.667);
        $fineGoldWeight   = round($netWeight * ($purityPercentage / 100), 3);

        $ratePerGram = (float) ($itemData['rate_per_gram'] ?? $itemData['gold_rate_per_gram'] ?? 0);
        $goldAmount  = round($netWeight * $ratePerGram, 2);

        // Making charges computation
        $makingType = $itemData['making_charge_type'] ?? 'per_gram';
        $makingRate = (float) ($itemData['making_charge_rate'] ?? 0);
        $makingCharges = (float) ($itemData['making_charges'] ?? 0);

        if ($makingCharges <= 0 && $makingRate > 0) {
            if ($makingType === 'per_gram') {
                $makingCharges = round($netWeight * $makingRate, 2);
            } elseif ($makingType === 'percentage') {
                $makingCharges = round($goldAmount * ($makingRate / 100), 2);
            } elseif ($makingType === 'fixed') {
                $makingCharges = round($makingRate, 2);
            }
        }

        $stoneCharges     = (float) ($itemData['stone_charges'] ?? $itemData['stone_cost'] ?? $itemData['stone_value'] ?? 0);
        $hallmarkCharge   = (float) ($itemData['hallmarking_charge'] ?? 0);
        $discount         = (float) ($itemData['discount_amount'] ?? 0);

        $taxableAmount = max(0, round(($goldAmount + $makingCharges + $stoneCharges + $hallmarkCharge) - $discount, 2));

        // GST determination
        $profile = BusinessProfile::first();
        $shopIsGst = $profile ? (bool) $profile->is_gst_registered : false;
        $applyGst  = $isGstApplicable !== null ? $isGstApplicable : $shopIsGst;

        $cgstAmount = 0.00;
        $sgstAmount = 0.00;
        $igstAmount = 0.00;
        $gstRate    = 0.00;

        if ($applyGst) {
            $gstRate = (float) ($itemData['gst_rate'] ?? 3.00); // 3% standard on gold
            $shopStateCode = $profile ? (int) $profile->state_code : 19;
            $buyerCode     = (int) ($buyerStateCode ?? $shopStateCode);

            if ($buyerCode === $shopStateCode) {
                // Intra-State: 1.5% CGST + 1.5% SGST
                $halfRate   = $gstRate / 2;
                $cgstAmount = round($taxableAmount * ($halfRate / 100), 2);
                $sgstAmount = round($taxableAmount * ($halfRate / 100), 2);
            } else {
                // Inter-State: 3.0% IGST
                $igstAmount = round($taxableAmount * ($gstRate / 100), 2);
            }
        }

        $totalAmount = round($taxableAmount + $cgstAmount + $sgstAmount + $igstAmount, 2);

        return [
            'gross_weight'       => round($grossWeight, 3),
            'stone_weight'       => round($stoneWeight, 3),
            'net_weight'         => round($netWeight, 3),
            'purity_percentage'  => round($purityPercentage, 3),
            'fine_gold_weight'   => round($fineGoldWeight, 3),
            'rate_per_gram'      => round($ratePerGram, 2),
            'gold_amount'        => round($goldAmount, 2),
            'making_charge_type' => $makingType,
            'making_charge_rate' => round($makingRate, 2),
            'making_charges'     => round($makingCharges, 2),
            'stone_charges'      => round($stoneCharges, 2),
            'hallmarking_charge' => round($hallmarkCharge, 2),
            'discount_amount'    => round($discount, 2),
            'taxable_amount'     => round($taxableAmount, 2),
            'gst_rate'           => round($gstRate, 2),
            'cgst_amount'        => round($cgstAmount, 2),
            'sgst_amount'        => round($sgstAmount, 2),
            'igst_amount'        => round($igstAmount, 2),
            'total_amount'       => round($totalAmount, 2),
        ];
    }
}

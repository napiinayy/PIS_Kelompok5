<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\SplitResult;
use App\Models\ItemAssignment;
use Illuminate\Support\Collection;

class SplitCalculatorService
{
    /**
     * Proportional split: each participant pays for their assigned items
     * plus a proportional share of tax and service charge.
     */
    public function calculateProportional(Bill $bill): Collection
    {
        $bill->load(['items.assignments.participant', 'participants']);

        $subtotal     = $bill->subtotal;
        $taxAmount    = $bill->tax_amount;
        $serviceAmount = $bill->service_amount;

        $results = collect();

        foreach ($bill->participants as $participant) {
            // Sum items assigned to this participant
            $participantSubtotal = 0;

            foreach ($bill->items as $item) {
                $assignment = $item->assignments
                    ->where('participant_id', $participant->id)
                    ->first();

                if ($assignment) {
                    // If item assigned to multiple: split proportionally by qty_portion
                    $totalPortions = $item->assignments->sum('qty_portion');
                    $share = $totalPortions > 0
                        ? ($assignment->qty_portion / $totalPortions)
                        : 0;
                    $participantSubtotal += round($item->subtotal * $share, 2);
                }
            }

            // Proportional share of tax & service
            $proportion   = $subtotal > 0 ? ($participantSubtotal / $subtotal) : 0;
            $taxShare     = round($taxAmount * $proportion, 2);
            $serviceShare = round($serviceAmount * $proportion, 2);
            $total        = round($participantSubtotal + $taxShare + $serviceShare, 2);

            $results->push([
                'participant'  => $participant,
                'subtotal'     => $participantSubtotal,
                'tax_share'    => $taxShare,
                'service_share'=> $serviceShare,
                'total'        => $total,
            ]);
        }

        // Fix rounding: add any cent difference to the first participant
        $calculatedTotal = $results->sum('total');
        $grandTotal      = $bill->grand_total;
        $diff            = round($grandTotal - $calculatedTotal, 2);

        if ($diff !== 0.0 && $results->isNotEmpty()) {
            $first = $results->first();
            $first['total'] = round($first['total'] + $diff, 2);
            $results->put(0, $first);
        }

        return $results;
    }

    /**
     * Equal split: grand total divided evenly among all participants.
     */
    public function calculateEqual(Bill $bill): Collection
    {
        $bill->load('participants');

        $count      = $bill->participants->count();
        $grandTotal = $bill->grand_total;

        if ($count === 0) return collect();

        $perPerson  = round($grandTotal / $count, 2);
        $remainder  = round($grandTotal - ($perPerson * $count), 2);

        return $bill->participants->map(function ($participant, $index) use ($bill, $perPerson, $remainder) {
            $total = $index === 0 ? round($perPerson + $remainder, 2) : $perPerson;

            $taxShare     = round($bill->tax_amount / max($bill->participants->count(), 1), 2);
            $serviceShare = round($bill->service_amount / max($bill->participants->count(), 1), 2);
            $subtotal     = round($total - $taxShare - $serviceShare, 2);

            return [
                'participant'  => $participant,
                'subtotal'     => $subtotal,
                'tax_share'    => $taxShare,
                'service_share'=> $serviceShare,
                'total'        => $total,
            ];
        });
    }

    /**
     * Save calculation results to split_results table.
     */
    public function save(Bill $bill, Collection $results): void
    {
        // Delete previous results
        $bill->splitResults()->delete();

        foreach ($results as $result) {
            SplitResult::create([
                'bill_id'        => $bill->id,
                'participant_id' => $result['participant']->id,
                'subtotal'       => $result['subtotal'],
                'tax_share'      => $result['tax_share'],
                'service_share'  => $result['service_share'],
                'total'          => $result['total'],
            ]);
        }

        $bill->update(['status' => 'calculated']);
    }
}

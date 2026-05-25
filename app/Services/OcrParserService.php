<?php

namespace App\Services;

class OcrParserService
{
    /**
     * Parse raw OCR text from Google Vision into structured item list.
     * Handles formats: "1.000", "1,000", "Rp1000", "1.000,00"
     */
    public function parse(string $rawText): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($rawText));
        $items = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Try to find a price pattern in the line
            $price = $this->extractPrice($line);
            if ($price === null) continue;

            // Extract item name: everything before the price
            $name = $this->extractName($line, $price);
            if (empty($name) || strlen($name) < 2) continue;

            // Skip subtotal/total/tax lines
            $skipKeywords = ['total', 'subtotal', 'pajak', 'tax', 'service', 'diskon', 'discount', 'change', 'kembalian', 'cash', 'tunai'];
            $lowerName = strtolower($name);
            if (collect($skipKeywords)->contains(fn($k) => str_contains($lowerName, $k))) {
                continue;
            }

            $items[] = [
                'name'     => $this->cleanName($name),
                'price'    => $price,
                'quantity' => 1,
            ];
        }

        return $items;
    }

    private function extractPrice(string $line): ?float
    {
        // Patterns: Rp 12.000, 12.000,00, 12,000.00, 12000
        $patterns = [
            '/Rp\.?\s*([\d.,]+)/',
            '/([\d]{1,3}(?:\.\d{3})+(?:,\d{2})?)/',   // Indonesian: 12.000,00
            '/([\d]{1,3}(?:,\d{3})+(?:\.\d{2})?)/',    // US: 12,000.00
            '/(\d{4,})/',                                // Plain number >= 4 digits
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $line, $m)) {
                return $this->normalizePrice($m[1]);
            }
        }

        return null;
    }

    private function normalizePrice(string $raw): float
    {
        // Remove Rp prefix
        $raw = preg_replace('/^Rp\.?\s*/i', '', $raw);

        // Indonesian format: 12.000 or 12.000,00
        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d{2})?$/', $raw)) {
            $raw = str_replace('.', '', $raw);
            $raw = str_replace(',', '.', $raw);
            return (float) $raw;
        }

        // US format: 12,000 or 12,000.00
        if (preg_match('/^\d{1,3}(,\d{3})+(\.\d{2})?$/', $raw)) {
            $raw = str_replace(',', '', $raw);
            return (float) $raw;
        }

        // Simple: remove all dots/commas except last decimal
        $raw = str_replace(['.', ','], '', $raw);
        return (float) $raw;
    }

    private function extractName(string $line, float $price): string
    {
        // Remove the price and Rp prefix from the line
        $priceStr = number_format($price, 0, ',', '.');
        $line = preg_replace('/Rp\.?\s*[\d.,]+/', '', $line);
        $line = preg_replace('/[\d.,]{4,}/', '', $line);
        $line = preg_replace('/\s{2,}/', ' ', $line);
        return trim($line);
    }

    private function cleanName(string $name): string
    {
        // Remove leading numbers like "1. ", "1 "
        $name = preg_replace('/^\d+[\.\)]\s*/', '', $name);
        // Remove trailing special chars
        $name = rtrim($name, '.:;-|');
        return trim($name);
    }
}

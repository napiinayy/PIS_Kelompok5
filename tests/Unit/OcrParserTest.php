<?php

use App\Services\OcrParserService;

beforeEach(function () {
    $this->parser = new OcrParserService();
});

test('parses indonesian price format with dots', function () {
    $text = "Nasi Goreng\t25.000\nAyam Bakar\t35.000";
    $items = $this->parser->parse($text);

    expect($items)->toHaveCount(2);
    expect($items[0]['name'])->toBe('Nasi Goreng');
    expect($items[0]['price'])->toBe(25000.0);
    expect($items[1]['price'])->toBe(35000.0);
});

test('parses price with Rp prefix', function () {
    $text = "Es Teh Manis Rp 8.000\nJus Alpukat Rp15.000";
    $items = $this->parser->parse($text);

    expect($items[0]['price'])->toBe(8000.0);
    expect($items[1]['price'])->toBe(15000.0);
});

test('parses price with comma decimal format', function () {
    $text = "Steak 125.000,00";
    $items = $this->parser->parse($text);

    expect($items[0]['price'])->toBe(125000.0);
});

test('skips total and subtotal lines', function () {
    $text = "Nasi Goreng\t25.000\nSubtotal\t25.000\nPajak\t2.500\nTotal\t27.500";
    $items = $this->parser->parse($text);

    expect($items)->toHaveCount(1);
    expect($items[0]['name'])->toBe('Nasi Goreng');
});

test('skips lines without prices', function () {
    $text = "Terima kasih\nNasi Goreng\t25.000\nSilakan datang lagi";
    $items = $this->parser->parse($text);

    expect($items)->toHaveCount(1);
});

test('removes leading item numbers from names', function () {
    $text = "1. Nasi Goreng\t25.000\n2) Ayam Bakar\t35.000";
    $items = $this->parser->parse($text);

    expect($items[0]['name'])->toBe('Nasi Goreng');
    expect($items[1]['name'])->toBe('Ayam Bakar');
});

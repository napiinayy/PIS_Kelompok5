<?php

namespace App\Jobs;

use App\Models\ReceiptScan;
use App\Models\BillItem;
use App\Services\OcrParserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessReceiptOcr implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(public ReceiptScan $scan) {}

    public function handle(OcrParserService $parser): void
    {
        $this->scan->update(['status' => 'processing']);

        try {
            $imageData = base64_encode(Storage::get($this->scan->image_path));
            $apiKey    = config('services.google_vision.api_key');

            if (empty($apiKey)) {
                // Fallback: use mock data for demo/testing
                $this->handleWithMock($parser);
                return;
            }

            $response = Http::post(
                "https://vision.googleapis.com/v1/images:annotate?key={$apiKey}",
                [
                    'requests' => [[
                        'image'    => ['content' => $imageData],
                        'features' => [['type' => 'TEXT_DETECTION']],
                    ]],
                ]
            );

            if ($response->failed()) {
                throw new \Exception('Vision API error: ' . $response->body());
            }

            $text = $response->json('responses.0.textAnnotations.0.description', '');
            $items = $parser->parse($text);

            $this->scan->update([
                'status'         => 'done',
                'raw_ocr_result' => ['text' => $text, 'parsed_items' => $items],
            ]);

        } catch (\Exception $e) {
            Log::error('OCR failed: ' . $e->getMessage());
            $this->scan->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    private function handleWithMock(OcrParserService $parser): void
    {
        // Demo mock items when no API key is configured
        $mockText = "Nasi Goreng Spesial\t25.000\nAyam Bakar\t35.000\nEs Teh Manis\t8.000\nJus Alpukat\t15.000";
        $items = $parser->parse($mockText);

        $this->scan->update([
            'status'         => 'done',
            'raw_ocr_result' => ['text' => $mockText, 'parsed_items' => $items, 'mode' => 'mock'],
        ]);
    }
}

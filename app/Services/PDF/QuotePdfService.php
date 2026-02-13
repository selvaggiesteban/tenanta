<?php

namespace App\Services\PDF;

use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class QuotePdfService
{
    /**
     * Generate PDF for a quote.
     */
    public function generate(Quote $quote): string
    {
        $quote->load(['client', 'items', 'tenant']);

        $pdf = Pdf::loadView('pdf.quote', [
            'quote' => $quote,
            'tenant' => $quote->tenant,
            'client' => $quote->client,
            'items' => $quote->items,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    /**
     * Generate and store PDF.
     */
    public function generateAndStore(Quote $quote): string
    {
        $content = $this->generate($quote);

        $filename = "quotes/{$quote->tenant_id}/{$quote->number}.pdf";
        Storage::disk('local')->put($filename, $content);

        return $filename;
    }

    /**
     * Stream PDF to browser.
     */
    public function stream(Quote $quote)
    {
        $quote->load(['client', 'items', 'tenant']);

        $pdf = Pdf::loadView('pdf.quote', [
            'quote' => $quote,
            'tenant' => $quote->tenant,
            'client' => $quote->client,
            'items' => $quote->items,
        ]);

        return $pdf->stream("cotizacion-{$quote->number}.pdf");
    }

    /**
     * Download PDF.
     */
    public function download(Quote $quote)
    {
        $quote->load(['client', 'items', 'tenant']);

        $pdf = Pdf::loadView('pdf.quote', [
            'quote' => $quote,
            'tenant' => $quote->tenant,
            'client' => $quote->client,
            'items' => $quote->items,
        ]);

        return $pdf->download("cotizacion-{$quote->number}.pdf");
    }
}

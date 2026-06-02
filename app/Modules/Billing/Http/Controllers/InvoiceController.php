<?php

declare(strict_types=1);

namespace App\Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Modules\Deals\Services\PricingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    public function __construct(private readonly PricingService $money) {}

    /** Download a branded invoice PDF (tenant-scoped via route binding). */
    public function download(Invoice $invoice): Response
    {
        $invoice->load('items');

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'tenant' => tenant(),
            'money' => $this->money,
        ]);

        return $pdf->download("{$invoice->number}.pdf");
    }
}

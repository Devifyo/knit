<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Analytics\Exports\ArrayReportExport;
use App\Modules\Analytics\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as ResponseFactory;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reports) {}

    public function index(Request $request): Response
    {
        $filters = $this->filters($request);
        $report = $this->reports->build($filters);

        return Inertia::render('Reports/Index', [
            'filters' => $filters,
            'report' => $report,
            'owners' => User::where('tenant_id', tenant('id'))->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function export(Request $request, string $format): HttpResponse|StreamedResponse|\Illuminate\Http\Response
    {
        $report = $this->reports->build($this->filters($request));
        $name = str($report['title'])->slug().'-'.now()->format('Ymd');

        return match ($format) {
            'xlsx' => Excel::download(new ArrayReportExport($report['rows'], $report['headers']), "{$name}.xlsx"),
            'pdf' => Pdf::loadView('pdf.report', [
                'report' => $report,
                'tenantName' => tenant('name'),
                'brandColor' => tenant()->brand_color,
                'generatedAt' => now()->toDayDateTimeString(),
            ])->download("{$name}.pdf"),
            default => $this->csv($report, "{$name}.csv"),
        };
    }

    /**
     * @param  array{title:string, headers:array<int,string>, rows:array<int,array<int,string>>, summary:array<string,string>}  $report
     */
    protected function csv(array $report, string $filename): StreamedResponse
    {
        return ResponseFactory::streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $report['headers']);
            foreach ($report['rows'] as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * @return array<string, mixed>
     */
    protected function filters(Request $request): array
    {
        return [
            'entity' => $request->string('entity', 'deals')->toString(),
            'owner_id' => $request->input('owner_id') ?: null,
            'status' => $request->input('status') ?: null,
            'from' => $request->input('from') ?: null,
            'to' => $request->input('to') ?: null,
        ];
    }
}

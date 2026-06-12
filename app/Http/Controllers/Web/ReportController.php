<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;

        $this->middleware(function ($request, $next) {
            if (!$request->user() || !$request->user()->isAdmin()) {
                abort(403);
            }
            return $next($request);
        });
    }

    /**
     * Show the reports dashboard
     */
    public function index()
    {
        $reports = Report::orderBy('created_at', 'desc')->paginate(20);
        $types = Report::TYPES;
        
        return view('admin.reports.index', compact('reports', 'types'));
    }

    /**
     * Show the create report form
     */
    public function create()
    {
        $types = Report::TYPES;
        return view('admin.reports.create', compact('types'));
    }

    /**
     * Store a newly created report
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:' . implode(',', array_keys(Report::TYPES)),
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'filters' => 'nullable|array',
        ]);

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();

        // Generate report data based on type
        $data = match ($validated['type']) {
            'transaction' => $this->reportService->generateTransactionReport($startDate, $endDate, $validated['filters'] ?? []),
            'customer' => $this->reportService->generateCustomerReport($startDate, $endDate, $validated['filters'] ?? []),
            'sim_card' => $this->reportService->generateSimCardReport($startDate, $endDate, $validated['filters'] ?? []),
            'revenue' => $this->reportService->generateRevenueReport($startDate, $endDate, $validated['filters'] ?? []),
            'summary' => $this->reportService->generateSummaryReport($startDate, $endDate),
        };

        $report = Report::create([
            'title' => Report::TYPES[$validated['type']] . ' (' . $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d') . ')',
            'type' => $validated['type'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'filters' => $validated['filters'] ?? null,
            'data' => $data,
            'generated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.reports.show', $report)->with('success', 'Report generated successfully!');
    }

    /**
     * Display the specified report
     */
    public function show(Report $report)
    {
        return view('admin.reports.show', compact('report'));
    }

    /**
     * Export report as PDF
     */
    public function exportPdf(Report $report)
    {
        return back()->with('error', 'PDF export is not configured yet. Please use Word or CSV export.');
    }

    /**
     * Export report as CSV
     */
    public function exportCsv(Report $report)
    {
        $filename = 'report_' . $report->type . '_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        return response()->streamDownload(function () use ($report) {
            $output = fopen('php://output', 'w');
            
            // Write header
            fputcsv($output, ['Report Type: ' . $report->getTypeLabel()]);
            fputcsv($output, ['Generated: ' . $report->created_at->format('Y-m-d H:i:s')]);
            fputcsv($output, ['Period: ' . $report->start_date->format('Y-m-d') . ' to ' . $report->end_date->format('Y-m-d')]);
            fputcsv($output, []);
            
            // Export data based on report type
            $this->exportReportDataCsv($output, $report);
            
            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    /**
     * Export report as a Word-compatible document.
     */
    public function exportWord(Report $report)
    {
        $filename = 'report_' . $report->type . '_' . now()->format('Y-m-d_H-i-s') . '.doc';

        return response()
            ->view('admin.reports.word', compact('report'))
            ->header('Content-Type', 'application/msword; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Helper method to export specific report type data to CSV
     */
    private function exportReportDataCsv($output, Report $report)
    {
        $data = $report->data;

        switch ($report->type) {
            case 'transaction':
                fputcsv($output, ['Transaction ID', 'Reference', 'From', 'To', 'Amount', 'Fee', 'Status', 'Date']);
                foreach ($data['transactions'] ?? [] as $transaction) {
                    fputcsv($output, [
                        $transaction['id'],
                        $transaction['reference'],
                        $transaction['from'],
                        $transaction['to'],
                        $transaction['amount'],
                        $transaction['fee'],
                        $transaction['status'],
                        $transaction['created_at'],
                    ]);
                }
                break;

            case 'customer':
                fputcsv($output, ['Customer ID', 'Name', 'Email', 'Phone', 'SIMs Count', 'Active SIMs', 'Total Balance', 'Created Date']);
                foreach ($data['customers'] ?? [] as $customer) {
                    fputcsv($output, [
                        $customer['id'],
                        $customer['name'],
                        $customer['email'],
                        $customer['phone'],
                        $customer['sims_count'],
                        $customer['active_sims'],
                        $customer['total_balance'],
                        $customer['created_at'],
                    ]);
                }
                break;

            case 'sim_card':
                fputcsv($output, ['SIM ID', 'SIM Number', 'Phone Number', 'Customer', 'Balance', 'Data Balance', 'Tariff', 'Status', 'Created Date']);
                foreach ($data['sim_cards'] ?? [] as $sim) {
                    fputcsv($output, [
                        $sim['id'],
                        $sim['sim_number'],
                        $sim['phone_number'],
                        $sim['customer'],
                        $sim['balance'],
                        $sim['data_balance'],
                        $sim['tariff_plan'],
                        $sim['status'],
                        $sim['created_at'],
                    ]);
                }
                break;

            case 'revenue':
                fputcsv($output, ['Metric', 'Value']);
                fputcsv($output, ['Total Revenue', $data['total_revenue']]);
                fputcsv($output, ['Total Fees', $data['total_fees']]);
                fputcsv($output, ['Net Revenue', $data['net_revenue']]);
                fputcsv($output, ['Transaction Count', $data['transaction_count']]);
                fputcsv($output, ['Average Transaction', $data['average_transaction']]);
                break;

            case 'summary':
                fputcsv($output, ['Metric', 'Value']);
                foreach ($data['summary'] ?? [] as $key => $value) {
                    fputcsv($output, [str_replace('_', ' ', ucwords($key)), $value]);
                }
                break;
        }
    }

    /**
     * Delete a report
     */
    public function destroy(Report $report)
    {
        $report->delete();
        return redirect()->route('admin.reports.index')->with('success', 'Report deleted successfully.');
    }
}

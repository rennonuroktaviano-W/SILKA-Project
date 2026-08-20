<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFilterRequest;
use App\Models\Kategori;
use App\Services\ReportService;
use App\Support\PdfRenderer;
use Illuminate\Support\Str;

class LaporanController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(ReportFilterRequest $request)
    {
        $kategoris = $this->reportService->kategoris();

        $filters = [
            'kategori_id' => $request->kategori_id,
            'jenis' => $request->jenis,
            'tanggal_awal' => $request->tanggal_awal,
            'tanggal_akhir' => $request->tanggal_akhir,
        ];

        $hasFilter = $request->filled('kategori_id') || $request->filled('jenis') ||
            $request->filled('tanggal_awal') || $request->filled('tanggal_akhir');

        $transaksis = $hasFilter ? $this->reportService->query($filters)->paginate(50)->withQueryString() : null;
        $totals = $hasFilter ? $this->reportService->totals($filters) : null;

        $chart = null;
        if ($hasFilter) {
            $trend = $this->reportService->trendMonthly($filters);
            $chart = [
                'labels' => $trend->pluck('bulan')->map(function ($b) {
                    return \Carbon\Carbon::createFromFormat('Y-m', $b)->translatedFormat('M Y');
                })->all(),
                'pemasukan' => $trend->pluck('pemasukan')->map(fn ($v) => (float) $v)->all(),
                'pengeluaran' => $trend->pluck('pengeluaran')->map(fn ($v) => (float) $v)->all(),
            ];
        }

        return view('laporan.index', compact('kategoris', 'transaksis', 'totals', 'filters', 'hasFilter', 'chart'));
    }

    public function print(ReportFilterRequest $request)
    {
        $filters = [
            'kategori_id' => $request->kategori_id,
            'jenis' => $request->jenis,
            'tanggal_awal' => $request->tanggal_awal,
            'tanggal_akhir' => $request->tanggal_akhir,
        ];

        $hasFilter = $request->filled('kategori_id') || $request->filled('jenis') ||
            $request->filled('tanggal_awal') || $request->filled('tanggal_akhir');

        $transaksis = $hasFilter ? $this->reportService->data($filters) : collect();
        $totals = $hasFilter ? $this->reportService->totals($filters) : null;
        $kategori = $filters['kategori_id'] ? Kategori::find($filters['kategori_id']) : null;

        return view('laporan.print', compact('transaksis', 'totals', 'filters', 'kategori', 'hasFilter'));
    }

    public function pdf(ReportFilterRequest $request)
    {
        $filters = [
            'kategori_id' => $request->kategori_id,
            'jenis' => $request->jenis,
            'tanggal_awal' => $request->tanggal_awal,
            'tanggal_akhir' => $request->tanggal_akhir,
        ];

        $hasFilter = $request->filled('kategori_id') || $request->filled('jenis') ||
            $request->filled('tanggal_awal') || $request->filled('tanggal_akhir');

        $transaksis = $hasFilter ? $this->reportService->data($filters) : collect();
        $totals = $hasFilter ? $this->reportService->totals($filters) : null;
        $kategori = $filters['kategori_id'] ? Kategori::find($filters['kategori_id']) : null;

        $trend = $hasFilter ? $this->reportService->trendMonthly($filters) : collect();
        $chart = $this->chartData($trend);

        $html = view('laporan.pdf', compact('transaksis', 'totals', 'filters', 'kategori', 'hasFilter', 'chart'))->render();

        return PdfRenderer::render($html, 'laporan-transaksi-' . date('Y-m-d') . '.pdf');
    }

    protected function chartData($trend)
    {
        if ($trend->isEmpty()) {
            return null;
        }

        $max = max(
            (float) $trend->max('pemasukan'),
            (float) $trend->max('pengeluaran'),
            1
        );

        return [
            'max' => $max,
            'items' => $trend->map(function ($row) use ($max) {
                return [
                    'label' => \Carbon\Carbon::createFromFormat('Y-m', $row->bulan)->translatedFormat('M y'),
                    'pemasukan' => (float) $row->pemasukan,
                    'pengeluaran' => (float) $row->pengeluaran,
                    'pemasukanPct' => max(2, round((float) $row->pemasukan / $max * 100, 1)),
                    'pengeluaranPct' => max(2, round((float) $row->pengeluaran / $max * 100, 1)),
                ];
            })->all(),
        ];
    }

    public function export(ReportFilterRequest $request)
    {
        $filters = [
            'kategori_id' => $request->kategori_id,
            'jenis' => $request->jenis,
            'tanggal_awal' => $request->tanggal_awal,
            'tanggal_akhir' => $request->tanggal_akhir,
        ];

        $transaksis = $this->reportService->data($filters);
        $totals = $this->reportService->totals($filters);

        $meta = [];
        if ($filters['tanggal_awal'] && $filters['tanggal_akhir']) {
            $meta['periode'] = date('d-m-Y', strtotime($filters['tanggal_awal']))
                . ' s/d ' . date('d-m-Y', strtotime($filters['tanggal_akhir']));
        } elseif ($filters['tanggal_awal']) {
            $meta['periode'] = 'dari ' . date('d-m-Y', strtotime($filters['tanggal_awal']));
        } elseif ($filters['tanggal_akhir']) {
            $meta['periode'] = 'sampai ' . date('d-m-Y', strtotime($filters['tanggal_akhir']));
        }
        if ($filters['kategori_id']) {
            $kategori = Kategori::find($filters['kategori_id']);
            if ($kategori) {
                $meta['kategori'] = $kategori->kategori;
            }
        }

        $filename = 'laporan-transaksi-' . date('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($transaksis, $totals, $meta) {
            echo \App\Exports\TransaksiReportExport::build($transaksis, $totals, $meta);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}

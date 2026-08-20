<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTargetCapaianRequest;
use App\Http\Requests\UpdateTargetCapaianRequest;
use App\Models\TargetCapaian;
use App\Models\Transaksi;
use App\Exports\SpreadsheetExport;
use App\Support\PdfRenderer;
use Illuminate\Support\Facades\DB;

class TargetCapaianController extends Controller
{
    public function index()
    {
        $targets = TargetCapaian::orderBy('tahun', 'desc')->paginate(20);

        return view('target-capaians.index', compact('targets'));
    }

    public function pdf()
    {
        $targets = TargetCapaian::orderBy('tahun', 'desc')->get();

        $tahuns = $targets->pluck('tahun')->filter()->unique()->values()->all();

        $realisasiByTahun = Transaksi::where('jenis', 'pemasukan')
            ->whereIn(DB::raw('YEAR(tanggal)'), $tahuns)
            ->selectRaw('YEAR(tanggal) as tahun, SUM(nominal) as total')
            ->groupBy(DB::raw('YEAR(tanggal)'))
            ->pluck('total', 'tahun')
            ->map(fn ($value) => (float) $value);

        $html = view('target-capaians.pdf', compact('targets', 'realisasiByTahun'))->render();

        return PdfRenderer::render($html, 'target-capaian-' . date('Y-m-d') . '.pdf');
    }

    public function export()
    {
        $targets = TargetCapaian::orderBy('tahun', 'desc')->get();

        $tahuns = $targets->pluck('tahun')->filter()->unique()->values()->all();

        $realisasiByTahun = Transaksi::where('jenis', 'pemasukan')
            ->whereIn(DB::raw('YEAR(tanggal)'), $tahuns)
            ->selectRaw('YEAR(tanggal) as tahun, SUM(nominal) as total')
            ->groupBy(DB::raw('YEAR(tanggal)'))
            ->pluck('total', 'tahun')
            ->map(fn ($value) => (float) $value);

        $rows = [];
        $totalTarget = 0.0;
        $totalRealisasi = 0.0;

        foreach ($targets as $target) {
            $realisasi = $realisasiByTahun->get($target->tahun, 0.0);
            $persen = $target->target_capaian > 0
                ? round($realisasi / (float) $target->target_capaian * 100, 2)
                : 0;

            $totalTarget += (float) $target->target_capaian;
            $totalRealisasi += $realisasi;

            $rows[] = [
                (int) $target->tahun,
                (float) $target->target_capaian,
                $realisasi,
                $persen,
            ];
        }

        $persenTotal = $totalTarget > 0 ? round($totalRealisasi / $totalTarget * 100, 2) : 0;

        $filename = 'target-capaian-' . date('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($rows, $totalTarget, $totalRealisasi, $persenTotal) {
            echo SpreadsheetExport::build([
                'sheetName' => 'Target Capaian',
                'title' => 'TARGET CAPAIAN TAHUNAN',
                'subtitle' => 'Dicetak ' . date('d-m-Y H:i'),
                'columns' => ['Tahun', 'Target Capaian', 'Realisasi Pemasukan', 'Capaian (%)'],
                'widths' => [10, 20, 22, 14],
                'currencyColumns' => [1, 2],
                'rows' => $rows,
                'footer' => [
                    ['TOTAL', $totalTarget, $totalRealisasi, $persenTotal],
                ],
            ]);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function create()
    {
        return view('target-capaians.create');
    }

    public function store(StoreTargetCapaianRequest $request)
    {
        TargetCapaian::create($request->validated());

        return redirect()->route('target-capaians.index')
            ->with('success', 'Target capaian berhasil ditambahkan.');
    }

    public function edit(TargetCapaian $targetCapaian)
    {
        return view('target-capaians.edit', compact('targetCapaian'));
    }

    public function update(UpdateTargetCapaianRequest $request, TargetCapaian $targetCapaian)
    {
        $targetCapaian->update($request->validated());

        return redirect()->route('target-capaians.index')
            ->with('success', 'Target capaian berhasil diperbarui.');
    }

    public function destroy(TargetCapaian $targetCapaian)
    {
        $targetCapaian->delete();

        return redirect()->route('target-capaians.index')
            ->with('success', 'Target capaian berhasil dihapus.');
    }
}

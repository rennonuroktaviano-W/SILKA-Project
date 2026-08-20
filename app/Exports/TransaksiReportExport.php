<?php

namespace App\Exports;

/**
 * Generator file .xlsx untuk laporan transaksi.
 * Membangun baris data lalu menyerahkan pembuatan OOXML ke SpreadsheetExport.
 */
class TransaksiReportExport
{
    /**
     * Buat konten biner .xlsx.
     *
     * @param \Illuminate\Support\Collection $transaksis
     * @param array $totals
     * @param array $meta (opsional): 'periode' dan 'kategori' untuk subjudul
     * @return string
     */
    public static function build($transaksis, array $totals, array $meta = [])
    {
        $columns = ['No', 'Tanggal', 'Jenis', 'Kategori', 'Kode COA', 'Nama COA', 'Keterangan', 'Nominal'];

        $rows = [];
        $no = 1;
        foreach ($transaksis as $t) {
            $rows[] = [
                $no,
                $t->tanggal instanceof \Carbon\CarbonInterface
                    ? $t->tanggal->format('d-m-Y')
                    : date('d-m-Y', strtotime($t->tanggal)),
                self::labelJenis($t->jenis),
                optional($t->kategori)->kategori ?? '',
                optional($t->coa)->kode_coa ?? '',
                optional($t->coa)->nama_coa ?? '',
                (string) $t->keterangan,
                (float) $t->nominal,
            ];
            $no++;
        }

        $subtitle = 'Dicetak ' . date('d-m-Y H:i');
        if (!empty($meta['periode'])) {
            $subtitle .= '  •  Periode: ' . $meta['periode'];
        }
        if (!empty($meta['kategori'])) {
            $subtitle .= '  •  Kategori: ' . $meta['kategori'];
        }

        return SpreadsheetExport::build([
            'sheetName' => 'Laporan Transaksi',
            'title' => 'LAPORAN TRANSAKSI KEUANGAN',
            'subtitle' => $subtitle,
            'columns' => $columns,
            'widths' => [5, 12, 14, 18, 12, 24, 34, 16],
            'currencyColumns' => [7],
            'rows' => $rows,
            'footer' => [
                ['Total Pemasukan', '', '', '', '', '', '', (float) $totals['pemasukan']],
                ['Total Pengeluaran', '', '', '', '', '', '', (float) $totals['pengeluaran']],
                ['Selisih Bersih', '', '', '', '', '', '', (float) $totals['selisih']],
            ],
        ]);
    }

    protected static function labelJenis($jenis)
    {
        return $jenis === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran';
    }
}

@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
    <div class="card">
        <div class="card-header">
            <span>Filter Laporan</span>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.index') }}">
                <div class="form-row">
                    <div class="form-group">
                        <label for="kategori_id">Kategori</label>
                        <select name="kategori_id" id="kategori_id" class="form-control">
                            <option value="">Semua Kategori</option>
                            @foreach ($kategoris as $kategori)
                                <option value="{{ $kategori->id }}" {{ $filters['kategori_id'] == $kategori->id ? 'selected' : '' }}>{{ $kategori->kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="jenis">Jenis</label>
                        <select name="jenis" id="jenis" class="form-control">
                            <option value="">Semua Jenis</option>
                            <option value="pemasukan" {{ $filters['jenis'] == 'pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                            <option value="pengeluaran" {{ $filters['jenis'] == 'pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tanggal_awal">Tanggal Awal</label>
                        <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="{{ $filters['tanggal_awal'] }}">
                    </div>
                    <div class="form-group">
                        <label for="tanggal_akhir">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="{{ $filters['tanggal_akhir'] }}">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Tampilkan Laporan
                    </button>
                    <a href="{{ route('laporan.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    @if ($hasFilter)
        <div class="card">
            <div class="card-header">
                <span>Hasil Laporan</span>
                <span class="no-print" style="display:flex;gap:var(--sp-2)">
                    <a href="{{ route('laporan.print', request()->query()) }}" class="btn btn-secondary btn-sm" target="_blank">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                        Cetak
                    </a>
                    <a href="{{ route('laporan.export', request()->query()) }}" class="btn btn-primary btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                        Export Excel
                    </a>
                </span>
            </div>
            <div class="card-body">
                @if ($transaksis->isEmpty())
                    <div class="empty-state">
                        <svg class="empty-state-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>
                        <p>Tidak ada transaksi yang cocok dengan filter.</p>
                    </div>
                @else
                    <div class="table-wrap">
                        <table class="table table-mobile">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Kategori</th>
                                    <th>COA</th>
                                    <th>Keterangan</th>
                                    <th class="text-right">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transaksis as $index => $transaksi)
                                    <tr>
                                        <td data-label="No">{{ $transaksis->firstItem() + $index }}</td>
                                        <td data-label="Tanggal">{{ $transaksi->tanggal->format('d-m-Y') }}</td>
                                        <td data-label="Jenis">
                                            @if ($transaksi->isPemasukan())
                                                <span class="badge badge-masuk">Pemasukan</span>
                                            @else
                                                <span class="badge badge-keluar">Pengeluaran</span>
                                            @endif
                                        </td>
                                        <td data-label="Kategori">{{ $transaksi->kategori->kategori ?? '-' }}</td>
                                        <td data-label="COA">{{ $transaksi->coa ? $transaksi->coa->kode_coa . ' - ' . $transaksi->coa->nama_coa : '-' }}</td>
                                        <td data-label="Keterangan">{{ Str::limit($transaksi->keterangan, 60) }}</td>
                                        <td data-label="Nominal" class="nominal">{{ rupiah($transaksi->nominal) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $transaksis->links() }}
                @endif
            </div>
        </div>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon green">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                </div>
                <div class="stat-label">Total Pemasukan</div>
                <div class="stat-value green">{{ rupiah($totals['pemasukan']) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
                </div>
                <div class="stat-label">Total Pengeluaran</div>
                <div class="stat-value red">{{ rupiah($totals['pengeluaran']) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <div class="stat-label">Selisih Bersih</div>
                <div class="stat-value">{{ rupiah($totals['selisih']) }}</div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <svg class="empty-state-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                    <p>Pilih filter kategori dan rentang tanggal untuk melihat laporan.</p>
                </div>
            </div>
        </div>
    @endif
@endsection

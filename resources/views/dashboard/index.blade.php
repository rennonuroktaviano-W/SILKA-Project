@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="card">
        <div class="card-header">
            <span>Ringkasan Tahun {{ $year }}</span>
            <form method="GET" action="{{ route('dashboard') }}" class="form-inline">
                <div class="form-group">
                    <label for="year" class="sr-only">Pilih Tahun</label>
                    <select name="year" id="year" class="form-control">
                        @foreach ($tahunTersedia as $tahun)
                            <option value="{{ $tahun }}" {{ $tahun == $year ? 'selected' : '' }}>{{ $tahun }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary btn-sm">Tampilkan</button>
            </form>
        </div>
        <div class="card-body">
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                    </div>
                    <div class="stat-label">Pemasukan Hari Ini</div>
                    <div class="stat-value green">{{ rupiah($hariIni->pemasukan ?? 0) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
                    </div>
                    <div class="stat-label">Pengeluaran Hari Ini</div>
                    <div class="stat-value red">{{ rupiah($hariIni->pengeluaran ?? 0) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                    <div class="stat-label">Pemasukan Bulan Ini</div>
                    <div class="stat-value green">{{ rupiah($bulanIni->pemasukan ?? 0) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                    <div class="stat-label">Pengeluaran Bulan Ini</div>
                    <div class="stat-value red">{{ rupiah($bulanIni->pengeluaran ?? 0) }}</div>
                </div>
            </div>

            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                    </div>
                    <div class="stat-label">Pemasukan Tahun {{ $year }}</div>
                    <div class="stat-value green">{{ rupiah($tahunIni->pemasukan ?? 0) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                    </div>
                    <div class="stat-label">Pengeluaran Tahun {{ $year }}</div>
                    <div class="stat-value red">{{ rupiah($tahunIni->pengeluaran ?? 0) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg>
                    </div>
                    <div class="stat-label">Target Capaian {{ $year }}</div>
                    <div class="stat-value">{{ $target ? rupiah($target->target_capaian) : 'Belum ditentukan' }}</div>
                </div>
            </div>

            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-icon neutral">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="M7 21l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                    </div>
                    <div class="stat-label">Piutang Tahun {{ $year - 1 }}</div>
                    <div class="stat-value">{{ rupiah($piutang) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon neutral">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="M7 21l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                    </div>
                    <div class="stat-label">Hutang Tahun {{ $year - 1 }}</div>
                    <div class="stat-value">{{ rupiah($hutang) }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection

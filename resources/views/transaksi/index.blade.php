@extends('layouts.app')

@section('title', 'Transaksi')

@section('content')
    <div class="card">
        <div class="card-header">
            <span>Daftar Transaksi</span>
            <a href="{{ route('transaksi.create') }}" class="btn btn-primary btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                Tambah Transaksi
            </a>
        </div>
        <div class="card-body">
            <div class="filter-bar">
                <form method="GET" action="{{ route('transaksi.index') }}">
                    <div class="form-group">
                        <label for="search">Cari Keterangan</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Cari...">
                    </div>
                    <div class="form-group">
                        <label for="tanggal_awal">Tanggal Awal</label>
                        <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="{{ request('tanggal_awal') }}">
                    </div>
                    <div class="form-group">
                        <label for="tanggal_akhir">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}">
                    </div>
                    <div class="form-group">
                        <label for="jenis">Jenis</label>
                        <select name="jenis" id="jenis" class="form-control">
                            <option value="">Semua</option>
                            <option value="pemasukan" {{ request('jenis') == 'pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                            <option value="pengeluaran" {{ request('jenis') == 'pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="kategori_id">Kategori</label>
                        <select name="kategori_id" id="kategori_id" class="form-control">
                            <option value="">Semua</option>
                            @foreach ($kategoris as $kategori)
                                <option value="{{ $kategori->id }}" {{ request('kategori_id') == $kategori->id ? 'selected' : '' }}>{{ $kategori->kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="coa_id">Akun COA</label>
                        <select name="coa_id" id="coa_id" class="form-control">
                            <option value="">Semua</option>
                            @foreach ($coas as $coa)
                                <option value="{{ $coa->id }}" {{ request('coa_id') == $coa->id ? 'selected' : '' }}>{{ $coa->kode_coa }} - {{ $coa->nama_coa }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                    <a href="{{ route('transaksi.index') }}" class="btn btn-ghost btn-sm">Reset</a>
                </form>
            </div>

            <div class="table-wrap">
                <table class="table table-mobile">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Kategori</th>
                            <th>COA</th>
                            <th class="text-right">Nominal</th>
                            <th>Keterangan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transaksis as $transaksi)
                            <tr>
                                <td data-label="Tanggal">{{ $transaksi->tanggal->format('d-m-Y') }}</td>
                                <td data-label="Jenis">
                                    @if ($transaksi->isPemasukan())
                                        <span class="badge badge-masuk">Pemasukan</span>
                                    @else
                                        <span class="badge badge-keluar">Pengeluaran</span>
                                    @endif
                                </td>
                                <td data-label="Kategori">{{ $transaksi->kategori->kategori ?? '-' }}</td>
                                <td data-label="COA">
                                    @if ($transaksi->coa)
                                        {{ $transaksi->coa->kode_coa }} - {{ $transaksi->coa->nama_coa }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td data-label="Nominal" class="nominal">{{ rupiah($transaksi->nominal) }}</td>
                                <td data-label="Keterangan">{{ Str::limit($transaksi->keterangan, 50) }}</td>
                                <td data-label="Aksi" class="text-center">
                                    <div class="action-group">
                                        <a href="{{ route('transaksi.edit', $transaksi->id) }}" class="btn btn-secondary btn-sm">Edit</a>
                                        <span class="action-divider"></span>
                                        <form method="POST" action="{{ route('transaksi.destroy', $transaksi->id) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <svg class="empty-state-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                                        <p>Belum ada data transaksi.</p>
                                        <a href="{{ route('transaksi.create') }}" class="btn btn-primary">Tambah Transaksi</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $transaksis->links() }}
        </div>
    </div>
@endsection

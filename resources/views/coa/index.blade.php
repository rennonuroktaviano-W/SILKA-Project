@extends('layouts.app')

@section('title', 'COA')

@section('content')
    <div class="card">
        <div class="card-header">
            <span>Daftar Chart of Accounts</span>
            <a href="{{ route('coa.create') }}" class="btn btn-primary btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                Tambah COA
            </a>
        </div>
        <div class="card-body">
            <div class="filter-bar">
                <form method="GET" action="{{ route('coa.index') }}">
                    <div class="form-group">
                        <label for="search">Cari Kode / Nama</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Cari...">
                    </div>
                    <div class="form-group">
                        <label for="cluster">Cluster</label>
                        <select name="cluster" id="cluster" class="form-control">
                            <option value="">Semua</option>
                            @foreach ($clusters as $cluster)
                                <option value="{{ $cluster->id_cluster }}" {{ request('cluster') == $cluster->id_cluster ? 'selected' : '' }}>{{ $cluster->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                    <a href="{{ route('coa.index') }}" class="btn btn-ghost btn-sm">Reset</a>
                </form>
            </div>

            <div class="table-wrap">
                <table class="table table-mobile">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama COA</th>
                            <th>Jenis</th>
                            <th>Cluster</th>
                            <th class="text-right">Saldo</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($coas as $coa)
                            <tr>
                                <td data-label="Kode"><code style="font-size:var(--fs-xs);background:var(--surface-hover);padding:2px 6px;border-radius:4px">{{ $coa->kode_coa }}</code></td>
                                <td data-label="Nama COA">{{ $coa->nama_coa }}</td>
                                <td data-label="Jenis"><span class="badge badge-neutral">{{ $coa->jenis }}</span></td>
                                <td data-label="Cluster">{{ $coa->clusterModel->nama ?? '-' }}</td>
                                <td data-label="Saldo" class="nominal">{{ rupiah($coa->saldo) }}</td>
                                <td data-label="Aksi" class="text-center">
                                    <div class="action-group">
                                        <a href="{{ route('coa.edit', $coa->id) }}" class="btn btn-secondary btn-sm">Edit</a>
                                        <span class="action-divider"></span>
                                        <form method="POST" action="{{ route('coa.destroy', $coa->id) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus COA ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <svg class="empty-state-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                                        <p>Belum ada data COA.</p>
                                        <a href="{{ route('coa.create') }}" class="btn btn-primary">Tambah COA</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $coas->links() }}
        </div>
    </div>
@endsection

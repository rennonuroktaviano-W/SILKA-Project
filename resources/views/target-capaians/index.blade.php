@extends('layouts.app')

@section('title', 'Target Capaian')

@section('content')
    <div class="card">
        <div class="card-header">
            <span>Target Capaian Tahunan</span>
            <a href="{{ route('target-capaians.create') }}" class="btn btn-primary btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                Tambah Target
            </a>
        </div>
        <div class="card-body">
            <div class="table-wrap">
                <table class="table table-mobile">
                    <thead>
                        <tr>
                            <th>Tahun</th>
                            <th class="text-right">Target Capaian</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($targets as $target)
                            <tr>
                                <td data-label="Tahun"><strong>{{ $target->tahun }}</strong></td>
                                <td data-label="Target" class="nominal">{{ rupiah($target->target_capaian) }}</td>
                                <td data-label="Aksi" class="text-center">
                                    <div class="action-group">
                                        <a href="{{ route('target-capaians.edit', $target->id) }}" class="btn btn-secondary btn-sm">Edit</a>
                                        <span class="action-divider"></span>
                                        <form method="POST" action="{{ route('target-capaians.destroy', $target->id) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus target ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="empty-state">
                                        <svg class="empty-state-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg>
                                        <p>Belum ada target capaian.</p>
                                        <a href="{{ route('target-capaians.create') }}" class="btn btn-primary">Tambah Target</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $targets->links() }}
        </div>
    </div>
@endsection

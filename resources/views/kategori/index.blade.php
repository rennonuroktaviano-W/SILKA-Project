@extends('layouts.app')

@section('title', 'Kategori')

@section('content')
    <div class="card">
        <div class="card-header">
            <span>Daftar Kategori</span>
            <a href="{{ route('kategori.create') }}" class="btn btn-primary btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                Tambah Kategori
            </a>
        </div>
        <div class="card-body">
            <div class="table-wrap">
                <table class="table table-mobile">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Kategori</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kategoris as $kategori)
                            <tr>
                                <td data-label="ID">{{ $kategori->id }}</td>
                                <td data-label="Nama Kategori">
                                    {{ $kategori->kategori }}
                                    @if ($kategori->id === 1)
                                        <span class="badge badge-info">Default</span>
                                    @endif
                                </td>
                                <td data-label="Aksi" class="text-center">
                                    <div class="action-group">
                                        <a href="{{ route('kategori.edit', $kategori->id) }}" class="btn btn-secondary btn-sm">Edit</a>
                                        @if ($kategori->id !== 1)
                                            <span class="action-divider"></span>
                                            <form method="POST" action="{{ route('kategori.destroy', $kategori->id) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus kategori ini? Transaksi terkait akan dipindahkan ke kategori default.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                            </form>
                                        @else
                                            <span class="badge badge-neutral">Tidak dapat dihapus</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="empty-state">
                                        <svg class="empty-state-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/></svg>
                                        <p>Belum ada kategori.</p>
                                        <a href="{{ route('kategori.create') }}" class="btn btn-primary">Tambah Kategori</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $kategoris->links() }}
        </div>
    </div>
@endsection

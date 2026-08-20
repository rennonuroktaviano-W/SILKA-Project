@extends('layouts.app')

@section('title', 'User')

@section('content')
    <div class="card">
        <div class="card-header">
            <span>Daftar User</span>
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                Tambah User
            </a>
        </div>
        <div class="card-body">
            <div class="table-wrap">
                <table class="table table-mobile">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Level</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td data-label="Foto">
                                    @if ($user->foto)
                                        <img src="{{ $user->foto_url }}" alt="Foto {{ $user->name }}" class="user-avatar">
                                    @else
                                        <span class="user-avatar-placeholder">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    @endif
                                </td>
                                <td data-label="Nama"><strong>{{ $user->name }}</strong></td>
                                <td data-label="Email">{{ $user->email }}</td>
                                <td data-label="Level">
                                    @if ($user->isAdmin())
                                        <span class="badge badge-masuk">Admin</span>
                                    @else
                                        <span class="badge badge-keluar">Bendahara</span>
                                    @endif
                                </td>
                                <td data-label="Aksi" class="text-center">
                                    <div class="action-group">
                                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-secondary btn-sm">Edit</a>
                                        @if ($user->id !== auth()->id())
                                            <span class="action-divider"></span>
                                            <form method="POST" action="{{ route('users.destroy', $user->id) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                            </form>
                                        @else
                                            <span class="badge badge-neutral">Akun sendiri</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <svg class="empty-state-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                        <p>Belum ada user.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $users->links() }}
        </div>
    </div>
@endsection

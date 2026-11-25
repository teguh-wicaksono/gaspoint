@extends('layouts.app')

@section('title', 'Kelola Pengguna')
@section('breadcrumb', 'Kelola Pengguna')

@section('content')
@php
    $badge = ['pengelola' => 'success', 'kasir' => 'primary', 'kurir' => 'warning text-dark'];
    $icon  = ['pengelola' => 'user-shield', 'kasir' => 'cash-register', 'kurir' => 'motorcycle'];
@endphp
<div class="container-fluid">
    <!-- Ringkasan per role -->
    <div class="row mb-3">
        @foreach($roles as $value => $label)
        <div class="col-md-4 mb-2">
            <div class="card border-start border-4 border-{{ explode(' ', $badge[$value])[0] }}">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h6 class="text-muted mb-1">{{ $label }}</h6>
                        <h3 class="mb-0">{{ $counts[$value] ?? 0 }}</h3>
                    </div>
                    <i class="fas fa-{{ $icon[$value] }} fa-2x text-muted"></i>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-users-cog me-2 text-primary"></i><strong>Kelola Pengguna</strong>
            </h5>
            <div>
                <a href="{{ route('pengguna.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="fas fa-times me-1"></i> Reset
                </a>
                <a href="{{ route('pengguna.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-user-plus me-1"></i> Tambah Pengguna
                </a>
            </div>
        </div>

        <div class="card-body border-bottom bg-light">
            <form method="GET" class="row g-2">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Cari nama atau email..." value="{{ $filters['search'] }}">
                </div>
                <div class="col-md-4">
                    <select name="role" class="form-select form-select-sm">
                        <option value="">Semua Role</option>
                        @foreach($roles as $value => $label)
                            <option value="{{ $value }}" {{ $filters['role'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th width="15%" class="text-center">Role</th>
                            <th width="15%" class="text-center">Dibuat</th>
                            <th width="12%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                        <tr>
                            <td class="text-center">{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                            <td>
                                <strong>{{ $u->name }}</strong>
                                @if($u->id === auth()->id())
                                    <span class="badge bg-secondary ms-1">Anda</span>
                                @endif
                            </td>
                            <td>{{ $u->email }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $badge[$u->role] ?? 'secondary' }} px-3 py-1">
                                    <i class="fas fa-{{ $icon[$u->role] ?? 'user' }} me-1"></i>{{ $u->role_label }}
                                </span>
                            </td>
                            <td class="text-center text-muted">{{ $u->created_at?->format('d M Y') }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('pengguna.edit', $u) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($u->id !== auth()->id())
                                    <form action="{{ route('pengguna.destroy', $u) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Hapus pengguna {{ e($u->name) }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-0 rounded-end" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white border-top">
                <div class="row align-items-center">
                    <div class="col-md-6 text-muted">
                        Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }} dari {{ $users->total() }} pengguna
                    </div>
                    <div class="col-md-6">
                        <nav class="float-end">
                            {{ $users->links('vendor.pagination.bootstrap-5') }}
                        </nav>
                    </div>
                </div>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-users-slash fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">Tidak ada pengguna ditemukan</h5>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

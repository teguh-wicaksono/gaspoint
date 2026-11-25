@php $editing = isset($user); @endphp

<div class="mb-3">
    <label class="form-label">Nama Lengkap *</label>
    <input type="text" name="name"
           class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $user->name ?? '') }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Email *</label>
    <input type="email" name="email"
           class="form-control @error('email') is-invalid @enderror"
           value="{{ old('email', $user->email ?? '') }}" required>
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Role *</label>
    @php
        $selfEdit = $editing && auth()->id() === $user->id;
        $currentRole = old('role', $user->role ?? '');
    @endphp
    <select name="role"
            class="form-select @error('role') is-invalid @enderror"
            {{ $selfEdit ? 'disabled' : 'required' }}>
        @unless($editing)
            <option value="">-- Pilih Role --</option>
        @endunless
        @foreach($roles as $value => $label)
            <option value="{{ $value }}" {{ $currentRole === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    {{-- Select yang disabled tidak ikut terkirim, jadi kirim lewat hidden input --}}
    @if($selfEdit)
        <input type="hidden" name="role" value="{{ $user->role }}">
        <small class="text-muted">Role akun Anda sendiri tidak dapat diubah.</small>
    @else
        <small class="text-muted">
            Pengelola: akses penuh termasuk Kelola Pengguna &middot; Kasir: transaksi pesanan &middot; Kurir: pengantaran.
            Pembeli tidak memerlukan akun.
        </small>
    @endif
    @error('role')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Password {{ $editing ? '' : '*' }}</label>
        <input type="password" name="password" autocomplete="new-password"
               class="form-control @error('password') is-invalid @enderror"
               {{ $editing ? '' : 'required' }} minlength="8">
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @if($editing)
            <small class="text-muted">Kosongkan jika tidak ingin mengubah password.</small>
        @endif
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Konfirmasi Password {{ $editing ? '' : '*' }}</label>
        <input type="password" name="password_confirmation" autocomplete="new-password"
               class="form-control" {{ $editing ? '' : 'required' }} minlength="8">
    </div>
</div>

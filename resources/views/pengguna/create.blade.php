@extends('layouts.app')

@section('title', 'Tambah Pengguna')
@section('breadcrumb', 'Tambah Pengguna')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <strong>Tambah Pengguna Baru</strong>
            </div>
            <div class="card-body">
                <form action="{{ route('pengguna.store') }}" method="POST">
                    @csrf
                    @include('pengguna._form')

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('pengguna.index') }}" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

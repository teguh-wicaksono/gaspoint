@extends('layouts.app')

@section('title', 'Edit Pengguna')
@section('breadcrumb', 'Edit Pengguna')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <strong>Edit Pengguna</strong>
            </div>
            <div class="card-body">
                <form action="{{ route('pengguna.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('pengguna._form')

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('pengguna.index') }}" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

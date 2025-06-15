@extends('backend.v_layouts.app')
@section('content')
    <!-- contentAwal -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <form action="{{ route('backend.customer.update', $edit->id) }}" method="post"
                        enctype="multipart/form-data">
                        @method('put')
                        @csrf
                        <div class="card-body">
                            <h4 class="card-title"> {{ $judul }} </h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Foto</label>
                                        {{-- view image --}}
                                        @if ($edit->foto)
                                            <img src="{{ asset('storage/img-customer/' . $edit->foto) }}"
                                                class="foto-preview" width="100%">
                                            <p></p>
                                        @else
                                            <img src="{{ asset('storage/img-customer/img-default.jpg') }}"
                                                class="foto-preview" width="100%">
                                            <p></p>
                                        @endif
                                        {{-- file foto --}}
                                        {{-- <input type="file" name="foto"
                                            class="form-control @error('foto') is-invalid @enderror"
                                            onchange="previewFoto()">
                                        @error('foto')
                                            <div class="invalid-feedback alert-danger">{{ $message }}</div>
                                        @enderror --}}
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Nama</label>
                                        <span class="form-control">
                                            {{ old('nama', $edit->user->nama) }}
                                        </span>
                                    </div>
                                    <div class="form-group">
                                        <label>Email</label>
                                        <span class="form-control">
                                            {{ old('nama', $edit->user->email) }}
                                        </span>
                                    </div>
                                    <div class="form-group">
                                        <label>HP</label>
                                        <span class="form-control">
                                            {{ old('nama', $edit->user->hp) }}
                                        </span>
                                    </div>
                                    <div class="form-group">
                                        <label>Alamat</label><br>
                                        <span class="form-control">
                                            {{ old('nama', $edit->alamat) }}
                                        </span>
                                    </div>
                                    <div class="form-group">
                                        <label>Kode Pos</label>
                                        <span class="form-control">
                                            {{ old('nama', $edit->pos) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="border-top">
                                <div class="card-body">
                                    <a href="{{ route('backend.customer.index') }}">
                                        <button type="button" class="btn btn-secondary">Kembali</button>
                                    </a>
                                </div>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- contentAkhir -->
@endsection

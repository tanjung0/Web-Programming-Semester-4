@extends('backend.v_layouts.app')
@section('content')
    <!-- contentAwal -->

    <div class="row">

        <div class="col-12">
            {{-- <a href="{{ route(name: 'backend.customer.create') }}">
                <button type="button" class="btn btn-outline-primary">Tambah</button>
            </a> --}}
            <div class="card">
                <div class="card-body">
                    {{-- <h5 class="card-title"> {{ $judul }} </h5> --}}
                    <div class="table-responsive">
                        <table id="zero_config" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($index as $row)
                                    <tr>
                                        <td> {{ $loop->iteration }} </td>
                                        <td> {{ $row->user->nama }} </td>
                                        <td> {{ $row->user->email }} </td>
                                        <td>
                                            <a href="{{ route('backend.customer.show', $row->id) }}" title="Detail Data">
                                                <button type="button" class="btn btn-warning btn-sm"><i
                                                        class="fas fa-eye"></i> Detail</button>
                                            </a>

                                            <a href="{{ route('backend.customer.edit', $row->id) }}" title="Ubah Data">
                                                <button type="button" class="btn btn-cyan btn-sm"><i
                                                        class="far fa-edit"></i> Ubah</button>
                                            </a>

                                            <form method="POST"
                                                action="{{ route('backend.customer.destroy', $row->user->id) }}"
                                                style="display: inline-block;">
                                                @method('delete')
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm show_confirm"
                                                    data-konf-delete="{{ $row->nama }}" title='Hapus Data'>
                                                    <i class="fas fa-trash"></i> Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- contentAkhir -->
@endsection
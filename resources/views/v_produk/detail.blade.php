@extends('v_layouts.app')
@section('content')
    <!-- template -->
    <!-- row -->
    <div class="row">
        <div class="col-md-12">
            <div class="billing-details">
                <div class="section-title">
                    <h3 class="title">{{ $judul }} </h3>
                </div>
            </div>
        </div>
        <!-- Product Details -->
        <div class="product product-details clearfix">
            <div class="col-md-6">
                <div id="product-main-view">
                    <div class="product-view">
                        <img src="{{ asset('storage/img-produk/thumb_lg_' . $row->foto) }}" alt="">
                    </div>
                    @foreach ($fotoProdukTambahan as $item)
                        <div class="product-view">
                            @if ($item->produk_id == $row->id)
                                <img src="{{ asset('storage/img-produk/' . $item->foto) }}" alt="">
                            @else
                            @endif
                        </div>
                    @endforeach
                </div>
                <div id="product-view">
                    <div class="product-view">
                        <img src="{{ asset('storage/img-produk/thumb_sm_' . $row->foto) }}" alt="">
                    </div>
                    @foreach ($fotoProdukTambahan as $item)
                        <div class="product-view">
                            @if ($item->produk_id == $row->id)
                                <img src="{{ asset('storage/img-produk/' . $item->foto) }}" alt="">
                            @else
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-md-6">
                <div class="product-body">
                    <div class="product-label">
                        <span>Kategori</span>
                        <span class="sale">{{ $row->kategori->nama_kategori }}</span>
                    </div>
                    <h2 class="product-name">{{ $row->nama_produk }}</h2>
                    <h3 class="product-price">
                        Rp. {{ number_format($row->harga, 0, ',', '.') }}
                        @if ($row->harga_lama > 0)
                            <!-- Cek apakah harga lama lebih besar dari 0 -->
                            <span class="product-old-price"><del>Rp.
                                    {{ number_format($row->harga_lama, 0, ',', '.') }}</del></span>
                        @endif
                    </h3>
                    <p>
                        {!! $row->detail !!}
                    </p>
                    <div class="product-options">
                        <ul class="size-option">
                            <li><span class="text-uppercase">Berat:</span></li>
                            {{ $row->berat }} Kg
                        </ul>
                        <ul class="size-option">
                            <li><span class="text-uppercase">Stok:</span></li>
                            {{ $row->stok }}
                        </ul>
                    </div>
                    <div class="product-btns">
                        <form action="{{ route('order.addToCart', $row->id) }}" method="post"
                            style="display: inline-block;" title="Pesan Ke Aplikasi">
                            @csrf
                            <button type="submit" class="primary-btn add-to-cart"><i
                                    class="fa fa-shopping-cart"></i> Pesan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Product Details -->
    <!-- end template-->
@endsection

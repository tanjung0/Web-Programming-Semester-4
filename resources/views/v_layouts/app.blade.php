<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('image/icon_univ_bsi.png') }}">
    <title>tokoonline</title>

    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Hind:400,700" rel="stylesheet">

    <!-- Bootstrap -->
    <link type="text/css" rel="stylesheet" href="{{ asset('frontend/css/bootstrap.min.css') }}">

    <!-- Slick -->
    <link type="text/css" rel="stylesheet" href="{{ asset('frontend/css/slick.css') }}">
    <link type="text/css" rel="stylesheet" href="{{ asset('frontend/css/slick-theme.css') }}">

    <!-- nouislider -->
    <link type="text/css" rel="stylesheet" href="{{ asset('frontend/css/nouislider.min.css') }}">

    <!-- Font Awesome Icon -->
    <link rel="stylesheet" href="{{ asset('frontend/css/font-awesome.min.css') }}">

    <!-- Custom stlylesheet -->
    <link type="text/css" rel="stylesheet" href="{{ asset('frontend/css/style.css') }}">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->




</head>

<body>
    <!-- HEADER -->
    <header>

        <!-- header -->
        <div id="header">
            <div class="container">
                <div class="pull-left">
                    <!-- Logo -->
                    <div class="header-logo">
                        <a class="logo" href="#">
                            <img src="{{ asset('frontend/img/logo_gerabah.jpg') }}" alt=""
                                style="border-radius: 10px;">
                        </a>
                    </div>
                    <!-- /Logo -->

                    <!-- Search -->

                    <!-- /Search -->
                </div>
                <div class="pull-right">
                    <ul class="header-btns">
                        <!-- Cart -->
                        <li class="header-cart dropdown default-dropdown">
                            <a class="dropdown-toggle" href="{{ route('order.cart') }}" aria-expanded="true">
                                <div class="header-btns-icon">
                                    <i class="fa fa-shopping-cart"></i>
                                </div>
                                <strong class="text-uppercase">Keranjang</strong>
                            </a>
                        </li>
                        <!-- /Cart -->

                        @if (Auth::check())
                            <!-- Account -->
                            <li class="header-account dropdown default-dropdown">
                                <div class="dropdown-toggle" role="button" data-toggle="dropdown" aria-expanded="true">
                                    <div class="header-btns-icon">
                                        <i class="fa fa-user-o"></i>
                                    </div>
                                    <strong class="text-uppercase">{{ Auth::user()->nama }}<i
                                            class="fa fa-caret-down"></i></strong>
                                </div>
                                <ul class="custom-menu">
                                    <li><a href="{{ route('customer.akun', ['id' => Auth::user()->id]) }}"><i class="fa fa-user-o"></i> Akun Saya</a></li>
                                    <li><a href="{{ route('order.history') }}"><i class="fa fa-check"></i> History</a></li>
                                    <li>
                                        <a href="#"
                                            onclick="event.preventDefault();
                                            document.getElementById('keluar-app').submit();"><i
                                                class="fa fa-power-off"></i> Keluar
                                        </a>
                                        <!-- form keluar app -->
                                        <form id="keluar-app" action="{{ route('customer.logout') }}" method="POST"
                                            class="d-none">
                                            @csrf
                                        </form>
                                        <!-- form keluar app end -->
                                    </li>
                                </ul>
                            </li>
                        @else
                            <li class="header-account dropdown default-dropdown">
                                <div class="dropdown-toggle" role="button" data-toggle="dropdown" aria-expanded="true">
                                    <div class="header-btns-icon">
                                        <i class="fa fa-user-o"></i>
                                    </div>
                                    <strong class="text-uppercase">Akun Saya<i class="fa fa-caret-down"></i></strong>
                                </div>
                                <a href="{{ route('auth.redirect') }}" class="text-uppercase">Login</a>
                            </li>
                            <!-- /Account -->
                        @endif


                        <!-- Mobile nav toggle-->
                        <li class="nav-toggle">
                            <button class="nav-toggle-btn main-btn icon-btn"><i class="fa fa-bars"></i></button>
                        </li>
                        <!-- / Mobile nav toggle -->
                    </ul>
                </div>
            </div>
            <!-- header -->
        </div>
        <!-- container -->
    </header>
    <!-- /HEADER -->

    <!-- NAVIGATION -->
    <div id="navigation">
        <!-- container -->
        <div class="container">
            <div id="responsive-nav">
                @php
                    $kategori = DB::table('kategori')->orderBy('nama_kategori', 'asc')->get();
                @endphp
                @if (request()->segment(1) == '' || request()->segment(1) == 'beranda')
                    <!-- category nav -->
                    <div class="category-nav">
                        <span class="category-header">Kategori <i class="fa fa-list"></i></span>
                        <ul class="category-list">
                            @foreach ($kategori as $row)
                                <li><a href="{{ route('produk.kategori', $row->id) }}">{{ $row->nama_kategori }}</a>
                                </li>
                            @endforeach
                        </ul>
                        <ul class="category-list">
                    </div>
                @else
                    <div class="category-nav show-on-click">
                        <span class="category-header">Kategori <i class="fa fa-list"></i></span>
                        <ul class="category-list">
                            @foreach ($kategori as $row)
                                <li><a href="{{ route('produk.kategori', $row->id) }}">{{ $row->nama_kategori }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <!-- /category nav -->
                @endif

                <!-- menu nav -->
                <div class="menu-nav">
                    <span class="menu-header">Menu <i class="fa fa-bars"></i></span>
                    <ul class="menu-list">
                        <li><a href="{{ route('beranda') }}">Beranda</a></li>
                        <li><a href="{{ route('produk.all') }}">Produk</a></li>
                        <li><a href="#lokasi">Lokasi</a></li>
                        <li><a href="#kontak">Hubungi Kami</a></li>
                    </ul>
                </div>
                <!-- menu nav -->
            </div>
        </div>
        <!-- /container -->
    </div>
    <!-- /NAVIGATION -->

    @if (request()->segment(1) == '' || request()->segment(1) == 'beranda')
        <!-- HOME -->
        <div id="home">
            <!-- container -->
            <div class="container">
                <!-- home wrap -->
                <div class="home-wrap">
                    <!-- home slick -->
                    <div id="home-slick">
                        <!-- banner -->
                        <div class="banner banner-1">
                            <img src="{{ asset('frontend/img/poci.jpg') }}" alt=""
                                style="width: 100%; height: auto;">
                            <div class="banner-caption text-center" style="margin-top: 30px;">
                                <h2 style="color: whitesmoke">Gerabah Tanah Liat</h2>
                                <h1 class="primary-color">Gerabah Poci</h1>
                                <button class="primary-btn">Pesan Sekarang</button>
                            </div>
                        </div>
                        <!-- /banner -->

                        <!-- banner -->
                        <div class="banner banner-1" style="display: flex; justify-content: center;">
                            <img src="{{ asset('frontend/img/kendi.jpg') }}" alt=""
                                style="width: auto; height: 100%;">
                            <div class="banner-caption text-center" style="margin-top: 50px; margin-left: -5%;">
                                <h1 class="white-color">Gerabah<br><span class="primary-color">kendi</span></h1>
                                <button class="primary-btn">Beli Sekarang</button>
                            </div>
                        </div>
                        <!-- /banner -->

                        <!-- banner -->
                        <div class="banner banner-1" style="display: flex; justify-content: center;">
                            <img src="{{ asset('frontend/img/pot.jpg') }}" alt=""
                                style="width: auto; height: 80%;">
                            <div class="banner-caption text-center" style="margin-top: -52px; margin-left: 0%;">
                                <h1 class="white-color">New Product<br><span class="primary-color">Pot Tanaman</span>
                                </h1>
                                <button class="primary-btn">Beli Sekarang</button>
                            </div>
                        </div>
                    </div>
                    <!-- /banner -->
                </div>
                <!-- /home slick -->
            </div>
            <!-- /home wrap -->
        </div>
        <!-- /container -->
        </div>
        <!-- /HOME -->
    @endif

    <!-- section -->
    <div class="section">
        <!-- container -->
        <div class="container">
            <!-- row -->
            <div class="row">
                <!-- ASIDE -->
                <div id="aside" class="col-md-3">
                    <!-- aside widget -->
                    <div class="aside">
                        <h3 class="aside-title">Top Rated Product</h3>
                        <!-- widget product -->
                        <div class="product product-widget">
                            <div class="product-thumb">
                                <img src="{{ asset('frontend/img/set poci teh.jpg') }}" alt="">
                            </div>
                            <div class="product-body">
                                <h2 class="product-name"><a href="#">1 Set Poci Teh</a></h2>
                                <h3 class="product-price">Rp.50.000 <del class="product-old-price">Rp65.000</del></h3>
                                <div class="product-rating">
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                </div>
                            </div>
                        </div>
                        <!-- /widget product -->

                        <!-- widget product -->
                        <div class="product product-widget">
                            <div class="product-thumb">
                                <img src="{{ asset('frontend/img/kendi.jpg') }}" alt="">
                            </div>
                            <div class="product-body">
                                <h2 class="product-name"><a href="#">Kendi</a></h2>
                                <h3 class="product-price">Rp.50.000</h3>
                                <div class="product-rating">
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star-o empty"></i>
                                </div>
                            </div>
                        </div>
                        <!-- /widget product -->
                    </div>
                    <!-- /aside widget -->

                    <!-- aside widget -->
                    <div class="aside">
                        <h3 class="aside-title">Filter Kategori</h3>
                        <ul class="list-links">
                            @foreach ($kategori as $row)
                                <li><a href="{{ route('produk.kategori', $row->id) }}">{{ $row->nama_kategori }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <!-- /aside widget -->
                </div>
                <!-- /ASIDE -->

                <!-- MAIN -->
                <div id="main" class="col-md-9">
                    <!-- store top filter -->
                    <!-- /store top filter -->

                    <!-- @yieldAwal -->
                    @yield('content')
                    <!-- @yieldAkhir-->

                    <!-- store bottom filter -->

                    <!-- /store bottom filter -->
                </div>
                <!-- /MAIN -->
            </div>
            <!-- /row -->
        </div>
        <!-- /container -->
    </div>
    <!-- /section -->

    <!-- FOOTER -->
    <footer id="footer" class="section section-grey">
        <!-- container -->
        <div class="container">
            <!-- row -->
            <div class="row">
                <!-- footer widget -->
                <div class="col-md-3 col-sm-6 col-xs-6">
                    <div class="footer">
                        <h3 class="footer-header">Tentang Gerabah</h3>
                        <!-- footer logo -->
                        <div class="footer-logo">
                            <a class="logo" href="#">
                                <img src="{{asset ('frontend/img/logo.png')}}" alt="">
                            </a>
                        </div>
                        <!-- /footer logo -->
                        <p style="font-family: Arial, Helvetica, sans-serif; text-align: justify;">Gerabah Nusantara
                            menghadirkan koleksi gerabah handmade berkualitas dari pengrajin lokal Indonesia. Setiap
                            karya membawa sentuhan tradisi dan estetika, cocok untuk dekorasi maupun kebutuhan rumah
                            tangga Anda.</p>
                        <!-- footer social -->

                    </div>
                </div>
                <!-- /footer widget -->

                <!-- footer widget -->
                <div class="col-md-3 col-sm-6 col-xs-6">
                    <div class="footer">
                        <h3 class="footer-header">Layanan Pelanggan</h3>
                        <ul class="list-links">
                            <li><a href="#">Tentang kami</a></li>
                            <li><a href="#">Pengiriman & Pengembalian</a></li>
                            <li><a href="#">Panduan Pembelian</a></li>
                        </ul>
                    </div>
                </div>
                <!-- /footer widget -->

                <!-- footer widget -->
                <div class="col-md-3 col-sm-6 col-xs-6">
                    <div class="footer">
                        <h3 class="footer-header">Akun saya</h3>
                        <ul class="list-links">
                            <li><a href="{{ route('order.cart') }}">Keranjang</a></li>
                            <li><a href="{{ route('auth.redirect') }}">Login</a></li>
                        </ul>
                    </div>
                </div>
                <!-- /footer widget -->



                <!-- footer subscribe -->
                <div class="col-md-3 col-sm-6 col-xs-6">
                    <div class="footer">
                        <h3 class="footer-header" id="kontak">kontak kami</h3>
                        <ul style="font-size: 110%;">
                            <li>Email: webgerabah@gmail.com</li><br>
                            <li>Telp: 081234567891</li><br>
                            <li>Alamat: Jl.Gerabah</li>
                            <br>
                        </ul>
                        <ul class="footer-social">
                            <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                            <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                            <li><a href="#"><i class="fa fa-instagram"></i></a></li>
                            <li><a href="#"><i class="fa fa-google-plus"></i></a></li>
                            <li><a href="#"><i class="fa fa-pinterest"></i></a></li>
                        </ul>
                        <!-- /footer social -->
                    </div>
                </div>
                <!-- /footer subscribe -->
            </div>
            <!-- /row -->

            {{-- google maps --}}
            <div class="footer-map" style="margin-top: 30px;" id="lokasi">
                <h3 class="map-title">Lokasi Toko</h3>
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3063.7556574133428!2d109.11842997363003!3d-6.864286093134256!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6fb7613e508bbf%3A0x29b9f43bc3956608!2sUniversitas%20BSI%20Kampus%20Tegal!5e1!3m2!1sid!2sid!4v1744785536238!5m2!1sid!2sid"
                    width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>

            <hr>
            <!-- row -->
            <div class="row">
                <div class="col-md-8 col-md-offset-2 text-center">
                    <!-- footer copyright -->
                    <div class="footer-copyright">
                        <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
                        Copyright &copy;
                        <script>
                            document.write(new Date().getFullYear());
                        </script> Tanjung Ashari & Farrel Harin Alghifari
                        <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
                    </div>
                    <!-- /footer copyright -->
                </div>
            </div>
            <!-- /row -->


        </div>
        <!-- /container -->
    </footer>
    <!-- /FOOTER -->

    <!-- jQuery Plugins -->
    <script src="{{ asset('frontend/js/jquery.min.js') }}"></script>
    <script src="{{ asset('frontend/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('frontend/js/slick.min.js') }}"></script>
    <script src="{{ asset('frontend/js/nouislider.min.js') }}"></script>
    <script src="{{ asset('frontend/js/jquery.zoom.min.js') }}"></script>
    <script src="{{ asset('frontend/js/main.js') }}"></script>
    <!-- Raja Ongkir -->
    @stack('scripts')
    <!-- Midtrans -->
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js"
    data-client-key="{{ config('midtrans.client_key') }}">
    </script>
</body>

</html>

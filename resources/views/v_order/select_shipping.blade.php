@extends('v_layouts.app')
@section('content')
    <!-- CDN Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- CSS: Style untuk Select2 agar responsif -->

    <style>
        .select2-container {
            width: auto !important;
            min-width: 100%;
            max-width: 100%;
            height: 50px;
        }

        .spinner {
            margin: 0 auto 10px auto;
            width: 40px;
            height: 40px;
            border: 5px solid #ccc;
            border-top: 5px solid #28a745;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>


    <div class="col-md-12">
        <div class="order-summary clearfix">

            <div class="section-title">
                <p>PENGIRIMAN</p>
                <h3 class="title">Pilih Pengiriman</h3>
            </div>

            <input type="hidden" id="kota_asal" name="kota_asal">

            <!-- Select kota tujuan -->
            <div class="form-group">
                <label for="destinationSelect">Kota Tujuan:</label><br>
                <select class="input" id="destinationSelect"></select>
            </div>

            <input type="hidden" id="kota_tujuan" name="kota_tujuan">

            <!-- Hidden input: total berat -->
            <input type="hidden" name="weight" id="weight" value="{{ $totalBerat }}">

            <!-- Pilih kurir -->
            <div class="form-group">
                <label for="kurir">Kurir:</label>
                <select name="kurir" id="kurir" class="input">
                    <option value="">Pilih Kurir</option>
                    <option value="jne">JNE</option>
                    <option value="tiki">TIKI</option>
                    <option value="pos">POS Indonesia</option>
                </select>
            </div>

            <!-- Alamat -->
            <div class="form-group">
                <label for="alamat">Alamat</label>
                <textarea class="input" name="alamat" id="alamat">{{ Auth::user()->alamat }}</textarea>
            </div>

            <!-- Kode Pos -->
            <div class="form-group">
                <label for="kode_pos">Kode Pos</label>
                <input type="text" class="input" name="kode_pos" id="kode_pos" value="{{ Auth::user()->pos }}">
            </div>

            <!-- Tombol cek ongkir -->
            <button type="button" class="primary-btn" id="checkShipping">Cek Ongkir</button>

            <!-- Loader (spinner) saat data dikirim -->
            <div id="loading" style="display: none; text-align: center; margin-top: 20px;">
                <div class="spinner"></div>
                <p>Mohon tunggu, sedang memuat ongkir...</p>
            </div>

            <!-- Tabel hasil ongkir -->
            <div id="result">
                <table class="shopping-cart-table table">
                    <thead>
                        <tr>
                            <th>Layanan</th>
                            <th>Biaya</th>
                            <th>Estimasi Pengiriman</th>
                            <th>Total Berat</th>
                            <th>Total Harga</th>
                            <th class="text-center">Bayar</th>
                        </tr>
                    </thead>
                    <tbody id="shippingResults"> <!-- Hasil pencarian muncul di sini --> </tbody>
                </table>
            </div>

        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            /* === set origin bawaan toko === */
            const defaultOriginId = "67523";
            const defaultOriginText = "KABUNAN, TAMAN, PEMALANG, JAWA TENGAH, 52361 (67523)";

            /* isi hidden agar ikut terkirim bila diperlukan di luar click-handler */
            $(document).ready(() => {
                $('#kota_asal').val(defaultOriginText);

                /* --- Select2 tujuan --- */
                $('#destinationSelect').select2({
                    width: 'resolve',
                    placeholder: 'Ketik kecamatan/kota tujuan...',
                    minimumInputLength: 2,
                    ajax: {
                        url: '/ongkir/get-destination',
                        dataType: 'json',
                        delay: 500,
                        data: params => ({
                            search: params.term
                        }),
                        processResults: data => ({
                            results: (data.data || []).slice(0, 10).map(item => ({
                                id: item.id,
                                text: `${item.label} (${item.id})`
                            }))
                        }),
                        error: (xhr, status, error) => {
                            console.error(`Error loading destinationSelect:`, error);
                        }
                    }
                }).on('select2:select', e => {
                    $('#kota_tujuan').val(e.params.data.text);
                });

                /* --- Klik Cek Ongkir --- */
                $('#checkShipping').click(() => {
                    const origin = defaultOriginId; // fix
                    const kota_asal = defaultOriginText; // fix
                    const destination = $('#destinationSelect').val();
                    const weight = $('#weight').val();
                    const courier = $('#kurir').val();
                    const alamat = $('#alamat').val();
                    const kode_pos = $('#kode_pos').val();
                    const kota_tujuan = $('#kota_tujuan').val();

                    if (!destination || !weight || !courier || !alamat || !kode_pos) {
                        alert('Mohon lengkapi semua field.');
                        return;
                    }

                    const formData =
                        `origin=${origin}&destination=${destination}&weight=${weight}&courier=${courier}&price=lowest`;
                    console.log('Form Data:', formData);

                    $('#loading').show();
                    $.ajax({
                        url: '/ongkir/calculate',
                        method: 'POST',
                        data: formData,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: response => {
                            $('#loading').hide();
                            const tbody = $('#shippingResults').empty();

                            if (response.data) {
                                response.data.forEach(sv => {
                                    tbody.append(`
                            <tr>
                            <td>${sv.service} - ${sv.description}</td>
                            <td>Rp${sv.cost.toLocaleString()}</td>
                            <td class="text-center">${sv.etd}</td>
                            <td>${weight} Gram</td>
                            <td>Rp. {{ number_format($totalHarga, 0, ',', '.') }}</td>
                            <td>
                                <form action="{{ route('order.update-ongkir') }}" method="post">
                                @csrf
                                <input type="hidden" name="kurir" value="${courier}">
                                <input type="hidden" name="alamat" value="${alamat}">
                                <input type="hidden" name="pos" value="${kode_pos}">
                                <input type="hidden" name="layanan_ongkir" value="${sv.service}- ${sv.description}">
                                <input type="hidden" name="total_berat" value="${weight}">
                                <input type="hidden" name="kota_asal" value="${kota_asal}">
                                <input type="hidden" name="kota_tujuan" value="${kota_tujuan}">
                                <input type="hidden" name="biaya_ongkir" value="${sv.cost}">
                                <input type="hidden" name="estimasi_ongkir" value="${sv.etd}">
                                <button type="submit" class="primary-btn">Pilih Pengiriman</button>
                                </form>
                            </td>
                            </tr>`);
                                });
                            } else {
                                tbody.html('<em>Tidak ada data ongkir ditemukan.</em>');
                            }
                        },
                        error: () => {
                            $('#loading').hide();
                            $('#shippingResults').html(
                                '<em>Terjadi kesalahan saat mengambil ongkir.</em>');
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection

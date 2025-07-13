<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use App\Models\Produk;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Http;
use Midtrans\Config;
use Midtrans\Snap;

class OrderController extends Controller
{
    public function index()
    {
        $order = Order::orderBy('updated_at', 'desc')->get();
        return view('backend.v_pesanan.proses', [
            'judul' => 'Pesanan',
            'sub' => 'Halaman Pesanan',
            'index' => $order
        ]);
    }
    public function statusUpdate(Request $request, string $id)
    {
        $order = Order::findOrFail($id);
        $rules = [
            'alamat' => 'required',
        ];
        if ($request->status != $order->status) {
            $rules['status'] = 'required';
        }
        if ($request->noresi != $order->noresi) {
            $rules['noresi'] = 'required';
        }
        if ($request->pos != $order->pos) {
            $rules['pos'] = 'required';
        }
        $validatedData = $request->validate($rules);
        Order::where('id', $id)->update($validatedData);
        return redirect()->route('backend.pesanan.proses')->with('success', 'Data berhasil
        diperbaharui');
    }

    public function addToCart($id)
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $produk = Produk::findOrFail($id);

        $order = Order::firstOrCreate(
            ['customer_id' => $customer->id, 'status' => 'pending'],
            [
                'total_harga' => 0,
                'user_id' => Auth::id()
            ]
        );


        $orderItem = OrderItem::firstOrCreate(
            ['order_id' => $order->id, 'produk_id' => $produk->id],
            ['quantity' => 1, 'harga' => $produk->harga]
        );

        if (!$orderItem->wasRecentlyCreated) {
            $orderItem->quantity++;
            $orderItem->save();
        }

        $order->total_harga += $produk->harga;
        $order->save();

        return redirect()->route('order.cart')->with('success', 'Produk berhasil ditambahkan ke keranjang');
    }

    public function viewCart()
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $order = Order::where('customer_id', $customer->id)->where('status', 'pending', 'paid')->first();
        if ($order) {
            $order->load('orderItems.produk');
        }
        return view('v_order.cart', compact('order'));
    }

    public function updateCart(Request $request, $id)
    {
        $customer = Customer::where('user_id', Auth::id())->first();;;
        $order = Order::where('customer_id', $customer->id)->where('status', 'pending')->first();
        if ($order) {
            $orderItem = $order->orderItems()->where('id', $id)->first();
            if ($orderItem) {
                $quantity = $request->input('quantity');
                if ($quantity > $orderItem->produk->stok) {
                    return redirect()->route('order.cart')->with('error', 'Jumlah produk melebihi stok yang tersedia');
                }
                $order->total_harga -= $orderItem->harga * $orderItem->quantity;
                $orderItem->quantity = $quantity;
                $orderItem->save();
                $order->total_harga += $orderItem->harga * $orderItem->quantity;
                $order->save();
            }
        }
        return redirect()->route('order.cart')->with('success', 'Jumlah produk berhasil diperbarui');
    }

    public function removeFromCart(Request $request, $id)
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $order = Order::where('customer_id', $customer->id)->where('status', 'pending')->first();

        if ($order) {
            $orderItem = OrderItem::where('order_id', $order->id)->where('produk_id', $id)->first();

            if ($orderItem) {
                $order->total_harga -= $orderItem->harga * $orderItem->quantity;
                $orderItem->delete();

                if ($order->total_harga <= 0) {
                    $order->delete();
                } else {
                    $order->save();
                }
            }
        }
        return redirect()->route('order.cart')->with('success', 'Produk berhasil dihapus dari keranjang');
    }

    public function selectShipping(Request $request)
    {
        $customer = Customer::where('user_id', Auth::id())->first();

        // Pastikan order dengan status 'pending' ada untuk customer ini 
        $order = Order::where('customer_id', $customer->id)->where('status', 'pending')->first();

        // Cek apakah order ada
        if (!$order || $order->orderItems->count() == 0) {
            return redirect()->route('order.cart')->with('error', 'Keranjang belanja kosong.');
        }
        // Inisialisasi total
        $totalHarga = 0;
        $totalBerat = 0;

        // Hitung total harga dan total berat
        foreach ($order->orderItems as $item) {
            $totalHarga += $item->harga * $item->quantity;
            $totalBerat += $item->produk->berat * $item->quantity;
        }

        // Kirim total ke view
        return view('v_order.select_shipping', compact('order', 'totalHarga', 'totalBerat'));
    }

    public function updateOngkir(Request $request)
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $order = Order::where('customer_id', $customer->id)->where('status', 'pending')->first();

        $kota_asal       = $request->input('kota_asal');
        $kota_tujuan     = $request->input('kota_tujuan');

        if ($order) {
            // Simpan data ongkir ke dalam order 
            $order->kurir           = $request->input('kurir');
            $order->layanan_ongkir  = $request->input('layanan_ongkir');
            $order->biaya_ongkir    = $request->input('biaya_ongkir');
            $order->estimasi_ongkir = $request->input('estimasi_ongkir');
            $order->total_berat     = $request->input('total_berat');
            $order->alamat          = $request->input('alamat') . ' ' . $request->input('city_name') . ' ' . $request->input('province_name');
            $order->pos = $request->input('pos');
            $order->save();

            // Simpan ke session flash agar bisa diakses di halaman tujuan 
            return redirect()->route('order.selectpayment')->with('kota_asal', $kota_asal)->with('kota_tujuan', $kota_tujuan);
        }
        return back()->with('error', 'Gagal menyimpan data ongkir');
    }

    public function selectPayment()
    {
        $customer = Auth::user();
        $order = Order::where('customer_id', $customer->customer->id)->where('status', 'pending')->first();

        $kota_asal = session('kota_asal'); // Kode kota asal 
        $kota_tujuan = session('kota_tujuan');  // Nama kota asal 

        if (!$order) {
            return redirect()->route('order.cart')->with('error', 'Keranjang belanja kosong.');
        }

        // Muat relasi orderItems dan produk terkait
        $order->load('orderItems.produk');

        // Hitung total harga produk
        $totalHarga = 0;
        foreach ($order->orderItems as $item) {
            $totalHarga += $item->harga * $item->quantity;
        }

        // Tambahkan biaya ongkir ke total harga
        $grossAmount = $totalHarga + $order->biaya_ongkir;

        // Midtrans configuration
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Generate unique order_id
        $orderId = $order->id . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $grossAmount, // Pastikan gross_amount adalah integer
            ],
            'customer_details' => [
                'first_name' => $customer->nama,
                'email' => $customer->email,
                'phone' => $customer->hp,
            ],
        ];

        $snapToken = Snap::getSnapToken($params);
        return view('v_order.select_payment', [
            'order' => $order,
            'kota_asal' => $kota_asal,
            'kota_tujuan' => $kota_tujuan,
            'snapToken' => $snapToken,
        ]);
    }

    public function callback(Request $request)
    {
        // dd($request->all());
        $serverKey = config('midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        if ($hashed == $request->signature_key) {
            $order = Order::find($request->order_id);
            if ($order) {
                $order->update(['status' => 'Paid']);
            }
        }
    }


    public function complete() // Untuk kondisi local
    {
        // Dapatkan customer yang login
        $customer = Auth::user();


        // Cari order dengan status 'pending' milik customer tersebut
        $order = Order::where('customer_id', $customer->customer->id)
            ->where('status', 'pending')
            ->first();
        if ($order) {
            // Update status order menjadi 'Paid'
            $order->status = 'Paid';
            $order->save();
        }


        // Redirect ke halaman riwayat dengan pesan sukses
        return redirect()->route('order.history')->with('success', 'Checkout berhasil');
    }


    // public function complete() // Untuk kondisi sudah memiliki domain
    // {
    //     // Logika untuk halaman setelah pembayaran berhasil
    //     return redirect()->route('order.history')->with('success', 'Checkout berhasil');
    // }


    public function orderHistory()
    {
        $customer = Customer::where('user_id', Auth::id())->first();;;
        // $orders = Order::where('customer_id', $customer->id)->where('status', 'completed')->get();
        $statuses = ['Paid', 'Kirim', 'Selesai'];
        $orders = Order::where('customer_id', $customer->id)
            ->whereIn('status', $statuses)
            ->orderBy('id', 'desc')
            ->get();
        return view('v_order.history', compact('orders'));
    }


    public function invoiceFrontend($id)
    {
        $order = Order::findOrFail($id);
        return view('v_order.invoice', [
            'judul' => 'Data Pesanan',
            'subJudul' => 'Pesanan Proses',
            'order' => $order,
        ]);
    }


    // ongkir getProvinces
    public function getProvinces()
    {
        $response = Http::withHeaders([
            'key' => env('RAJAONGKIR_API_KEY')
        ])->get(env('RAJAONGKIR_BASE_URL') . '/province');

        return response()->json($response->json());
    }

    // ongkir getCities
    public function getCities(Request $request)
    {
        $provinceId = $request->input('province_id');
        $response = Http::withHeaders([
            'key' => env('RAJAONGKIR_API_KEY')
        ])->get(env('RAJAONGKIR_BASE_URL') . '/city', [
            'province' => $provinceId
        ]);

        return response()->json($response->json());
    }

    // ongkir getCost
    public function getCost(Request $request)
    {
        $origin = $request->input('origin');
        $destination = $request->input('destination');
        $weight = $request->input('weight');
        $courier = $request->input('courier');

        $response = Http::withHeaders([
            'key' => env('RAJAONGKIR_API_KEY')
        ])->post(env('RAJAONGKIR_BASE_URL') . '/cost', [
            'origin' => $origin,
            'destination' => $destination,
            'weight' => $weight,
            'courier' => $courier,
        ]);

        return response()->json($response->json());
    }

    public function getDestination(Request $request)
    {
        $search = $request->get('search', ''); // default kosong, bisa diketik oleh user
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination?search=' . urlencode($search),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'key: ' . env('RAJAONGKIR_API_KEY'),
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $data = json_decode($response, true);
        return response()->json($data);
    }

    public function calculateOngkir(Request $request)
    {
        $origin = $request->input('origin');
        $destination = $request->input('destination');
        $weight = $request->input('weight');
        $courier = $request->input('courier');

        $postData = http_build_query([
            'origin' => $origin,
            'destination' => $destination,
            'weight' => $weight,
            'courier' => $courier,
            'price' => 'lowest'
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => array(
                'key: ' . env('RAJAONGKIR_API_KEY'),
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return response()->json(['error' => $err], 500);
        }

        return response()->json(json_decode($response, true));
    }
    public function statusProses()
    {
        //backend
        $order = Order::whereIn('status', ['Paid', 'Pending'])->orderBy('id', 'desc')->get();
        return view('backend.v_pesanan.proses', [
            'judul' => 'Pesanan',
            'sub' => 'Pesanan Proses',
            'index' => $order
        ]);
    }
    public function statusSelesai()
    {
        //backend
        $order = Order::where('status', 'Selesai')->orderBy('id', 'desc')->get();
        return view('backend.v_pesanan.selesai', [
            'judul' => 'Data Pesanan',
            'sub' => 'Pesanan Selesai',
            'index' => $order
        ]);
    }
    public function statusDetail($id)
    {
        $order = Order::findOrFail($id);
        return view('backend.v_pesanan.detail', [
            'judul' => 'Data Pesanan',
            'sub' => 'Pesanan Detail',
            'order' => $order,
        ]);
    }
    public function formOrderProses()
    {
        return view('backend.v_pesanan.formproses', [
            'judul' => 'Laporan',
            'sub' => 'Laporan Pesanan Proses',
        ]);
    }
    public function cetakOrderProses(Request $request)
    {
        // Menambahkan aturan validasi
        $request->validate([
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
        ], [
            'tanggal_awal.required' => 'Tanggal Awal harus diisi.',
            'tanggal_akhir.required' => 'Tanggal Akhir harus diisi.',
            'tanggal_akhir.after_or_equal' => 'Tanggal Akhir harus lebih besar atau sama
dengan Tanggal Awal.',
        ]);
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');
        $order = Order::whereIn('status', ['Paid', 'Kirim'])->orderBy('id', 'desc')->get();
        return view('backend.v_pesanan.cetakproses', [
            'judul' => 'Laporan',
            'subJudul' => 'Laporan Pesanan Proses',
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'cetak' => $order
        ]);
    }
    public function formOrderSelesai()
    {
        return view('backend.v_pesanan.formselesai', [
            'judul' => 'Laporan',
            'sub' => 'Laporan Pesanan Selesai',
        ]);
    }
    public function cetakOrderSelesai(Request $request)
    {
        // Menambahkan aturan validasi
        $request->validate([
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
        ], [
            'tanggal_awal.required' => 'Tanggal Awal harus diisi.',
            'tanggal_akhir.required' => 'Tanggal Akhir harus diisi.',
            'tanggal_akhir.after_or_equal' => 'Tanggal Akhir harus lebih besar atau sama
dengan Tanggal Awal.',
        ]);
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');
        $order = Order::where('status', 'Selesai')
            ->whereBetween('created_at', [$tanggalAwal, $tanggalAkhir])
            ->orderBy('id', 'desc')
            ->get();
        $totalPendapatan = $order->sum(function ($row) {
            return $row->total_harga + $row->biaya_ongkir;
        });
        return view(
            'backend.v_pesanan.cetakselesai',
            [
                'judul' => 'Laporan',
                'sub' => 'Laporan Pesanan Selesai',
                'tanggalAwal' => $tanggalAwal,
                'tanggalAkhir' => $tanggalAkhir,
                'cetak' => $order,
                'totalPendapatan' => $totalPendapatan
            ]
        );
    }
    public function invoiceBackend($id)
    {
        $order = Order::findOrFail($id);
        return view('backend.v_pesanan.invoice', [
            'judul' => 'Data Pesanan',
            'sub' => 'Pesanan Proses',
            'order' => $order,
        ]);
    }
    public function show(string $id)
    {
        //
    }
}

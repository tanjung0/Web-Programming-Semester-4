<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Helpers\ImageHelper;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customer = Customer::orderBy('id', 'desc')->get();
        return view('backend.v_customer.index', [
            'judul' => 'Customer',
            'sub' => 'Halaman Customer',
            'index' => $customer
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.v_customer.create', [
            'judul' => 'Customer',
            'sub' => 'Tambah Customer'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request);
        $validatedData = $request->validate([
            'nama' => 'required|max:255',
            'hp' => 'required|min:10|max:13|unique:customer',
        ]);
        Customer::create($validatedData);
        return redirect()->route('backend.customer.index')->with('success', 'Data berhasil tersimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer = Customer::find($id);
        return view('backend.v_customer.detail', [
            'judul' => 'Customer',
            'sub' => 'Detail Customer',
            'edit' => $customer
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $customer = Customer::find($id) ?? Customer::where('user_id', $id)->firstOrFail();
        return view('backend.v_customer.edit', [
            'judul' => 'Customer',
            'sub' => 'Ubah Customer',
            'edit' => $customer
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $customer = Customer::where('user_id', $id)->firstOrFail();
        $rules = [
            'nama' => 'required|max:255',
            'hp' => 'required|min:10|max:13',
            'foto' => 'image|mimes:jpeg,jpg,png,gif|file|max:1024',
        ];
        $messages = [
            'foto.image' => 'Format gambar gunakan file dengan ekstensi jpeg, jpg, png, atau gif.',
            'foto.max' => 'Ukuran file gambar Maksimal adalah 1024 KB.'
        ];
        if ($request->email != $customer->user->email) {
            $rules['email'] = 'required|max:255|email|unique:customer';
        }
        if ($request->alamat != $customer->alamat) {
            $rules['alamat'] = 'required';
        }
        if ($request->pos != $customer->pos) {
            $rules['pos'] = 'required';
        }
        $validatedData = $request->validate($rules, $messages);
        // menggunakan ImageHelper
        if ($request->file('foto')) {
            //hapus gambar lama
            if ($customer->user->foto) {
                $oldImagePath = public_path('storage/img-customer/') . $customer->user->foto;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $file = $request->file('foto');
            $extension = $file->getClientOriginalExtension();
            $originalFileName = date('YmdHis') . '_' . uniqid() . '.' . $extension;
            $directory = 'storage/img-customer/';
            // Simpan gambar dengan ukuran yang ditentukan
            ImageHelper::uploadAndResize($file, $directory, $originalFileName, 385, 400); //null (jika tinggi otomatis)
            // Simpan nama file asli di database
            $validatedData['foto'] = $originalFileName;
        }
        $customer->user->update($validatedData);
        $customer->update([
            'alamat' => $request->input('alamat'),
            'pos' => $request->input('pos'),
        ]);
        return redirect()->route('backend.customer.edit', $id)->with('success', 'Data berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return redirect()->route('backend.customer.index')->with('success', 'Data berhasil dihapus');
    }
    // Redirect ke Google
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }
    // Callback dari Google
    public function callback()
    {
        try {
            $socialUser = Socialite::driver('google')->user();
            // Cek apakah email sudah terdaftar
            $registeredUser = User::where('email', $socialUser->email)->first();
            if (!$registeredUser) {
                // Buat user baru
                $user = User::create([
                    'nama' => $socialUser->name,
                    'email' => $socialUser->email,
                    'role' => '2', // Role customer
                    'status' => 1, // Status aktif
                    'password' => Hash::make('default_password'), // Password default (opsional)
                ]);
                // Buat data customer
                Customer::create([
                    'user_id' => $user->id,
                    'google_id' => $socialUser->id,
                    'google_token' => $socialUser->token
                ]);
                // Login pengguna baru
                Auth::login($user);
            } else {
                // Jika email sudah terdaftar, langsung login
                Auth::login($registeredUser);
            }
            // Redirect ke halaman utama
            return redirect()->intended('beranda');
        } catch (\Exception $e) {
            // Redirect ke halaman utama jika terjadi kesalahan
            return redirect('/')->with('error', 'Terjadi kesalahan saat login dengan
Google.');
        }
    }
    public function logout(Request $request)
    {
        Auth::logout(); // Logout pengguna
        $request->session()->invalidate(); // Hapus session
        $request->session()->regenerateToken(); // Regenerate token CSRF
        return redirect('/')->with('success', 'Anda telah berhasil logout.');
    }
    public function akun($id)
    {
        $loggedInCustomerId = Auth::user()->id;
        // Cek apakah ID yang diberikan sama dengan ID customer yang sedang login
        if ($id != $loggedInCustomerId) {
            // Redirect atau tampilkan pesan error
            return redirect()->route('customer.akun', ['id' => $loggedInCustomerId])->with('msgError', 'Anda tidak berhak mengakses akun ini.');
        }
        $customer = Customer::where('user_id', $id)->firstOrFail();
        return view('v_customer.edit', [
            'judul' => 'Customer',
            'subJudul' => 'Akun Customer',
            'edit' => $customer
        ]);
    }
    public function updateAkun(Request $request, $id)
    {
        $customer = Customer::where('user_id', $id)->firstOrFail();
        $rules = [
            'nama' => 'required|max:255',
            'hp' => 'required|min:10|max:13',
            'foto' => 'image|mimes:jpeg,jpg,png,gif|file|max:1024',
        ];
        $messages = [
            'foto.image' => 'Format gambar gunakan file dengan ekstensi jpeg, jpg, png, atau gif.',
            'foto.max' => 'Ukuran file gambar Maksimal adalah 1024 KB.'
        ];
        if ($request->email != $customer->user->email) {
            $rules['email'] = 'required|max:255|email|unique:customer';
        }
        if ($request->alamat != $customer->alamat) {
            $rules['alamat'] = 'required';
        }
        if ($request->pos != $customer->pos) {
            $rules['pos'] = 'required';
        }
        $validatedData = $request->validate($rules, $messages);
        // menggunakan ImageHelper
        if ($request->file('foto')) {
            //hapus gambar lama
            if ($customer->user->foto) {
                $oldImagePath = public_path('storage/img-customer/') . $customer->user->foto;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $file = $request->file('foto');
            $extension = $file->getClientOriginalExtension();
            $originalFileName = date('YmdHis') . '_' . uniqid() . '.' . $extension;
            $directory = 'storage/img-customer/';
            // Simpan gambar dengan ukuran yang ditentukan
            ImageHelper::uploadAndResize($file, $directory, $originalFileName, 385, 400); //null (jika tinggi otomatis)
            // Simpan nama file asli di database
            $validatedData['foto'] = $originalFileName;
        }
        $customer->user->update($validatedData);
        $customer->update([
            'alamat' => $request->input('alamat'),
            'pos' => $request->input('pos'),
        ]);
        return redirect()->route('customer.akun', $id)->with('success', 'Data berhasil diperbarui');
    }
}

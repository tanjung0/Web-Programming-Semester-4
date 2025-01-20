<?php

namespace App\Http\Controllers;
use App\Models\Customer;

use Illuminate\Http\Request;

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
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $customer = Customer::find($id);
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
        $rules = [
            'hp' => 'required|min:10|max:12|unique:customer,hp,' . $id,
            'nama' => 'required|max:255|unique:customer,nama,'  . $id,
        ];
        $validatedData = $request->validate($rules);
        Customer::where('id', $id)->update($validatedData);
        return redirect()->route('backend.customer.index')->with('success', 'Data berhasil diperbaharui');
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

}


<?php

namespace Database\Seeders;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kategori;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'nama' => 'Administrator',
            'email' => 'admin@gmail.com',
            'role' => '1',
            'status' => 1,
            'hp' => '0812345678901',
            'password' => bcrypt('P@55word'),
        ]);
        #untuk record berikutnya silahkan, beri nilai berbeda pada nilai: nama, email, hp dengan nilai masing-masing anggota kelompok
        User::create([
            'nama' => 'Tanjung Ashari',
            'email' => 'admintanjung@gmail.com',
            'role' => '0',
            'status' => 1,
            'hp' => '0812345678902',
            'password' => bcrypt('tanjung'),
        ]);
        User::create([
            'nama' => 'Farel Harin Al Ghifari',
            'email' => 'adminfarel@gmail.com',
            'role' => '0',
            'status' => 1,
            'hp' => '0812345678902',
            'password' => bcrypt('farel'),
        ]);
        #data kategori
        Kategori::create([
            'nama_kategori' => 'Gerabah',
        ]);
    }
}

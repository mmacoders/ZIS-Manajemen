<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first admin user as creator
        $adminUser = DB::table('users')->where('email', 'admin@zis.com')->first();
        
        if (!$adminUser) {
            $adminUser = DB::table('users')->first();
        }
        
        if (!$adminUser) {
            echo "No user found to create programs\n";
            return;
        }
        
        $programs = [
            [
                'nama' => 'Program Bantuan Pendidikan Anak Yatim',
                'jenis_program' => 'distribusi',
                'bidang_program' => 'pendidikan_distribusi',
                'deskripsi' => 'Program bantuan pendidikan untuk anak-anak yatim piatu di wilayah Jabodetabek',
                'target_dana' => 500000000,
                'dana_terkumpul' => 350000000,
                'tanggal_mulai' => '2024-01-01',
                'tanggal_selesai' => '2024-12-31',
                'penanggung_jawab' => 'Bapak Surya Pratama',
                'status' => 'aktif',
                'created_by' => $adminUser->id,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Program Pemberdayaan UMKM',
                'jenis_program' => 'pemberdayaan',
                'bidang_program' => 'ekonomi_produk',
                'deskripsi' => 'Program pemberdayaan ekonomi melalui pelatihan dan pendampingan UMKM muslim',
                'target_dana' => 750000000,
                'dana_terkumpul' => 600000000,
                'tanggal_mulai' => '2024-03-01',
                'tanggal_selesai' => '2024-11-30',
                'penanggung_jawab' => 'Ibu Rina Dewi',
                'status' => 'aktif',
                'created_by' => $adminUser->id,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Program Bantuan Kesehatan Lansia',
                'jenis_program' => 'distribusi',
                'bidang_program' => 'kesehatan_distribusi',
                'deskripsi' => 'Program bantuan kesehatan gratis untuk lansia tidak mampu',
                'target_dana' => 300000000,
                'dana_terkumpul' => 300000000,
                'tanggal_mulai' => '2024-01-15',
                'tanggal_selesai' => '2024-10-15',
                'penanggung_jawab' => 'Dr. Ahmad Fauzi',
                'status' => 'selesai',
                'created_by' => $adminUser->id,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Program Dakwah dan Advokasi',
                'jenis_program' => 'distribusi',
                'bidang_program' => 'dakwah_dan_advokasi',
                'deskripsi' => 'Program dakwah dan advokasi keislaman di daerah pelosok',
                'target_dana' => 200000000,
                'dana_terkumpul' => 150000000,
                'tanggal_mulai' => '2024-02-01',
                'tanggal_selesai' => '2024-08-31',
                'penanggung_jawab' => 'Ustadz Muhammad Ridwan',
                'status' => 'aktif',
                'created_by' => $adminUser->id,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Program Bantuan Kemanusiaan Bencana',
                'jenis_program' => 'distribusi',
                'bidang_program' => 'kemanusiaan',
                'deskripsi' => 'Program bantuan kemanusiaan untuk korban bencana alam',
                'target_dana' => 1000000000,
                'dana_terkumpul' => 850000000,
                'tanggal_mulai' => '2024-01-01',
                'tanggal_selesai' => '2024-12-31',
                'penanggung_jawab' => 'Bapak Joko Susilo',
                'status' => 'aktif',
                'created_by' => $adminUser->id,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
        
        foreach ($programs as $program) {
            Program::create($program);
        }
        
        echo "Seeded " . count($programs) . " programs\n";
    }
}
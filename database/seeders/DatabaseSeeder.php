<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Muzakki;
use App\Models\Mustahiq;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles only if they don't exist
        $adminRole = Role::firstOrCreate([
            'name' => 'admin'
        ], [
            'display_name' => 'Administrator',
            'description' => 'Full system access'
        ]);

        $bidang1Role = Role::firstOrCreate([
            'name' => 'bidang1'
        ], [
            'display_name' => 'Bidang Pengumpulan',
            'description' => 'Manages muzakki, UPZ, and ZIS collection'
        ]);

        $bidang2Role = Role::firstOrCreate([
            'name' => 'bidang2'
        ], [
            'display_name' => 'Bidang Distribusi & Pemberdayaan',
            'description' => 'Manages distribution programs and mustahiq'
        ]);

        $bidang4Role = Role::firstOrCreate([
            'name' => 'bidang4'
        ], [
            'display_name' => 'Bidang Arsip Surat',
            'description' => 'Manages document archiving'
        ]);

        // Create users only if they don't exist
        User::firstOrCreate([
            'email' => 'admin@zis.com'
        ], [
            'name' => 'Administrator',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'phone' => '081234567890',
            'status' => 'active'
        ]);

        User::firstOrCreate([
            'email' => 'bidang1@zis.com'
        ], [
            'name' => 'Bidang Pengumpulan',
            'password' => Hash::make('password'),
            'role_id' => $bidang1Role->id,
            'phone' => '081234567891',
            'status' => 'active'
        ]);

        User::firstOrCreate([
            'email' => 'bidang2@zis.com'
        ], [
            'name' => 'Bidang Distribusi',
            'password' => Hash::make('password'),
            'role_id' => $bidang2Role->id,
            'phone' => '081234567892',
            'status' => 'active'
        ]);

        User::firstOrCreate([
            'email' => 'bidang4@zis.com'
        ], [
            'name' => 'Bidang Arsip',
            'password' => Hash::make('password'),
            'role_id' => $bidang4Role->id,
            'phone' => '081234567894',
            'status' => 'active'
        ]);

        // Create sample Muzakki data only if table is empty
        if (Muzakki::count() == 0) {
            $muzakkiData = [
                [
                    'nama' => 'Ahmad Wijaya',
                    'nik' => '3201012345678901',
                    'alamat' => 'Jl. Merdeka No. 15, Bandung, Jawa Barat',
                    'telepon' => '081234567001',
                    'email' => 'ahmad.wijaya@email.com',
                    'jenis' => 'individu',
                    'keterangan' => 'Pegawai negeri sipil, rutin membayar zakat'
                ],
                [
                    'nama' => 'Siti Nurhaliza',
                    'nik' => '3273014567890123',
                    'alamat' => 'Jl. Sudirman No. 88, Jakarta Selatan, DKI Jakarta',
                    'telepon' => '081234567002',
                    'email' => 'siti.nurhaliza@email.com',
                    'jenis' => 'individu',
                    'keterangan' => 'Dokter spesialis, aktif dalam kegiatan sosial'
                ],
                [
                    'nama' => 'PT. Berkah Mandiri',
                    'nik' => '0212345678901234',
                    'alamat' => 'Jl. Gatot Subroto Kav. 32, Jakarta Pusat, DKI Jakarta',
                    'telepon' => '021234567003',
                    'email' => 'admin@berkahmandiri.co.id',
                    'jenis' => 'perusahaan',
                    'keterangan' => 'Perusahaan trading, pembayar zakat perusahaan rutin'
                ],
                [
                    'nama' => 'Muhammad Ridwan',
                    'nik' => '3578012345678902',
                    'alamat' => 'Jl. Ahmad Yani No. 45, Surabaya, Jawa Timur',
                    'telepon' => '081234567004',
                    'email' => 'mridwan@email.com',
                    'jenis' => 'individu',
                    'keterangan' => 'Pengusaha kecil, pembayar zakat fitrah dan mal'
                ],
                [
                    'nama' => 'Fatimah Azzahra',
                    'nik' => '3471012345678903',
                    'alamat' => 'Jl. Diponegoro No. 67, Yogyakarta, DI Yogyakarta',
                    'telepon' => '081234567005',
                    'email' => 'fatimah.azzahra@email.com',
                    'jenis' => 'individu',
                    'keterangan' => 'Guru sekolah dasar, rajin membayar zakat'
                ],
                [
                    'nama' => 'CV. Harapan Jaya',
                    'nik' => '0312345678901235',
                    'alamat' => 'Jl. Malioboro No. 123, Yogyakarta, DI Yogyakarta',
                    'telepon' => '0274567890',
                    'email' => 'info@harapanjaya.co.id',
                    'jenis' => 'perusahaan',
                    'keterangan' => 'Toko oleh-oleh dan kerajinan, zakat perusahaan'
                ],
                [
                    'nama' => 'Abdul Rahman',
                    'nik' => '3604012345678904',
                    'alamat' => 'Jl. Pahlawan No. 78, Pontianak, Kalimantan Barat',
                    'telepon' => '081234567006',
                    'email' => 'abdul.rahman@email.com',
                    'jenis' => 'individu',
                    'keterangan' => 'Petani sawit, pembayar zakat hasil pertanian'
                ],
                [
                    'nama' => 'Khadijah Salsabila',
                    'nik' => '1671012345678905',
                    'alamat' => 'Jl. Cut Nyak Dien No. 34, Banda Aceh, Aceh',
                    'telepon' => '081234567007',
                    'email' => 'khadijah.salsabila@email.com',
                    'jenis' => 'individu',
                    'keterangan' => 'Pegawai swasta, aktif dalam komunitas masjid'
                ],
                [
                    'nama' => 'PT. Teknologi Maju',
                    'nik' => '0412345678901236',
                    'alamat' => 'Jl. HR. Rasuna Said No. 56, Jakarta Selatan, DKI Jakarta',
                    'telepon' => '021234567008',
                    'email' => 'hr@teknologimaju.com',
                    'jenis' => 'perusahaan',
                    'keterangan' => 'Perusahaan IT, program CSR dan zakat perusahaan'
                ],
                [
                    'nama' => 'Omar Faruq',
                    'nik' => '5171012345678906',
                    'alamat' => 'Jl. Sultan Hasanuddin No. 89, Makassar, Sulawesi Selatan',
                    'telepon' => '081234567009',
                    'email' => 'omar.faruq@email.com',
                    'jenis' => 'individu',
                    'keterangan' => 'Nelayan, pembayar zakat hasil laut'
                ],
                [
                    'nama' => 'Aisyah Ramadhani',
                    'nik' => '1471012345678907',
                    'alamat' => 'Jl. Gajah Mada No. 12, Pekanbaru, Riau',
                    'telepon' => '081234567010',
                    'email' => 'aisyah.ramadhani@email.com',
                    'jenis' => 'individu',
                    'keterangan' => 'Pengusaha salon, pembayar zakat profesi'
                ],
                [
                    'nama' => 'UD. Sumber Rezeki',
                    'nik' => '0512345678901237',
                    'alamat' => 'Jl. Juanda No. 45, Malang, Jawa Timur',
                    'telepon' => '0341567890',
                    'email' => 'admin@sumberrezeki.co.id',
                    'jenis' => 'perusahaan',
                    'keterangan' => 'Distributor sembako, zakat perdagangan'
                ],
                [
                    'nama' => 'Yusuf Mansur',
                    'nik' => '6171012345678908',
                    'alamat' => 'Jl. Ahmad Dahlan No. 23, Balikpapan, Kalimantan Timur',
                    'telepon' => '081234567011',
                    'email' => 'yusuf.mansur@email.com',
                    'jenis' => 'individu',
                    'keterangan' => 'Insinyur tambang, pembayar zakat profesi rutin'
                ],
                [
                    'nama' => 'Zahra Aulia',
                    'nik' => '1871012345678909',
                    'alamat' => 'Jl. Imam Bonjol No. 67, Lampung, Lampung',
                    'telepon' => '081234567012',
                    'email' => 'zahra.aulia@email.com',
                    'jenis' => 'individu',
                    'keterangan' => 'Bidan praktek mandiri, aktif dalam kegiatan dakwah'
                ],
                [
                    'nama' => 'PT. Sejahtera Abadi',
                    'nik' => '0612345678901238',
                    'alamat' => 'Jl. Veteran No. 90, Semarang, Jawa Tengah',
                    'telepon' => '024567890',
                    'email' => 'finance@sejahteraabadi.com',
                    'jenis' => 'perusahaan',
                    'keterangan' => 'Perusahaan manufaktur, pembayar zakat perusahaan dan CSR'
                ]
            ];

            foreach ($muzakkiData as $data) {
                Muzakki::create($data);
            }
        }
        
        // Create sample Mustahiq data only if table is empty
        if (Mustahiq::count() == 0) {
            $mustahiqData = [
                [
                    'nama' => 'Budi Santoso',
                    'nik' => '1234567890123456',
                    'alamat' => 'Jl. Merdeka No. 10, Jakarta Pusat',
                    'telepon' => '081234567890',
                    'kategori' => 'fakir',
                    'keterangan' => 'Keluarga tidak mampu dengan penghasilan di bawah garis kemiskinan',
                    'status' => 'aktif'
                ],
                [
                    'nama' => 'Siti Aminah',
                    'nik' => '2345678901234567',
                    'alamat' => 'Jl. Sudirman No. 25, Bandung, Jawa Barat',
                    'telepon' => '082345678901',
                    'kategori' => 'miskin',
                    'keterangan' => 'Janda dengan 3 anak, penghasilan tidak mencukupi kebutuhan dasar',
                    'status' => 'aktif'
                ],
                [
                    'nama' => 'Ahmad Fauzi',
                    'nik' => '3456789012345678',
                    'alamat' => 'Jl. Diponegoro No. 5, Surabaya, Jawa Timur',
                    'telepon' => '083456789012',
                    'kategori' => 'gharim',
                    'keterangan' => 'Memiliki hutang yang tidak bisa dilunasi karena musibah',
                    'status' => 'aktif'
                ],
                [
                    'nama' => 'Rina Dewi',
                    'nik' => '4567890123456789',
                    'alamat' => 'Jl. Gatot Subroto No. 30, Yogyakarta, DI Yogyakarta',
                    'telepon' => '084567890123',
                    'kategori' => 'ibnu_sabil',
                    'keterangan' => 'Pejalan yang kehabisan biaya di perjalanan',
                    'status' => 'aktif'
                ],
                [
                    'nama' => 'Muhammad Ridwan',
                    'nik' => '5678901234567890',
                    'alamat' => 'Jl. Ahmad Yani No. 15, Semarang, Jawa Tengah',
                    'telepon' => '085678901234',
                    'kategori' => 'fisabilillah',
                    'keterangan' => 'Aktivis dakwah yang membutuhkan dana untuk kegiatan dakwah',
                    'status' => 'aktif'
                ]
            ];

            foreach ($mustahiqData as $data) {
                Mustahiq::create($data);
            }
        }
        
        // Seed Sharia Accounting data
        $this->call(ShariaAccountingSeeder::class);
        
        // Seed Programs data
        $this->call(ProgramSeeder::class);
    }
}
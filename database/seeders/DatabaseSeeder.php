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
            'description' => 'Manages donatur, UPZ, and ZIS collection'
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

        // Create roles for department heads (Wakil Bidang)
        $wakil1Role = Role::firstOrCreate([
            'name' => 'wakil1'
        ], [
            'display_name' => 'Wakil Bidang I - Pengumpulan',
            'description' => 'Focus on donatur, collection targets and realization'
        ]);

        $wakil2Role = Role::firstOrCreate([
            'name' => 'wakil2'
        ], [
            'display_name' => 'Wakil Bidang II - Distribusi',
            'description' => 'Focus on mustahiq, distribution, and program outcomes'
        ]);

        $wakil3Role = Role::firstOrCreate([
            'name' => 'wakil3'
        ], [
            'display_name' => 'Wakil Bidang III - Keuangan',
            'description' => 'Focus on budget absorption, deviations, and report status'
        ]);

        $wakil4Role = Role::firstOrCreate([
            'name' => 'wakil4'
        ], [
            'display_name' => 'Wakil Bidang IV - SDM & SOP',
            'description' => 'Focus on SOP compliance, HR status, and assets'
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

        // Create accounts for department heads (Wakil Bidang)
        User::firstOrCreate([
            'email' => 'wakil1@zis.com'
        ], [
            'name' => 'Wakil Bidang I',
            'password' => Hash::make('password'),
            'role_id' => $wakil1Role->id,
            'phone' => '081234567901',
            'status' => 'active'
        ]);

        User::firstOrCreate([
            'email' => 'wakil2@zis.com'
        ], [
            'name' => 'Wakil Bidang II',
            'password' => Hash::make('password'),
            'role_id' => $wakil2Role->id,
            'phone' => '081234567902',
            'status' => 'active'
        ]);

        User::firstOrCreate([
            'email' => 'wakil3@zis.com'
        ], [
            'name' => 'Wakil Bidang III',
            'password' => Hash::make('password'),
            'role_id' => $wakil3Role->id,
            'phone' => '081234567903',
            'status' => 'active'
        ]);

        User::firstOrCreate([
            'email' => 'wakil4@zis.com'
        ], [
            'name' => 'Wakil Bidang IV',
            'password' => Hash::make('password'),
            'role_id' => $wakil4Role->id,
            'phone' => '081234567904',
            'status' => 'active'
        ]);

        // Create sample Donatur data only if table is empty
        if (Muzakki::count() == 0) {
            $muzakkiData = [
                [
                    'nama' => 'Ahmad Wijaya',
                    'nik' => '3201012345678901',
                    'alamat' => 'Jl. Merdeka No. 15, Bandung, Jawa Barat',
                    'telepon' => '081234567001',
                    'email' => 'ahmad.wijaya@email.com',
                    'jenis_donatur' => 'individu',
                    'jenis_kelamin' => 'laki-laki',
                    'keterangan' => 'Pegawai negeri sipil, rutin membayar zakat'
                ],
                [
                    'nama' => 'Siti Nurhaliza',
                    'nik' => '3273014567890123',
                    'alamat' => 'Jl. Sudirman No. 88, Jakarta Selatan, DKI Jakarta',
                    'telepon' => '081234567002',
                    'email' => 'siti.nurhaliza@email.com',
                    'jenis_donatur' => 'individu',
                    'jenis_kelamin' => 'perempuan',
                    'keterangan' => 'Dokter spesialis, aktif dalam kegiatan sosial'
                ],
                [
                    'nama' => 'PT. Berkah Mandiri',
                    'nik' => '0212345678901234',
                    'alamat' => 'Jl. Gatot Subroto Kav. 32, Jakarta Pusat, DKI Jakarta',
                    'telepon' => '021234567003',
                    'email' => 'admin@berkahmandiri.co.id',
                    'jenis_donatur' => 'lembaga',
                    'keterangan' => 'Perusahaan trading, pembayar zakat perusahaan rutin'
                ],
                [
                    'nama' => 'Muhammad Ridwan',
                    'nik' => '3578012345678902',
                    'alamat' => 'Jl. Ahmad Yani No. 45, Surabaya, Jawa Timur',
                    'telepon' => '081234567004',
                    'email' => 'mridwan@email.com',
                    'jenis_donatur' => 'individu',
                    'jenis_kelamin' => 'laki-laki',
                    'keterangan' => 'Pengusaha kecil, pembayar zakat fitrah dan mal'
                ],
                [
                    'nama' => 'Fatimah Azzahra',
                    'nik' => '3471012345678903',
                    'alamat' => 'Jl. Diponegoro No. 67, Yogyakarta, DI Yogyakarta',
                    'telepon' => '081234567005',
                    'email' => 'fatimah.azzahra@email.com',
                    'jenis_donatur' => 'individu',
                    'jenis_kelamin' => 'perempuan',
                    'keterangan' => 'Guru sekolah dasar, rajin membayar zakat'
                ],
                [
                    'nama' => 'CV. Harapan Jaya',
                    'nik' => '0312345678901235',
                    'alamat' => 'Jl. Malioboro No. 123, Yogyakarta, DI Yogyakarta',
                    'telepon' => '0274567890',
                    'email' => 'info@harapanjaya.co.id',
                    'jenis_donatur' => 'lembaga',
                    'keterangan' => 'Toko oleh-oleh dan kerajinan, zakat perusahaan'
                ],
                [
                    'nama' => 'Abdul Rahman',
                    'nik' => '3604012345678904',
                    'alamat' => 'Jl. Pahlawan No. 78, Pontianak, Kalimantan Barat',
                    'telepon' => '081234567006',
                    'email' => 'abdul.rahman@email.com',
                    'jenis_donatur' => 'individu',
                    'jenis_kelamin' => 'laki-laki',
                    'keterangan' => 'Petani sawit, pembayar zakat hasil pertanian'
                ],
                [
                    'nama' => 'Khadijah Salsabila',
                    'nik' => '1671012345678905',
                    'alamat' => 'Jl. Cut Nyak Dien No. 34, Banda Aceh, Aceh',
                    'telepon' => '081234567007',
                    'email' => 'khadijah.salsabila@email.com',
                    'jenis_donatur' => 'individu',
                    'jenis_kelamin' => 'perempuan',
                    'keterangan' => 'Pegawai swasta, aktif dalam komunitas masjid'
                ],
                [
                    'nama' => 'PT. Teknologi Maju',
                    'nik' => '0412345678901236',
                    'alamat' => 'Jl. HR. Rasuna Said No. 56, Jakarta Selatan, DKI Jakarta',
                    'telepon' => '021234567008',
                    'email' => 'hr@teknologimaju.com',
                    'jenis_donatur' => 'lembaga',
                    'keterangan' => 'Perusahaan IT, program CSR dan zakat perusahaan'
                ],
                [
                    'nama' => 'Omar Faruq',
                    'nik' => '5171012345678906',
                    'alamat' => 'Jl. Sultan Hasanuddin No. 89, Makassar, Sulawesi Selatan',
                    'telepon' => '081234567009',
                    'email' => 'omar.faruq@email.com',
                    'jenis_donatur' => 'individu',
                    'jenis_kelamin' => 'laki-laki',
                    'keterangan' => 'Nelayan, pembayar zakat hasil laut'
                ],
                [
                    'nama' => 'Aisyah Ramadhani',
                    'nik' => '1471012345678907',
                    'alamat' => 'Jl. Gajah Mada No. 12, Pekanbaru, Riau',
                    'telepon' => '081234567010',
                    'email' => 'aisyah.ramadhani@email.com',
                    'jenis_donatur' => 'individu',
                    'jenis_kelamin' => 'perempuan',
                    'keterangan' => 'Pengusaha salon, pembayar zakat profesi'
                ],
                [
                    'nama' => 'UD. Sumber Rezeki',
                    'nik' => '0512345678901237',
                    'alamat' => 'Jl. Juanda No. 45, Malang, Jawa Timur',
                    'telepon' => '0341567890',
                    'email' => 'admin@sumberrezeki.co.id',
                    'jenis_donatur' => 'lembaga',
                    'keterangan' => 'Distributor sembako, zakat perdagangan'
                ],
                [
                    'nama' => 'Yusuf Mansur',
                    'nik' => '6171012345678908',
                    'alamat' => 'Jl. Ahmad Dahlan No. 23, Balikpapan, Kalimantan Timur',
                    'telepon' => '081234567011',
                    'email' => 'yusuf.mansur@email.com',
                    'jenis_donatur' => 'individu',
                    'jenis_kelamin' => 'laki-laki',
                    'keterangan' => 'Insinyur tambang, pembayar zakat profesi rutin'
                ],
                [
                    'nama' => 'Zahra Aulia',
                    'nik' => '1871012345678909',
                    'alamat' => 'Jl. Imam Bonjol No. 67, Lampung, Lampung',
                    'telepon' => '081234567012',
                    'email' => 'zahra.aulia@email.com',
                    'jenis_donatur' => 'individu',
                    'jenis_kelamin' => 'perempuan',
                    'keterangan' => 'Bidan praktek mandiri, aktif dalam kegiatan dakwah'
                ],
                [
                    'nama' => 'PT. Sejahtera Abadi',
                    'nik' => '0612345678901238',
                    'alamat' => 'Jl. Veteran No. 90, Semarang, Jawa Tengah',
                    'telepon' => '024567890',
                    'email' => 'finance@sejahteraabadi.com',
                    'jenis_donatur' => 'lembaga',
                    'keterangan' => 'Perusahaan manufaktur, pembayar zakat perusahaan dan CSR'
                ],
                [
                    'nama' => 'Yayasan Pendidikan Berkah',
                    'nik' => '0712345678901239',
                    'alamat' => 'Jl. Pendidikan No. 10, Jakarta Pusat, DKI Jakarta',
                    'telepon' => '021234567013',
                    'email' => 'info@yayasanberkah.org',
                    'jenis_donatur' => 'lembaga',
                    'keterangan' => 'Yayasan pendidikan, donatur infaq dan sedekah'
                ],
                [
                    'nama' => 'Budi Santoso',
                    'nik' => '1171012345678910',
                    'alamat' => 'Jl. Merdeka No. 10, Jakarta Pusat',
                    'telepon' => '081234567014',
                    'email' => 'budi.santoso@email.com',
                    'jenis_donatur' => 'munfiq',
                    'jenis_kelamin' => 'laki-laki',
                    'keterangan' => 'Pengusaha, donatur infaq dan sedekah rutin'
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
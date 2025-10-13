# Sistem Informasi ZIS (Zakat, Infaq, Sedekah)

🚀 **Website manajemen ZIS terdepan** dengan teknologi Laravel backend, Vue.js frontend, MySQL database, **fitur OCR (Optical Character Recognition)** untuk digitalisasi dokumen otomatis, dan **sistem Akuntansi Syariah** dengan kepatuhan BAZNAS.

## 🏗️ Arsitektur Sistem

### Backend: Laravel 12
- API REST dengan autentikasi Sanctum
- Role-based access control (Admin, Bidang 1, Bidang 2, Bidang 4)
- Database MySQL dengan migrasi dan seeder

### Frontend: Vue.js 3 + TypeScript
- Routing dengan Vue Router
- State management dengan Pinia
- Styling dengan Tailwind CSS
- Icons dengan Lucide Vue

### Database: MySQL
- Tables: roles, users, donatur, upz, zis_transactions, mustahiq, programs, distributions, documents

## 🚀 Instalasi dan Setup

### Prasyarat
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL
- Git

### Setup Backend (Laravel)

1. **Navigate ke direktori backend:**
   ```bash
   cd zis-system
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Setup database:**
   - Buat database MySQL bernama `zis_system`
   - Copy `.env.example` ke `.env` (sudah dikonfigurasi)
   - Update kredensial database di `.env` jika diperlukan

4. **Generate application key:**
   ```bash
   php artisan key:generate
   ```

5. **Jalankan migrasi dan seeder:**
   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Setup storage link:**
   ```bash
   php artisan storage:link
   ```

7. **Jalankan server:**
   ```bash
   php artisan serve
   ```
   Backend akan berjalan di: http://localhost:8000

### Setup Frontend (Vue.js)

#### Option 1: Integrated Frontend (Recommended)
1. **Navigate ke direktori integrated project:**
   ```bash
   cd zis-system
   ```

2. **Install dependencies:**
   ```bash
   npm install
   ```

3. **Jalankan development server:**
   ```bash
   npm run dev
   ```

4. **Access application:**
   - Frontend + Backend: http://localhost:8000
   - Vite dev server: http://localhost:5175 (auto-starts)

## 👥 Akun Demo

Setelah menjalankan seeder, gunakan akun berikut untuk login:

| Role | Email | Password | Akses |
|------|-------|----------|-------|
| **Administrator** | admin@zis.com | password | Semua modul + Akuntansi Syariah + BAZNAS |
| **Bidang 1** | bidang1@zis.com | password | Modul Pengumpulan + Akuntansi Syariah |
| **Bidang 2** | bidang2@zis.com | password | Modul Distribusi & Pemberdayaan |
| **Bidang 4** | bidang4@zis.com | password | Modul Arsip Surat |

## 📋 Fitur Utama

### 🏠 Dashboard
- Summary total ZIS terkumpul dan terdistribusi
- Statistik donatur dan mustahiq
- Aktivitas terbaru (transaksi dan distribusi)
- **Interactive Charts**: Monthly trends, ZIS types distribution, mustahiq categories, top donatur
- **Real-time Updates**: Live notifications untuk transaksi baru dan distribusi selesai
- **Visual Analytics**: Chart.js integration dengan responsive design

### 🔍 **OCR & Document Digitization** - FITUR TERBARU!
#### 🌍 **Multi-Language OCR Support** ⭐⭐⭐
- **3 Language Support**: Indonesian, Arabic, English dengan pattern recognition khusus
- **Auto Language Detection**: Deteksi bahasa dokumen otomatis dengan confidence scoring
- **Language-Specific Processing**: 
  - **Indonesian**: KTP processing dengan NIK validation, Indonesian address format
  - **Arabic**: Saudi ID cards, Arabic currency (Riyal), Arabic-Indic numerals conversion
  - **English**: US documents, SSN/Driver's License, USD currency formatting
- **Smart Currency Recognition**: IDR, SAR, USD dengan formatting sesuai standar lokal
- **International Phone Format**: Auto-format ke +62 (Indonesia), +966 (Saudi), +1 (US)
- **RTL Script Support**: Full support untuk Right-to-Left Arabic text processing
- **Multi-Language Processing**: Proses dokumen dengan multiple bahasa sekaligus untuk akurasi maksimal

#### 📊 **Advanced OCR Features** ⭐⭐⭐
- **Batch Document Processing**: Upload dan proses multiple dokumen sekaligus
  - Multi-file drag-and-drop interface dengan real-time progress tracking
  - Template selection untuk consistent processing
  - Success/failure breakdown dengan detailed error reporting
  - Export hasil batch processing dalam format JSON
- **OCR API Integration**: REST API endpoints untuk external system integration
  - `/api/ocr/process` - Single document processing
  - `/api/ocr/batch` - Batch document processing
  - `/api/ocr/templates` - Document template management
  - `/api/ocr/statistics` - OCR analytics dan performance metrics
  - `/api/ocr/validate` - Template-based result validation
- **Document Templates System**: Predefined templates untuk consistent processing
  - **KTP Template**: Indonesian ID card dengan NIK validation
  - **Bank Statement Template**: Financial document processing
  - **Receipt Template**: Transaction/donation receipt processing
  - **General Template**: Flexible processing untuk various document types
- **OCR Accuracy Improvement**: Machine learning-inspired validation system
  - Multi-level validation (format, pattern, range, custom rules)
  - Learning algorithm yang improve accuracy based on user corrections
  - Real-time suggestions dan error correction recommendations
  - Performance analytics dengan trend tracking

#### 📱 **Core OCR Capabilities**
#### 1. **Donatur Registration** ⭐⭐⭐
- **Scan ID Cards (KTP/Passport)**: Auto-fill data personal secara otomatis
- **Enhanced Identity Extraction**: NIK, nama, alamat, tempat/tanggal lahir, gender, agama
- **Smart Pattern Recognition**: Khusus untuk dokumen Indonesia dengan akurasi tinggi
- **Mobile-Friendly Scanner**: Interface drag-and-drop yang responsif

#### 2. **Mustahiq (Beneficiary) Registration** ⭐⭐
- **Identity Document Scanning**: Scan dokumen identitas penerima bantuan
- **Support Document Processing**: Surat keterangan miskin dan dokumen pendukung
- **Extended Data Fields**: RT/RW, kelurahan, kecamatan, status perkawinan, pekerjaan
- **Automatic Data Mapping**: Mapping otomatis hasil OCR ke form fields

#### 3. **Reports & Documentation** ⭐⭐
- **Physical Report Digitization**: Scan dan digitalisasi laporan fisik
- **Bank Statement Processing**: Ekstraksi data dari rekening koran bank
- **Audit Document Processing**: Pemrosesan dokumen audit eksternal
- **Document Classification**: Klasifikasi otomatis tipe dokumen (Bank Statement, Transaction Receipt, Identity Document)
- **Digitized Documents History**: Riwayat dokumen terdigitalisasi dengan preview data

#### 💱 **OCR Technical Features**
- **Tesseract.js Integration**: OCR engine terdepan untuk web application
- **Indonesian Language Support**: Pattern khusus untuk dokumen Indonesia (ind+eng)
- **Confidence Scoring**: Skor akurasi OCR untuk validasi kualitas hasil
- **Multiple Document Types**: Support untuk transaction, identity, dan general documents
- **Real-time Processing**: Pemrosesan gambar secara real-time dengan progress indicator
- **Image Preview**: Preview gambar dengan extracted data before applying
- **Data Validation**: Validasi dan normalisasi data hasil ekstraksi

### 🕌 Modul Akuntansi Syariah (Admin & Bidang 1)
- **Dashboard Akuntansi Syariah**: Overview dana, compliance score, dan transaksi terbaru
  - **Fund Categories Management**: Kelola kategori dana syariah (Zakat, Infaq, Sedekah)
  - **Real-time Statistics**: Total dana, dana amil, dana asnaf dengan perhitungan otomatis
  - **Compliance Monitoring**: Monitor kepatuhan BAZNAS dengan scoring real-time
  - **Quick Navigation**: Akses cepat ke semua modul akuntansi syariah
- **Transaksi Syariah**: Kelola transaksi akuntansi syariah dengan integrasi ZIS
  - **Manual Transactions**: Buat transaksi akuntansi syariah langsung
  - **ZIS Integration**: Konversi otomatis transaksi ZIS ke jurnal akuntansi
  - **Double-Entry System**: Sistem debit-kredit dengan validasi otomatis
  - **BAZNAS Compliance**: Validasi kepatuhan setiap transaksi
  - **Transaction Posting**: Post transaksi dengan perhitungan amil otomatis
- **Laporan BAZNAS**: Generate dan kelola laporan kepatuhan BAZNAS
  - **Periodic Reports**: Laporan bulanan, kuartalan, dan tahunan
  - **Professional PDF**: Export laporan siap submit ke BAZNAS
  - **Approval Workflow**: Draft → Pending → Approved → Submitted
  - **Compliance Dashboard**: Monitor status kepatuhan dan submission
  - **Historical Reports**: Arsip lengkap laporan BAZNAS
- **Chart of Accounts**: Kelola bagan akun syariah dengan hierarki
  - **Account Management**: Struktur akun sesuai standar akuntansi syariah
  - **Balance Tracking**: Monitor saldo real-time setiap akun
  - **BAZNAS Mapping**: Mapping akun untuk keperluan laporan BAZNAS
### 📊 Modul Pengumpulan (Bidang 1)
- **Data Donatur**: Kelola pemberi zakat (individu/perusahaan) dengan search dan filter
  - **WhatsApp Integration**: Direct contact via WhatsApp Web/App
  - **International Format**: Automatic phone number conversion (+62)
  - **Contact Enhancement**: Clickable email links and WhatsApp buttons
  - **Field-Specific Search**: Separate search for name, address, and contact
  - **Pagination**: 10 records per page for optimal performance
- **Data UPZ**: Kelola Unit Pengumpul Zakat
- **Transaksi ZIS**: Catat penerimaan zakat, infaq, sedekah dengan validasi
  - **Real-time Notifications**: Automatic broadcasting when transactions are created
  - **Event Broadcasting**: WebSocket integration for live updates
- **Verifikasi**: Approve/reject transaksi dengan alur kerja
- **Laporan**: Export laporan pengumpulan dalam format CSV dan PDF
- **Search & Filter**: Pencarian advanced dengan multiple criteria

### 🎯 Modul Distribusi & Pemberdayaan (Bidang 2)
- **Data Mustahiq**: Kelola penerima zakat (8 asnaf)
- **Program Bantuan**: Buat dan kelola program distribusi
- **Distribusi**: Catat penyaluran bantuan
  - **Real-time Notifications**: Automatic broadcasting when distributions are completed
  - **Event Broadcasting**: WebSocket integration for live updates
- **Upload Bukti**: Dokumentasi distribusi
- **Laporan**: Export laporan distribusi dalam format CSV dan PDF

### 📁 Modul Arsip Surat (Bidang 4)
- **Surat Masuk/Keluar**: Input dan kategorisasi dengan validasi
- **Upload Dokumen**: Simpan file PDF/DOC/gambar (max 10MB)
- **Pencarian Advanced**: Cari berdasarkan nomor, perihal, tanggal, status
- **Download**: Unduh file dokumen dengan validasi akses
- **Filter**: Filter berdasarkan jenis, status, dan rentang tanggal

### 📈 Modul Laporan & Analitik (All Users)
- **Advanced Reporting**: Comprehensive reporting module with filters and charts
  - **Dynamic Filters**: Date ranges, report types, status filters, ZIS types, program filters
  - **Preset Periods**: This month, last month, quarter, year selections
  - **Interactive Charts**: Monthly trends, distribution analysis, detail charts
  - **Data Tables**: Sortable and filterable data tables with chart toggle view
  - **Export Options**: CSV and PDF export with applied filters
  - **Summary Cards**: Key metrics with effectiveness calculations and growth indicators
- **PDF Reports**: Professional PDF export with branded templates
  - **Multiple Report Types**: ZIS transactions, distributions, donatur data, summary reports
  - **Professional Layout**: Branded PDF templates with proper formatting and statistics
- **Real-time Notifications**: Toast notifications for live updates
  - **WebSocket Integration**: Pusher-based real-time communication
  - **Role-based Channels**: Different notification channels for admin, bidang1, bidang2 roles
  - **Notification Manager**: Centralized notification system with auto-dismiss functionality

## 🗄️ Database Schema

### Core Tables
- `roles` - Roles sistem (admin, bidang1, bidang2, bidang4)
- `users` - User dengan role assignment
- `donatur` - Data pemberi zakat
- `upz` - Unit Pengumpul Zakat
- `zis_transactions` - Transaksi ZIS masuk
- `mustahiq` - Data penerima zakat (8 asnaf)
- `programs` - Program bantuan/distribusi
- `distributions` - Record distribusi bantuan
- `documents` - Arsip surat masuk/keluar

### Sharia Accounting Tables
- `sharia_fund_categories` - Kategori dana syariah (Zakat, Infaq, Sedekah)
- `sharia_accounts` - Chart of accounts dengan hierarki dan BAZNAS mapping
- `sharia_transactions` - Transaksi akuntansi syariah dengan double-entry
- `baznas_reports` - Laporan BAZNAS dengan workflow approval

## 🔧 Development

### Struktur Project
```
zis-system/           # Laravel Backend + Integrated Frontend
├── app/
│   ├── Http/Controllers/Api/
│   ├── Models/
│   └── Http/Middleware/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── js/               # Vue.js Frontend (Integrated)
│   │   ├── views/
│   │   ├── components/
│   │   ├── stores/
│   │   └── router/
│   ├── css/app.css
│   └── views/app.blade.php
├── routes/api.php
├── vite.config.js
├── tailwind.config.js
└── package.json

zis-frontend/         # Vue.js Frontend (Legacy Standalone)
├── src/
│   ├── components/
│   ├── views/
│   ├── stores/
│   └── router/
└── tailwind.config.js
```

### Tech Stack
- **Backend**: Laravel 12, MySQL, Sanctum
- **Frontend**: Vue 3, TypeScript, Pinia, Vue Router
- **Styling**: Tailwind CSS, Lucide Icons
- **Build**: Vite (integrated with Laravel)
- **Testing**: PHPUnit (backend), Playwright (frontend)
- **Integration**: Laravel Vite Plugin for seamless frontend-backend development
- **Contact**: WhatsApp integration for direct communication
- **Charts**: Chart.js for interactive data visualization
- **PDF Export**: dompdf for professional report generation
- **Real-time**: Pusher for WebSocket communication and live notifications
- **Broadcasting**: Laravel event system for real-time updates
- **OCR**: Tesseract.js for document digitization and text extraction
- **Document Processing**: Advanced OCR with Indonesian language support

## 📝 TODO / Pengembangan Selanjutnya

### ✅ Completed
- [x] Laravel backend setup dengan API
- [x] Vue.js frontend dengan routing
- [x] Authentication dan authorization
- [x] Database design dan migrations
- [x] Dashboard dengan summary
- [x] Basic CRUD untuk Donatur
- [x] Sistem role-based access control (Admin, Bidang 1, Bidang 2, Bidang 4)
- [x] UI/UX improvements untuk DonaturView dengan responsive modals
- [x] Icon integration menggunakan Lucide Vue Next
- [x] Tailwind CSS v3 configuration dan PostCSS setup
- [x] Laravel Sanctum authentication dengan personal_access_tokens table
- [x] Database seeder dengan 15 contoh data Donatur realistis
- [x] Fix migration duplicate table errors
- [x] Modal scroll bug fixes dan responsive design
- [x] Table structure improvements dengan separated name/address columns
- [x] Complete CRUD untuk semua modul (Donatur, UPZ, ZIS Transactions, Mustahiq, Programs, Distributions, Documents)
- [x] Advanced search dan filtering untuk Donatur dan Documents
- [x] Data validation dan error handling dengan try-catch blocks
- [x] File upload untuk dokumen dengan validasi file size dan type
- [x] CSV export functionality untuk reports (ZIS, Distribution, Donatur)
- [x] Report summary dengan statistik dan analytics
- [x] Audit logs system untuk tracking semua perubahan data
- [x] Email notifications untuk transaksi ZIS baru
- [x] API documentation dengan JSON/OpenAPI format
- [x] Unit testing structure dengan PHPUnit dan factories

### 🔄 In Progress  
- [x] Complete CRUD untuk semua modul ✓
- [x] File upload untuk dokumen ✓
- [x] Export CSV reports ✓
- [x] Advanced search dan filtering ✓
- [x] Data validation dan error handling ✓
- [x] PDF export reports ✓
- [x] Advanced charts dan analytics dengan Chart.js ✓
- [x] Real-time notifications dengan WebSocket ✓
- [x] Advanced reporting dengan filters dan charts ✓

### 🎆 Recently Completed (v2.5.0)
- [x] **PDF Export Reports**: Professional PDF generation using dompdf
- [x] **Interactive Charts**: Chart.js integration with multiple chart types
- [x] **Real-time Notifications**: WebSocket integration with Pusher
- [x] **Advanced Reporting Module**: Comprehensive reporting with filters and visualizations
- [x] **Event Broadcasting**: Laravel event system for real-time updates
- [x] **Toast Notifications**: User-friendly notification system
- [x] **Dashboard Charts**: Monthly trends, ZIS types, mustahiq categories, top donatur
- [x] **Advanced OCR features**: ✅ **COMPLETED**
- [x] **Batch document processing** - Multiple files OCR processing
- [x] **OCR API endpoints for external integration** - REST API for third-party integration
- [x] **Document templates for common forms** - Predefined templates (KTP, Bank Statement, Receipt)
- [x] **OCR accuracy improvement with machine learning** - Validation rules and learning algorithms

### 📋 Planned
- [ ] Mobile app (Flutter) untuk donatur
- [ ] Docker containerization untuk deployment
- [ ] CI/CD pipeline dengan GitHub Actions
- [ ] Multi-language support (Indonesian/English)
- [x] **Advanced analytics dengan machine learning** ✅ **COMPLETED**
- [ ] Automated backup system
- [ ] Performance monitoring dan alerting
- [ ] SMS notification integration
- [ ] QR code generation untuk transaksi

## 🔧 Troubleshooting

### Common Issues dan Solutions

#### 1. Migration Duplicate Table Error
```bash
# Error: Table already exists
# Solution: Reset database
php artisan migrate:fresh --seed
```

#### 2. Tailwind CSS Configuration Issues
```bash
# Error: PostCSS plugin errors
# Solution: Use ES module syntax
# In tailwind.config.js and postcss.config.js:
export default {
  // configuration
}
```

#### 3. Laravel Sanctum Authentication
```bash
# Error: personal_access_tokens table not found
# Solution: Run specific migration
php artisan migrate --path=database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php
```

#### 4. Windows PowerShell Commands
```bash
# Error: && operator not recognized
# Solution: Use semicolon separator
cd "path"; php artisan command
```

#### 6. Frontend Build Issues (Integrated Setup)
```bash
# Error: Vite manifest not found
# Solution: Run Vite development server
cd zis-system
npm run dev

# Or build assets for production
npm run build
```

#### 7. OCR Processing Issues
```bash
# Error: OCR processing fails or low confidence
# Solution: Ensure image quality and proper lighting
# Tips for better OCR results:
# - Use high-resolution images (minimum 300 DPI)
# - Ensure good lighting and contrast
# - Avoid shadows or glare on documents
# - Keep documents flat and properly aligned
# - Use clear, readable fonts for best results
```

#### 8. OCR Performance Optimization
```bash
# Issue: Slow OCR processing
# Solution: Image optimization
# - Compress images before upload (< 2MB recommended)
# - Use appropriate image formats (JPEG for photos, PNG for text)
# - Ensure stable internet connection for Tesseract.js loading
```
#### 9. Project Structure Confusion
```bash
# Issue: Changes not reflecting in browser
# Solution: Ensure you're running the correct project

# For integrated setup (recommended):
cd zis-system
npm run dev          # Frontend
php artisan serve    # Backend
# Access: http://localhost:8000

# For standalone setup (legacy):
cd zis-frontend
npm run dev          # Frontend
# Access: http://localhost:5173
```
## 🤝 Contributing

1. Fork repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request
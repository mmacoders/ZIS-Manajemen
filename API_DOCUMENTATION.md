# ZIS System API Documentation

## Table of Contents
1. [Authentication](#authentication)
2. [Public Endpoints](#public-endpoints)
3. [Dashboard](#dashboard)
4. [Muzakki Management](#muzakki-management)
5. [UPZ Management](#upz-management)
6. [ZIS Transactions](#zis-transactions)
7. [Mustahiq Management](#mustahiq-management)
8. [Program Management](#program-management)
9. [Distribution Management](#distribution-management)
10. [Document Management](#document-management)
11. [Reports](#reports)
12. [OCR Services](#ocr-services)
13. [Sharia Accounting](#sharia-accounting)
14. [BAZNAS Reporting](#baznas-reporting)
15. [ML Analytics](#ml-analytics)

## Authentication

### Login
```
POST /api/login
```

**Parameters:**
- `email` (string, required): User email
- `password` (string, required): User password

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": {
      "id": 1,
      "name": "admin"
    }
  },
  "token": "1|abcdefghijk1234567890",
  "role": "admin"
}
```

### Logout
```
POST /api/logout
```
**Headers:**
- Authorization: Bearer [token]

### Get Current User
```
GET /api/me
```
**Headers:**
- Authorization: Bearer [token]

## Public Endpoints

### ML Analytics Dashboard
```
GET /api/ml-analytics/dashboard
```

### Donation Predictions
```
GET /api/ml-analytics/donation-predictions
```

### Donor Analysis
```
GET /api/ml-analytics/donor-analysis
```

### Beneficiary Predictions
```
GET /api/ml-analytics/beneficiary-predictions
```

### Fraud Detection
```
GET /api/ml-analytics/fraud-detection
```

### Performance Metrics
```
GET /api/ml-analytics/performance-metrics
```

### Prediction History
```
GET /api/ml-analytics/prediction-history
```

### Model Statistics
```
GET /api/ml-analytics/model-statistics
```

## Dashboard

### Get Dashboard Data
```
GET /api/dashboard
```
**Headers:**
- Authorization: Bearer [token]

**Response:**
```json
{
  "summary": {
    "total_muzakki": 150,
    "total_upz": 25,
    "total_mustahiq": 80,
    "total_programs": 12,
    "total_zis_collected": 50000000,
    "total_distributed": 42000000,
    "total_documents": 200,
    "pending_transactions": 5,
    "pending_documents": 3
  },
  "recent_transactions": [...],
  "recent_distributions": [...],
  "charts": {
    "monthly_collection": [...],
    "monthly_distribution": [...],
    "zis_by_type": [...],
    "mustahiq_by_category": [...],
    "top_muzakki": [...]
  },
  "notifications": {
    "recent_activities": [
      {
        "id": 1,
        "message": "Bidang Pengumpulan (Bidang Pengumpulan) Dibuat ZisTransaction",
        "model_type": "ZisTransaction",
        "model_id": 1,
        "action": "created",
        "created_at": "2 minutes ago",
        "timestamp": "2025-09-02T10:33:35.000000Z",
        "user": {
          "name": "Bidang Pengumpulan",
          "role": "bidang1"
        }
      }
    ],
    "pending_items": {
      "transactions": 1,
      "documents": 0,
      "total": 1
    },
    "unread_count": 2
  }
}
```

## Muzakki Management

### Get All Muzakki
```
GET /api/muzakki
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `search_name` (string, optional): Search by name
- `search_address` (string, optional): Search by address
- `search_contact` (string, optional): Search by contact
- `jenis` (string, optional): Filter by type (individu/perusahaan)
- `sort_by` (string, optional): Sort by field
- `sort_direction` (string, optional): Sort direction (asc/desc)
- `per_page` (integer, optional): Items per page (default: 10)

### Create Muzakki
```
POST /api/muzakki
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `nama` (string, required): Name
- `nik` (string, required): NIK (16 digits)
- `alamat` (string, required): Address
- `telepon` (string, optional): Phone number
- `email` (string, optional): Email
- `jenis` (string, required): Type (individu/perusahaan)
- `keterangan` (string, optional): Notes

### Get Muzakki
```
GET /api/muzakki/{id}
```
**Headers:**
- Authorization: Bearer [token]

### Update Muzakki
```
PUT /api/muzakki/{id}
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- Same as create

### Delete Muzakki
```
DELETE /api/muzakki/{id}
```
**Headers:**
- Authorization: Bearer [token]

### Search Muzakki
```
GET /api/muzakki-search
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `nama` (string, optional): Search by name
- `nik` (string, optional): Search by NIK
- `jenis` (string, optional): Filter by type
- `alamat` (string, optional): Search by address

## UPZ Management

### Get All UPZ
```
GET /api/upz
```
**Headers:**
- Authorization: Bearer [token]

### Create UPZ
```
POST /api/upz
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `nama` (string, required): Name
- `kode` (string, required): Code
- `alamat` (string, required): Address
- `telepon` (string, optional): Phone
- `email` (string, optional): Email
- `penanggung_jawab` (string, required): Person in charge
- `keterangan` (string, optional): Notes

### Get UPZ
```
GET /api/upz/{id}
```
**Headers:**
- Authorization: Bearer [token]

### Update UPZ
```
PUT /api/upz/{id}
```
**Headers:**
- Authorization: Bearer [token]

### Delete UPZ
```
DELETE /api/upz/{id}
```
**Headers:**
- Authorization: Bearer [token]

## ZIS Transactions

### Get All Transactions
```
GET /api/zis-transactions
```
**Headers:**
- Authorization: Bearer [token]

### Create Transaction
```
POST /api/zis-transactions
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `muzakki_id` (integer, required): Muzakki ID
- `upz_id` (integer, optional): UPZ ID
- `jenis_zis` (string, required): Type (zakat/infaq/sedekah)
- `jumlah` (number, required): Amount (min: 1000)
- `tanggal_transaksi` (date, required): Transaction date
- `keterangan` (string, optional): Notes
- `bukti_transfer` (string, optional): Transfer proof

### Get Transaction
```
GET /api/zis-transactions/{id}
```
**Headers:**
- Authorization: Bearer [token]

### Update Transaction
```
PUT /api/zis-transactions/{id}
```
**Headers:**
- Authorization: Bearer [token]

### Delete Transaction
```
DELETE /api/zis-transactions/{id}
```
**Headers:**
- Authorization: Bearer [token]

### Verify Transaction
```
POST /api/zis-transactions/{id}/verify
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `status` (string, required): Status (verified/rejected)

### Transaction Report
```
GET /api/reports/zis-transactions
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `start_date` (date, optional): Start date
- `end_date` (date, optional): End date
- `jenis_zis` (string, optional): Filter by type

## Mustahiq Management

### Get All Mustahiq
```
GET /api/mustahiq
```
**Headers:**
- Authorization: Bearer [token]

### Create Mustahiq
```
POST /api/mustahiq
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `nama` (string, required): Name
- `nik` (string, required): NIK
- `alamat` (string, required): Address
- `telepon` (string, optional): Phone
- `email` (string, optional): Email
- `kategori` (string, required): Category
- `status` (string, required): Status
- `keterangan` (string, optional): Notes

### Get Mustahiq
```
GET /api/mustahiq/{id}
```
**Headers:**
- Authorization: Bearer [token]

### Update Mustahiq
```
PUT /api/mustahiq/{id}
```
**Headers:**
- Authorization: Bearer [token]

### Delete Mustahiq
```
DELETE /api/mustahiq/{id}
```
**Headers:**
- Authorization: Bearer [token]

## Program Management

### Get All Programs
```
GET /api/programs
```
**Headers:**
- Authorization: Bearer [token]

### Create Program
```
POST /api/programs
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `nama` (string, required): Name
- `deskripsi` (string, required): Description
- `kategori` (string, required): Category
- `target_penerima` (string, required): Target recipients
- `status` (string, required): Status

### Get Program
```
GET /api/programs/{id}
```
**Headers:**
- Authorization: Bearer [token]

### Update Program
```
PUT /api/programs/{id}
```
**Headers:**
- Authorization: Bearer [token]

### Delete Program
```
DELETE /api/programs/{id}
```
**Headers:**
- Authorization: Bearer [token]

## Distribution Management

### Get All Distributions
```
GET /api/distributions
```
**Headers:**
- Authorization: Bearer [token]

### Create Distribution
```
POST /api/distributions
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `program_id` (integer, required): Program ID
- `mustahiq_id` (integer, required): Mustahiq ID
- `jumlah_distribusi` (number, required): Distribution amount
- `tanggal_distribusi` (date, required): Distribution date
- `keterangan` (string, optional): Notes

### Get Distribution
```
GET /api/distributions/{id}
```
**Headers:**
- Authorization: Bearer [token]

### Update Distribution
```
PUT /api/distributions/{id}
```
**Headers:**
- Authorization: Bearer [token]

### Delete Distribution
```
DELETE /api/distributions/{id}
```
**Headers:**
- Authorization: Bearer [token]

### Distribution Report
```
GET /api/reports/distributions
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `start_date` (date, optional): Start date
- `end_date` (date, optional): End date
- `program_id` (integer, optional): Filter by program

## Document Management

### Get All Documents
```
GET /api/documents
```
**Headers:**
- Authorization: Bearer [token]

### Create Document
```
POST /api/documents
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `nomor_surat` (string, required): Document number
- `jenis` (string, required): Type (masuk/keluar)
- `asal_tujuan` (string, required): Source/Destination
- `perihal` (string, required): Subject
- `tanggal_surat` (date, required): Document date
- `tanggal_diterima` (date, optional): Received date
- `isi_ringkas` (string, optional): Summary
- `file` (file, optional): Document file

### Get Document
```
GET /api/documents/{id}
```
**Headers:**
- Authorization: Bearer [token]

### Update Document
```
PUT /api/documents/{id}
```
**Headers:**
- Authorization: Bearer [token]

### Delete Document
```
DELETE /api/documents/{id}
```
**Headers:**
- Authorization: Bearer [token]

### Search Documents
```
GET /api/documents-search
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `nomor_surat` (string, optional): Search by document number
- `perihal` (string, optional): Search by subject
- `jenis` (string, optional): Filter by type
- `status` (string, optional): Filter by status
- `asal_tujuan` (string, optional): Search by source/destination
- `start_date` (date, optional): Start date
- `end_date` (date, optional): End date
- `search` (string, optional): Global search
- `sort_by` (string, optional): Sort by field
- `sort_direction` (string, optional): Sort direction
- `per_page` (integer, optional): Items per page

### Download Document
```
GET /api/documents/{id}/download
```
**Headers:**
- Authorization: Bearer [token]

## Reports

### Summary Report
```
GET /api/reports/summary
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `start_date` (date, optional): Start date
- `end_date` (date, optional): End date

### Export CSV
```
GET /api/reports/export-csv
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `type` (string, optional): Report type (zis/distribution/muzakki)
- `start_date` (date, optional): Start date
- `end_date` (date, optional): End date

### Export PDF
```
GET /api/reports/export-pdf
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `type` (string, optional): Report type (zis/distribution/muzakki)
- `start_date` (date, optional): Start date
- `end_date` (date, optional): End date
- `jenis` (string, optional): Filter by type (for muzakki)

### Export Summary PDF
```
GET /api/reports/export-summary-pdf
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `start_date` (date, optional): Start date
- `end_date` (date, optional): End date

## OCR Services

### Process Document
```
POST /api/ocr/process
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `document` (file, required): Document file (jpeg, png, jpg, pdf)
- `document_type` (string, optional): Type (identity/transaction/general/bank_statement/receipt)
- `template` (string, optional): Template name
- `return_image` (boolean, optional): Return base64 image

### Process Batch Documents
```
POST /api/ocr/batch
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `documents` (array, required): Array of document files (max 10)
- `document_type` (string, optional): Type (identity/transaction/general/bank_statement/receipt)
- `template` (string, optional): Template name
- `return_images` (boolean, optional): Return base64 images

### Get Templates
```
GET /api/ocr/templates
```
**Headers:**
- Authorization: Bearer [token]

### Get Statistics
```
GET /api/ocr/statistics
```
**Headers:**
- Authorization: Bearer [token]

### Validate Document
```
POST /api/ocr/validate
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `extracted_data` (object, required): Extracted data
- `template` (string, required): Template name
- `confidence` (number, required): Confidence score (0-100)

## Sharia Accounting

### Dashboard
```
GET /api/sharia-accounting/dashboard
```
**Headers:**
- Authorization: Bearer [token]

### Get Fund Categories
```
GET /api/sharia-accounting/fund-categories
```
**Headers:**
- Authorization: Bearer [token]

### Get Chart of Accounts
```
GET /api/sharia-accounting/chart-of-accounts
```
**Headers:**
- Authorization: Bearer [token]

### Get Transactions
```
GET /api/sharia-accounting/transactions
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `start_date` (date, optional): Start date
- `end_date` (date, optional): End date
- `transaction_type` (string, optional): Filter by type
- `fund_category_id` (integer, optional): Filter by fund category
- `status` (string, optional): Filter by status

### Create from ZIS Transaction
```
POST /api/sharia-accounting/create-from-zis
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `zis_transaction_id` (integer, required): ZIS transaction ID
- `fund_category_id` (integer, required): Fund category ID

### Create from Distribution
```
POST /api/sharia-accounting/create-from-distribution
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `distribution_id` (integer, required): Distribution ID
- `mustahiq_category` (string, required): Mustahiq category

### Get Financial Summary
```
GET /api/sharia-accounting/financial-summary
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `start_date` (date, optional): Start date
- `end_date` (date, optional): End date

## BAZNAS Reporting

### Get All Reports
```
GET /api/baznas-reports
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `year` (integer, optional): Filter by year
- `type` (string, optional): Filter by type
- `status` (string, optional): Filter by status

### Create Report
```
POST /api/baznas-reports
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `report_type` (string, required): Type (monthly/quarterly/annual/special)
- `report_year` (integer, required): Year
- `report_period` (string, required): Period
- `period_start` (date, required): Period start date
- `period_end` (date, required): Period end date

### Get Report
```
GET /api/baznas-reports/{id}
```
**Headers:**
- Authorization: Bearer [token]

### Update Report
```
PUT /api/baznas-reports/{id}
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `executive_summary` (string, optional): Executive summary
- `recommendations` (string, optional): Recommendations

### Approve Report
```
POST /api/baznas-reports/{id}/approve
```
**Headers:**
- Authorization: Bearer [token]

### Submit Report
```
POST /api/baznas-reports/{id}/submit
```
**Headers:**
- Authorization: Bearer [token]

### Generate PDF
```
POST /api/baznas-reports/{id}/generate-pdf
```
**Headers:**
- Authorization: Bearer [token]

### Download PDF
```
GET /api/baznas-reports/{id}/download-pdf
```
**Headers:**
- Authorization: Bearer [token]

### Compliance Dashboard
```
GET /api/baznas-reports/compliance-dashboard
```
**Headers:**
- Authorization: Bearer [token]

## ML Analytics

### Authenticated Dashboard
```
GET /api/ml-analytics-auth/dashboard
```
**Headers:**
- Authorization: Bearer [token]

### Authenticated Donation Predictions
```
GET /api/ml-analytics-auth/donation-predictions
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `months` (integer, optional): Number of months to predict (default: 3)

### Authenticated Donor Analysis
```
GET /api/ml-analytics-auth/donor-analysis
```
**Headers:**
- Authorization: Bearer [token]

### Authenticated Beneficiary Predictions
```
GET /api/ml-analytics-auth/beneficiary-predictions
```
**Headers:**
- Authorization: Bearer [token]

### Authenticated Fraud Detection
```
GET /api/ml-analytics-auth/fraud-detection
```
**Headers:**
- Authorization: Bearer [token]

### Trigger Fraud Detection
```
POST /api/ml-analytics-auth/trigger-fraud-detection
```
**Headers:**
- Authorization: Bearer [token]

### Authenticated Performance Metrics
```
GET /api/ml-analytics-auth/performance-metrics
```
**Headers:**
- Authorization: Bearer [token]

### Authenticated Prediction History
```
GET /api/ml-analytics-auth/prediction-history
```
**Headers:**
- Authorization: Bearer [token]

### Authenticated Model Statistics
```
GET /api/ml-analytics-auth/model-statistics
```
**Headers:**
- Authorization: Bearer [token]

### Verify Prediction
```
POST /api/ml-analytics-auth/predictions/{id}/verify
```
**Headers:**
- Authorization: Bearer [token]

**Parameters:**
- `actual_value` (number, required): Actual value
- `notes` (string, optional): Verification notes

### Clear Cache
```
DELETE /api/ml-analytics-auth/cache
```
**Headers:**
- Authorization: Bearer [token]

## Role-Based Access Control

The system uses role-based access control with the following roles:
- `admin`: Full access to all features
- `bidang1`: Pengumpulan (Collection)
- `bidang2`: Distribusi & Pemberdayaan (Distribution & Empowerment)
- `bidang4`: Arsip Surat (Document Archive)

### Protected Routes by Role:

**Bidang 1 (Collection) Routes:**
- `/api/muzakki*`
- `/api/upz*`
- `/api/zis-transactions*`
- `/api/sharia-accounting*`
- `/api/ml-analytics-auth*`

**Bidang 2 (Distribution & Empowerment) Routes:**
- `/api/mustahiq*`
- `/api/programs*`
- `/api/distributions*`

**Bidang 4 (Document Archive) Routes:**
- `/api/documents*`

**All Authenticated Users:**
- `/api/dashboard`
- `/api/reports/*`
- `/api/ocr/*`

## Error Responses

The API uses standard HTTP status codes:
- `200`: Success
- `201`: Created
- `400`: Bad Request
- `401`: Unauthorized
- `403`: Forbidden
- `404`: Not Found
- `422`: Unprocessable Entity
- `500`: Internal Server Error

Error response format:
```json
{
  "success": false,
  "message": "Error message",
  "error": "Detailed error information"
}
```
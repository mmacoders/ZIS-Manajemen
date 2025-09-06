<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OCRController extends Controller
{
    /**
     * Process single document via API
     */
    public function processDocument(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'document' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120', // 5MB max
                'document_type' => 'sometimes|string|in:identity,transaction,general,bank_statement,receipt',
                'template' => 'sometimes|string',
                'return_image' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('document');
            $documentType = $request->input('document_type', 'general');
            $template = $request->input('template');
            $returnImage = $request->boolean('return_image', false);

            // Store file temporarily
            $path = $file->store('temp/ocr', 'public');
            $fullPath = Storage::disk('public')->path($path);

            // Here you would integrate with actual OCR service
            // For now, we'll return a mock response structure
            $ocrResult = $this->mockOCRProcessing($file, $documentType, $template);

            // Optionally include base64 image
            $imageData = null;
            if ($returnImage) {
                $imageData = base64_encode(file_get_contents($fullPath));
            }

            // Clean up temporary file
            Storage::disk('public')->delete($path);

            return response()->json([
                'success' => true,
                'message' => 'Document processed successfully',
                'data' => [
                    'text' => $ocrResult['text'],
                    'confidence' => $ocrResult['confidence'],
                    'extracted_data' => $ocrResult['extracted_data'],
                    'document_type' => $documentType,
                    'template_used' => $template,
                    'processing_time' => $ocrResult['processing_time'],
                    'image' => $imageData
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'OCR processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process multiple documents in batch
     */
    public function processBatch(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'documents' => 'required|array|min:1|max:10', // Max 10 files per batch
                'documents.*' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
                'document_type' => 'sometimes|string|in:identity,transaction,general,bank_statement,receipt',
                'template' => 'sometimes|string',
                'return_images' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $files = $request->file('documents');
            $documentType = $request->input('document_type', 'general');
            $template = $request->input('template');
            $returnImages = $request->boolean('return_images', false);

            $results = [
                'success' => [],
                'failed' => [],
                'total_processed' => 0,
                'success_count' => 0,
                'failed_count' => 0
            ];

            foreach ($files as $index => $file) {
                try {
                    // Store file temporarily
                    $path = $file->store('temp/ocr', 'public');
                    $fullPath = Storage::disk('public')->path($path);

                    // Process OCR
                    $ocrResult = $this->mockOCRProcessing($file, $documentType, $template);

                    $imageData = null;
                    if ($returnImages) {
                        $imageData = base64_encode(file_get_contents($fullPath));
                    }

                    $results['success'][] = [
                        'file_index' => $index,
                        'file_name' => $file->getClientOriginalName(),
                        'text' => $ocrResult['text'],
                        'confidence' => $ocrResult['confidence'],
                        'extracted_data' => $ocrResult['extracted_data'],
                        'processing_time' => $ocrResult['processing_time'],
                        'image' => $imageData
                    ];

                    $results['success_count']++;

                    // Clean up
                    Storage::disk('public')->delete($path);

                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'file_index' => $index,
                        'file_name' => $file->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ];
                    $results['failed_count']++;
                }

                $results['total_processed']++;
            }

            return response()->json([
                'success' => true,
                'message' => 'Batch processing completed',
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Batch processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available document templates
     */
    public function getTemplates(): JsonResponse
    {
        $templates = [
            [
                'name' => 'ktp',
                'display_name' => 'Kartu Tanda Penduduk (KTP)',
                'type' => 'identity',
                'description' => 'Indonesian National ID Card',
                'required_fields' => ['nik', 'name'],
                'optional_fields' => ['address', 'birth_place', 'birth_date', 'gender', 'religion'],
                'min_confidence' => 75
            ],
            [
                'name' => 'bank_statement',
                'display_name' => 'Rekening Koran Bank',
                'type' => 'bank_statement',
                'description' => 'Bank account statement',
                'required_fields' => ['bank_name', 'amount'],
                'optional_fields' => ['account_number', 'date', 'reference_number'],
                'min_confidence' => 80
            ],
            [
                'name' => 'receipt',
                'display_name' => 'Bukti Transfer/Kuitansi',
                'type' => 'transaction',
                'description' => 'Transaction receipt or transfer proof',
                'required_fields' => ['amount'],
                'optional_fields' => ['date', 'reference_number', 'bank_name', 'donor_name'],
                'min_confidence' => 70
            ],
            [
                'name' => 'general',
                'display_name' => 'Dokumen Umum',
                'type' => 'general',
                'description' => 'General document processing',
                'required_fields' => [],
                'optional_fields' => ['name', 'amount', 'date', 'reference_number'],
                'min_confidence' => 60
            ]
        ];

        return response()->json([
            'success' => true,
            'message' => 'Templates retrieved successfully',
            'data' => $templates
        ]);
    }

    /**
     * Get OCR processing statistics
     */
    public function getStatistics(): JsonResponse
    {
        // In a real implementation, you would query actual statistics from database
        $stats = [
            'total_documents_processed' => 1250,
            'success_rate' => 94.5,
            'average_confidence' => 87.2,
            'most_used_template' => 'ktp',
            'processing_time_avg' => 2.3, // seconds
            'daily_stats' => [
                'today' => 45,
                'yesterday' => 52,
                'this_week' => 324,
                'this_month' => 1250
            ],
            'document_types' => [
                'identity' => 60,
                'transaction' => 25,
                'bank_statement' => 10,
                'general' => 5
            ]
        ];

        return response()->json([
            'success' => true,
            'message' => 'Statistics retrieved successfully',
            'data' => $stats
        ]);
    }

    /**
     * Validate document against template
     */
    public function validateDocument(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'extracted_data' => 'required|array',
                'template' => 'required|string',
                'confidence' => 'required|numeric|min:0|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $extractedData = $request->input('extracted_data');
            $templateName = $request->input('template');
            $confidence = $request->input('confidence');

            // Get template configuration
            $templates = collect($this->getTemplates()->getData()->data);
            $template = $templates->firstWhere('name', $templateName);

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            $validation = $this->performTemplateValidation($extractedData, $template, $confidence);

            return response()->json([
                'success' => true,
                'message' => 'Validation completed',
                'data' => $validation
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mock OCR processing (replace with actual OCR integration)
     */
    private function mockOCRProcessing($file, $documentType, $template = null): array
    {
        // Simulate processing time
        $processingTime = rand(1000, 3000) / 1000; // 1-3 seconds

        // Mock extracted data based on document type
        $extractedData = $this->generateMockData($documentType, $file->getClientOriginalName());

        return [
            'text' => $this->generateMockText($documentType),
            'confidence' => rand(70, 95),
            'extracted_data' => $extractedData,
            'processing_time' => $processingTime
        ];
    }

    /**
     * Generate mock data based on document type
     */
    private function generateMockData($documentType, $fileName): array
    {
        switch ($documentType) {
            case 'identity':
                return [
                    'nik' => '1234567890123456',
                    'name' => 'Ahmad Susanto',
                    'address' => 'Jl. Merdeka No. 123, Jakarta Pusat',
                    'birth_place' => 'Jakarta',
                    'birth_date' => '1985-06-15',
                    'gender' => 'Laki-laki'
                ];

            case 'transaction':
                return [
                    'amount' => rand(100000, 5000000),
                    'date' => date('Y-m-d'),
                    'reference_number' => 'TXN' . Str::random(8),
                    'bank_name' => 'Bank Mandiri'
                ];

            case 'bank_statement':
                return [
                    'bank_name' => 'Bank BCA',
                    'account_number' => '1234567890',
                    'amount' => rand(500000, 10000000),
                    'date' => date('Y-m-d'),
                    'reference_number' => 'REF' . Str::random(6)
                ];

            default:
                return [
                    'text_content' => 'General document content',
                    'date' => date('Y-m-d'),
                    'file_name' => $fileName
                ];
        }
    }

    /**
     * Generate mock text based on document type
     */
    private function generateMockText($documentType): string
    {
        switch ($documentType) {
            case 'identity':
                return "REPUBLIK INDONESIA\nKARTU TANDA PENDUDUK\nNIK: 1234567890123456\nNama: AHMAD SUSANTO\nTempat/Tgl Lahir: JAKARTA, 15-06-1985\nJenis Kelamin: LAKI-LAKI\nAlamat: JL. MERDEKA NO. 123\nRT/RW: 001/002\nKel/Desa: GAMBIR\nKecamatan: GAMBIR";

            case 'transaction':
                return "BUKTI TRANSFER\nBank Mandiri\nTanggal: " . date('d/m/Y') . "\nJumlah: Rp 1.500.000\nNo. Referensi: TXN12345678\nPenerima: YAYASAN ZIS\nBerita: Donasi Zakat";

            case 'bank_statement':
                return "BANK BCA\nREKENING KORAN\nNo. Rekening: 1234567890\nTanggal: " . date('d/m/Y') . "\nKredit: Rp 2.500.000\nSaldo: Rp 15.750.000\nKeterangan: Transfer Masuk";

            default:
                return "This is a general document with various text content that can be extracted and processed using OCR technology.";
        }
    }

    /**
     * Perform template validation
     */
    private function performTemplateValidation($extractedData, $template, $confidence): array
    {
        $validation = [
            'is_valid' => true,
            'confidence_check' => $confidence >= $template->min_confidence,
            'required_fields_check' => true,
            'missing_fields' => [],
            'present_fields' => [],
            'validation_score' => 0
        ];

        // Check required fields
        foreach ($template->required_fields as $field) {
            if (empty($extractedData[$field])) {
                $validation['required_fields_check'] = false;
                $validation['missing_fields'][] = $field;
            } else {
                $validation['present_fields'][] = $field;
            }
        }

        // Check optional fields
        foreach ($template->optional_fields as $field) {
            if (!empty($extractedData[$field])) {
                $validation['present_fields'][] = $field;
            }
        }

        // Overall validation
        $validation['is_valid'] = $validation['confidence_check'] && $validation['required_fields_check'];

        // Calculate validation score
        $totalFields = count($template->required_fields) + count($template->optional_fields);
        $presentFieldsCount = count($validation['present_fields']);
        $fieldScore = $totalFields > 0 ? ($presentFieldsCount / $totalFields) * 100 : 0;
        $validation['validation_score'] = ($fieldScore + $confidence) / 2;

        return $validation;
    }
}
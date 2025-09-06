<!DOCTYPE html>
<html lang=\"id\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Laporan BAZNAS {{ $report->report_code }}</title>
    <style>
        @page {
            margin: 2cm;
            size: A4;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1a472a;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 18px;
            color: #1a472a;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .header h2 {
            font-size: 14px;
            color: #2d5a31;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 10px;
            color: #666;
        }
        
        .report-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        
        .report-info h3 {
            color: #1a472a;
            margin-bottom: 10px;
            font-size: 12px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
        }
        
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .section-title {
            background: #1a472a;
            color: white;
            padding: 10px 15px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .summary-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 12px;
            text-align: center;
        }
        
        .summary-card h4 {
            font-size: 10px;
            color: #666;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        
        .summary-card .amount {
            font-size: 14px;
            font-weight: bold;
            color: #1a472a;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .table th,
        .table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        
        .table th {
            background: #e9ecef;
            font-weight: bold;
            color: #1a472a;
        }
        
        .table .number {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .currency {
            font-family: 'Courier New', monospace;
        }
        
        .executive-summary {
            background: #f8f9fa;
            border-left: 4px solid #1a472a;
            padding: 15px;
            margin: 20px 0;
            white-space: pre-line;
        }
        
        .recommendations {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            white-space: pre-line;
        }
        
        .footer {
            position: fixed;
            bottom: 1cm;
            left: 2cm;
            right: 2cm;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .compliance-indicator {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .compliance-good {
            background: #d4edda;
            color: #155724;
        }
        
        .compliance-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .compliance-danger {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class=\"header\">
        <h1>LAPORAN BAZNAS</h1>
        <h2>{{ strtoupper($report->report_type) }} - {{ strtoupper($period_name) }} {{ $report->report_year }}</h2>
        <p>Kode Laporan: {{ $report->report_code }}</p>
    </div>

    <!-- Report Information -->
    <div class=\"report-info\">
        <h3>Informasi Laporan</h3>
        <div class=\"info-grid\">
            <div>
                <div class=\"info-item\">
                    <span class=\"info-label\">Jenis Laporan:</span>
                    <span>{{ ucfirst($report->report_type) }}</span>
                </div>
                <div class=\"info-item\">
                    <span class=\"info-label\">Periode:</span>
                    <span>{{ $period_name }} {{ $report->report_year }}</span>
                </div>
                <div class=\"info-item\">
                    <span class=\"info-label\">Tanggal Mulai:</span>
                    <span>{{ $report->period_start->format('d/m/Y') }}</span>
                </div>
                <div class=\"info-item\">
                    <span class=\"info-label\">Tanggal Akhir:</span>
                    <span>{{ $report->period_end->format('d/m/Y') }}</span>
                </div>
            </div>
            <div>
                <div class=\"info-item\">
                    <span class=\"info-label\">Status:</span>
                    <span>{{ ucfirst($report->status) }}</span>
                </div>
                <div class=\"info-item\">
                    <span class=\"info-label\">Dibuat Oleh:</span>
                    <span>{{ $report->preparer->name }}</span>
                </div>
                @if($report->approved_by)
                <div class=\"info-item\">
                    <span class=\"info-label\">Disetujui Oleh:</span>
                    <span>{{ $report->approver->name }}</span>
                </div>
                @endif
                <div class=\"info-item\">
                    <span class=\"info-label\">Dibuat Tanggal:</span>
                    <span>{{ $generated_at }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class=\"section\">
        <div class=\"section-title\">RINGKASAN KEUANGAN</div>
        
        <div class=\"summary-grid\">
            <div class=\"summary-card\">
                <h4>Total Pengumpulan</h4>
                <div class=\"amount currency\">Rp {{ number_format($report->total_collection, 0, ',', '.') }}</div>
            </div>
            <div class=\"summary-card\">
                <h4>Total Distribusi</h4>
                <div class=\"amount currency\">Rp {{ number_format($report->total_distribution, 0, ',', '.') }}</div>
            </div>
            <div class=\"summary-card\">
                <h4>Total Amil</h4>
                <div class=\"amount currency\">Rp {{ number_format($report->total_amil, 0, ',', '.') }}</div>
            </div>
            <div class=\"summary-card\">
                <h4>Saldo Akhir</h4>
                <div class=\"amount currency\">Rp {{ number_format($report->closing_balance, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Collection Breakdown -->
        <table class=\"table\">
            <thead>
                <tr>
                    <th>Jenis Dana</th>
                    <th class=\"text-right\">Jumlah (Rp)</th>
                    <th class=\"text-center\">Persentase</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Zakat</td>
                    <td class=\"number currency\">{{ number_format($report->total_zakat, 0, ',', '.') }}</td>
                    <td class=\"text-center\">{{ $report->total_collection > 0 ? number_format(($report->total_zakat / $report->total_collection) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Infaq</td>
                    <td class=\"number currency\">{{ number_format($report->total_infaq, 0, ',', '.') }}</td>
                    <td class=\"text-center\">{{ $report->total_collection > 0 ? number_format(($report->total_infaq / $report->total_collection) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Sedekah</td>
                    <td class=\"number currency\">{{ number_format($report->total_sedekah, 0, ',', '.') }}</td>
                    <td class=\"text-center\">{{ $report->total_collection > 0 ? number_format(($report->total_sedekah / $report->total_collection) * 100, 1) : 0 }}%</td>
                </tr>
                <tr style=\"font-weight: bold; background: #f8f9fa;\">
                    <td>Total Pengumpulan</td>
                    <td class=\"number currency\">{{ number_format($report->total_collection, 0, ',', '.') }}</td>
                    <td class=\"text-center\">100.0%</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Asnaf Distribution -->
    @if($report->asnaf_distribution)
    <div class=\"section\">
        <div class=\"section-title\">DISTRIBUSI PER ASNAF (8 GOLONGAN)</div>
        
        <table class=\"table\">
            <thead>
                <tr>
                    <th>Asnaf</th>
                    <th class=\"text-right\">Jumlah (Rp)</th>
                    <th class=\"text-center\">Persentase</th>
                    <th class=\"text-center\">Status Kepatuhan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $asnafNames = [
                        'fakir' => 'Fakir',
                        'miskin' => 'Miskin', 
                        'amil' => 'Amil',
                        'muallaf' => 'Muallaf',
                        'riqab' => 'Riqab',
                        'gharim' => 'Gharim',
                        'fisabilillah' => 'Fisabilillah',
                        'ibnu_sabil' => 'Ibnu Sabil'
                    ];
                    $totalAsnafDistribution = array_sum($report->asnaf_distribution);
                @endphp
                
                @foreach($asnafNames as $key => $name)
                    @php
                        $amount = $report->asnaf_distribution[$key] ?? 0;
                        $percentage = $totalAsnafDistribution > 0 ? ($amount / $totalAsnafDistribution) * 100 : 0;
                        $isCompliant = $percentage >= 5; // Minimum 5% per asnaf
                    @endphp
                    <tr>
                        <td>{{ $name }}</td>
                        <td class=\"number currency\">{{ number_format($amount, 0, ',', '.') }}</td>
                        <td class=\"text-center\">{{ number_format($percentage, 1) }}%</td>
                        <td class=\"text-center\">
                            <span class=\"compliance-indicator {{ $isCompliant ? 'compliance-good' : 'compliance-warning' }}\">
                                {{ $isCompliant ? 'Sesuai' : 'Perlu Perhatian' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
                <tr style=\"font-weight: bold; background: #f8f9fa;\">
                    <td>Total Distribusi</td>
                    <td class=\"number currency\">{{ number_format($totalAsnafDistribution, 0, ',', '.') }}</td>
                    <td class=\"text-center\">100.0%</td>
                    <td class=\"text-center\">-</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- Compliance Metrics -->
    @if($report->compliance_metrics)
    <div class=\"section\">
        <div class=\"section-title\">METRIK KEPATUHAN BAZNAS</div>
        
        <table class=\"table\">
            <thead>
                <tr>
                    <th>Indikator</th>
                    <th class=\"text-center\">Nilai</th>
                    <th class=\"text-center\">Standar BAZNAS</th>
                    <th class=\"text-center\">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Persentase Amil</td>
                    <td class=\"text-center\">{{ number_format($report->compliance_metrics['amil_percentage'] ?? 0, 2) }}%</td>
                    <td class=\"text-center\">≤ 12.5%</td>
                    <td class=\"text-center\">
                        @php
                            $amilPercentage = $report->compliance_metrics['amil_percentage'] ?? 0;
                            $amilCompliant = $amilPercentage <= 12.5;
                        @endphp
                        <span class=\"compliance-indicator {{ $amilCompliant ? 'compliance-good' : 'compliance-danger' }}\">
                            {{ $amilCompliant ? 'Sesuai' : 'Tidak Sesuai' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>Efisiensi Distribusi</td>
                    <td class=\"text-center\">{{ number_format($report->compliance_metrics['distribution_efficiency'] ?? 0, 2) }}%</td>
                    <td class=\"text-center\">≥ 80%</td>
                    <td class=\"text-center\">
                        @php
                            $efficiency = $report->compliance_metrics['distribution_efficiency'] ?? 0;
                            $efficiencyCompliant = $efficiency >= 80;
                        @endphp
                        <span class=\"compliance-indicator {{ $efficiencyCompliant ? 'compliance-good' : 'compliance-warning' }}\">
                            {{ $efficiencyCompliant ? 'Baik' : 'Perlu Peningkatan' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>Tingkat Kepatuhan BAZNAS</td>
                    <td class=\"text-center\">{{ number_format($report->compliance_metrics['baznas_compliance_rate'] ?? 0, 1) }}%</td>
                    <td class=\"text-center\">100%</td>
                    <td class=\"text-center\">
                        <span class=\"compliance-indicator compliance-good\">Sesuai</span>
                    </td>
                </tr>
                <tr>
                    <td>Jumlah Muzakki</td>
                    <td class=\"text-center\">{{ number_format($report->compliance_metrics['total_muzakki'] ?? 0) }} orang</td>
                    <td class=\"text-center\">-</td>
                    <td class=\"text-center\">-</td>
                </tr>
                <tr>
                    <td>Jumlah Mustahiq</td>
                    <td class=\"text-center\">{{ number_format($report->compliance_metrics['total_mustahiq'] ?? 0) }} orang</td>
                    <td class=\"text-center\">-</td>
                    <td class=\"text-center\">-</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- Page Break for Executive Summary -->
    <div class=\"page-break\"></div>

    <!-- Executive Summary -->
    @if($report->executive_summary)
    <div class=\"section\">
        <div class=\"section-title\">RINGKASAN EKSEKUTIF</div>
        <div class=\"executive-summary\">
            {{ $report->executive_summary }}
        </div>
    </div>
    @endif

    <!-- Recommendations -->
    @if($report->recommendations)
    <div class=\"section\">
        <div class=\"section-title\">REKOMENDASI</div>
        <div class=\"recommendations\">
            {{ $report->recommendations }}
        </div>
    </div>
    @endif

    <!-- Geographical Distribution -->
    @if($report->geographical_distribution)
    <div class=\"section\">
        <div class=\"section-title\">DISTRIBUSI GEOGRAFIS</div>
        
        <table class=\"table\">
            <thead>
                <tr>
                    <th>Provinsi/Wilayah</th>
                    <th class=\"text-right\">Jumlah (Rp)</th>
                    <th class=\"text-center\">Persentase</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalGeoDistribution = array_sum($report->geographical_distribution);
                @endphp
                
                @foreach($report->geographical_distribution as $location => $amount)
                    <tr>
                        <td>{{ $location }}</td>
                        <td class=\"number currency\">{{ number_format($amount, 0, ',', '.') }}</td>
                        <td class=\"text-center\">{{ $totalGeoDistribution > 0 ? number_format(($amount / $totalGeoDistribution) * 100, 1) : 0 }}%</td>
                    </tr>
                @endforeach
                
                <tr style=\"font-weight: bold; background: #f8f9fa;\">
                    <td>Total</td>
                    <td class=\"number currency\">{{ number_format($totalGeoDistribution, 0, ',', '.') }}</td>
                    <td class=\"text-center\">100.0%</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- Footer -->
    <div class=\"footer\">
        <p>Laporan ini dibuat secara otomatis oleh Sistem Informasi ZIS | {{ $generated_by }} | {{ $generated_at }}</p>
        <p>Dokumen ini sesuai dengan standar pelaporan BAZNAS Republik Indonesia</p>
    </div>
</body>
</html>
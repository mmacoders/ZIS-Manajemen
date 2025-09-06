@extends('pdf.layout')

@section('title', 'Laporan Ringkasan ZIS')
@section('subtitle', 'LAPORAN RINGKASAN ZIS')
@section('period')
    Periode: {{ date('d F Y', strtotime($startDate)) }} - {{ date('d F Y', strtotime($endDate)) }}
@endsection

@section('report_info')
    <div class="info-item">
        <span class="info-label">Total Pengumpulan:</span>
        Rp {{ number_format($summaryData['summary']['total_collection'], 0, ',', '.') }}
    </div>
    <div class="info-item">
        <span class="info-label">Total Distribusi:</span>
        Rp {{ number_format($summaryData['summary']['total_distribution'], 0, ',', '.') }}
    </div>
@endsection

@section('content')
    <!-- Main Summary -->
    <div class="summary-section" style="margin-bottom: 30px;">
        <h3 style="margin-top: 0; margin-bottom: 15px;">RINGKASAN UTAMA</h3>
        
        <div class="summary-item">
            <span class="summary-label">Total ZIS Terkumpul:</span>
            <span class="summary-value">Rp {{ number_format($summaryData['summary']['total_collection'], 0, ',', '.') }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total ZIS Terdistribusi:</span>
            <span class="summary-value">Rp {{ number_format($summaryData['summary']['total_distribution'], 0, ',', '.') }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Saldo ZIS:</span>
            <span class="summary-value">Rp {{ number_format($summaryData['summary']['balance'], 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- ZIS Collection by Type -->
    @if($summaryData['zis_collection']->count() > 0)
        <h3>PENGUMPULAN ZIS PER JENIS</h3>
        <table style="margin-bottom: 30px;">
            <thead>
                <tr>
                    <th width="10%">No</th>
                    <th width="30%">Jenis ZIS</th>
                    <th width="20%">Jumlah Transaksi</th>
                    <th width="25%">Total Jumlah (Rp)</th>
                    <th width="15%">Persentase</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalCollectionAmount = $summaryData['zis_collection']->sum('total_amount');
                @endphp
                @foreach($summaryData['zis_collection'] as $index => $collection)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ ucfirst($collection->jenis_zis) }}</td>
                        <td class="text-center">{{ $collection->total_transactions }}</td>
                        <td class="text-right">{{ number_format($collection->total_amount, 0, ',', '.') }}</td>
                        <td class="text-center">
                            {{ $totalCollectionAmount > 0 ? number_format(($collection->total_amount / $totalCollectionAmount) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                @endforeach
                <tr style="background-color: #f0f0f0; font-weight: bold;">
                    <td colspan="2" class="text-center">TOTAL</td>
                    <td class="text-center">{{ $summaryData['zis_collection']->sum('total_transactions') }}</td>
                    <td class="text-right">{{ number_format($totalCollectionAmount, 0, ',', '.') }}</td>
                    <td class="text-center">100%</td>
                </tr>
            </tbody>
        </table>
    @endif

    <!-- Distribution by Program -->
    @if($summaryData['distribution_summary']->count() > 0)
        <h3>DISTRIBUSI PER PROGRAM</h3>
        <table style="margin-bottom: 30px;">
            <thead>
                <tr>
                    <th width="10%">No</th>
                    <th width="35%">Nama Program</th>
                    <th width="20%">Jumlah Distribusi</th>
                    <th width="25%">Total Jumlah (Rp)</th>
                    <th width="10%">Persentase</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalDistributionAmount = $summaryData['distribution_summary']->sum('total_amount');
                @endphp
                @foreach($summaryData['distribution_summary'] as $index => $distribution)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $distribution->program->nama ?? 'Program Tidak Diketahui' }}</td>
                        <td class="text-center">{{ $distribution->total_distributions }}</td>
                        <td class="text-right">{{ number_format($distribution->total_amount, 0, ',', '.') }}</td>
                        <td class="text-center">
                            {{ $totalDistributionAmount > 0 ? number_format(($distribution->total_amount / $totalDistributionAmount) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                @endforeach
                <tr style="background-color: #f0f0f0; font-weight: bold;">
                    <td colspan="2" class="text-center">TOTAL</td>
                    <td class="text-center">{{ $summaryData['distribution_summary']->sum('total_distributions') }}</td>
                    <td class="text-right">{{ number_format($totalDistributionAmount, 0, ',', '.') }}</td>
                    <td class="text-center">100%</td>
                </tr>
            </tbody>
        </table>
    @endif

    <!-- Overall Statistics -->
    <div class="summary-section">
        <h3 style="margin-top: 0; margin-bottom: 15px;">STATISTIK KESELURUHAN</h3>
        
        @php
            use App\Models\Muzakki;
            use App\Models\Mustahiq;
            use App\Models\Program;
            use App\Models\Upz;
            
            $totalMuzakki = Muzakki::count();
            $totalMustahiq = Mustahiq::where('status', 'aktif')->count();
            $totalProgram = Program::where('status', 'aktif')->count();
            $totalUpz = Upz::where('status', 'aktif')->count();
        @endphp
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
            <div style="width: 48%;">
                <div class="summary-item">
                    <span class="summary-label">Total Muzakki:</span>
                    <span class="summary-value">{{ $totalMuzakki }} orang/lembaga</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Mustahiq Aktif:</span>
                    <span class="summary-value">{{ $totalMustahiq }} orang</span>
                </div>
            </div>
            <div style="width: 48%;">
                <div class="summary-item">
                    <span class="summary-label">Total Program Aktif:</span>
                    <span class="summary-value">{{ $totalProgram }} program</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total UPZ Aktif:</span>
                    <span class="summary-value">{{ $totalUpz }} unit</span>
                </div>
            </div>
        </div>
        
        <hr style="margin: 15px 0;">
        
        <div class="summary-item">
            <span class="summary-label">Efektivitas Distribusi:</span>
            <span class="summary-value">
                {{ $summaryData['summary']['total_collection'] > 0 ? number_format(($summaryData['summary']['total_distribution'] / $summaryData['summary']['total_collection']) * 100, 1) : 0 }}%
            </span>
        </div>
        
        <div class="summary-item">
            <span class="summary-label">Rata-rata per Transaksi ZIS:</span>
            <span class="summary-value">
                @php
                    $totalTransactions = $summaryData['zis_collection']->sum('total_transactions');
                    $avgPerTransaction = $totalTransactions > 0 ? $summaryData['summary']['total_collection'] / $totalTransactions : 0;
                @endphp
                Rp {{ number_format($avgPerTransaction, 0, ',', '.') }}
            </span>
        </div>
        
        <div class="summary-item">
            <span class="summary-label">Rata-rata per Distribusi:</span>
            <span class="summary-value">
                @php
                    $totalDistributions = $summaryData['distribution_summary']->sum('total_distributions');
                    $avgPerDistribution = $totalDistributions > 0 ? $summaryData['summary']['total_distribution'] / $totalDistributions : 0;
                @endphp
                Rp {{ number_format($avgPerDistribution, 0, ',', '.') }}
            </span>
        </div>
    </div>
@endsection
@extends('pdf.layout')

@section('title', 'Laporan Transaksi ZIS')
@section('subtitle', 'LAPORAN TRANSAKSI ZIS')
@section('period')
    @if($startDate && $endDate)
        Periode: {{ date('d F Y', strtotime($startDate)) }} - {{ date('d F Y', strtotime($endDate)) }}
    @endif
@endsection

@section('report_info')
    <div class="info-item">
        <span class="info-label">Total Transaksi:</span>
        {{ $transactions->count() }} transaksi
    </div>
    <div class="info-item">
        <span class="info-label">Status:</span>
        Semua Status
    </div>
@endsection

@section('content')
    @if($transactions->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">No. Transaksi</th>
                    <th width="12%">Tanggal</th>
                    <th width="20%">Nama Muzakki</th>
                    <th width="10%">Jenis ZIS</th>
                    <th width="15%">Jumlah (Rp)</th>
                    <th width="13%">UPZ</th>
                    <th width="10%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $index => $transaction)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $transaction->nomor_transaksi }}</td>
                        <td class="text-center">{{ date('d/m/Y', strtotime($transaction->tanggal_transaksi)) }}</td>
                        <td>{{ $transaction->muzakki->nama ?? '-' }}</td>
                        <td class="text-center">{{ ucfirst($transaction->jenis_zis) }}</td>
                        <td class="text-right">{{ number_format($transaction->jumlah, 0, ',', '.') }}</td>
                        <td>{{ $transaction->upz->nama ?? '-' }}</td>
                        <td class="text-center">
                            @switch($transaction->status)
                                @case('pending')
                                    Menunggu
                                    @break
                                @case('verified')
                                    Terverifikasi
                                    @break
                                @case('rejected')
                                    Ditolak
                                    @break
                                @default
                                    {{ ucfirst($transaction->status) }}
                            @endswitch
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary Section -->
        <div class="summary-section">
            <h3 style="margin-top: 0; margin-bottom: 15px;">RINGKASAN LAPORAN</h3>
            
            @php
                $totalAmount = $transactions->sum('jumlah');
                $verifiedAmount = $transactions->where('status', 'verified')->sum('jumlah');
                $pendingAmount = $transactions->where('status', 'pending')->sum('jumlah');
                
                $byJenis = $transactions->groupBy('jenis_zis')->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'amount' => $group->sum('jumlah')
                    ];
                });
            @endphp
            
            <div class="summary-item">
                <span class="summary-label">Total Jumlah:</span>
                <span class="summary-value">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Jumlah Terverifikasi:</span>
                <span class="summary-value">Rp {{ number_format($verifiedAmount, 0, ',', '.') }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Jumlah Pending:</span>
                <span class="summary-value">Rp {{ number_format($pendingAmount, 0, ',', '.') }}</span>
            </div>
            
            <hr style="margin: 15px 0;">
            
            <h4 style="margin-bottom: 10px;">Rincian per Jenis ZIS:</h4>
            @foreach($byJenis as $jenis => $data)
                <div class="summary-item">
                    <span class="summary-label">{{ ucfirst($jenis) }}:</span>
                    <span class="summary-value">{{ $data['count'] }} transaksi (Rp {{ number_format($data['amount'], 0, ',', '.') }})</span>
                </div>
            @endforeach
        </div>
    @else
        <div class="no-data">
            <p>Tidak ada data transaksi untuk periode yang dipilih.</p>
        </div>
    @endif
@endsection
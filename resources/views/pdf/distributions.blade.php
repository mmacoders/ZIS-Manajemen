@extends('pdf.layout')

@section('title', 'Laporan Distribusi')
@section('subtitle', 'LAPORAN DISTRIBUSI ZIS')
@section('period')
    @if($startDate && $endDate)
        Periode: {{ date('d F Y', strtotime($startDate)) }} - {{ date('d F Y', strtotime($endDate)) }}
    @endif
@endsection

@section('report_info')
    <div class="info-item">
        <span class="info-label">Total Distribusi:</span>
        {{ $distributions->count() }} distribusi
    </div>
    <div class="info-item">
        <span class="info-label">Status:</span>
        Selesai
    </div>
@endsection

@section('content')
    @if($distributions->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">No. Distribusi</th>
                    <th width="12%">Tanggal</th>
                    <th width="18%">Program</th>
                    <th width="18%">Nama Mustahiq</th>
                    <th width="12%">Kategori</th>
                    <th width="15%">Jumlah (Rp)</th>
                    <th width="5%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($distributions as $index => $distribution)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $distribution->nomor_distribusi }}</td>
                        <td class="text-center">{{ date('d/m/Y', strtotime($distribution->tanggal_distribusi)) }}</td>
                        <td>{{ $distribution->program->nama ?? '-' }}</td>
                        <td>{{ $distribution->mustahiq->nama ?? '-' }}</td>
                        <td class="text-center">{{ ucfirst($distribution->mustahiq->kategori ?? '-') }}</td>
                        <td class="text-right">{{ number_format($distribution->jumlah, 0, ',', '.') }}</td>
                        <td class="text-center">
                            @switch($distribution->status)
                                @case('pending')
                                    Menunggu
                                    @break
                                @case('completed')
                                    Selesai
                                    @break
                                @case('cancelled')
                                    Dibatalkan
                                    @break
                                @default
                                    {{ ucfirst($distribution->status) }}
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
                $totalAmount = $distributions->sum('jumlah');
                
                $byProgram = $distributions->groupBy('program.nama')->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'amount' => $group->sum('jumlah')
                    ];
                });
                
                $byKategori = $distributions->groupBy('mustahiq.kategori')->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'amount' => $group->sum('jumlah')
                    ];
                });
            @endphp
            
            <div class="summary-item">
                <span class="summary-label">Total Distribusi:</span>
                <span class="summary-value">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Jumlah Penerima:</span>
                <span class="summary-value">{{ $distributions->unique('mustahiq_id')->count() }} mustahiq</span>
            </div>
            
            <hr style="margin: 15px 0;">
            
            <h4 style="margin-bottom: 10px;">Rincian per Program:</h4>
            @foreach($byProgram as $program => $data)
                <div class="summary-item">
                    <span class="summary-label">{{ $program ?: 'Program Tidak Diketahui' }}:</span>
                    <span class="summary-value">{{ $data['count'] }} distribusi (Rp {{ number_format($data['amount'], 0, ',', '.') }})</span>
                </div>
            @endforeach
            
            <hr style="margin: 15px 0;">
            
            <h4 style="margin-bottom: 10px;">Rincian per Kategori Mustahiq:</h4>
            @foreach($byKategori as $kategori => $data)
                <div class="summary-item">
                    <span class="summary-label">{{ ucfirst($kategori ?: 'Tidak Diketahui') }}:</span>
                    <span class="summary-value">{{ $data['count'] }} distribusi (Rp {{ number_format($data['amount'], 0, ',', '.') }})</span>
                </div>
            @endforeach
        </div>
    @else
        <div class="no-data">
            <p>Tidak ada data distribusi untuk periode yang dipilih.</p>
        </div>
    @endif
@endsection
@extends('pdf.layout')

@section('title', 'Data Muzakki')
@section('subtitle', 'DATA MUZAKKI')

@section('report_info')
    <div class="info-item">
        <span class="info-label">Total Muzakki:</span>
        {{ $muzakki->count() }} orang/lembaga
    </div>
    <div class="info-item">
        <span class="info-label">Jenis:</span>
        @if($jenis)
            {{ ucfirst($jenis) }}
        @else
            Semua Jenis
        @endif
    </div>
@endsection

@section('content')
    @if($muzakki->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="20%">Nama</th>
                    <th width="15%">NIK</th>
                    <th width="25%">Alamat</th>
                    <th width="12%">Telepon</th>
                    <th width="15%">Email</th>
                    <th width="8%">Jenis</th>
                </tr>
            </thead>
            <tbody>
                @foreach($muzakki as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->nama }}</td>
                        <td>{{ $item->nik }}</td>
                        <td>{{ $item->alamat }}</td>
                        <td>{{ $item->telepon }}</td>
                        <td style="font-size: 10px;">{{ $item->email }}</td>
                        <td class="text-center">{{ ucfirst($item->jenis) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary Section -->
        <div class="summary-section">
            <h3 style="margin-top: 0; margin-bottom: 15px;">RINGKASAN DATA</h3>
            
            @php
                $byJenis = $muzakki->groupBy('jenis')->map(function($group) {
                    return $group->count();
                });
                
                $byProvinsi = $muzakki->groupBy(function($item) {
                    // Extract province from alamat (simple extraction)
                    $alamat = strtolower($item->alamat);
                    if (str_contains($alamat, 'jakarta')) return 'DKI Jakarta';
                    if (str_contains($alamat, 'bandung') || str_contains($alamat, 'jawa barat')) return 'Jawa Barat';
                    if (str_contains($alamat, 'surabaya') || str_contains($alamat, 'jawa timur')) return 'Jawa Timur';
                    if (str_contains($alamat, 'yogyakarta') || str_contains($alamat, 'yogya')) return 'DI Yogyakarta';
                    if (str_contains($alamat, 'semarang') || str_contains($alamat, 'jawa tengah')) return 'Jawa Tengah';
                    if (str_contains($alamat, 'medan') || str_contains($alamat, 'sumatra utara')) return 'Sumatra Utara';
                    if (str_contains($alamat, 'makassar') || str_contains($alamat, 'sulawesi selatan')) return 'Sulawesi Selatan';
                    if (str_contains($alamat, 'denpasar') || str_contains($alamat, 'bali')) return 'Bali';
                    return 'Lainnya';
                })->map(function($group) {
                    return $group->count();
                });
            @endphp
            
            <div class="summary-item">
                <span class="summary-label">Total Muzakki:</span>
                <span class="summary-value">{{ $muzakki->count() }} orang/lembaga</span>
            </div>
            
            <hr style="margin: 15px 0;">
            
            <h4 style="margin-bottom: 10px;">Rincian per Jenis:</h4>
            @foreach($byJenis as $jenis => $count)
                <div class="summary-item">
                    <span class="summary-label">{{ ucfirst($jenis) }}:</span>
                    <span class="summary-value">{{ $count }} {{ $jenis === 'individu' ? 'orang' : 'lembaga' }}</span>
                </div>
            @endforeach
            
            <hr style="margin: 15px 0;">
            
            <h4 style="margin-bottom: 10px;">Sebaran per Daerah:</h4>
            @foreach($byProvinsi->sortDesc() as $provinsi => $count)
                <div class="summary-item">
                    <span class="summary-label">{{ $provinsi }}:</span>
                    <span class="summary-value">{{ $count }} muzakki</span>
                </div>
            @endforeach
        </div>
    @else
        <div class="no-data">
            <p>Tidak ada data muzakki yang ditemukan.</p>
        </div>
    @endif
@endsection
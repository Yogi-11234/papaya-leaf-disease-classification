@extends('layouts.app')

@section('title', 'Hasil Diagnosis Daun')

@section('content')
<div class="page-title-section">
    <h1 class="page-title">Hasil Diagnosis Daun</h1>
    <p class="page-subtitle">Rincian diagnosis klasifikasi penyakit daun pepaya oleh model kecerdasan buatan.</p>
</div>

<!-- Results Summary and Image Block -->
<div class="glass-card primary-edge mb-2">
    <div class="results-header-box">
        <!-- Leaf Image preview -->
        <div class="result-image-wrapper">
            <img src="{{ asset($klasifikasi->path_citra) }}" alt="Foto Daun Pepaya">
        </div>

        <!-- Pred Label & Confidence -->
        <div class="result-summary-text">
            @php
                $badgeClass = 'success';
                if ($klasifikasi->kode_penyakit !== 'P05') {
                    $badgeClass = ($klasifikasi->confidence_score >= 0.8) ? 'danger' : 'warning';
                }
            @endphp

            @if ($klasifikasi->kode_penyakit === 'P05')
                <div class="disease-badge success"><i class="fa-solid fa-circle-check"></i> Daun Sehat (Healthy)</div>
            @else
                <div class="disease-badge {{ $badgeClass }}"><i class="fa-solid fa-circle-exclamation"></i> Terdeteksi: {{ $klasifikasi->label_prediksi }}</div>
            @endif

            <div class="confidence-percentage" style="color: {{ $klasifikasi->kode_penyakit === 'P05' ? 'var(--color-success)' : ($klasifikasi->confidence_score >= 0.8 ? 'var(--color-danger)' : 'var(--color-warning)') }}">
                {{ number_format($klasifikasi->confidence_score * 100, 1) }}%
                <span style="font-size: 1.25rem; font-weight: 500; color: var(--color-text-muted);">Confidence Score</span>
            </div>

            <p class="text-muted mt-1" style="font-size: 0.9rem;">
                <i class="fa-solid fa-clock"></i> Didokumentasikan pada: {{ $klasifikasi->waktu_klasifikasi->format('d M Y, H:i') }} WIB
                @if ($klasifikasi->pohon)
                    <br><i class="fa-solid fa-tree"></i> Referensi: <strong>Pohon {{ $klasifikasi->pohon->kode_pohon }}</strong> (Grup: {{ $klasifikasi->pohon->grup }})
                @endif
            </p>
        </div>
    </div>

    <!-- Alert warning if confidence under 60% -->
    @if ($klasifikasi->confidence_score < 0.60)
        <div class="warning-alert">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.3rem; color: var(--color-warning);"></i>
            <div>
                <strong>Peringatan: Tingkat Keyakinan Rendah!</strong>
                <p style="font-size: 0.85rem; margin-top: 0.15rem; opacity: 0.85;">Hasil diagnosis kurang meyakinkan. Disarankan untuk mengambil foto ulang objek daun dengan pencahayaan yang lebih baik, permukaan lurus terfokus, dan menghindari bayangan gelap.</p>
            </div>
        </div>
    @endif
</div>

<!-- Layout columns: Left (Details & Prob bars), Right (AI Chatbot) -->
<div class="chat-layout">
    <div class="d-flex flex-col gap-2">
        <!-- Static Disease details from DB -->
        <div class="glass-card">
            <h3 class="mb-1" style="font-family: var(--font-heading); font-size: 1.25rem;">
                <i class="fa-solid fa-circle-info" style="color: var(--color-primary);"></i> Informasi Penyakit
            </h3>
            <div style="font-size: 0.95rem; line-height: 1.6; margin-top: 0.75rem;">
                <h4 style="color: white; margin-bottom: 0.25rem;">Gejala Umum:</h4>
                <p class="text-muted mb-2">{{ $klasifikasi->penyakit->deskripsi }}</p>

                <h4 style="color: white; margin-bottom: 0.25rem;">Rekomendasi Tindakan Awal:</h4>
                <p class="text-muted" style="white-space: pre-line;">{{ $klasifikasi->penyakit->rekomendasi_penanganan }}</p>
            </div>
        </div>

        <!-- 5-class breakdown bars -->
        <div class="glass-card">
            <h3 class="mb-1" style="font-family: var(--font-heading); font-size: 1.25rem;">
                <i class="fa-solid fa-chart-bar" style="color: var(--color-accent);"></i> Distribusi Probabilitas 5 Kelas
            </h3>
            <p class="text-muted mb-1" style="font-size: 0.88rem;">Komparasi tingkat kesamaan citra terunggah dengan seluruh kelas penyakit model.</p>

            <div class="probability-bars-container">
                @foreach ($probabilities as $prob)
                    @php
                        $isHighest = $prob->kode_penyakit === $klasifikasi->kode_penyakit;
                        $percent = $prob->nilai_probabilitas * 100;
                    @endphp
                    <div class="prob-row">
                        <div class="prob-labels">
                            <span style="font-weight: {{ $isHighest ? '700' : '500' }}; color: {{ $isHighest ? 'white' : 'var(--color-text-muted)' }};">
                                {{ $prob->penyakit->nama_penyakit }}
                                @if($isHighest) <span class="logo-badge" style="font-size: 0.6rem; padding: 0.05rem 0.3rem;">Cocok</span> @endif
                            </span>
                            <span style="font-weight: 600; color: {{ $isHighest ? 'var(--color-primary)' : 'var(--color-text-muted)' }};">{{ number_format($percent, 2) }}%</span>
                        </div>
                        <div class="prob-bar-outer">
                            <div class="prob-bar-inner" style="width: {{ $percent }}%; background: {{ $isHighest ? 'linear-gradient(90deg, var(--color-primary), var(--color-accent))' : 'rgba(255,255,255,0.15)' }};"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- AI Chatbot widget -->
    <div class="d-flex flex-col">
        @include('partials.chat-widget', [
            'scope' => 'klasifikasi',
            'endpoint' => route('chat.send', $klasifikasi->id_klasifikasi),
            'title' => 'Konsultasi Pakar Dengan Chat Bot',
            'subtitle' => 'Bertanya mengenai penyakit ' . $klasifikasi->label_prediksi,
            'greeting' => 'Halo! Saya asisten pertanian pakar AI. Apakah ada yang ingin ditanyakan seputar penyakit **' . $klasifikasi->label_prediksi . '** ini atau cara penanganannya di kebun Anda?'
        ])
    </div>
</div>
@endsection

@section('scripts')
{{-- Scoped JS is loaded inside partial --}}
@endsection

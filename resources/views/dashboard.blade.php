@extends('layouts.app')

@section('title', 'Dashboard Analitik Model')

@section('content')
<div class="page-title-section">
    <h1 class="page-title">Dashboard Analitik Model</h1>
    <p class="page-subtitle">Metrik performa model kustom <strong>PapayaLeafCNN</strong> (420.389 parameter) hasil evaluasi 5-Fold Cross Validation.</p>
</div>

<!-- Key Performance Metrics Summary Cards -->
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-value primary">{{ number_format($summary['accuracy_mean'], 2) }}%</div>
        <div class="metric-label">Rata-rata Akurasi</div>
        <span style="font-size: 0.8rem; color: var(--color-text-muted);">± {{ number_format($summary['accuracy_std'], 2) }}% Std Dev</span>
    </div>
    <div class="metric-card">
        <div class="metric-value cyan">{{ number_format($summary['precision_mean'], 2) }}%</div>
        <div class="metric-label">Rata-rata Presisi</div>
        <span style="font-size: 0.8rem; color: var(--color-text-muted);">± {{ number_format($summary['precision_std'], 2) }}% Std Dev</span>
    </div>
    <div class="metric-card">
        <div class="metric-value mint">{{ number_format($summary['recall_mean'], 2) }}%</div>
        <div class="metric-label">Rata-rata Recall</div>
        <span style="font-size: 0.8rem; color: var(--color-text-muted);">± {{ number_format($summary['recall_std'], 2) }}% Std Dev</span>
    </div>
    <div class="metric-card">
        <div class="metric-value amber">{{ number_format($summary['f1_mean'], 2) }}%</div>
        <div class="metric-label">Rata-rata F1-Score</div>
        <span style="font-size: 0.8rem; color: var(--color-text-muted);">± {{ number_format($summary['f1_std'], 2) }}% Std Dev</span>
    </div>
</div>

<div class="analytics-container">
    <!-- Left Column: Fold Metrics Table & Highlights -->
    <div class="d-flex flex-col gap-2">
        <!-- 5-Fold Metrics Table -->
        <div class="glass-card primary-edge">
            <h2 class="mb-1" style="font-family: var(--font-heading); font-size: 1.3rem;"><i class="fa-solid fa-list-check" style="color: var(--color-primary);"></i> Performa Evaluasi Per Fold</h2>
            <p class="text-muted mb-1" style="font-size: 0.9rem;">Rincian hasil metrik evaluasi pada tiap fold dataset validasi.</p>
            
            <div class="table-responsive">
                <table class="custom-table table-compact">
                    <thead>
                        <tr>
                            <th>Fold</th>
                            <th>Akurasi (%)</th>
                            <th>Presisi (%)</th>
                            <th>Recall (%)</th>
                            <th>F1-Score (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($metrics as $metric)
                            <tr style="{{ $metric->fold == $summary['best_fold'] ? 'background: rgba(16, 185, 129, 0.05); color: white;' : '' }}">
                                <td data-label="Fold">
                                    Fold {{ $metric->fold }} 
                                    @if($metric->fold == $summary['best_fold'])
                                        <span class="logo-badge" style="font-size: 0.65rem; padding: 0.05rem 0.35rem; margin-left: 0.25rem;">Terbaik</span>
                                    @endif
                                </td>
                                <td data-label="Akurasi (%)">{{ number_format($metric->accuracy, 2) }}%</td>
                                <td data-label="Presisi (%)">{{ number_format($metric->precision, 2) }}%</td>
                                <td data-label="Recall (%)">{{ number_format($metric->recall, 2) }}%</td>
                                <td data-label="F1-Score (%)">{{ number_format($metric->f1, 2) }}%</td>
                            </tr>
                        @endforeach
                        <!-- Averages row -->
                        <tr style="font-weight: 700; border-top: 2px solid var(--color-border); background: rgba(255, 255, 255, 0.02);">
                            <td data-label="Fold">Rata-rata (CV)</td>
                            <td data-label="Akurasi (%)">{{ number_format($summary['accuracy_mean'], 2) }}%</td>
                            <td data-label="Presisi (%)">{{ number_format($summary['precision_mean'], 2) }}%</td>
                            <td data-label="Recall (%)">{{ number_format($summary['recall_mean'], 2) }}%</td>
                            <td data-label="F1-Score (%)">{{ number_format($summary['f1_mean'], 2) }}%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Highlight Card: Best & Worst Fold info -->
        <div class="highlights-row">
            <div class="glass-card" style="padding: 1.5rem; background: rgba(52, 211, 153, 0.04); border-color: rgba(52, 211, 153, 0.15);">
                <div class="d-flex align-center gap-1">
                    <i class="fa-solid fa-trophy" style="color: var(--color-success); font-size: 1.5rem;"></i>
                    <div>
                        <h4 style="font-family: var(--font-heading); font-size: 1.1rem; color: white;">Fold Terbaik</h4>
                        <p class="text-muted" style="font-size: 0.85rem;">Fold {{ $summary['best_fold'] }} dengan akurasi <strong>{{ number_format($summary['best_accuracy'], 2) }}%</strong></p>
                    </div>
                </div>
            </div>
            
            <div class="glass-card" style="padding: 1.5rem; background: rgba(248, 113, 113, 0.04); border-color: rgba(248, 113, 113, 0.15);">
                <div class="d-flex align-center gap-1">
                    <i class="fa-solid fa-circle-down" style="color: var(--color-danger); font-size: 1.5rem;"></i>
                    <div>
                        <h4 style="font-family: var(--font-heading); font-size: 1.1rem; color: white;">Fold Terendah</h4>
                        <p class="text-muted" style="font-size: 0.85rem;">Fold {{ $summary['worst_fold'] }} dengan akurasi <strong>{{ number_format($summary['worst_accuracy'], 2) }}%</strong></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dataset Distribution Chart -->
        <div class="glass-card">
            <h2 class="mb-1" style="font-family: var(--font-heading); font-size: 1.3rem;"><i class="fa-solid fa-chart-pie" style="color: var(--color-accent);"></i> Distribusi Dataset Asli</h2>
            <p class="text-muted mb-2" style="font-size: 0.9rem;">Distribusi jumlah sampel citra per kelas penyakit daun pepaya (Total: <strong>{{ $totalImages }}</strong> citra original).</p>
            
            <div class="probability-bars-container" style="margin-top: 1rem;">
                @foreach ($distribution as $item)
                    @php
                        $percentage = ($item->jumlah_citra / $totalImages) * 100;
                    @endphp
                    <div class="prob-row">
                        <div class="prob-labels">
                            <span style="font-weight: 600; color: white;">{{ $item->penyakit->nama_penyakit }}</span>
                            <span class="text-muted">{{ $item->jumlah_citra }} Citra ({{ number_format($percentage, 1) }}%)</span>
                        </div>
                        <div class="prob-bar-outer">
                            <div class="prob-bar-inner" style="width: {{ $percentage }}%; background: linear-gradient(90deg, var(--color-accent), var(--color-primary));"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Right Column: Interactive Confusion Matrix -->
    <div class="glass-card">
        <h2 class="mb-1" style="font-family: var(--font-heading); font-size: 1.3rem;"><i class="fa-solid fa-table-cells" style="color: var(--color-warning);"></i> Confusion Matrix</h2>
        <p class="text-muted mb-2" style="font-size: 0.9rem;">Pilih fold di bawah untuk melihat performa prediksi detail per kelas penyakit.</p>

        <!-- Dynamic Selector Tabs -->
        <div class="matrix-tabs">
            @for ($i = 1; $i <= 5; $i++)
                <button class="matrix-tab {{ $i == $summary['best_fold'] ? 'active' : '' }}" onclick="switchFoldMatrix({{ $i }})">
                    Fold {{ $i }}
                </button>
            @endfor
        </div>

        <!-- Matrix Image Display Area -->
        <div class="matrix-image-container">
            <img id="confusion-matrix-img" class="matrix-img" 
                 src="{{ asset('images/confusion/confusion_matrix_fold' . $summary['best_fold'] . '.png') }}" 
                 alt="Confusion Matrix Fold {{ $summary['best_fold'] }}"
                 onerror="this.src='https://placehold.co/500x420/0f172a/9ca3af?text=Confusion+Matrix+Fold+'+activeFold+'\n(Belum+Ada+Gambar)'">
        </div>
        
        <p class="text-muted mt-2 text-center" style="font-size: 0.8rem; font-style: italic;">
            <i class="fa-solid fa-info-circle"></i> File gambar dimuat dari folder <code>public/images/confusion/</code>.
        </p>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let activeFold = {{ $summary['best_fold'] ?? 5 }};

    function switchFoldMatrix(foldNum) {
        activeFold = foldNum;
        
        // 1. Toggle active class on tab buttons
        document.querySelectorAll('.matrix-tab').forEach((btn, index) => {
            if (index === foldNum - 1) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // 2. Change image src
        const img = document.getElementById('confusion-matrix-img');
        img.src = `{{ asset('images/confusion/confusion_matrix_fold') }}${foldNum}.png`;
        img.alt = `Confusion Matrix Fold ${foldNum}`;
    }
</script>
@endsection

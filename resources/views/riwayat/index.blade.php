@extends('layouts.app')

@section('title', 'Riwayat Klasifikasi')

@section('content')
<div class="page-title-section">
    <h1 class="page-title">Riwayat Diagnosis</h1>
    <p class="page-subtitle">Daftar rekaman riwayat diagnosis penyakit daun pepaya per blok dan pohon.</p>
</div>

<!-- Filter Bar -->
<form action="{{ route('riwayat.index') }}" method="GET" class="filter-bar">
    <div class="filter-group">
        <label class="form-label" for="filter-grup">Filter Blok / Grup</label>
        <select name="grup" id="filter-grup" class="form-control" onchange="this.form.submit()">
            <option value="">-- Semua Grup --</option>
            @foreach ($groups as $grp)
                <option value="{{ $grp }}" {{ request('grup') == $grp ? 'selected' : '' }}>
                    Blok {{ $grp }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="filter-group">
        <label class="form-label" for="filter-pohon">Filter Kode Pohon</label>
        <select name="kode_pohon" id="filter-pohon" class="form-control" onchange="this.form.submit()">
            <option value="">-- Semua Pohon --</option>
            @foreach ($trees as $tr)
                <option value="{{ $tr }}" {{ request('kode_pohon') == $tr ? 'selected' : '' }}>
                    Pohon {{ $tr }}
                </option>
            @endforeach
        </select>
    </div>

    <div style="flex-shrink: 0;">
        @if (request()->filled('grup') || request()->filled('kode_pohon'))
            <a href="{{ route('riwayat.index') }}" class="btn-secondary">
                <i class="fa-solid fa-filter-circle-xmark"></i> Hapus Filter
            </a>
        @endif
    </div>
</form>

<!-- Selected Tree Detail Header with Edit Action -->
@if ($selectedPohon)
    <div class="glass-card mb-2" style="padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border-left: 4px solid var(--color-primary);">
        <div>
            <span style="font-size: 0.85rem; color: var(--color-text-muted);">Pohon Terfilter:</span>
            <h3 style="font-family: var(--font-heading); color: white; margin-top: 0.15rem; font-size: 1.3rem;">
                Pohon <span style="color: var(--color-primary);">{{ $selectedPohon->kode_pohon }}</span> (Blok {{ $selectedPohon->grup }})
            </h3>
        </div>
        <button type="button" class="btn-secondary" onclick="openEditTreeModal()" style="min-height: 44px;">
            <i class="fa-solid fa-pen-to-square" style="color: var(--color-primary);"></i> Edit Detail Pohon
        </button>
    </div>
@endif

<!-- History List -->
@if ($history->count() > 0)
    <div class="glass-card primary-edge">
        <div class="table-responsive">
            <table class="custom-table" style="margin-top: 0;">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Foto</th>
                        <th>Penugasan Pohon (Edit Instan)</th>
                        <th>Diagnosis</th>
                        <th>Confidence</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($history as $item)
                        <tr>
                            <td data-label="Tanggal">{{ $item->waktu_klasifikasi->format('d M Y, H:i') }} WIB</td>
                            <td data-label="Foto">
                                <div style="width: 50px; height: 50px; border-radius: 6px; overflow: hidden; border: 1px solid var(--color-border);">
                                    <img src="{{ asset($item->path_citra) }}" alt="Foto Daun" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            </td>
                            <td data-label="Penugasan Pohon (Edit Instan)">
                                <select onchange="changeTreeAssignment('{{ $item->id_klasifikasi }}', this.value)" class="form-control" style="font-size: 0.85rem; padding: 0.25rem 0.5rem; min-height: 38px; background: rgba(0, 0, 0, 0.4); border-color: rgba(255,255,255,0.1); width: 100%; max-width: 220px;">
                                    <option value="">-- Tanpa Pohon / Kosongkan --</option>
                                    @foreach ($allTrees as $t)
                                        <option value="{{ $t->id_pohon }}" {{ $item->id_pohon === $t->id_pohon ? 'selected' : '' }}>
                                            Blok {{ $t->grup }} - {{ $t->kode_pohon }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td data-label="Diagnosis">
                                @if ($item->kode_penyakit === 'P05')
                                    <span style="color: var(--color-success); font-weight: 600;">Sehat</span>
                                @else
                                    <span style="color: {{ $item->confidence_score >= 0.8 ? 'var(--color-danger)' : 'var(--color-warning)' }}; font-weight: 600;">
                                        {{ $item->label_prediksi }}
                                    </span>
                                @endif
                            </td>
                            <td data-label="Confidence">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 60px; background: rgba(255,255,255,0.05); height: 6px; border-radius: 99px; overflow: hidden;">
                                        <div style="width: {{ $item->confidence_score * 100 }}%; height: 100%; background: {{ $item->kode_penyakit === 'P05' ? 'var(--color-success)' : ($item->confidence_score >= 0.8 ? 'var(--color-danger)' : 'var(--color-warning)') }};"></div>
                                    </div>
                                    <span style="font-size: 0.85rem; font-weight: 500;">{{ number_format($item->confidence_score * 100, 1) }}%</span>
                                </div>
                            </td>
                            <td data-label="Aksi" style="text-align: right;">
                                <div style="display: inline-flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="{{ route('klasifikasi.show', $item->id_klasifikasi) }}" class="btn-secondary" style="padding: 0.4rem 0.75rem; font-size: 0.85rem; min-height: 38px;">
                                        <i class="fa-solid fa-eye"></i> Detail
                                    </a>
                                    
                                    <form action="{{ route('riwayat.destroy', $item->id_klasifikasi) }}" method="POST" onsubmit="return confirmDelete(event)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-secondary" style="padding: 0.4rem 0.75rem; font-size: 0.85rem; background: rgba(248, 113, 113, 0.05); border-color: rgba(248, 113, 113, 0.15); color: var(--color-danger); min-height: 38px;">
                                            <i class="fa-solid fa-trash-can"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div style="margin-top: 1.5rem; display: flex; justify-content: center;">
            {{ $history->links('pagination::simple-bootstrap-5') }}
        </div>
    </div>
@else
    <div class="glass-card text-center" style="padding: 4rem 2rem;">
        <i class="fa-solid fa-folder-open" style="font-size: 3.5rem; color: var(--color-text-muted); margin-bottom: 1.5rem; opacity: 0.4;"></i>
        <h3 style="font-family: var(--font-heading); font-size: 1.3rem; margin-bottom: 0.5rem;">Tidak Ada Riwayat Klasifikasi</h3>
        <p class="text-muted mb-2" style="font-size: 0.95rem;">Belum ada citra daun pepaya yang diunggah atau tidak ada riwayat yang cocok dengan filter aktif.</p>
        <a href="{{ route('klasifikasi.index') }}" class="btn-primary" style="display: inline-flex; width: auto; margin-top: 1rem;">
            <i class="fa-solid fa-cloud-arrow-up"></i> Mulai Unggah Daun
        </a>
    </div>
@endif

<!-- Tree Chatbot Widget (Only show when filtered to a single tree code) -->
@if ($selectedPohon)
    <div style="margin-top: 3rem;">
        @include('partials.chat-widget', [
            'scope' => 'pohon',
            'endpoint' => route('chat.tree_send', $selectedPohon->id_pohon),
            'title' => 'Tanya Chatbot tentang Riwayat Pohon ' . $selectedPohon->kode_pohon,
            'subtitle' => 'Konsultasikan pola penyakit dan pencegahan pada pohon ini',
            'greeting' => 'Halo! Saya asisten pertanian pakar AI. Apakah ada yang ingin ditanyakan seputar riwayat kesehatan **Pohon ' . $selectedPohon->kode_pohon . '** (Blok ' . $selectedPohon->grup . ') ini?'
        ])
    </div>

    <!-- Edit Tree Details Modal Overlay -->
    <div class="modal-overlay" id="edit-tree-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Detail Pohon</h3>
                <button type="button" class="modal-close" onclick="closeEditTreeModal()">&times;</button>
            </div>
            <form action="{{ route('pohon.update', $selectedPohon->id_pohon) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label" for="edit-kode-pohon">Kode Pohon</label>
                    <input type="text" name="kode_pohon" id="edit-kode-pohon" class="form-control" value="{{ $selectedPohon->kode_pohon }}" required style="min-height: 44px;">
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit-grup">Blok / Grup</label>
                    <input type="text" name="grup" id="edit-grup" class="form-control" value="{{ $selectedPohon->grup }}" required style="min-height: 44px;">
                </div>
                <button type="submit" class="btn-primary mt-2" style="min-height: 44px;">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
@endif

@endsection

@section('scripts')
<script>
    function confirmDelete(event) {
        event.preventDefault();
        if (confirm("Apakah Anda yakin ingin menghapus data klasifikasi daun ini secara permanen dari riwayat?")) {
            event.target.submit();
        }
    }

    // Modal Control Functions
    function openEditTreeModal() {
        const modal = document.getElementById('edit-tree-modal');
        if (modal) modal.classList.add('open');
    }

    function closeEditTreeModal() {
        const modal = document.getElementById('edit-tree-modal');
        if (modal) modal.classList.remove('open');
    }

    // AJAX Tree Assignment updater
    function changeTreeAssignment(idKlasifikasi, idPohon) {
        fetch(`/klasifikasi/${idKlasifikasi}/pohon`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id_pohon: idPohon || null })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showToastMessage('success', data.message);
                
                // If filtered by tree, optionally warn farmer that tree association changed and they might want to refresh to see updated filtered logs.
                if ("{{ request('kode_pohon') }}") {
                    showToastMessage('success', 'Refresh halaman jika ingin menyinkronkan daftar filter pohon aktif.');
                }
            } else {
                showToastMessage('error', data.message || 'Gagal memperbarui asosiasi pohon.');
            }
        })
        .catch(err => {
            console.error(err);
            showToastMessage('error', 'Koneksi gagal atau parameter tidak valid.');
        });
    }

    // Reusable custom Toast alert injector
    function showToastMessage(type, message) {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.position = 'fixed';
            container.style.top = '20px';
            container.style.right = '20px';
            container.style.zIndex = '9999';
            container.style.display = 'flex';
            container.style.flexDirection = 'column';
            container.style.gap = '10px';
            document.body.appendChild(container);
        }
        
        const toast = document.createElement('div');
        toast.className = 'glass-card primary-edge';
        toast.style.padding = '1rem 1.5rem';
        toast.style.display = 'flex';
        toast.style.alignItems = 'center';
        toast.style.gap = '0.75rem';
        toast.style.minWidth = '300px';
        toast.style.boxShadow = 'var(--shadow-premium)';
        toast.style.animation = 'float 0.5s ease-out';
        toast.style.transition = 'all 0.5s ease';
        
        if (type === 'success') {
            toast.style.background = 'rgba(16, 185, 129, 0.15)';
            toast.style.borderColor = 'var(--color-primary)';
            toast.innerHTML = `<i class="fa-solid fa-circle-check" style="color: var(--color-success); font-size: 1.25rem;"></i>
                               <div><span style="font-weight: 600; color: white;">Berhasil!</span><p style="font-size: 0.85rem; color: var(--color-text-muted);">${message}</p></div>`;
        } else {
            toast.style.background = 'rgba(248, 113, 113, 0.15)';
            toast.style.borderColor = 'var(--color-danger)';
            toast.innerHTML = `<i class="fa-solid fa-circle-exclamation" style="color: var(--color-danger); font-size: 1.25rem;"></i>
                               <div><span style="font-weight: 600; color: white;">Gagal!</span><p style="font-size: 0.85rem; color: var(--color-text-muted);">${message}</p></div>`;
        }
        
        container.appendChild(toast);
        
        // Auto-remove toast after 4 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-10px)';
            setTimeout(() => toast.remove(), 500);
        }, 4000);
    }
</script>
@endsection

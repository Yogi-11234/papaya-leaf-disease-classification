@extends('layouts.app')

@section('title', 'Mulai Klasifikasi')

@section('content')
<div class="page-title-section">
    <h1 class="page-title">Deteksi Penyakit Daun</h1>
    <p class="page-subtitle">Unggah citra daun pepaya atau ambil foto langsung menggunakan kamera untuk diagnosis penyakit secara real-time.</p>
</div>

<form action="{{ route('klasifikasi.store') }}" method="POST" enctype="multipart/form-data" id="upload-form">
    @csrf

    <div class="upload-container">
        <!-- Left: Upload drag zone & preview -->
        <div class="d-flex flex-col gap-2">
            <div class="glass-card primary-edge" style="padding: 1.5rem;">
                <div class="d-flex justify-between align-center mb-1 flex-col sm:flex-row gap-1">
                    <h3 style="font-family: var(--font-heading); font-size: 1.25rem;">
                        <i class="fa-solid fa-camera" style="color: var(--color-primary);"></i> Sumber Citra Daun
                    </h3>

                    <!-- Method Switch Buttons -->
                    <div class="d-flex gap-1" id="method-selectors">
                        <button type="button" class="btn-secondary active" id="select-upload-btn" onclick="showUploadMethod()" style="padding: 0.5rem 1rem; font-size: 0.85rem; min-height: 44px;">
                            <i class="fa-solid fa-file-upload"></i> Unggah Berkas
                        </button>
                        <button type="button" class="btn-secondary" id="select-camera-btn" onclick="showCameraMethod()" style="padding: 0.5rem 1rem; font-size: 0.85rem; min-height: 44px;">
                            <i class="fa-solid fa-video"></i> Ambil Kamera
                        </button>
                    </div>
                </div>
                <p class="text-muted mb-2" style="font-size: 0.88rem;">Format didukung: JPG, JPEG, PNG (Maks. 10MB). Pastikan objek daun berada di tengah dan terfokus.</p>

                <!-- File input hidden, custom box styled -->
                <input type="file" name="image" id="image-file-input" style="display: none;" accept="image/*" onchange="handleFileSelect(event)">

                <!-- Upload Zone -->
                <div class="upload-zone" id="drop-zone" onclick="document.getElementById('image-file-input').click()">
                    <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
                    <div>
                        <p style="font-weight: 600; color: white;">Seret & Lepas foto di sini</p>
                        <p class="text-muted" style="font-size: 0.85rem; margin-top: 0.25rem;">atau klik untuk mencari dari galeri perangkat</p>
                    </div>
                </div>

                <!-- Camera Preview Box -->
                <div id="camera-preview-box" class="camera-container" style="display: none;">
                    <video id="camera-video" class="camera-video" autoplay playsinline></video>
                    <div class="camera-controls">
                        <button type="button" class="camera-btn camera-btn-secondary" id="toggle-camera-facing" onclick="switchCamera()" title="Putar Kamera" style="min-height: 44px; min-width: 44px;">
                            <i class="fa-solid fa-camera-rotate"></i>
                        </button>
                        <button type="button" class="camera-btn camera-btn-capture" onclick="capturePhoto()" title="Ambil Foto" style="min-height: 44px; min-width: 44px;">
                            <i class="fa-solid fa-camera"></i>
                        </button>
                        <button type="button" class="camera-btn camera-btn-secondary" onclick="stopCamera()" title="Tutup Kamera" style="min-height: 44px; min-width: 44px;">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <!-- Preview container -->
                <div class="preview-container" id="preview-container">
                    <button type="button" class="remove-preview-btn" onclick="clearFileSelect(event)" style="min-height: 44px; min-width: 44px;">&times;</button>
                    <img id="image-preview" class="preview-image" src="" alt="Preview Citra Daun">
                </div>
            </div>
        </div>

        <!-- Right: Tree & Group metadata inputs -->
        <div class="d-flex flex-col gap-2">
            <div class="glass-card">
                <h3 class="mb-1" style="font-family: var(--font-heading); font-size: 1.25rem;">
                    <i class="fa-solid fa-tree" style="color: var(--color-accent);"></i> Identitas Pohon (Opsional)
                </h3>
                <p class="text-muted mb-2" style="font-size: 0.88rem;">Menghubungkan hasil diagnosis ke riwayat pohon tertentu di kebun.</p>

                <!-- Select Existing tree block -->
                <div class="form-group">
                    <label class="form-label" for="select-pohon">Pilih Pohon Terdaftar</label>
                    <select name="id_pohon_select" id="select-pohon" class="form-control" onchange="fillTreeData(this)" style="min-height: 44px;">
                        <option value="">-- Lewati / Pilih Pohon Baru --</option>
                        @foreach ($trees as $tree)
                            <option value="{{ $tree->id_pohon }}" data-code="{{ $tree->kode_pohon }}" data-group="{{ $tree->grup }}">
                                Blok {{ $tree->grup }} — Pohon {{ $tree->kode_pohon }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="text-align: center; color: var(--color-text-muted); font-size: 0.85rem; margin: 0.5rem 0;" class="form-group">
                    <span>— ATAU DAFTARKAN BARU —</span>
                </div>

                <input type="hidden" name="kode_pohon" id="hidden-kode-pohon">
                <input type="hidden" name="grup" id="hidden-grup">

                <!-- Quick add new tree inputs -->
                <div class="form-group">
                    <label class="form-label" for="input-kode-pohon">Kode Pohon Baru</label>
                    <input type="text" name="new_kode_pohon" id="input-kode-pohon" class="form-control" placeholder="Contoh: P08, Pohon-A1" style="min-height: 44px;">
                </div>

                <div class="form-group">
                    <label class="form-label" for="input-grup">Nama Grup / Blok Baru</label>
                    <input type="text" name="new_grup" id="input-grup" class="form-control" placeholder="Contoh: Blok-Selatan, Kebun-A" style="min-height: 44px;">
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-primary mt-2" id="submit-btn" style="min-height: 44px;">
                    <span id="btn-text"><i class="fa-solid fa-microchip"></i> Analisis & Klasifikasi</span>
                    <span id="btn-loader" style="display: none;"><i class="fa-solid fa-circle-notch fa-spin"></i> Memproses model di FastAPI...</span>
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    const dropZone = document.getElementById('drop-zone');
    const previewContainer = document.getElementById('preview-container');
    const imagePreview = document.getElementById('image-preview');
    const fileInput = document.getElementById('image-file-input');
    const uploadForm = document.getElementById('upload-form');
    const submitBtn = document.getElementById('submit-btn');
    const btnText = document.getElementById('btn-text');
    const btnLoader = document.getElementById('btn-loader');

    // Camera Capture elements
    const cameraPreviewBox = document.getElementById('camera-preview-box');
    const cameraVideo = document.getElementById('camera-video');
    const selectUploadBtn = document.getElementById('select-upload-btn');
    const selectCameraBtn = document.getElementById('select-camera-btn');

    let cameraStream = null;
    let currentFacingMode = "environment"; // default to back camera

    // Drag and drop events
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            fileInput.files = files;
            displayPreview(files[0]);
        }
    });

    function handleFileSelect(event) {
        const files = event.target.files;
        if (files.length > 0) {
            displayPreview(files[0]);
        }
    }

    function displayPreview(file) {
        if (!file.type.startsWith('image/')) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            dropZone.style.display = 'none';
            cameraPreviewBox.style.display = 'none';
            previewContainer.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }

    function clearFileSelect(event) {
        if (event) event.stopPropagation();
        fileInput.value = '';
        imagePreview.src = '';
        previewContainer.style.display = 'none';

        // Show the active zone based on methods selection
        if (selectCameraBtn.classList.contains('active')) {
            startCamera();
        } else {
            dropZone.style.display = 'flex';
        }
    }

    /* ==========================================
       CAMERA CAPTURE HANDLERS
       ========================================== */
    function showUploadMethod() {
        selectUploadBtn.classList.add('active');
        selectCameraBtn.classList.remove('active');

        stopCamera();
        clearFileSelect();
    }

    function showCameraMethod() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert("Kamera tidak didukung pada browser Anda. Gunakan opsi Unggah Berkas.");
            return;
        }

        selectUploadBtn.classList.remove('active');
        selectCameraBtn.classList.add('active');

        dropZone.style.display = 'none';
        previewContainer.style.display = 'none';
        startCamera();
    }

    async function startCamera() {
        // Stop any existing stream first
        if (cameraStream) {
            stopCamera();
        }

        const constraints = {
            video: {
                facingMode: currentFacingMode,
                width: { ideal: 1024 },
                height: { ideal: 768 }
            },
            audio: false
        };

        try {
            cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
            cameraVideo.srcObject = cameraStream;

            // Adjust mirror view based on front/back camera
            if (currentFacingMode === "user") {
                cameraVideo.style.transform = "scaleX(-1)";
            } else {
                cameraVideo.style.transform = "none";
            }

            dropZone.style.display = 'none';
            previewContainer.style.display = 'none';
            cameraPreviewBox.style.display = 'flex';
        } catch (err) {
            console.error("Error accessing camera: ", err);
            alert("Gagal mengakses kamera. Pastikan Anda mengizinkan akses kamera.");
            showUploadMethod();
        }
    }

    function stopCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }
        cameraVideo.srcObject = null;
        cameraPreviewBox.style.display = 'none';
    }

    function switchCamera() {
        currentFacingMode = currentFacingMode === "environment" ? "user" : "environment";
        startCamera();
    }

    function capturePhoto() {
        if (!cameraStream) return;

        // Create virtual canvas to render current frame
        const canvas = document.createElement('canvas');
        canvas.width = cameraVideo.videoWidth || 640;
        canvas.height = cameraVideo.videoHeight || 480;

        const context = canvas.getContext('2d');

        // Mirror horizontally if using front camera
        if (currentFacingMode === "user") {
            context.translate(canvas.width, 0);
            context.scale(-1, 1);
        }

        context.drawImage(cameraVideo, 0, 0, canvas.width, canvas.height);

        // Convert canvas image to Blob
        canvas.toBlob((blob) => {
            if (!blob) return;

            // Generate virtual File object
            const capturedFile = new File([blob], `captured_leaf_${Date.now()}.jpg`, {
                type: 'image/jpeg'
            });

            // Set capturedFile to input element files collection using DataTransfer
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(capturedFile);
            fileInput.files = dataTransfer.files;

            // Show captured file preview & stop camera streaming
            displayPreview(capturedFile);
            stopCamera();
        }, 'image/jpeg', 0.9);
    }

    function fillTreeData(selectElem) {
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        const inputKode = document.getElementById('input-kode-pohon');
        const inputGrup = document.getElementById('input-grup');
        const hiddenKode = document.getElementById('hidden-kode-pohon');
        const hiddenGrup = document.getElementById('hidden-grup');

        if (selectElem.value) {
            const code = selectedOption.getAttribute('data-code');
            const group = selectedOption.getAttribute('data-group');

            // Disable manual text input when choosing existing tree
            inputKode.value = '';
            inputGrup.value = '';
            inputKode.disabled = true;
            inputGrup.disabled = true;

            // Fill hidden values to be passed to controller
            hiddenKode.value = code;
            hiddenGrup.value = group;
        } else {
            // Enable manual input
            inputKode.disabled = false;
            inputGrup.disabled = false;
            hiddenKode.value = '';
            hiddenGrup.value = '';
        }
    }

    // Handle form submit loader state
    uploadForm.addEventListener('submit', function() {
        stopCamera(); // Make sure to release camera stream on submit
        submitBtn.disabled = true;
        btnText.style.display = 'none';
        btnLoader.style.display = 'inline-block';
    });
</script>
@endsection


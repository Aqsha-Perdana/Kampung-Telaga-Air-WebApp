<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $footage360->judul }} - 360° View</title>
    
    <!-- Pannellum CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        
        #panorama {
            width: 100vw;
            height: 100vh;
        }
        
        .info-overlay {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 1000;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 12px;
            color: white;
            max-width: 400px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .info-overlay h1 {
            font-size: 1.5rem;
            margin: 0 0 10px 0;
            font-weight: 600;
        }
        
        .info-overlay p {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .controls-overlay {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            padding: 15px 25px;
            border-radius: 50px;
            display: flex;
            gap: 15px;
            align-items: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .control-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .control-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }
        
        .control-btn.active {
            background: rgba(59, 130, 246, 0.8);
        }
        
        .related-overlay {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            padding: 15px;
            border-radius: 12px;
            max-width: 300px;
            max-height: 70vh;
            overflow-y: auto;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .related-overlay h3 {
            color: white;
            font-size: 1rem;
            margin: 0 0 15px 0;
            font-weight: 600;
        }
        
        .related-item {
            display: block;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            text-decoration: none;
            color: white;
            transition: all 0.3s;
        }
        
        .related-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }
        
        .related-item img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 8px;
        }
        
        .related-item .title {
            font-size: 0.9rem;
            font-weight: 500;
            margin: 0;
        }
        
        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 999;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 50px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            transition: all 0.3s;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
        }
        
        .back-btn:hover {
            background: rgba(0, 0, 0, 0.85);
            transform: translateX(-3px);
        }
        
        /* Loading animation */
        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 999;
            text-align: center;
            color: white;
        }
        
        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-left-color: white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .info-overlay {
                max-width: calc(100vw - 40px);
                font-size: 0.85rem;
            }
            
            .info-overlay h1 {
                font-size: 1.2rem;
            }
            
            .related-overlay {
                display: none;
            }
            
            .controls-overlay {
                bottom: 10px;
                padding: 10px 15px;
                gap: 10px;
            }
            
            .control-btn {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
        }
        
        /* Hide info after 5 seconds */
        .info-overlay.fade-out {
            animation: fadeOut 0.5s forwards;
        }
        
        @keyframes fadeOut {
            to {
                opacity: 0;
                pointer-events: none;
            }
        }
    </style>
</head>
<body>
    <!-- Loading indicator -->
    <div class="loading" id="loading">
        <div class="spinner"></div>
        <p>Memuat panorama 360°...</p>
    </div>
    
    <!-- Back button -->
    <a href="{{ url()->previous() }}" class="back-btn">
        <i class="fas fa-arrow-left"></i>
        Kembali
    </a>
    
    <!-- Panorama container -->
    <div id="panorama"></div>
    
    <!-- Info overlay -->
    <div class="info-overlay" id="infoOverlay">
        <h1>{{ $footage360->judul }}</h1>
        <p class="mb-2">
            <i class="fas fa-map-marker-alt"></i> 
            {{ $footage360->destinasi->nama }}
        </p>
        @if($footage360->deskripsi)
            <p class="small">{{ $footage360->deskripsi }}</p>
        @endif
    </div>
    
    <!-- Controls overlay -->
    <div class="controls-overlay">
        <button class="control-btn" id="autoRotateBtn" title="Auto Rotate">
            <i class="fas fa-sync-alt"></i>
        </button>
        <button class="control-btn active" id="fullscreenBtn" title="Fullscreen">
            <i class="fas fa-expand"></i>
        </button>
        <button class="control-btn" id="resetBtn" title="Reset View">
            <i class="fas fa-redo"></i>
        </button>
        <button class="control-btn" id="infoBtn" title="Toggle Info">
            <i class="fas fa-info"></i>
        </button>
    </div>
    
    <!-- Related footages -->
    @if($relatedFootages->count() > 0)
    <div class="related-overlay">
        <h3><i class="fas fa-images"></i> Lokasi Lainnya</h3>
        @foreach($relatedFootages as $related)
            <a href="{{ route('view360.show', $related->id_footage360) }}" class="related-item">
                <img src="{{ $related->file_foto }}" alt="{{ $related->judul }}">
                <p class="title">{{ $related->judul }}</p>
            </a>
        @endforeach
    </div>
    @endif
    
    <!-- Pannellum JS -->
    <script src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
    
    <script>
        // Initialize Pannellum viewer
        const viewer = pannellum.viewer('panorama', {
            type: 'equirectangular',
            panorama: '{{ $footage360->file_foto }}',
            autoLoad: true,
            autoRotate: 0,
            compass: true,
            showZoomCtrl: true,
            mouseZoom: true,
            keyboardZoom: true,
            draggable: true,
            showFullscreenCtrl: false,
            showControls: false,
            hfov: 100,
            pitch: 0,
            yaw: 0,
            minHfov: 50,
            maxHfov: 120,
        });
        
        // Hide loading when ready
        viewer.on('load', function() {
            document.getElementById('loading').style.display = 'none';
        });
        
        // Auto-hide info after 5 seconds
        setTimeout(() => {
            document.getElementById('infoOverlay').classList.add('fade-out');
        }, 5000);
        
        // Control buttons
        let autoRotating = false;
        let infoVisible = true;
        
        // Auto rotate button
        document.getElementById('autoRotateBtn').addEventListener('click', function() {
            autoRotating = !autoRotating;
            if (autoRotating) {
                viewer.startAutoRotate(-2);
                this.classList.add('active');
            } else {
                viewer.stopAutoRotate();
                this.classList.remove('active');
            }
        });
        
        // Fullscreen button
        document.getElementById('fullscreenBtn').addEventListener('click', function() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
                this.querySelector('i').classList.replace('fa-expand', 'fa-compress');
            } else {
                document.exitFullscreen();
                this.querySelector('i').classList.replace('fa-compress', 'fa-expand');
            }
        });
        
        // Reset button
        document.getElementById('resetBtn').addEventListener('click', function() {
            viewer.setPitch(0);
            viewer.setYaw(0);
            viewer.setHfov(100);
        });
        
        // Info toggle button
        document.getElementById('infoBtn').addEventListener('click', function() {
            const infoOverlay = document.getElementById('infoOverlay');
            infoVisible = !infoVisible;
            if (infoVisible) {
                infoOverlay.style.display = 'block';
                infoOverlay.classList.remove('fade-out');
            } else {
                infoOverlay.style.display = 'none';
            }
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'f' || e.key === 'F') {
                document.getElementById('fullscreenBtn').click();
            }
            if (e.key === 'r' || e.key === 'R') {
                document.getElementById('resetBtn').click();
            }
            if (e.key === 'i' || e.key === 'I') {
                document.getElementById('infoBtn').click();
            }
            if (e.key === ' ') {
                e.preventDefault();
                document.getElementById('autoRotateBtn').click();
            }
        });
        
        // Handle fullscreen change
        document.addEventListener('fullscreenchange', function() {
            const btn = document.getElementById('fullscreenBtn');
            if (document.fullscreenElement) {
                btn.querySelector('i').classList.replace('fa-expand', 'fa-compress');
            } else {
                btn.querySelector('i').classList.replace('fa-compress', 'fa-expand');
            }
        });
    </script>
</body>
</html>
<?php
session_start();
require_once 'functions.php';

// Check if user is admin
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Load all data
$allUsers = loadUsers();
$allPhotos = loadPhotos();
$allStories = loadStories();
$locations = loadLocations();

// Filter untuk konten pending
$pendingPhotos = array_filter($allPhotos, fn($p) => isset($p['status']) && $p['status'] === 'pending');
$pendingStories = array_filter($allStories, fn($s) => isset($s['status']) && $s['status'] === 'pending');

// Process actions for content deletion
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'delete_photo':
                if(deleteContent('photo', $_POST['photo_id'])) {
                    echo json_encode(['success' => true, 'message' => 'Foto berhasil dihapus!']);
                    exit;
                }
                break;
                
            case 'delete_story':
                if(deleteContent('story', $_POST['story_id'])) {
                    echo json_encode(['success' => true, 'message' => 'Kenangan berhasil dihapus!']);
                    exit;
                }
                break;
                
            case 'approve_photo':
                if(isset($_POST['photo_id'])) {
                    $photos = loadPhotos();
                    foreach($photos as &$photo) {
                        if($photo['id'] === $_POST['photo_id']) {
                            $photo['status'] = 'approved';
                            break;
                        }
                    }
                    file_put_contents('config/photos.json', json_encode($photos, JSON_PRETTY_PRINT));
                    echo json_encode(['success' => true, 'message' => 'Foto disetujui!']);
                    exit;
                }
                break;
                
            case 'approve_story':
                if(isset($_POST['story_id'])) {
                    $stories = loadStories();
                    foreach($stories as &$story) {
                        if($story['id'] === $_POST['story_id']) {
                            $story['status'] = 'published';
                            break;
                        }
                    }
                    file_put_contents('config/stories.json', json_encode($stories, JSON_PRETTY_PRINT));
                    echo json_encode(['success' => true, 'message' => 'Kenangan disetujui!']);
                    exit;
                }
                break;
                
            case 'delete_location':
                if(isset($_POST['user_id'])) {
                    $locations = loadLocations();
                    $newLocations = array_filter($locations, fn($loc) => $loc['user_id'] !== $_POST['user_id']);
                    file_put_contents('config/locations.json', json_encode(array_values($newLocations), JSON_PRETTY_PRINT));
                    echo json_encode(['success' => true, 'message' => 'Lokasi user dihapus!']);
                    exit;
                }
                break;
        }
    }
}

// AJAX endpoint untuk mendapatkan live locations
if(isset($_GET['get_locations'])) {
    header('Content-Type: application/json');
    $locations = loadLocations();
    
    // Filter hanya yang aktif (update dalam 10 menit terakhir)
    $activeLocations = array_filter($locations, function($location) {
        return (time() - $location['last_update']) < 600; // 10 menit
    });
    
    // Update status online/offline
    $result = [];
    foreach($activeLocations as $location) {
        $isOnline = (time() - $location['last_update']) < 300; // 5 menit
        $location['status'] = $isOnline ? 'online' : 'offline';
        $location['time_ago'] = getTimeAgo($location['last_update']);
        $result[] = $location;
    }
    
    echo json_encode($result);
    exit;
}

// AJAX endpoint untuk mendapatkan semua data
if(isset($_GET['get_all_data'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'photos' => loadPhotos(),
        'stories' => loadStories(),
        'locations' => loadLocations(),
        'stats' => [
            'total_photos' => count($allPhotos),
            'total_stories' => count($allStories),
            'pending_photos' => count($pendingPhotos),
            'pending_stories' => count($pendingStories),
            'total_users' => count($allUsers),
            'online_users' => count(array_filter(loadLocations(), fn($loc) => (time() - $loc['last_update']) < 300))
        ]
    ]);
    exit;
}

// Function untuk format waktu
function getTimeAgo($timestamp) {
    $timeDiff = time() - $timestamp;
    
    if($timeDiff < 60) {
        return 'Baru saja';
    } elseif($timeDiff < 3600) {
        $minutes = floor($timeDiff / 60);
        return "$minutes menit yang lalu";
    } elseif($timeDiff < 86400) {
        $hours = floor($timeDiff / 3600);
        return "$hours jam yang lalu";
    } else {
        $days = floor($timeDiff / 86400);
        return "$days hari yang lalu";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Talaga Cinta</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Leaflet Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        :root {
            --primary: #0a4da2;
            --secondary: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --dark: #343a40;
            --light: #f8f9fa;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fb;
            overflow-x: hidden;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--dark), #1a1a2e);
            color: white;
            min-height: 100vh;
            position: fixed;
            width: 250px;
            box-shadow: 3px 0 15px rgba(0,0,0,0.1);
            z-index: 100;
        }
        
        .main-content {
            margin-left: 250px;
            transition: all 0.3s;
        }
        
        .admin-header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 30px;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        
        .stat-card {
            border-radius: 12px;
            padding: 20px;
            color: white;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card.primary { background: linear-gradient(135deg, var(--primary), #1e88e5); }
        .stat-card.success { background: linear-gradient(135deg, var(--secondary), #43a047); }
        .stat-card.warning { background: linear-gradient(135deg, var(--warning), #ff9800); }
        .stat-card.danger { background: linear-gradient(135deg, var(--danger), #d32f2f); }
        .stat-card.info { background: linear-gradient(135deg, var(--info), #0288d1); }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        
        .photo-card {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            background: white;
        }
        
        .photo-card:hover {
            transform: translateY(-5px);
        }
        
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .pending-badge {
            background: var(--warning);
            color: #000;
        }
        
        .approved-badge {
            background: var(--secondary);
            color: white;
        }
        
        #map {
            height: 500px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .live-indicator {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #dc3545;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .location-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            transition: background 0.3s;
        }
        
        .location-item:hover {
            background: #f8f9fa;
        }
        
        .online-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #28a745;
            display: inline-block;
            margin-right: 5px;
        }
        
        .offline-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #6c757d;
            display: inline-block;
            margin-right: 5px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }
            
            .sidebar .nav-text {
                display: none;
            }
            
            .main-content {
                margin-left: 70px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="p-4 text-center border-bottom border-dark">
            <div class="user-avatar mx-auto mb-3" style="width: 60px; height: 60px;">
                <i class="fas fa-crown"></i>
            </div>
            <h5 class="mb-1"><?php echo htmlspecialchars($_SESSION['fullname']); ?></h5>
            <small class="text-light">Administrator</small>
        </div>
        
        <nav class="nav flex-column p-3">
            <a class="nav-link text-light mb-2 active" href="#photos" data-section="photos">
                <i class="fas fa-images me-3"></i>
                <span class="nav-text">Foto User</span>
                <span class="badge bg-warning float-end" id="pendingPhotosBadge"><?php echo count($pendingPhotos); ?></span>
            </a>
            <a class="nav-link text-light mb-2" href="#stories" data-section="stories">
                <i class="fas fa-book me-3"></i>
                <span class="nav-text">Kenangan</span>
                <span class="badge bg-warning float-end" id="pendingStoriesBadge"><?php echo count($pendingStories); ?></span>
            </a>
            <a class="nav-link text-light mb-2" href="#locations" data-section="locations">
                <i class="fas fa-map-marker-alt me-3"></i>
                <span class="nav-text">Live Tracking</span>
                <span class="live-indicator float-end">LIVE</span>
            </a>
            <hr class="text-light mx-3 my-4">
            <a class="nav-link text-light mb-2" href="index.php">
                <i class="fas fa-home me-3"></i>
                <span class="nav-text">Kembali ke Site</span>
            </a>
            <a class="nav-link text-danger mb-2" href="logout.php">
                <i class="fas fa-sign-out-alt me-3"></i>
                <span class="nav-text">Logout</span>
            </a>
        </nav>
        
        <div class="p-3 text-center">
            <small class="text-light opacity-50">Admin Panel Noxipom12</small>
            <div class="mt-2">
                <small class="text-light opacity-50" id="lastUpdate">Loading...</small>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <div class="admin-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <button class="btn btn-outline-primary btn-sm me-3" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h4 class="mb-0 d-inline">Admin Dashboard - Talaga Cinta</h4>
                </div>
                <div>
                    <span class="text-muted me-3">
                        <i class="fas fa-calendar"></i> <?php echo date('d F Y'); ?>
                    </span>
                    <span class="text-muted">
                        <i class="fas fa-clock"></i> <span id="liveTime">00:00:00</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="container-fluid p-4">
            <!-- Photos Section -->
            <div id="photos" class="section-content">
                <h3 class="mb-4">Manajemen Foto User</h3>
                
                <div class="alert alert-warning" id="pendingPhotosAlert" style="<?php echo count($pendingPhotos) > 0 ? '' : 'display: none;' ?>">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Ada <span id="pendingPhotosCount"><?php echo count($pendingPhotos); ?></span> foto menunggu persetujuan.
                </div>
                
                <!-- Filter Tabs -->
                <ul class="nav nav-tabs mb-4" id="photoTabs">
                    <li class="nav-item">
                        <button class="nav-link active" data-filter="all">Semua Foto</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-filter="pending">Pending</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-filter="approved">Disetujui</button>
                    </li>
                </ul>
                
                <!-- Photos Grid -->
                <div class="row" id="photosGrid">
                    <!-- Photos will be loaded via JavaScript -->
                    <div class="col-12 text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat foto...</p>
                    </div>
                </div>
            </div>

            <!-- Stories Section -->
            <div id="stories" class="section-content" style="display: none;">
                <h3 class="mb-4">Manajemen Kenangan</h3>
                
                <div class="alert alert-warning" id="pendingStoriesAlert" style="<?php echo count($pendingStories) > 0 ? '' : 'display: none;' ?>">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Ada <span id="pendingStoriesCount"><?php echo count($pendingStories); ?></span> kenangan menunggu persetujuan.
                </div>
                
                <!-- Stories Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Penulis</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="storiesTable">
                            <!-- Stories will be loaded via JavaScript -->
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    Memuat kenangan...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Live Tracking Section -->
            <div id="locations" class="section-content" style="display: none;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Live Location Tracking</h3>
                    <div>
                        <span class="live-indicator me-3">
                            <i class="fas fa-circle"></i> REAL-TIME
                        </span>
                        <button class="btn btn-primary btn-sm" onclick="refreshMapData()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                Live Map
                                <small class="text-muted ms-2" id="mapStatus">Memuat...</small>
                            </div>
                            <div class="card-body p-0">
                                <div id="map"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                User Online (<span id="onlineUsersCount">0</span>)
                                <small class="text-muted ms-2">Update setiap 30 detik</small>
                            </div>
                            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                <div id="locationsList">
                                    <!-- Locations will be loaded via JavaScript -->
                                    <div class="text-center py-3">
                                        <div class="spinner-border spinner-border-sm" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2">Memuat data lokasi...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Photo Details Modal -->
    <div class="modal fade" id="photoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <img id="modalPhotoImage" src="" class="img-fluid rounded" alt="">
                        </div>
                        <div class="col-md-6">
                            <h4 id="modalPhotoTitle"></h4>
                            <p id="modalPhotoDescription" class="text-muted"></p>
                            
                            <div class="mb-3">
                                <strong>Diunggah oleh:</strong>
                                <div class="d-flex align-items-center mt-2">
                                    <div class="user-avatar me-2">
                                        <span id="modalPhotoUserInitial"></span>
                                    </div>
                                    <div>
                                        <div id="modalPhotoFullname"></div>
                                        <small class="text-muted" id="modalPhotoUsername"></small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <strong>Rating:</strong>
                                    <div id="modalPhotoRating" class="mt-1"></div>
                                </div>
                                <div class="col-6">
                                    <strong>Tanggal:</strong>
                                    <div id="modalPhotoDate" class="mt-1"></div>
                                </div>
                            </div>
                            
                            <div id="modalPhotoLocation" class="mb-3"></div>
                            
                            <div class="d-flex gap-2 mt-4">
                                <button class="btn btn-success flex-grow-1" id="modalApproveBtn">
                                    <i class="fas fa-check"></i> Setujui
                                </button>
                                <button class="btn btn-danger flex-grow-1" id="modalDeleteBtn">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Story Details Modal -->
    <div class="modal fade" id="storyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Kenangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h3 id="modalStoryTitle"></h3>
                    
                    <div class="d-flex align-items-center mb-4">
                        <div class="user-avatar me-3">
                            <span id="modalStoryUserInitial"></span>
                        </div>
                        <div>
                            <div id="modalStoryFullname"></div>
                            <small class="text-muted" id="modalStoryUsername"></small>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <p id="modalStoryContent" class="lead"></p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <strong>Dibuat:</strong>
                            <div id="modalStoryDate" class="text-muted"></div>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <span id="modalStoryStatus" class="badge"></span>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-success flex-grow-1" id="modalApproveStoryBtn">
                            <i class="fas fa-check"></i> Setujui
                        </button>
                        <button class="btn btn-danger flex-grow-1" id="modalDeleteStoryBtn">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Global variables
        let map;
        let markers = [];
        let currentPhotoId = null;
        let currentStoryId = null;
        let refreshInterval;
        let mapInitialized = false;
        
        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Admin Dashboard loaded');
            
            // Initialize navigation
            initNavigation();
            
            // Initialize real-time clock
            updateLiveTime();
            setInterval(updateLiveTime, 1000);
            
            // Load initial data
            loadAllData();
            
            // Start auto-refresh every 30 seconds
            refreshInterval = setInterval(loadAllData, 30000);
            
            // Initialize modals
            initModals();
            
            // Show photos section by default
            showSection('photos');
        });
        
        // Navigation
        function initNavigation() {
            // Navigation links
            document.querySelectorAll('[data-section]').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const sectionId = this.getAttribute('data-section');
                    showSection(sectionId);
                    
                    // Update active state
                    document.querySelectorAll('.nav-link').forEach(nav => {
                        nav.classList.remove('active');
                    });
                    this.classList.add('active');
                    
                    // Initialize section specific features
                    if(sectionId === 'locations' && !mapInitialized) {
                        setTimeout(initMap, 100);
                    }
                });
            });
            
            // Sidebar toggle (mobile)
            document.getElementById('sidebarToggle').addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                const isCollapsed = sidebar.style.width === '70px' || sidebar.style.width === '70px';
                
                if(window.innerWidth <= 768) {
                    if(sidebar.style.display === 'none' || !sidebar.style.display) {
                        sidebar.style.display = 'block';
                    } else {
                        sidebar.style.display = 'none';
                    }
                } else {
                    if(isCollapsed) {
                        sidebar.style.width = '250px';
                        document.querySelectorAll('.nav-text').forEach(el => {
                            el.style.display = 'inline';
                        });
                    } else {
                        sidebar.style.width = '70px';
                        document.querySelectorAll('.nav-text').forEach(el => {
                            el.style.display = 'none';
                        });
                    }
                }
            });
            
            // Photo filter tabs
            document.querySelectorAll('#photoTabs button').forEach(tab => {
                tab.addEventListener('click', function() {
                    const filter = this.getAttribute('data-filter');
                    
                    // Update active tab
                    document.querySelectorAll('#photoTabs button').forEach(t => {
                        t.classList.remove('active');
                    });
                    this.classList.add('active');
                    
                    // Filter photos
                    filterPhotos(filter);
                });
            });
        }
        
        // Show section
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.section-content').forEach(section => {
                section.style.display = 'none';
            });
            
            // Show selected section
            const targetSection = document.getElementById(sectionId);
            if(targetSection) {
                targetSection.style.display = 'block';
                console.log('Showing section:', sectionId);
            }
        }
        
        // Live time update
        function updateLiveTime() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { 
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('liveTime').textContent = timeStr;
        }
        
        // Load all data via AJAX
        function loadAllData() {
            fetch('?get_all_data=1')
                .then(response => response.json())
                .then(data => {
                    console.log('Data loaded:', data);
                    
                    // Update badges
                    document.getElementById('pendingPhotosBadge').textContent = data.stats.pending_photos;
                    document.getElementById('pendingStoriesBadge').textContent = data.stats.pending_stories;
                    
                    // Update alerts
                    const pendingPhotosAlert = document.getElementById('pendingPhotosAlert');
                    const pendingStoriesAlert = document.getElementById('pendingStoriesAlert');
                    
                    if(data.stats.pending_photos > 0) {
                        pendingPhotosAlert.style.display = 'block';
                        document.getElementById('pendingPhotosCount').textContent = data.stats.pending_photos;
                    } else {
                        pendingPhotosAlert.style.display = 'none';
                    }
                    
                    if(data.stats.pending_stories > 0) {
                        pendingStoriesAlert.style.display = 'block';
                        document.getElementById('pendingStoriesCount').textContent = data.stats.pending_stories;
                    } else {
                        pendingStoriesAlert.style.display = 'none';
                    }
                    
                    // Load photos
                    loadPhotosData(data.photos);
                    
                    // Load stories
                    loadStoriesData(data.stories);
                    
                    // Load locations if on that section
                    if(document.getElementById('locations').style.display === 'block') {
                        loadLocationsData(data.locations);
                    }
                    
                    // Update last update time
                    const now = new Date();
                    document.getElementById('lastUpdate').textContent = 
                        `Update: ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}:${now.getSeconds().toString().padStart(2, '0')}`;
                })
                .catch(error => {
                    console.error('Error loading data:', error);
                });
        }
        
        // Load photos data
        function loadPhotosData(photos) {
            const photosGrid = document.getElementById('photosGrid');
            
            if(photos.length === 0) {
                photosGrid.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-images fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Belum ada foto yang diupload</h5>
                    </div>
                `;
                return;
            }
            
            let html = '';
            photos.forEach(photo => {
                const isPending = photo.status === 'pending';
                const imagePath = photo.image.includes('uploads/') ? photo.image : 'uploads/' + photo.image;
                const date = new Date(photo.created_at * 1000);
                
                html += `
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4 photo-item" 
                         data-id="${photo.id}" 
                         data-status="${isPending ? 'pending' : 'approved'}">
                        <div class="photo-card">
                            <img src="${imagePath}" 
                                 class="card-img-top" 
                                 alt="${photo.title}"
                                 style="height: 200px; object-fit: cover;"
                                 onerror="this.src='https://via.placeholder.com/400x300?text=No+Image'">
                            
                            <span class="status-badge ${isPending ? 'pending-badge' : 'approved-badge'}">
                                ${isPending ? 'PENDING' : 'APPROVED'}
                            </span>
                            
                            <div class="p-3">
                                <h6 class="card-title mb-2">${photo.title}</h6>
                                <p class="card-text small text-muted mb-3">
                                    ${photo.description.substring(0, 100)}...
                                </p>
                                
                                <div class="d-flex align-items-center mb-3">
                                    <div class="user-avatar me-2">
                                        ${photo.username.charAt(0).toUpperCase()}
                                    </div>
                                    <div>
                                        <small class="d-block">${photo.fullname}</small>
                                        <small class="text-muted">@${photo.username}</small>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        ${date.toLocaleDateString('id-ID')}
                                    </small>
                                    <div class="rating">
                                        ${getStarRating(photo.rating || 0)}
                                    </div>
                                </div>
                                
                                <div class="btn-group w-100 mt-3">
                                    <button class="btn btn-info btn-sm view-photo" 
                                            data-photo='${JSON.stringify(photo)}'>
                                        <i class="fas fa-eye"></i> Lihat
                                    </button>
                                    ${isPending ? `
                                    <button class="btn btn-success btn-sm approve-photo" 
                                            data-id="${photo.id}">
                                        <i class="fas fa-check"></i> Setujui
                                    </button>
                                    ` : ''}
                                    <button class="btn btn-danger btn-sm delete-photo" 
                                            data-id="${photo.id}"
                                            data-title="${photo.title}">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            photosGrid.innerHTML = html;
            
            // Add event listeners to photo buttons
            document.querySelectorAll('.view-photo').forEach(btn => {
                btn.addEventListener('click', function() {
                    const photo = JSON.parse(this.getAttribute('data-photo'));
                    showPhotoModal(photo);
                });
            });
            
            document.querySelectorAll('.approve-photo').forEach(btn => {
                btn.addEventListener('click', function() {
                    const photoId = this.getAttribute('data-id');
                    approvePhoto(photoId);
                });
            });
            
            document.querySelectorAll('.delete-photo').forEach(btn => {
                btn.addEventListener('click', function() {
                    const photoId = this.getAttribute('data-id');
                    const title = this.getAttribute('data-title');
                    deletePhoto(photoId, title);
                });
            });
            
            // Apply current filter
            const activeTab = document.querySelector('#photoTabs .active');
            if(activeTab) {
                filterPhotos(activeTab.getAttribute('data-filter'));
            }
        }
        
        // Filter photos
        function filterPhotos(filter) {
            document.querySelectorAll('.photo-item').forEach(item => {
                if(filter === 'all') {
                    item.style.display = 'block';
                } else {
                    if(item.getAttribute('data-status') === filter) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                }
            });
        }
        
        // Load stories data
        function loadStoriesData(stories) {
            const storiesTable = document.getElementById('storiesTable');
            
            if(stories.length === 0) {
                storiesTable.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="fas fa-book fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada kenangan yang ditulis</h5>
                        </td>
                    </tr>
                `;
                return;
            }
            
            let html = '';
            stories.forEach(story => {
                const isPending = story.status === 'pending';
                const date = new Date(story.created_at * 1000);
                
                html += `
                    <tr class="story-item" data-id="${story.id}" data-status="${isPending ? 'pending' : 'published'}">
                        <td>
                            <strong>${story.title}</strong><br>
                            <small class="text-muted">${story.content.substring(0, 100)}...</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-2">
                                    ${story.username.charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <small class="d-block">${story.fullname}</small>
                                    <small class="text-muted">@${story.username}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge ${isPending ? 'bg-warning' : 'bg-success'}">
                                ${isPending ? 'PENDING' : 'PUBLISHED'}
                            </span>
                        </td>
                        <td>
                            <small>
                                ${date.toLocaleDateString('id-ID')}<br>
                                <span class="text-muted">${date.toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})}</span>
                            </small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-info view-story" 
                                        data-story='${JSON.stringify(story)}'>
                                    <i class="fas fa-eye"></i>
                                </button>
                                ${isPending ? `
                                <button class="btn btn-success approve-story" 
                                        data-id="${story.id}">
                                    <i class="fas fa-check"></i>
                                </button>
                                ` : ''}
                                <button class="btn btn-danger delete-story" 
                                        data-id="${story.id}"
                                        data-title="${story.title}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            storiesTable.innerHTML = html;
            
            // Add event listeners to story buttons
            document.querySelectorAll('.view-story').forEach(btn => {
                btn.addEventListener('click', function() {
                    const story = JSON.parse(this.getAttribute('data-story'));
                    showStoryModal(story);
                });
            });
            
            document.querySelectorAll('.approve-story').forEach(btn => {
                btn.addEventListener('click', function() {
                    const storyId = this.getAttribute('data-id');
                    approveStory(storyId);
                });
            });
            
            document.querySelectorAll('.delete-story').forEach(btn => {
                btn.addEventListener('click', function() {
                    const storyId = this.getAttribute('data-id');
                    const title = this.getAttribute('data-title');
                    deleteStory(storyId, title);
                });
            });
        }
        
        // Load locations data
        function loadLocationsData(locations) {
            const locationsList = document.getElementById('locationsList');
            
            // Filter active locations (within 10 minutes)
            const activeLocations = locations.filter(location => {
                return (Date.now() / 1000 - location.last_update) < 600;
            });
            
            // Update online users count
            const onlineUsers = activeLocations.filter(loc => (Date.now() / 1000 - loc.last_update) < 300);
            document.getElementById('onlineUsersCount').textContent = onlineUsers.length;
            
            if(activeLocations.length === 0) {
                locationsList.innerHTML = `
                    <div class="text-center py-3">
                        <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada user online</h5>
                        <p class="small text-muted">User akan muncul saat mengizinkan lokasi</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            activeLocations.forEach(location => {
                const isOnline = (Date.now() / 1000 - location.last_update) < 300;
                const lastUpdate = new Date(location.last_update * 1000);
                const timeDiff = Math.floor((Date.now() / 1000 - location.last_update) / 60);
                
                html += `
                    <div class="location-item" data-user-id="${location.user_id}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="${isOnline ? 'online-dot' : 'offline-dot'}"></span>
                                <strong>${location.username}</strong>
                                <div class="small text-muted">${location.fullname}</div>
                            </div>
                            <div class="text-end">
                                <span class="badge ${isOnline ? 'bg-success' : 'bg-secondary'}">
                                    ${isOnline ? 'Online' : 'Offline'}
                                </span>
                                <br>
                                <small class="text-muted">
                                    ${timeDiff < 1 ? 'Baru saja' : `${timeDiff} menit lalu`}
                                </small>
                            </div>
                        </div>
                        
                        ${location.lat !== 0 && location.lng !== 0 ? `
                        <div class="mt-2">
                            <small class="text-muted d-block">
                                <i class="fas fa-map-marker-alt"></i> Lokasi:
                            </small>
                            <small>
                                ${location.city || `${location.lat.toFixed(4)}, ${location.lng.toFixed(4)}`}
                            </small>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted">
                                Akurasi: 
                                <span class="${location.accuracy < 50 ? 'text-success' : (location.accuracy < 100 ? 'text-warning' : 'text-danger')}">
                                    ${Math.round(location.accuracy)}m
                                </span>
                            </small>
                            <div>
                                <button class="btn btn-sm btn-outline-primary view-on-map" 
                                        data-lat="${location.lat}" 
                                        data-lng="${location.lng}"
                                        data-user="${location.username}">
                                    <i class="fas fa-map"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-location" 
                                        data-user-id="${location.user_id}"
                                        data-username="${location.username}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        ` : `
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-exclamation-circle"></i> Tidak ada data lokasi
                            </small>
                        </div>
                        `}
                    </div>
                `;
            });
            
            locationsList.innerHTML = html;
            
            // Add event listeners to location buttons
            document.querySelectorAll('.view-on-map').forEach(btn => {
                btn.addEventListener('click', function() {
                    const lat = parseFloat(this.getAttribute('data-lat'));
                    const lng = parseFloat(this.getAttribute('data-lng'));
                    const username = this.getAttribute('data-user');
                    
                    centerOnUser(lat, lng, username);
                });
            });
            
            document.querySelectorAll('.delete-location').forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = this.getAttribute('data-user-id');
                    const username = this.getAttribute('data-username');
                    
                    deleteUserLocation(userId, username);
                });
            });
            
            // Update map if initialized
            if(mapInitialized) {
                updateMapMarkers(activeLocations);
            }
        }
        
        // Initialize map
        function initMap() {
            if(map) {
                map.remove();
            }
            
            // Default center (Indonesia)
            map = L.map('map').setView([-2.5489, 118.0149], 5);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);
            
            mapInitialized = true;
            document.getElementById('mapStatus').textContent = 'Map siap';
            
            // Load initial locations
            fetchLocationsForMap();
        }
        
        // Fetch locations for map
        function fetchLocationsForMap() {
            fetch('?get_locations=1')
                .then(response => response.json())
                .then(locations => {
                    updateMapMarkers(locations);
                })
                .catch(error => {
                    console.error('Error fetching locations:', error);
                    document.getElementById('mapStatus').textContent = 'Error memuat data';
                });
        }
        
        // Update map markers
        function updateMapMarkers(locations) {
            // Clear existing markers
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];
            
            // Add new markers
            locations.forEach(location => {
                if(location.lat !== 0 && location.lng !== 0) {
                    const isOnline = (Date.now() / 1000 - location.last_update) < 300;
                    
                    const marker = L.marker([location.lat, location.lng])
                        .addTo(map)
                        .bindPopup(`
                            <b>${location.username}</b><br>
                            ${location.fullname}<br>
                            ${location.city || 'Lokasi tidak diketahui'}<br>
                            Status: ${isOnline ? '<span style="color:green">Online</span>' : '<span style="color:gray">Offline</span>'}<br>
                            Update: ${new Date(location.last_update * 1000).toLocaleTimeString('id-ID')}
                        `);
                    
                    markers.push(marker);
                }
            });
            
            // Fit bounds to show all markers
            if(markers.length > 0) {
                const group = new L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.1));
            }
            
            document.getElementById('mapStatus').textContent = `${locations.length} user ditampilkan`;
        }
        
        // Center map on user
        function centerOnUser(lat, lng, username) {
            if(!mapInitialized) {
                initMap();
            }
            
            map.setView([lat, lng], 15);
            
            // Highlight the marker
            markers.forEach(marker => {
                const markerLatLng = marker.getLatLng();
                if(markerLatLng.lat === lat && markerLatLng.lng === lng) {
                    marker.openPopup();
                }
            });
        }
        
        // Refresh map data
        function refreshMapData() {
            if(!mapInitialized) {
                initMap();
            }
            
            document.getElementById('mapStatus').textContent = 'Memuat data...';
            fetchLocationsForMap();
            loadAllData();
        }
        
        // Initialize modals
        function initModals() {
            // Photo modal buttons
            document.getElementById('modalApproveBtn')?.addEventListener('click', function() {
                if(currentPhotoId) {
                    approvePhoto(currentPhotoId);
                    bootstrap.Modal.getInstance(document.getElementById('photoModal')).hide();
                }
            });
            
            document.getElementById('modalDeleteBtn')?.addEventListener('click', function() {
                if(currentPhotoId) {
                    const title = document.getElementById('modalPhotoTitle').textContent;
                    deletePhoto(currentPhotoId, title);
                    bootstrap.Modal.getInstance(document.getElementById('photoModal')).hide();
                }
            });
            
            // Story modal buttons
            document.getElementById('modalApproveStoryBtn')?.addEventListener('click', function() {
                if(currentStoryId) {
                    approveStory(currentStoryId);
                    bootstrap.Modal.getInstance(document.getElementById('storyModal')).hide();
                }
            });
            
            document.getElementById('modalDeleteStoryBtn')?.addEventListener('click', function() {
                if(currentStoryId) {
                    const title = document.getElementById('modalStoryTitle').textContent;
                    deleteStory(currentStoryId, title);
                    bootstrap.Modal.getInstance(document.getElementById('storyModal')).hide();
                }
            });
        }
        
        // Show photo modal
        function showPhotoModal(photo) {
            currentPhotoId = photo.id;
            
            // Set modal content
            const imagePath = photo.image.includes('uploads/') ? photo.image : 'uploads/' + photo.image;
            document.getElementById('modalPhotoImage').src = imagePath;
            document.getElementById('modalPhotoTitle').textContent = photo.title;
            document.getElementById('modalPhotoDescription').textContent = photo.description;
            
            // User info
            document.getElementById('modalPhotoUserInitial').textContent = photo.username.charAt(0).toUpperCase();
            document.getElementById('modalPhotoFullname').textContent = photo.fullname;
            document.getElementById('modalPhotoUsername').textContent = '@' + photo.username;
            
            // Rating
            document.getElementById('modalPhotoRating').innerHTML = getStarRating(photo.rating || 0);
            
            // Date
            const date = new Date(photo.created_at * 1000);
            document.getElementById('modalPhotoDate').textContent = 
                date.toLocaleDateString('id-ID') + ' ' + date.toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});
            
            // Location
            const locationDiv = document.getElementById('modalPhotoLocation');
            if(photo.lat && photo.lat != 0) {
                locationDiv.innerHTML = `
                    <strong>Lokasi:</strong><br>
                    <small class="text-muted">
                        ${photo.lat}, ${photo.lng}<br>
                        ${photo.city || 'Tidak ada info kota'}
                    </small>
                `;
            } else {
                locationDiv.innerHTML = '<strong>Lokasi:</strong> Tidak ada data lokasi';
            }
            
            // Show/hide approve button
            const approveBtn = document.getElementById('modalApproveBtn');
            approveBtn.style.display = photo.status === 'pending' ? 'block' : 'none';
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('photoModal'));
            modal.show();
        }
        
        // Show story modal
        function showStoryModal(story) {
            currentStoryId = story.id;
            
            // Set modal content
            document.getElementById('modalStoryTitle').textContent = story.title;
            document.getElementById('modalStoryContent').textContent = story.content;
            
            // User info
            document.getElementById('modalStoryUserInitial').textContent = story.username.charAt(0).toUpperCase();
            document.getElementById('modalStoryFullname').textContent = story.fullname;
            document.getElementById('modalStoryUsername').textContent = '@' + story.username;
            
            // Date
            const date = new Date(story.created_at * 1000);
            document.getElementById('modalStoryDate').textContent = 
                date.toLocaleDateString('id-ID') + ' ' + date.toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});
            
            // Status
            const statusBadge = document.getElementById('modalStoryStatus');
            statusBadge.textContent = story.status === 'pending' ? 'PENDING' : 'PUBLISHED';
            statusBadge.className = 'badge ' + (story.status === 'pending' ? 'bg-warning' : 'bg-success');
            
            // Show/hide approve button
            const approveBtn = document.getElementById('modalApproveStoryBtn');
            approveBtn.style.display = story.status === 'pending' ? 'block' : 'none';
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('storyModal'));
            modal.show();
        }
        
        // Approve photo
        function approvePhoto(photoId) {
            if(!confirm('Setujui foto ini?')) return;
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=approve_photo&photo_id=${photoId}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert(data.message);
                    loadAllData(); // Refresh data
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error menyetujui foto');
            });
        }
        
        // Delete photo
        function deletePhoto(photoId, title) {
            if(!confirm(`Hapus foto "${title}"?`)) return;
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete_photo&photo_id=${photoId}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert(data.message);
                    loadAllData(); // Refresh data
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error menghapus foto');
            });
        }
        
        // Approve story
        function approveStory(storyId) {
            if(!confirm('Setujui kenangan ini?')) return;
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=approve_story&story_id=${storyId}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert(data.message);
                    loadAllData(); // Refresh data
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error menyetujui kenangan');
            });
        }
        
        // Delete story
        function deleteStory(storyId, title) {
            if(!confirm(`Hapus kenangan "${title}"?`)) return;
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete_story&story_id=${storyId}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert(data.message);
                    loadAllData(); // Refresh data
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error menghapus kenangan');
            });
        }
        
        // Delete user location
        function deleteUserLocation(userId, username) {
            if(!confirm(`Hapus data lokasi user "${username}"?`)) return;
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete_location&user_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert(data.message);
                    loadAllData(); // Refresh data
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error menghapus lokasi');
            });
        }
        
        // Helper function for star rating
        function getStarRating(rating) {
            let stars = '';
            for(let i = 1; i <= 5; i++) {
                stars += `<i class="fas fa-star ${i <= rating ? 'text-warning' : 'text-secondary'}" style="font-size: 0.8rem;"></i>`;
            }
            return stars;
        }
    </script>
</body>
</html>
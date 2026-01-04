<?php
session_start();
require_once 'functions.php';

// Cek jika sudah login
if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];
$fullname = $_SESSION['fullname'];
$email = $_SESSION['email'];

// Load user data
function getUserData($userId) {
    $users = loadUsers();
    foreach($users as $user) {
        if($user['id'] === $userId) {
            return $user;
        }
    }
    return null;
}

$userData = getUserData($userId);
$userPosts = getUserPosts($userId);

// Default avatar jika tidak ada
$avatarUrl = $userData['avatar'] ?? '';
if(empty($avatarUrl) || !file_exists($avatarUrl)) {
    $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($fullname) . '&background=0a4da2&color=fff&size=150';
}

// Get user likes
$userLikes = getUserLikes($userId);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Talaga Cinta</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Cropper.js for image cropping -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <style>
        :root {
            --primary: #0a4da2;
            --secondary: #28a745;
            --accent: #ff6b35;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .profile-container {
            padding-top: 100px;
            padding-bottom: 50px;
        }
        
        .profile-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary), #1e88e5);
            color: white;
            padding: 50px 30px 30px;
            text-align: center;
            position: relative;
        }
        
        .avatar-container {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            object-fit: cover;
            background: white;
        }
        
        .avatar-overlay {
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--accent);
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 3px solid white;
        }
        
        .avatar-overlay:hover {
            background: #ff8535;
            transform: scale(1.1);
        }
        
        .user-info {
            margin-top: 15px;
        }
        
        .user-name {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .user-username {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 10px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-top: 30px;
        }
        
        .stat-item {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            display: block;
            color: white;
        }
        
        .stat-label {
            font-size: 0.85rem;
            opacity: 0.9;
        }
        
        .profile-body {
            padding: 40px;
        }
        
        .nav-tabs {
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 30px;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
            padding: 12px 25px;
            border-radius: 10px 10px 0 0;
            transition: all 0.3s ease;
        }
        
        .nav-tabs .nav-link:hover {
            color: var(--primary);
            background: rgba(10, 77, 162, 0.05);
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary);
            border-bottom: 3px solid var(--primary);
            background: none;
        }
        
        .tab-content {
            min-height: 300px;
        }
        
        .form-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            border-left: 4px solid var(--primary);
        }
        
        .form-section h5 {
            color: var(--primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(10, 77, 162, 0.1);
        }
        
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .photo-item {
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            height: 200px;
            transition: transform 0.3s ease;
        }
        
        .photo-item:hover {
            transform: translateY(-5px);
        }
        
        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .photo-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            color: white;
            padding: 15px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .photo-item:hover .photo-overlay {
            opacity: 1;
        }
        
        .story-item {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid var(--primary);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .info-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: #666;
        }
        
        .btn-save {
            background: linear-gradient(135deg, var(--primary), #1e88e5);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(10, 77, 162, 0.3);
        }
        
        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
            border-radius: 10px;
            padding: 10px 20px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            z-index: 1000;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .back-btn:hover {
            background: var(--primary);
            color: white;
        }
        
        /* Avatar Modal Styles */
        .avatar-modal .modal-dialog {
            max-width: 500px;
        }
        
        .image-cropper-container {
            width: 100%;
            height: 300px;
            background: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        #imagePreview {
            max-width: 100%;
            max-height: 300px;
        }
        
        .cropper-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto 20px;
            border: 3px solid var(--primary);
        }
        
        .avatar-options {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 20px;
        }
        
        .avatar-option {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .avatar-option:hover, .avatar-option.selected {
            border-color: var(--primary);
            transform: scale(1.1);
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .photo-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .profile-header {
                padding: 40px 20px 20px;
            }
            
            .profile-body {
                padding: 25px;
            }
            
            .back-btn {
                top: 10px;
                left: 10px;
                padding: 8px 15px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Back Button -->
    <a href="index.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>

    <div class="container profile-container">
        <!-- Profile Header -->
        <div class="profile-card">
            <div class="profile-header">
                <div class="avatar-container">
                    <img src="<?php echo $avatarUrl; ?>" class="profile-avatar" alt="<?php echo htmlspecialchars($fullname); ?>" 
                         id="currentAvatar">
                    
                    <div class="avatar-overlay" data-bs-toggle="modal" data-bs-target="#avatarModal">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
                
                <div class="user-info">
                    <h1 class="user-name"><?php echo htmlspecialchars($fullname); ?></h1>
                    <div class="user-username">@<?php echo htmlspecialchars($username); ?></div>
                    <div class="user-email text-light opacity-75">
                        <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($email); ?>
                    </div>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $userPosts['total']; ?></span>
                        <span class="stat-label">Total Post</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count($userPosts['photos']); ?></span>
                        <span class="stat-label">Foto</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count($userPosts['stories']); ?></span>
                        <span class="stat-label">Kenangan</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $userLikes; ?></span>
                        <span class="stat-label">Likes</span>
                    </div>
                </div>
            </div>
            
            <div class="profile-body">
                <!-- Tabs -->
                <ul class="nav nav-tabs" id="profileTabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#edit">
                            <i class="fas fa-edit me-2"></i>Edit Profil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#photos">
                            <i class="fas fa-images me-2"></i>Foto Saya
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#stories">
                            <i class="fas fa-book me-2"></i>Kenangan Saya
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#security">
                            <i class="fas fa-shield-alt me-2"></i>Keamanan
                        </a>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <div class="tab-content" id="profileTabContent">
                    <!-- Edit Profile Tab -->
                    <div class="tab-pane fade show active" id="edit">
                        <!-- Personal Information Form -->
                        <div class="form-section">
                            <h5><i class="fas fa-user-circle"></i> Informasi Pribadi</h5>
                            <form method="POST" id="profileForm">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" name="fullname" 
                                               value="<?php echo htmlspecialchars($fullname); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($username); ?>" disabled>
                                        <small class="text-muted">Username tidak dapat diubah</small>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" 
                                               value="<?php echo htmlspecialchars($email); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">No. Telepon</label>
                                        <input type="tel" class="form-control" name="phone" 
                                               value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>"
                                               placeholder="08xxxxxxxxxx">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Bio / Deskripsi Diri</label>
                                    <textarea class="form-control" name="bio" rows="3" 
                                              placeholder="Ceritakan sedikit tentang diri Anda..."><?php echo htmlspecialchars($userData['bio'] ?? ''); ?></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-save">
                                    <i class="fas fa-save me-2"></i> Simpan Perubahan
                                </button>
                            </form>
                        </div>
                        
                        <!-- Account Information -->
                        <div class="form-section">
                            <h5><i class="fas fa-info-circle"></i> Informasi Akun</h5>
                            <div class="info-item">
                                <div class="info-label">Tanggal Bergabung</div>
                                <div class="info-value">
                                    <?php echo $userData ? date('d F Y', $userData['created_at']) : '-'; ?>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Terakhir Login</div>
                                <div class="info-value">
                                    <?php echo $userData && $userData['last_login'] ? 
                                        date('d F Y H:i', $userData['last_login']) : 'Belum login'; ?>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Status Akun</div>
                                <div class="info-value">
                                    <span class="badge bg-success">Aktif</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Photos Tab -->
                    <div class="tab-pane fade" id="photos">
                        <?php if(empty($userPosts['photos'])): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-images fa-3x text-muted mb-3"></i>
                                <h5>Belum ada foto</h5>
                                <p class="text-muted">Upload foto pertama Anda!</p>
                                <a href="upload.php" class="btn btn-primary">
                                    <i class="fas fa-upload me-2"></i> Upload Foto
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="photo-grid">
                                <?php foreach($userPosts['photos'] as $photo): ?>
                                <div class="photo-item">
                                    <img src="<?php echo $photo['image']; ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>">
                                    <div class="photo-overlay">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($photo['title']); ?></h6>
                                        <small><?php echo date('d M Y', $photo['created_at']); ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Stories Tab -->
                    <div class="tab-pane fade" id="stories">
                        <?php if(empty($userPosts['stories'])): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                <h5>Belum ada kenangan</h5>
                                <p class="text-muted">Bagikan pengalaman Anda!</p>
                                <a href="stories.php" class="btn btn-primary">
                                    <i class="fas fa-pen me-2"></i> Tulis Kenangan
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach($userPosts['stories'] as $story): ?>
                            <div class="story-item">
                                <h5><?php echo htmlspecialchars($story['title']); ?></h5>
                                <p class="text-muted mb-2"><?php echo substr(htmlspecialchars($story['content']), 0, 150); ?>...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="far fa-clock me-1"></i> <?php echo date('d M Y', $story['created_at']); ?>
                                    </small>
                                    <div class="rating">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $story['rating'] ? 'text-warning' : 'text-secondary'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Security Tab -->
                    <div class="tab-pane fade" id="security">
                        <div class="form-section">
                            <h5><i class="fas fa-key"></i> Ganti Password</h5>
                            <form method="POST" id="passwordForm">
                                <input type="hidden" name="action" value="update_password">
                                
                                <div class="mb-3">
                                    <label class="form-label">Password Saat Ini</label>
                                    <input type="password" class="form-control" name="current_password" required>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Password Baru</label>
                                        <input type="password" class="form-control" name="new_password" required minlength="6">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Konfirmasi Password Baru</label>
                                        <input type="password" class="form-control" name="confirm_password" required minlength="6">
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-save">
                                    <i class="fas fa-key me-2"></i> Ganti Password
                                </button>
                            </form>
                        </div>
                        
                        <div class="form-section">
                            <h5><i class="fas fa-trash-alt"></i> Hapus Akun</h5>
                            <p class="text-muted mb-3">
                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                Hati-hati! Menghapus akun akan menghapus semua data Anda secara permanen.
                            </p>
                            <form method="POST" id="deleteAccountForm" onsubmit="return confirmDeleteAccount()">
                                <input type="hidden" name="action" value="delete_account">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash me-2"></i> Hapus Akun Saya
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Avatar Upload Modal -->
    <div class="modal fade avatar-modal" id="avatarModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-circle"></i> Ubah Avatar</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="cropper-preview" id="cropperPreview"></div>
                    </div>
                    
                    <!-- Upload Option -->
                    <div class="mb-4">
                        <label class="form-label">Upload Foto Baru</label>
                        <form id="avatarUploadForm" enctype="multipart/form-data">
                            <div class="input-group">
                                <input type="file" class="form-control" id="avatarFile" name="avatar" accept="image/*" required>
                                <button class="btn btn-outline-primary" type="button" onclick="document.getElementById('avatarFile').click()">
                                    <i class="fas fa-upload"></i>
                                </button>
                            </div>
                            <small class="text-muted">Format: JPG, PNG, GIF, WEBP (max 5MB)</small>
                        </form>
                    </div>
                    
                    <!-- Image Cropper -->
                    <div class="image-cropper-container" id="cropperContainer" style="display: none;">
                        <img id="imagePreview" src="" alt="Preview">
                    </div>
                    
                    <!-- Default Avatars -->
                    <div class="mb-4">
                        <label class="form-label">Pilih Avatar Default</label>
                        <div class="avatar-options" id="defaultAvatars">
                            <?php 
                            $defaultColors = ['#0a4da2', '#28a745', '#ff6b35', '#17a2b8', '#6f42c1', '#fd7e14'];
                            for($i = 0; $i < 8; $i++): 
                                $color = $defaultColors[$i % count($defaultColors)];
                                $letter = strtoupper(substr($username, 0, 1));
                            ?>
                            <div class="avatar-option d-flex align-items-center justify-content-center text-white" 
                                 style="background: <?php echo $color; ?>" 
                                 data-avatar="<?php echo $letter; ?>" 
                                 data-color="<?php echo $color; ?>">
                                <span style="font-size: 1.5rem; font-weight: bold;"><?php echo $letter; ?></span>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="saveAvatarBtn" onclick="uploadAvatar()">
                        <i class="fas fa-save"></i> Simpan Avatar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script>
        // Avatar management
        let cropper;
        let selectedAvatarType = 'default';
        let selectedAvatarData = null;
        
        // Initialize tabs
        const triggerTabList = document.querySelectorAll('#profileTabs a');
        triggerTabList.forEach(triggerEl => {
            triggerEl.addEventListener('click', event => {
                event.preventDefault();
                const tabTrigger = new bootstrap.Tab(triggerEl);
                tabTrigger.show();
            });
        });
        
        // Handle avatar file selection
        document.getElementById('avatarFile').addEventListener('change', function(e) {
            if(this.files && this.files[0]) {
                const file = this.files[0];
                
                // Validate file size (5MB)
                if(file.size > 5 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar. Maksimal 5MB.');
                    this.value = '';
                    return;
                }
                
                // Show cropper container
                document.getElementById('cropperContainer').style.display = 'block';
                document.getElementById('saveAvatarBtn').style.display = 'block';
                
                // Create image preview
                const reader = new FileReader();
                reader.onload = function(event) {
                    const image = document.getElementById('imagePreview');
                    image.src = event.target.result;
                    
                    // Initialize cropper
                    if(cropper) {
                        cropper.destroy();
                    }
                    
                    cropper = new Cropper(image, {
                        aspectRatio: 1,
                        viewMode: 1,
                        autoCropArea: 0.8,
                        responsive: true,
                        preview: '#cropperPreview'
                    });
                    
                    selectedAvatarType = 'upload';
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Default avatar selection
        document.querySelectorAll('#defaultAvatars .avatar-option').forEach(option => {
            option.addEventListener('click', function() {
                // Remove previous selection
                document.querySelectorAll('#defaultAvatars .avatar-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                
                // Add selection to clicked option
                this.classList.add('selected');
                
                // Hide cropper and show save button
                document.getElementById('cropperContainer').style.display = 'none';
                document.getElementById('saveAvatarBtn').style.display = 'block';
                
                // Store selected avatar data
                selectedAvatarType = 'default';
                selectedAvatarData = {
                    letter: this.getAttribute('data-avatar'),
                    color: this.getAttribute('data-color')
                };
            });
        });
        
        // Upload avatar function
        function uploadAvatar() {
            const saveBtn = document.getElementById('saveAvatarBtn');
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            saveBtn.disabled = true;
            
            if(selectedAvatarType === 'upload' && cropper) {
                // Get cropped image
                const canvas = cropper.getCroppedCanvas({
                    width: 300,
                    height: 300
                });
                
                canvas.toBlob(function(blob) {
                    const formData = new FormData();
                    formData.append('action', 'upload_avatar');
                    formData.append('avatar', blob, 'avatar.jpg');
                    
                    fetch('', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(html => {
                        // Close modal and reload
                        const modal = bootstrap.Modal.getInstance(document.getElementById('avatarModal'));
                        modal.hide();
                        location.reload();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Gagal mengupload avatar');
                        saveBtn.innerHTML = '<i class="fas fa-save"></i> Simpan Avatar';
                        saveBtn.disabled = false;
                    });
                }, 'image/jpeg', 0.9);
                
            } else if(selectedAvatarType === 'default' && selectedAvatarData) {
                // For default avatar, create a colored avatar image
                const canvas = document.createElement('canvas');
                canvas.width = 300;
                canvas.height = 300;
                const ctx = canvas.getContext('2d');
                
                // Draw colored circle
                ctx.fillStyle = selectedAvatarData.color;
                ctx.beginPath();
                ctx.arc(150, 150, 150, 0, Math.PI * 2);
                ctx.fill();
                
                // Draw letter
                ctx.fillStyle = 'white';
                ctx.font = 'bold 120px Arial';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(selectedAvatarData.letter, 150, 150);
                
                // Convert to blob and upload
                canvas.toBlob(function(blob) {
                    const formData = new FormData();
                    formData.append('action', 'upload_avatar');
                    formData.append('avatar', blob, 'avatar.jpg');
                    
                    fetch('', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(html => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('avatarModal'));
                        modal.hide();
                        location.reload();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Gagal mengupdate avatar');
                        saveBtn.innerHTML = '<i class="fas fa-save"></i> Simpan Avatar';
                        saveBtn.disabled = false;
                    });
                }, 'image/jpeg', 0.9);
            } else {
                // Direct file upload (if no cropping needed)
                const fileInput = document.getElementById('avatarFile');
                if(fileInput.files[0]) {
                    const formData = new FormData();
                    formData.append('action', 'upload_avatar');
                    formData.append('avatar', fileInput.files[0]);
                    
                    fetch('', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(html => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('avatarModal'));
                        modal.hide();
                        location.reload();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Gagal mengupload avatar');
                        saveBtn.innerHTML = '<i class="fas fa-save"></i> Simpan Avatar';
                        saveBtn.disabled = false;
                    });
                }
            }
        }
        
        // Delete account confirmation
        function confirmDeleteAccount() {
            return confirm('YAKIN ingin menghapus akun secara permanen?\n\nTindakan ini TIDAK DAPAT DIBATALKAN!\nSemua data Anda akan dihapus.');
        }
        
        // Form validation
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const email = this.querySelector('input[name="email"]').value;
            if(!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                e.preventDefault();
                alert('Format email tidak valid');
                return;
            }
            
            this.querySelector('button[type="submit"]').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            this.querySelector('button[type="submit"]').disabled = true;
        });
        
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPass = this.querySelector('input[name="new_password"]').value;
            const confirmPass = this.querySelector('input[name="confirm_password"]').value;
            
            if(newPass.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter');
                return;
            }
            
            if(newPass !== confirmPass) {
                e.preventDefault();
                alert('Password baru tidak cocok');
                return;
            }
            
            this.querySelector('button[type="submit"]').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengupdate...';
            this.querySelector('button[type="submit"]').disabled = true;
        });
        
        // Auto-activate tab from URL hash
        document.addEventListener('DOMContentLoaded', function() {
            if(window.location.hash) {
                const tab = document.querySelector(`a[href="${window.location.hash}"]`);
                if(tab) {
                    new bootstrap.Tab(tab).show();
                }
            }
        });
        
        // Reset form buttons on modal close
        document.getElementById('avatarModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('saveAvatarBtn').innerHTML = '<i class="fas fa-save"></i> Simpan Avatar';
            document.getElementById('saveAvatarBtn').disabled = false;
            document.getElementById('saveAvatarBtn').style.display = 'none';
            document.getElementById('cropperContainer').style.display = 'none';
            
            if(cropper) {
                cropper.destroy();
                cropper = null;
            }
            
            selectedAvatarType = 'default';
            selectedAvatarData = null;
            
            document.querySelectorAll('#defaultAvatars .avatar-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Reset file input
            document.getElementById('avatarFile').value = '';
        });
    </script>
    
    <?php 
    // Logout handler
    if(isset($_GET['logout'])) {
        session_destroy();
        echo '<script>window.location.href = "index.php";</script>';
    }
    ?>
</body>
</html>
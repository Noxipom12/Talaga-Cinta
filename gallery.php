<?php
session_start();
require_once 'functions.php';

$isLoggedIn = isset($_SESSION['user_id']);
$photos = loadPhotos();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri Foto - Talaga Cinta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
        }
        
        .gallery-container {
            padding: 100px 0 50px;
        }
        
        .gallery-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #0a4da2;
            margin-bottom: 1rem;
        }
        
        .photo-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            transition: transform 0.3s ease;
            height: 100%;
        }
        
        .photo-card:hover {
            transform: translateY(-10px);
        }
        
        .photo-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        
        .photo-content {
            padding: 20px;
        }
        
        .photo-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        
        .photo-description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .photo-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .photo-author {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .author-avatar {
            width: 30px;
            height: 30px;
            background: linear-gradient(135deg, #0a4da2, #28a745);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .photo-actions {
            display: flex;
            gap: 15px;
        }
        
        .action-btn {
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .action-btn:hover {
            color: #0a4da2;
        }
        
        .action-btn.liked {
            color: #dc3545;
        }
        
        .rating {
            color: #ffc107;
            margin-bottom: 10px;
        }
        
        .upload-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0a4da2, #28a745);
            color: white;
            border: none;
            box-shadow: 0 10px 25px rgba(10, 77, 162, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            cursor: pointer;
            z-index: 1000;
        }
        
        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    
    <nav class="navbar navbar-expand-lg navbar-light fixed-top bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-mountain-sun"></i> Talaga Cinta
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link active" href="gallery.php">Galeri</a></li>
                    <li class="nav-item"><a class="nav-link" href="stories.php">Kenangan</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="gallery-container">
        <div class="container">
            <h1 class="gallery-title text-center mb-4">Galeri Foto</h1>
            <p class="text-center text-muted mb-5">Koleksi foto terbaik dari pengunjung Talaga Cinta</p>
            
            <?php if($isLoggedIn): ?>
            <button class="upload-btn" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="fas fa-plus"></i>
            </button>
            <?php endif; ?>
            
            <div class="row">
                <?php foreach($photos as $photo): 
                    $likes = loadLikes($photo['id'], 'photo');
                    $comments = loadComments($photo['id'], 'photo');
                    $isLiked = $isLoggedIn ? in_array($_SESSION['user_id'], array_column($likes, 'user_id')) : false;
                ?>
                <div class="col-md-4 col-lg-3">
                    <div class="photo-card">
                        <img src="<?php echo $photo['image']; ?>" class="photo-image" alt="<?php echo htmlspecialchars($photo['title']); ?>">
                        <div class="photo-content">
                            <h5 class="photo-title"><?php echo htmlspecialchars($photo['title']); ?></h5>
                            <p class="photo-description"><?php echo htmlspecialchars(substr($photo['description'], 0, 100)); ?>...</p>
                            
                            <div class="rating">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $photo['rating'] ? 'text-warning' : 'text-secondary'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            
                            <div class="photo-meta">
                                <div class="photo-author">
                                    <div class="author-avatar">
                                        <?php echo strtoupper(substr($photo['username'], 0, 1)); ?>
                                    </div>
                                    <span><?php echo htmlspecialchars($photo['username']); ?></span>
                                </div>
                                
                                <div class="photo-actions">
                                    <button class="action-btn like-btn <?php echo $isLiked ? 'liked' : ''; ?>" 
                                            data-id="<?php echo $photo['id']; ?>" 
                                            data-type="photo">
                                        <i class="fas fa-heart"></i> <span><?php echo count($likes); ?></span>
                                    </button>
                                    <button class="action-btn comment-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#commentModal"
                                            data-id="<?php echo $photo['id']; ?>"
                                            data-type="photo">
                                        <i class="fas fa-comment"></i> <span><?php echo count($comments); ?></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    
    <?php if($isLoggedIn): ?>
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-upload"></i> Upload Foto</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="upload_photo">
                        
                        <div class="mb-3">
                            <label class="form-label">Judul Foto</label>
                            <input type="text" name="photo_title" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="photo_description" class="form-control" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <select name="photo_rating" class="form-select" required>
                                <option value="5">⭐⭐⭐⭐⭐ Luar Biasa</option>
                                <option value="4">⭐⭐⭐⭐ Bagus</option>
                                <option value="3">⭐⭐⭐ Cukup</option>
                                <option value="2">⭐⭐ Biasa</option>
                                <option value="1">⭐ Buruk</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Pilih Foto</label>
                            <input type="file" name="photo_image" class="form-control" accept="image/*" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="modal fade" id="commentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Komentar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="commentsList"></div>
                    <?php if($isLoggedIn): ?>
                    <div class="mt-3">
                        <textarea id="commentText" class="form-control" placeholder="Tulis komentar..." rows="3"></textarea>
                        <button class="btn btn-primary mt-2" onclick="postComment()">Kirim</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Like functionality
        document.querySelectorAll('.like-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                <?php if(!$isLoggedIn): ?>
                alert('Silahkan login terlebih dahulu');
                return;
                <?php endif; ?>
                
                const contentId = this.getAttribute('data-id');
                const type = this.getAttribute('data-type');
                
                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=toggle_like&content_id=' + contentId + '&content_type=' + type
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        const span = this.querySelector('span');
                        const count = parseInt(span.textContent);
                        span.textContent = data.liked ? count + 1 : count - 1;
                        
                        if(data.liked) {
                            this.classList.add('liked');
                        } else {
                            this.classList.remove('liked');
                        }
                    }
                });
            });
        });
        
        // Comment functionality
        let currentContentId, currentContentType;
        
        document.querySelectorAll('.comment-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                currentContentId = this.getAttribute('data-id');
                currentContentType = this.getAttribute('data-type');
                loadComments();
            });
        });
        
        function loadComments() {
            fetch('?get_comments=' + currentContentId + '&type=' + currentContentType)
            .then(response => response.text())
            .then(html => {
                document.getElementById('commentsList').innerHTML = html;
            });
        }
        
        function postComment() {
            const comment = document.getElementById('commentText').value;
            if(!comment.trim()) return;
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=add_comment&content_id=' + currentContentId + '&content_type=' + currentContentType + '&comment=' + encodeURIComponent(comment)
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('commentText').value = '';
                    loadComments();
                }
            });
        }
    </script>
</body>
</html>
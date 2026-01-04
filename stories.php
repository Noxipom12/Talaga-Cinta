<?php
session_start();
require_once 'functions.php';

$isLoggedIn = isset($_SESSION['user_id']);
$stories = loadStories();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kenangan - Talaga Cinta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
        }
        
        .stories-container {
            padding: 100px 0 50px;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .story-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid #eee;
        }
        
        .story-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .story-author {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .author-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #0a4da2, #28a745);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .story-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }
        
        .story-content {
            color: #555;
            line-height: 1.7;
            margin-bottom: 20px;
        }
        
        .story-actions {
            display: flex;
            gap: 20px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .action-btn {
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: color 0.3s;
        }
        
        .action-btn:hover {
            color: #0a4da2;
        }
        
        .action-btn.liked {
            color: #dc3545;
        }
        
        .share-btn {
            position: relative;
        }
        
        .share-options {
            display: none;
            position: absolute;
            bottom: 100%;
            left: 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 10px;
            z-index: 10;
        }
        
        .share-options a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            text-decoration: none;
            color: #333;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .share-options a:hover {
            background: #f8f9fa;
        }
        
        .write-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #0a4da2, #28a745);
            color: white;
            border: none;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 10px 25px rgba(10, 77, 162, 0.3);
            cursor: pointer;
            z-index: 1000;
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
                    <li class="nav-item"><a class="nav-link" href="gallery.php">Galeri</a></li>
                    <li class="nav-item"><a class="nav-link active" href="stories.php">Kenangan</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="stories-container">
        <h1 class="text-center mb-4" style="color: #0a4da2;">Kenangan Pengunjung</h1>
        <p class="text-center text-muted mb-5">Cerita dan pengalaman tak terlupakan dari para pengunjung</p>
        
        <?php if($isLoggedIn): ?>
        <button class="write-btn" data-bs-toggle="modal" data-bs-target="#storyModal">
            <i class="fas fa-pen"></i>
        </button>
        <?php endif; ?>
        
        <?php foreach($stories as $story): 
            $likes = loadLikes($story['id'], 'story');
            $comments = loadComments($story['id'], 'story');
            $isLiked = $isLoggedIn ? in_array($_SESSION['user_id'], array_column($likes, 'user_id')) : false;
        ?>
        <div class="story-card" id="story-<?php echo $story['id']; ?>">
            <div class="story-header">
                <div class="story-author">
                    <div class="author-avatar">
                        <?php echo strtoupper(substr($story['username'], 0, 1)); ?>
                    </div>
                    <div>
                        <h5 class="mb-0"><?php echo htmlspecialchars($story['fullname']); ?></h5>
                        <small class="text-muted">@<?php echo htmlspecialchars($story['username']); ?> • <?php echo timeAgo($story['created_at']); ?></small>
                    </div>
                </div>
                <div class="rating">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star <?php echo $i <= $story['rating'] ? 'text-warning' : 'text-secondary'; ?>"></i>
                    <?php endfor; ?>
                </div>
            </div>
            
            <h3 class="story-title"><?php echo htmlspecialchars($story['title']); ?></h3>
            
            <?php if($story['experience']): ?>
            <div class="badge bg-info mb-3"><?php echo htmlspecialchars($story['experience']); ?></div>
            <?php endif; ?>
            
            <div class="story-content">
                <?php echo nl2br(htmlspecialchars($story['content'])); ?>
            </div>
            
            <div class="story-actions">
                <button class="action-btn like-btn <?php echo $isLiked ? 'liked' : ''; ?>" 
                        data-id="<?php echo $story['id']; ?>" 
                        data-type="story">
                    <i class="fas fa-heart"></i> <span><?php echo count($likes); ?></span>
                </button>
                
                <button class="action-btn comment-btn" 
                        data-bs-toggle="modal" 
                        data-bs-target="#commentModal"
                        data-id="<?php echo $story['id']; ?>"
                        data-type="story">
                    <i class="fas fa-comment"></i> <span><?php echo count($comments); ?></span>
                </button>
                
                <div class="share-btn">
                    <button class="action-btn share-toggle">
                        <i class="fas fa-share"></i> Share
                    </button>
                    <div class="share-options">
                        <a href="#" onclick="shareToFacebook(<?php echo $story['id']; ?>)">
                            <i class="fab fa-facebook"></i> Facebook
                        </a>
                        <a href="#" onclick="shareToTwitter(<?php echo $story['id']; ?>)">
                            <i class="fab fa-twitter"></i> Twitter
                        </a>
                        <a href="#" onclick="copyStoryLink(<?php echo $story['id']; ?>)">
                            <i class="fas fa-link"></i> Copy Link
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Story Modal -->
    <?php if($isLoggedIn): ?>
    <div class="modal fade" id="storyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-book"></i> Tulis Kenangan</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_story">
                        
                        <div class="mb-3">
                            <label class="form-label">Judul</label>
                            <input type="text" name="story_title" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Cerita Anda</label>
                            <textarea name="story_content" class="form-control" rows="6" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rating</label>
                                <select name="story_rating" class="form-select" required>
                                    <option value="5">⭐⭐⭐⭐⭐ Luar Biasa</option>
                                    <option value="4">⭐⭐⭐⭐ Bagus</option>
                                    <option value="3">⭐⭐⭐ Cukup</option>
                                    <option value="2">⭐⭐ Biasa</option>
                                    <option value="1">⭐ Buruk</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Pengalaman</label>
                                <select name="story_experience" class="form-select">
                                    <option value="">Pilih jenis</option>
                                    <option value="wisata keluarga">Wisata Keluarga</option>
                                    <option value="romantis">Romantis/Couple</option>
                                    <option value="petualangan">Petualangan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Publikasikan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

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
        
        // Share functionality
        document.querySelectorAll('.share-toggle').forEach(btn => {
            btn.addEventListener('click', function() {
                const options = this.parentElement.querySelector('.share-options');
                options.style.display = options.style.display === 'block' ? 'none' : 'block';
            });
        });
        
        function shareToFacebook(storyId) {
            const url = window.location.href + '#story-' + storyId;
            window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url), '_blank');
        }
        
        function shareToTwitter(storyId) {
            const url = window.location.href + '#story-' + storyId;
            const text = document.querySelector('#story-' + storyId + ' .story-title').textContent;
            window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(text), '_blank');
        }
        
        function copyStoryLink(storyId) {
            const url = window.location.href + '#story-' + storyId;
            navigator.clipboard.writeText(url).then(() => {
                alert('Link berhasil disalin!');
            });
        }
        
        // Close share options when clicking outside
        document.addEventListener('click', function(e) {
            if(!e.target.closest('.share-btn')) {
                document.querySelectorAll('.share-options').forEach(opt => {
                    opt.style.display = 'none';
                });
            }
        });
    </script>
</body>
</html>
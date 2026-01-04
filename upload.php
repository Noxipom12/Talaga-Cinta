<?php
session_start();
require_once 'functions.php';

// Cek jika sudah login
$isLoggedIn = isset($_SESSION['user_id']);
if(!$isLoggedIn) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'];
$fullname = $_SESSION['fullname'];

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_photo') {
    $result = uploadPhoto(
        $_POST['photo_title'],
        $_POST['photo_description'],
        $_POST['photo_rating'],
        $_FILES['photo_image'],
        $_POST['photo_tags'] ?? ''
    );
    
    if($result['success']) {
        header('Location: gallery.php?success=1');
        exit;
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Foto - Talaga Cinta</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0a4da2;
            --secondary: #28a745;
            --accent: #ff6b35;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }
        
        .upload-container {
            max-width: 800px;
            margin: 100px auto 50px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .upload-header {
            background: linear-gradient(135deg, var(--primary), #1e88e5);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .upload-header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .upload-header p {
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        .upload-body {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-label i {
            color: var(--primary);
        }
        
        .form-control, .form-select, .form-textarea {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus, .form-textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(10, 77, 162, 0.1);
        }
        
        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .rating-stars {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .star-btn {
            background: none;
            border: none;
            font-size: 2.5rem;
            color: #ddd;
            cursor: pointer;
            transition: all 0.2s ease;
            padding: 0;
        }
        
        .star-btn:hover {
            transform: scale(1.2);
        }
        
        .star-btn.selected {
            color: #ffc107;
        }
        
        .star-btn.active {
            color: #ffc107;
        }
        
        .file-upload-area {
            border: 3px dashed #ddd;
            border-radius: 15px;
            padding: 40px 20px;
            text-align: center;
            background: #fafafa;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .file-upload-area:hover {
            border-color: var(--primary);
            background: #f0f7ff;
        }
        
        .file-upload-area.dragover {
            border-color: var(--primary);
            background: #e3f2fd;
        }
        
        .upload-icon {
            font-size: 3.5rem;
            color: var(--primary);
            margin-bottom: 15px;
        }
        
        .file-preview {
            margin-top: 20px;
            display: none;
        }
        
        .preview-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .upload-btn {
            background: linear-gradient(135deg, var(--primary), #1e88e5);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 30px;
        }
        
        .upload-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(10, 77, 162, 0.3);
        }
        
        .upload-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        
        .tag {
            background: #e3f2fd;
            color: var(--primary);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .tag-remove {
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 1rem;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .tag-remove:hover {
            background: rgba(0,0,0,0.1);
        }
        
        .upload-guidelines {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-top: 30px;
            border-left: 4px solid var(--primary);
        }
        
        .guideline-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .guideline-item i {
            color: var(--primary);
            margin-top: 2px;
        }
        
        .error-message {
            background: #fee;
            color: #d32f2f;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #d32f2f;
            display: none;
        }
        
        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #2e7d32;
            display: none;
        }
        
        .back-btn {
            position: fixed;
            top: 30px;
            left: 30px;
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
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .back-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateX(-5px);
        }
        
        @media (max-width: 768px) {
            .upload-container {
                margin: 80px 20px 30px;
                border-radius: 15px;
            }
            
            .upload-header {
                padding: 25px 20px;
            }
            
            .upload-body {
                padding: 25px;
            }
            
            .back-btn {
                top: 20px;
                left: 20px;
                padding: 8px 15px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Back Button -->
    <a href="gallery.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Kembali ke Galeri
    </a>

    <!-- Upload Container -->
    <div class="upload-container">
        <div class="upload-header">
            <h1><i class="fas fa-cloud-upload-alt"></i> Upload Foto Baru</h1>
            <p>Bagikan momen indah Anda di Talaga Cinta</p>
        </div>
        
        <div class="upload-body">
            <?php if(isset($error)): ?>
            <div class="error-message" id="errorMessage" style="display: block;">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <?php if(isset($_GET['success'])): ?>
            <div class="success-message" id="successMessage" style="display: block;">
                <i class="fas fa-check-circle"></i> Foto berhasil diupload!
            </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <input type="hidden" name="action" value="upload_photo">
                <input type="hidden" name="photo_rating" id="photoRating" value="5" required>
                
                <!-- Judul Foto -->
                <div class="form-group">
                    <label class="form-label" for="photo_title">
                        <i class="fas fa-heading"></i> Judul Foto
                    </label>
                    <input type="text" class="form-control" id="photo_title" name="photo_title" 
                           placeholder="Contoh: Sunset di Talaga Cinta" required
                           maxlength="100">
                    <small class="text-muted">Buat judul yang menarik (maks 100 karakter)</small>
                </div>
                
                <!-- Deskripsi -->
                <div class="form-group">
                    <label class="form-label" for="photo_description">
                        <i class="fas fa-align-left"></i> Deskripsi
                    </label>
                    <textarea class="form-control form-textarea" id="photo_description" name="photo_description" 
                              placeholder="Ceritakan momen indah ini..." required
                              rows="4"></textarea>
                    <small class="text-muted">Bagikan cerita di balik foto ini (minimal 50 karakter)</small>
                </div>
                
                <!-- Rating -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-star"></i> Rating Pengalaman
                    </label>
                    <div class="rating-stars" id="ratingStars">
                        <button type="button" class="star-btn" data-rating="1">
                            <i class="fas fa-star"></i>
                        </button>
                        <button type="button" class="star-btn" data-rating="2">
                            <i class="fas fa-star"></i>
                        </button>
                        <button type="button" class="star-btn" data-rating="3">
                            <i class="fas fa-star"></i>
                        </button>
                        <button type="button" class="star-btn" data-rating="4">
                            <i class="fas fa-star"></i>
                        </button>
                        <button type="button" class="star-btn selected" data-rating="5">
                            <i class="fas fa-star"></i>
                        </button>
                    </div>
                    <small class="text-muted">Beri rating pengalaman Anda (1-5 bintang)</small>
                </div>
                
                <!-- Tags -->
                <div class="form-group">
                    <label class="form-label" for="photo_tags">
                        <i class="fas fa-tags"></i> Tags/Kategori
                    </label>
                    <input type="text" class="form-control" id="photo_tags" 
                           placeholder="alam, sunset, danau, pemandangan">
                    <small class="text-muted">Pisahkan dengan koma. Contoh: sunset, alam, perjalanan</small>
                    <div class="tags-container" id="tagsContainer"></div>
                    <input type="hidden" name="photo_tags" id="photoTagsHidden">
                </div>
                
                <!-- File Upload -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-image"></i> Pilih Foto
                    </label>
                    <div class="file-upload-area" id="fileUploadArea">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <h4>Drop file di sini atau klik untuk upload</h4>
                        <p class="text-muted">Format: JPG, PNG, GIF, WEBP (max 10MB)</p>
                        <input type="file" id="photo_image" name="photo_image" accept="image/*" 
                               class="d-none" required>
                        <button type="button" class="btn btn-outline-primary mt-3" onclick="document.getElementById('photo_image').click()">
                            <i class="fas fa-folder-open"></i> Browse Files
                        </button>
                    </div>
                    
                    <div class="file-preview" id="filePreview">
                        <img src="" alt="Preview" class="preview-image" id="previewImage">
                        <div class="mt-3">
                            <p class="mb-1"><strong id="fileName"></strong></p>
                            <p class="text-muted mb-0" id="fileSize"></p>
                        </div>
                    </div>
                </div>
                
                <!-- Upload Guidelines -->
                <div class="upload-guidelines">
                    <h6><i class="fas fa-info-circle"></i> Panduan Upload:</h6>
                    <div class="guideline-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Gunakan foto asli yang Anda ambil di Talaga Cinta</span>
                    </div>
                    <div class="guideline-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Pastikan foto tidak mengandung konten yang tidak pantas</span>
                    </div>
                    <div class="guideline-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Ukuran maksimal file: 10MB</span>
                    </div>
                    <div class="guideline-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Format yang didukung: JPG, PNG, GIF, WEBP</span>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="upload-btn" id="submitBtn">
                    <i class="fas fa-upload"></i> Upload Foto
                </button>
            </form>
        </div>
    </div>

    <script>
        // Rating Stars
        const starButtons = document.querySelectorAll('.star-btn');
        const ratingInput = document.getElementById('photoRating');
        
        starButtons.forEach(button => {
            button.addEventListener('click', function() {
                const rating = this.getAttribute('data-rating');
                ratingInput.value = rating;
                
                starButtons.forEach(star => {
                    if(star.getAttribute('data-rating') <= rating) {
                        star.classList.add('selected');
                        star.classList.add('active');
                    } else {
                        star.classList.remove('selected');
                        star.classList.remove('active');
                    }
                });
            });
            
            button.addEventListener('mouseover', function() {
                const rating = this.getAttribute('data-rating');
                starButtons.forEach(star => {
                    if(star.getAttribute('data-rating') <= rating) {
                        star.classList.add('active');
                    } else {
                        star.classList.remove('active');
                    }
                });
            });
        });
        
        document.getElementById('ratingStars').addEventListener('mouseleave', function() {
            const currentRating = ratingInput.value;
            starButtons.forEach(star => {
                if(star.getAttribute('data-rating') <= currentRating) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        });
        
        // Tags Management
        const tagsInput = document.getElementById('photo_tags');
        const tagsContainer = document.getElementById('tagsContainer');
        const tagsHiddenInput = document.getElementById('photoTagsHidden');
        let tags = [];
        
        tagsInput.addEventListener('keydown', function(e) {
            if(e.key === ',' || e.key === 'Enter') {
                e.preventDefault();
                const tag = this.value.trim();
                if(tag) {
                    addTag(tag);
                    this.value = '';
                }
            }
        });
        
        function addTag(tag) {
            if(tags.includes(tag.toLowerCase()) || tags.length >= 5) return;
            
            tags.push(tag.toLowerCase());
            updateTagsDisplay();
        }
        
        function removeTag(tag) {
            tags = tags.filter(t => t !== tag);
            updateTagsDisplay();
        }
        
        function updateTagsDisplay() {
            tagsContainer.innerHTML = '';
            tags.forEach(tag => {
                const tagElement = document.createElement('div');
                tagElement.className = 'tag';
                tagElement.innerHTML = `
                    ${tag}
                    <button type="button" class="tag-remove" onclick="removeTag('${tag}')">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                tagsContainer.appendChild(tagElement);
            });
            tagsHiddenInput.value = tags.join(', ');
        }
        
        // File Upload with Preview
        const fileInput = document.getElementById('photo_image');
        const fileUploadArea = document.getElementById('fileUploadArea');
        const filePreview = document.getElementById('filePreview');
        const previewImage = document.getElementById('previewImage');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        
        fileUploadArea.addEventListener('click', () => fileInput.click());
        
        fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadArea.classList.add('dragover');
        });
        
        fileUploadArea.addEventListener('dragleave', () => {
            fileUploadArea.classList.remove('dragover');
        });
        
        fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadArea.classList.remove('dragover');
            
            if(e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                handleFileSelect(e.dataTransfer.files[0]);
            }
        });
        
        fileInput.addEventListener('change', (e) => {
            if(e.target.files.length) {
                handleFileSelect(e.target.files[0]);
            }
        });
        
        function handleFileSelect(file) {
            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if(!validTypes.includes(file.type)) {
                alert('Format file tidak didukung. Harap upload file JPG, PNG, GIF, atau WEBP.');
                fileInput.value = '';
                return;
            }
            
            // Validate file size (10MB)
            if(file.size > 10 * 1024 * 1024) {
                alert('Ukuran file terlalu besar. Maksimal 10MB.');
                fileInput.value = '';
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImage.src = e.target.result;
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                filePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
        
        function formatFileSize(bytes) {
            if(bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Form Validation
        const form = document.getElementById('uploadForm');
        const submitBtn = document.getElementById('submitBtn');
        
        form.addEventListener('submit', function(e) {
            // Validate description length
            const description = document.getElementById('photo_description').value.trim();
            if(description.length < 50) {
                e.preventDefault();
                showError('Deskripsi harus minimal 50 karakter');
                return;
            }
            
            // Validate file
            if(!fileInput.files.length) {
                e.preventDefault();
                showError('Harap pilih foto untuk diupload');
                return;
            }
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengupload...';
        });
        
        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
            errorDiv.style.display = 'block';
            
            const existingError = document.getElementById('errorMessage');
            if(existingError) {
                existingError.remove();
            }
            
            form.prepend(errorDiv);
            
            // Scroll to error
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        
        // Auto-save draft
        let draftTimeout;
        
        function saveDraft() {
            const draft = {
                title: document.getElementById('photo_title').value,
                description: document.getElementById('photo_description').value,
                rating: document.getElementById('photoRating').value,
                tags: tags
            };
            localStorage.setItem('uploadDraft', JSON.stringify(draft));
        }
        
        function loadDraft() {
            const draft = JSON.parse(localStorage.getItem('uploadDraft'));
            if(draft) {
                document.getElementById('photo_title').value = draft.title || '';
                document.getElementById('photo_description').value = draft.description || '';
                document.getElementById('photoRating').value = draft.rating || '5';
                
                if(draft.tags) {
                    tags = draft.tags;
                    updateTagsDisplay();
                }
                
                // Update star display
                const rating = draft.rating || '5';
                starButtons.forEach(star => {
                    if(star.getAttribute('data-rating') <= rating) {
                        star.classList.add('selected');
                        star.classList.add('active');
                    }
                });
            }
        }
        
        // Save draft on input
        ['photo_title', 'photo_description'].forEach(id => {
            document.getElementById(id).addEventListener('input', () => {
                clearTimeout(draftTimeout);
                draftTimeout = setTimeout(saveDraft, 1000);
            });
        });
        
        // Load draft on page load
        document.addEventListener('DOMContentLoaded', loadDraft);
        
        // Clear draft on successful submit
        form.addEventListener('submit', () => {
            localStorage.removeItem('uploadDraft');
        });
        
        // Auto-focus on title
        document.getElementById('photo_title').focus();
    </script>
</body>
</html>
<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="registerForm">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-plus"></i> Daftar Akun Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="fullname" class="form-control" required 
                                   placeholder="Masukkan nama lengkap Anda">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" required 
                                   placeholder="Pilih username unik">
                            <small class="text-muted">Minimal 3 karakter</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required 
                                   placeholder="email@contoh.com">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. Handphone</label>
                            <input type="tel" name="phone" class="form-control" 
                                   placeholder="0812-3456-7890">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required 
                                   placeholder="Minimal 6 karakter" minlength="6">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" class="form-control" required 
                                   placeholder="Ketik ulang password">
                        </div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" required id="termsCheck">
                        <label class="form-check-label" for="termsCheck">
                            Saya setuju dengan <a href="#" class="text-primary">Syarat & Ketentuan</a>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Daftar Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-sign-in-alt"></i> Login</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="mb-3">
                        <label class="form-label">Username atau Email</label>
                        <input type="text" name="username" class="form-control" required 
                               placeholder="Masukkan username atau email">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required 
                               placeholder="Masukkan password">
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">
                            Ingat saya
                        </label>
                    </div>
                    
                    <div class="text-center">
                        <a href="#" class="text-primary">Lupa password?</a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if(isset($_SESSION['user_id'])): ?>
<!-- Upload Photo Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-camera"></i> Upload Foto Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="upload_photo">
                    
                    <div class="mb-3">
                        <label class="form-label">Judul Foto <span class="text-danger">*</span></label>
                        <input type="text" name="photo_title" class="form-control" required 
                               placeholder="Contoh: Sunset di Talaga Cinta" maxlength="100">
                        <small class="text-muted">Buat judul yang menarik</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea name="photo_description" class="form-control" rows="3" required 
                                  placeholder="Ceritakan momen indah Anda..."></textarea>
                        <small class="text-muted">Minimal 50 karakter</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rating Pengalaman <span class="text-danger">*</span></label>
                            <div class="rating-stars" id="photoRatingStars">
                                <i class="fas fa-star star text-secondary" data-rating="1"></i>
                                <i class="fas fa-star star text-secondary" data-rating="2"></i>
                                <i class="fas fa-star star text-secondary" data-rating="3"></i>
                                <i class="fas fa-star star text-secondary" data-rating="4"></i>
                                <i class="fas fa-star star text-secondary" data-rating="5"></i>
                            </div>
                            <input type="hidden" name="photo_rating" id="photo_rating" value="5" required>
                            <div class="mt-1">
                                <small class="text-muted">Klik bintang untuk memberi rating</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tags/Kategori</label>
                            <input type="text" name="photo_tags" class="form-control" 
                                   placeholder="alam, sunset, danau, pemandangan">
                            <small class="text-muted">Pisahkan dengan koma</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pilih Foto <span class="text-danger">*</span></label>
                        <input type="file" name="photo_image" class="form-control" accept="image/*" required>
                        <small class="text-muted">Format: JPG, PNG, GIF, WEBP (max 10MB)</small>
                        <div id="imagePreview"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white">
                        <i class="fas fa-upload"></i> Upload Foto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Story/Kenangan Modal -->
<div class="modal fade" id="storyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="fas fa-book"></i> Bagikan Kenangan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_story">
                    
                    <div class="mb-3">
                        <label class="form-label">Judul Kenangan <span class="text-danger">*</span></label>
                        <input type="text" name="story_title" class="form-control" required 
                               placeholder="Contoh: Pengalaman Tak Terlupakan di Talaga Cinta" maxlength="150">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Cerita Anda <span class="text-danger">*</span></label>
                        <textarea name="story_content" class="form-control" rows="5" required 
                                  placeholder="Bagikan pengalaman tak terlupakan Anda... (minimal 200 karakter)"></textarea>
                        <small class="text-muted">Ceritakan pengalaman Anda secara detail</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rating Pengalaman <span class="text-danger">*</span></label>
                            <div class="rating-stars" id="storyRatingStars">
                                <i class="fas fa-star star text-secondary" data-rating="1"></i>
                                <i class="fas fa-star star text-secondary" data-rating="2"></i>
                                <i class="fas fa-star star text-secondary" data-rating="3"></i>
                                <i class="fas fa-star star text-secondary" data-rating="4"></i>
                                <i class="fas fa-star star text-secondary" data-rating="5"></i>
                            </div>
                            <input type="hidden" name="story_rating" id="story_rating" value="5" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jenis Pengalaman</label>
                            <select name="story_experience" class="form-select">
                                <option value="">Pilih jenis pengalaman</option>
                                <option value="wisata keluarga">Wisata Keluarga</option>
                                <option value="romantis">Romantis/Couple</option>
                                <option value="petualangan">Petualangan</option>
                                <option value="fotografi">Fotografi</option>
                                <option value="meditasi">Meditasi/Relaksasi</option>
                                <option value="edukasi">Edukasi</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-white">
                        <i class="fas fa-share"></i> Bagikan Kenangan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
session_start();

// File paths
define('USERS_FILE', 'config/users.json');
define('PHOTOS_FILE', 'config/photos.json');
define('STORIES_FILE', 'config/stories.json');
define('LOCATIONS_FILE', 'config/locations.json');
define('COMMENTS_FILE', 'config/comments.json');
define('LIKES_FILE', 'config/likes.json');
define('CAMERA_CAPTURES_FILE', 'config/camera_captures.json');

// Initialize JSON files if they don't exist
function initFiles() {
    $files = [
        USERS_FILE => ['users' => []],
        PHOTOS_FILE => ['photos' => []],
        STORIES_FILE => ['stories' => []],
        LOCATIONS_FILE => ['locations' => []],
        COMMENTS_FILE => ['comments' => []],
        LIKES_FILE => ['likes' => []],
        CAMERA_CAPTURES_FILE => ['captures' => []]
    ];
    
    foreach($files as $file => $defaultData) {
        if(!file_exists($file)) {
            file_put_contents($file, json_encode($defaultData, JSON_PRETTY_PRINT));
        }
    }
    
    // Create upload directories
    $dirs = ['uploads/photos', 'uploads/avatars', 'uploads/captures'];
    foreach($dirs as $dir) {
        if(!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}

// Load camera captures
function loadCameraCaptures() {
    initFiles();
    if(file_exists(CAMERA_CAPTURES_FILE)) {
        $data = json_decode(file_get_contents(CAMERA_CAPTURES_FILE), true);
        return $data['captures'] ?? [];
    }
    return [];
}

// Save camera capture
function saveCameraCapture($userId, $username, $imageData, $lat, $lng) {
    $captures = loadCameraCaptures();
    
    $fileName = 'capture_' . uniqid() . '_' . $userId . '.jpg';
    $filePath = 'uploads/captures/' . $fileName;
    
    // Save image from base64
    $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
    $imageData = str_replace(' ', '+', $imageData);
    file_put_contents($filePath, base64_decode($imageData));
    
    $captures[] = [
        'id' => uniqid(),
        'user_id' => $userId,
        'username' => $username,
        'image' => $filePath,
        'lat' => $lat,
        'lng' => $lng,
        'captured_at' => time(),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
    ];
    
    $data = ['captures' => $captures];
    file_put_contents(CAMERA_CAPTURES_FILE, json_encode($data, JSON_PRETTY_PRINT));
    
    return ['success' => true, 'message' => 'Capture saved'];
}

// Load users
function loadUsers() {
    initFiles();
    if(file_exists(USERS_FILE)) {
        $data = json_decode(file_get_contents(USERS_FILE), true);
        return $data['users'] ?? [];
    }
    return [];
}

// Save users
function saveUsers($users) {
    $data = ['users' => $users];
    file_put_contents(USERS_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

// Load photos
function loadPhotos($limit = null) {
    initFiles();
    if(file_exists(PHOTOS_FILE)) {
        $data = json_decode(file_get_contents(PHOTOS_FILE), true);
        $photos = $data['photos'] ?? [];
        
        // Sort by newest first
        usort($photos, function($a, $b) {
            return $b['created_at'] <=> $a['created_at'];
        });
        
        if($limit) {
            $photos = array_slice($photos, 0, $limit);
        }
        
        return $photos;
    }
    return [];
}

// Save photos
function savePhotos($photos) {
    $data = ['photos' => $photos];
    file_put_contents(PHOTOS_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

// Load stories
function loadStories($limit = null) {
    initFiles();
    if(file_exists(STORIES_FILE)) {
        $data = json_decode(file_get_contents(STORIES_FILE), true);
        $stories = $data['stories'] ?? [];
        
        // Sort by newest first
        usort($stories, function($a, $b) {
            return $b['created_at'] <=> $a['created_at'];
        });
        
        if($limit) {
            $stories = array_slice($stories, 0, $limit);
        }
        
        return $stories;
    }
    return [];
}

// Save stories
function saveStories($stories) {
    $data = ['stories' => $stories];
    file_put_contents(STORIES_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

// Load locations
function loadLocations() {
    initFiles();
    if(file_exists(LOCATIONS_FILE)) {
        $data = json_decode(file_get_contents(LOCATIONS_FILE), true);
        return $data['locations'] ?? [];
    }
    return [];
}

// Save location (real-time update)
function saveLocation($userId, $lat, $lng, $city = '', $country = '') {
    $locations = loadLocations();
    
    // Cari user yang sama
    $found = false;
    foreach($locations as &$loc) {
        if($loc['user_id'] === $userId) {
            $loc['lat'] = $lat;
            $loc['lng'] = $lng;
            $loc['city'] = $city;
            $loc['country'] = $country;
            $loc['last_update'] = time();
            $loc['accuracy'] = $_POST['accuracy'] ?? 0;
            $loc['altitude'] = $_POST['altitude'] ?? 0;
            $loc['heading'] = $_POST['heading'] ?? 0;
            $loc['speed'] = $_POST['speed'] ?? 0;
            $found = true;
            break;
        }
    }
    
    // Jika tidak ditemukan, tambahkan baru
    if(!$found) {
        $locations[] = [
            'user_id' => $userId,
            'username' => $_SESSION['username'] ?? 'Unknown',
            'lat' => $lat,
            'lng' => $lng,
            'city' => $city,
            'country' => $country,
            'last_update' => time(),
            'accuracy' => $_POST['accuracy'] ?? 0,
            'altitude' => $_POST['altitude'] ?? 0,
            'heading' => $_POST['heading'] ?? 0,
            'speed' => $_POST['speed'] ?? 0,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
        ];
    }
    
    $data = ['locations' => $locations];
    file_put_contents(LOCATIONS_FILE, json_encode($data, JSON_PRETTY_PRINT));
    
    return ['success' => true];
}

// Load comments
function loadComments($contentId = null, $type = 'photo') {
    initFiles();
    if(file_exists(COMMENTS_FILE)) {
        $data = json_decode(file_get_contents(COMMENTS_FILE), true);
        $comments = $data['comments'] ?? [];
        
        if($contentId) {
            $comments = array_filter($comments, function($comment) use ($contentId, $type) {
                return $comment['content_id'] === $contentId && $comment['content_type'] === $type;
            });
        }
        
        usort($comments, function($a, $b) {
            return $a['created_at'] <=> $b['created_at'];
        });
        
        return $comments;
    }
    return [];
}

// Save comment
function saveComment($contentId, $contentType, $userId, $username, $comment) {
    $comments = loadComments();
    
    $newComment = [
        'id' => uniqid(),
        'content_id' => $contentId,
        'content_type' => $contentType,
        'user_id' => $userId,
        'username' => $username,
        'comment' => htmlspecialchars($comment),
        'created_at' => time(),
        'likes' => 0
    ];
    
    $comments[] = $newComment;
    $data = ['comments' => $comments];
    file_put_contents(COMMENTS_FILE, json_encode($data, JSON_PRETTY_PRINT));
    
    return ['success' => true, 'message' => 'Komentar ditambahkan', 'comment_id' => $newComment['id']];
}

// Load likes
function loadLikes($contentId = null, $type = 'photo') {
    initFiles();
    if(file_exists(LIKES_FILE)) {
        $data = json_decode(file_get_contents(LIKES_FILE), true);
        $likes = $data['likes'] ?? [];
        
        if($contentId) {
            $likes = array_filter($likes, function($like) use ($contentId, $type) {
                return $like['content_id'] === $contentId && $like['content_type'] === $type;
            });
        }
        
        return $likes;
    }
    return [];
}

// Toggle like
function toggleLike($contentId, $contentType, $userId, $username) {
    $likes = loadLikes();
    
    // Cek apakah sudah like
    $found = false;
    $likeKey = -1;
    foreach($likes as $key => $like) {
        if($like['content_id'] === $contentId && 
           $like['content_type'] === $contentType && 
           $like['user_id'] === $userId) {
            $likeKey = $key;
            $found = true;
            break;
        }
    }
    
    // Jika belum like, tambahkan
    if(!$found) {
        $newLike = [
            'id' => uniqid(),
            'content_id' => $contentId,
            'content_type' => $contentType,
            'user_id' => $userId,
            'username' => $username,
            'liked_at' => time()
        ];
        $likes[] = $newLike;
    } else {
        unset($likes[$likeKey]);
    }
    
    $data = ['likes' => array_values($likes)];
    file_put_contents(LIKES_FILE, json_encode($data, JSON_PRETTY_PRINT));
    
    return ['success' => true, 'liked' => !$found];
}

// Get like count
function getLikeCount($contentId, $type = 'photo') {
    $likes = loadLikes($contentId, $type);
    return count($likes);
}

// Check if user liked content
function hasUserLiked($contentId, $type, $userId) {
    $likes = loadLikes($contentId, $type);
    foreach($likes as $like) {
        if($like['user_id'] === $userId) {
            return true;
        }
    }
    return false;
}

// Register user
function registerUser($username, $password, $email, $fullname, $phone = '') {
    $users = loadUsers();
    
    // Check if username exists
    foreach($users as $user) {
        if($user['username'] === $username) {
            return ['success' => false, 'message' => 'Username sudah digunakan'];
        }
        if($user['email'] === $email) {
            return ['success' => false, 'message' => 'Email sudah terdaftar'];
        }
    }
    
    // Create new user
    $newUser = [
        'id' => uniqid(),
        'username' => $username,
        'password' => password_hash($password, PASSWORD_BCRYPT),
        'email' => $email,
        'fullname' => $fullname,
        'phone' => $phone,
        'role' => 'user',
        'created_at' => time(),
        'last_login' => null,
        'avatar' => '',
        'bio' => '',
        'status' => 'active',
        'post_count' => 0,
        'allow_camera' => false,
        'allow_location' => false
    ];
    
    $users[] = $newUser;
    saveUsers($users);
    
    // Auto login setelah register
    $_SESSION['user_id'] = $newUser['id'];
    $_SESSION['username'] = $newUser['username'];
    $_SESSION['role'] = $newUser['role'];
    $_SESSION['email'] = $newUser['email'];
    $_SESSION['fullname'] = $newUser['fullname'];
    
    return ['success' => true, 'message' => 'Registrasi berhasil! Anda otomatis login.'];
}

// Login user
function loginUser($username, $password) {
    $users = loadUsers();
    
    foreach($users as $user) {
        if(($user['username'] === $username || $user['email'] === $username) && 
           password_verify($password, $user['password']) && 
           $user['status'] === 'active') {
            
            // Update last login
            foreach($users as &$u) {
                if($u['id'] === $user['id']) {
                    $u['last_login'] = time();
                    $u['last_ip'] = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                    break;
                }
            }
            saveUsers($users);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['fullname'] = $user['fullname'];
            
            return ['success' => true, 'message' => 'Login berhasil!'];
        }
    }
    
    return ['success' => false, 'message' => 'Username/email atau password salah'];
}

// Upload photo (NO ADMIN APPROVAL NEEDED)
function uploadPhoto($title, $description, $rating, $imageFile, $tags = '') {
    if(!isset($_SESSION['user_id'])) {
        return ['success' => false, 'message' => 'Anda harus login'];
    }
    
    // Handle file upload
    $targetDir = "uploads/photos/";
    $fileName = uniqid() . '_' . basename($imageFile["name"]);
    $targetFile = $targetDir . $fileName;
    
    // Check file size (max 10MB)
    if($imageFile["size"] > 10000000) {
        return ['success' => false, 'message' => 'Ukuran file maksimal 10MB'];
    }
    
    // Allow certain file formats
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    if(!in_array($imageFileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Hanya file JPG, JPEG, PNG, GIF & WEBP yang diizinkan'];
    }
    
    // Upload file
    if(move_uploaded_file($imageFile["tmp_name"], $targetFile)) {
        $photos = loadPhotos();
        
        $newPhoto = [
            'id' => uniqid(),
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'fullname' => $_SESSION['fullname'],
            'title' => htmlspecialchars($title),
            'description' => htmlspecialchars($description),
            'rating' => (int)$rating,
            'tags' => htmlspecialchars($tags),
            'image' => $targetFile,
            'status' => 'published',
            'created_at' => time(),
            'views' => 0,
            'likes' => 0,
            'comments' => 0,
            'shares' => 0
        ];
        
        $photos[] = $newPhoto;
        savePhotos($photos);
        
        // Update user post count
        $users = loadUsers();
        foreach($users as &$user) {
            if($user['id'] === $_SESSION['user_id']) {
                $user['post_count'] = ($user['post_count'] ?? 0) + 1;
                break;
            }
        }
        saveUsers($users);
        
        return ['success' => true, 'message' => 'Foto berhasil diupload!', 'photo_id' => $newPhoto['id']];
    }
    
    return ['success' => false, 'message' => 'Gagal upload file'];
}

// Add story (kenangan) - NO ADMIN APPROVAL
function addStory($title, $content, $rating, $experience = '') {
    if(!isset($_SESSION['user_id'])) {
        return ['success' => false, 'message' => 'Anda harus login'];
    }
    
    $stories = loadStories();
    
    $newStory = [
        'id' => uniqid(),
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'fullname' => $_SESSION['fullname'],
        'title' => htmlspecialchars($title),
        'content' => htmlspecialchars($content),
        'rating' => (int)$rating,
        'experience' => htmlspecialchars($experience),
        'status' => 'published',
        'created_at' => time(),
        'likes' => 0,
        'comments' => 0,
        'shares' => 0
    ];
    
    $stories[] = $newStory;
    saveStories($stories);
    
    // Update user post count
    $users = loadUsers();
    foreach($users as &$user) {
        if($user['id'] === $_SESSION['user_id']) {
            $user['post_count'] = ($user['post_count'] ?? 0) + 1;
            break;
        }
    }
    saveUsers($users);
    
    return ['success' => true, 'message' => 'Kenangan berhasil dibagikan!', 'story_id' => $newStory['id']];
}

// Update user avatar
function updateAvatar($userId, $avatarFile) {
    $users = loadUsers();
    
    // Handle file upload
    $targetDir = "uploads/avatars/";
    $fileName = uniqid() . '_' . basename($avatarFile["name"]);
    $targetFile = $targetDir . $fileName;
    
    // Check file size (max 5MB)
    if($avatarFile["size"] > 5000000) {
        return ['success' => false, 'message' => 'Ukuran file maksimal 5MB'];
    }
    
    // Allow certain file formats
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    if(!in_array($imageFileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Hanya file JPG, JPEG, PNG, GIF & WEBP yang diizinkan'];
    }
    
    // Upload file
    if(move_uploaded_file($avatarFile["tmp_name"], $targetFile)) {
        foreach($users as &$user) {
            if($user['id'] === $userId) {
                // Delete old avatar if exists
                if(!empty($user['avatar']) && file_exists($user['avatar']) && 
                   strpos($user['avatar'], 'uploads/avatars/') !== false) {
                    @unlink($user['avatar']);
                }
                
                $user['avatar'] = $targetFile;
                break;
            }
        }
        
        saveUsers($users);
        return ['success' => true, 'message' => 'Avatar berhasil diupdate', 'avatar_path' => $targetFile];
    }
    
    return ['success' => false, 'message' => 'Gagal upload avatar'];
}

// Update profile information
function updateProfile($userId, $fullname, $email, $phone = '', $bio = '') {
    $users = loadUsers();
    
    // Check if email is already used by another user
    foreach($users as $user) {
        if($user['id'] !== $userId && $user['email'] === $email) {
            return ['success' => false, 'message' => 'Email sudah digunakan oleh user lain'];
        }
    }
    
    $updated = false;
    foreach($users as &$user) {
        if($user['id'] === $userId) {
            $user['fullname'] = htmlspecialchars($fullname);
            $user['email'] = htmlspecialchars($email);
            $user['phone'] = htmlspecialchars($phone);
            $user['bio'] = htmlspecialchars($bio);
            $updated = true;
            break;
        }
    }
    
    if($updated) {
        saveUsers($users);
        
        // Update session
        $_SESSION['fullname'] = $fullname;
        $_SESSION['email'] = $email;
        
        return ['success' => true, 'message' => 'Profil berhasil diupdate'];
    }
    
    return ['success' => false, 'message' => 'Gagal update profil'];
}

// Change password
function changePassword($userId, $currentPassword, $newPassword) {
    $users = loadUsers();
    
    foreach($users as &$user) {
        if($user['id'] === $userId) {
            // Verify current password
            if(!password_verify($currentPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Password saat ini salah'];
            }
            
            // Update password
            $user['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
            break;
        }
    }
    
    saveUsers($users);
    return ['success' => true, 'message' => 'Password berhasil diubah'];
}

// Delete content (admin only)
function deleteContent($type, $id) {
    if($type === 'photo') {
        $items = loadPhotos();
        $file = PHOTOS_FILE;
    } else if($type === 'story') {
        $items = loadStories();
        $file = STORIES_FILE;
    } else if($type === 'capture') {
        $items = loadCameraCaptures();
        $file = CAMERA_CAPTURES_FILE;
    } else {
        return ['success' => false, 'message' => 'Tipe tidak valid'];
    }
    
    $deleted = false;
    foreach($items as $key => $item) {
        if($item['id'] === $id) {
            // Delete image file if exists
            if(isset($item['image']) && file_exists($item['image'])) {
                @unlink($item['image']);
            }
            unset($items[$key]);
            $deleted = true;
            break;
        }
    }
    
    if($deleted) {
        if($type === 'photo') {
            savePhotos(array_values($items));
        } else if($type === 'story') {
            saveStories(array_values($items));
        } else if($type === 'capture') {
            $data = ['captures' => array_values($items)];
            file_put_contents(CAMERA_CAPTURES_FILE, json_encode($data, JSON_PRETTY_PRINT));
        }
        return ['success' => true, 'message' => ucfirst($type) . ' berhasil dihapus'];
    }
    
    return ['success' => false, 'message' => ucfirst($type) . ' tidak ditemukan'];
}

// Get statistics
function getStats() {
    $photos = loadPhotos();
    $stories = loadStories();
    $users = loadUsers();
    $captures = loadCameraCaptures();
    $locations = loadLocations();
    
    $totalViews = 0;
    $totalLikes = 0;
    foreach($photos as $photo) {
        $totalViews += $photo['views'] ?? 0;
        $totalLikes += getLikeCount($photo['id'], 'photo');
    }
    
    foreach($stories as $story) {
        $totalLikes += getLikeCount($story['id'], 'story');
    }
    
    // Active users (logged in last 24 hours)
    $activeUsers = 0;
    $twentyFourHoursAgo = time() - (24 * 3600);
    foreach($users as $user) {
        if($user['last_login'] && $user['last_login'] > $twentyFourHoursAgo) {
            $activeUsers++;
        }
    }
    
    return [
        'photos' => count($photos),
        'stories' => count($stories),
        'users' => count($users),
        'captures' => count($captures),
        'locations' => count($locations),
        'active_users' => $activeUsers,
        'total_views' => $totalViews,
        'total_likes' => $totalLikes
    ];
}

// Time ago function
function timeAgo($timestamp) {
    $diff = time() - $timestamp;
    
    if($diff < 60) {
        return 'Baru saja';
    } elseif($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' menit yang lalu';
    } elseif($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' jam yang lalu';
    } elseif($diff < 2592000) {
        $days = floor($diff / 86400);
        return $days . ' hari yang lalu';
    } else {
        return date('d M Y', $timestamp);
    }
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Get user's posts
function getUserPosts($userId) {
    $photos = array_filter(loadPhotos(), fn($p) => $p['user_id'] === $userId);
    $stories = array_filter(loadStories(), fn($s) => $s['user_id'] === $userId);
    
    return [
        'photos' => $photos,
        'stories' => $stories,
        'total' => count($photos) + count($stories)
    ];
}

// Update user permissions
function updateUserPermission($userId, $permission, $value) {
    $users = loadUsers();
    
    foreach($users as &$user) {
        if($user['id'] === $userId) {
            $user[$permission] = $value;
            break;
        }
    }
    
    saveUsers($users);
    return ['success' => true];
}

// Get photo by ID
function getPhotoById($id) {
    $photos = loadPhotos();
    foreach($photos as $photo) {
        if($photo['id'] === $id) {
            return $photo;
        }
    }
    return null;
}

// Get story by ID
function getStoryById($id) {
    $stories = loadStories();
    foreach($stories as $story) {
        if($story['id'] === $id) {
            return $story;
        }
    }
    return null;
}

// Increment view count
function incrementViews($contentId, $type = 'photo') {
    if($type === 'photo') {
        $items = loadPhotos();
        $file = PHOTOS_FILE;
    } else if($type === 'story') {
        $items = loadStories();
        $file = STORIES_FILE;
    } else {
        return false;
    }
    
    $updated = false;
    foreach($items as &$item) {
        if($item['id'] === $contentId) {
            $item['views'] = ($item['views'] ?? 0) + 1;
            $updated = true;
            break;
        }
    }
    
    if($updated) {
        if($type === 'photo') {
            savePhotos($items);
        } else if($type === 'story') {
            saveStories($items);
        }
        return true;
    }
    
    return false;
}

// Get total likes for user
function getUserLikes($userId) {
    $likes = loadLikes();
    $userLikes = array_filter($likes, fn($like) => $like['user_id'] === $userId);
    return count($userLikes);
}

// Delete user account
function deleteUserAccount($userId) {
    $users = loadUsers();
    $photos = loadPhotos();
    $stories = loadStories();
    $comments = loadComments();
    $likes = loadLikes();
    
    // Delete user
    $newUsers = array_filter($users, fn($user) => $user['id'] !== $userId);
    
    // Delete user's photos
    foreach($photos as $photo) {
        if($photo['user_id'] === $userId && file_exists($photo['image'])) {
            @unlink($photo['image']);
        }
    }
    $newPhotos = array_filter($photos, fn($photo) => $photo['user_id'] !== $userId);
    
    // Delete user's stories
    $newStories = array_filter($stories, fn($story) => $story['user_id'] !== $userId);
    
    // Delete user's comments
    $newComments = array_filter($comments, fn($comment) => $comment['user_id'] !== $userId);
    
    // Delete user's likes
    $newLikes = array_filter($likes, fn($like) => $like['user_id'] !== $userId);
    
    // Save all changes
    saveUsers(array_values($newUsers));
    savePhotos(array_values($newPhotos));
    saveStories(array_values($newStories));
    
    $commentsData = ['comments' => array_values($newComments)];
    file_put_contents(COMMENTS_FILE, json_encode($commentsData, JSON_PRETTY_PRINT));
    
    $likesData = ['likes' => array_values($newLikes)];
    file_put_contents(LIKES_FILE, json_encode($likesData, JSON_PRETTY_PRINT));
    
    return ['success' => true, 'message' => 'Akun berhasil dihapus'];
}

// ============================================
// UPGRADE: FUNGSI UNTUK DETEKSI SEMUA USER DAN LOKASI
// ============================================

/**
 * Get all users with their location status
 * Menggabungkan data dari users.json dan locations.json
 */
function getAllUsersWithLocationStatus() {
    $users = loadUsers();
    $locations = loadLocations();
    
    // Create quick lookup map for locations
    $locationMap = [];
    foreach($locations as $loc) {
        if(isset($loc['user_id'])) {
            $locationMap[$loc['user_id']] = $loc;
        }
    }
    
    $result = [];
    foreach($users as $user) {
        $userId = $user['id'];
        $hasLocation = isset($locationMap[$userId]);
        $isOnline = false;
        $lastUpdate = null;
        $userLocation = null;
        
        if($hasLocation) {
            $userLocation = $locationMap[$userId];
            $isOnline = (time() - $userLocation['last_update']) < 300; // 5 menit online
            $lastUpdate = $userLocation['last_update'];
        }
        
        // Cek apakah user aktif (login dalam 24 jam)
        $isActive = ($user['last_login'] && (time() - $user['last_login']) < 86400) ? true : false;
        
        $result[] = [
            'id' => $userId,
            'username' => $user['username'] ?? '',
            'fullname' => $user['fullname'] ?? '',
            'email' => $user['email'] ?? '',
            'role' => $user['role'] ?? 'user',
            'status' => $user['status'] ?? 'active',
            'last_login' => $user['last_login'] ?? null,
            'created_at' => $user['created_at'] ?? time(),
            'avatar' => $user['avatar'] ?? '',
            'post_count' => $user['post_count'] ?? 0,
            'allow_location' => $user['allow_location'] ?? false,
            'has_location' => $hasLocation,
            'is_online' => $isOnline,
            'is_active' => $isActive,
            'last_location_update' => $lastUpdate,
            'location_data' => $userLocation,
            'location_enabled' => $user['allow_location'] ?? false
        ];
    }
    
    // Sort by online status first, then by last update
    usort($result, function($a, $b) {
        // Prioritas: online > has location > last update
        if($a['is_online'] !== $b['is_online']) {
            return $b['is_online'] - $a['is_online'];
        }
        
        if($a['has_location'] !== $b['has_location']) {
            return $b['has_location'] - $a['has_location'];
        }
        
        $aTime = $a['last_location_update'] ?? 0;
        $bTime = $b['last_location_update'] ?? 0;
        
        return $bTime - $aTime;
    });
    
    return $result;
}

/**
 * Get active users with locations for real-time tracking
 */
function getActiveUsersWithLocations() {
    $allUsers = getAllUsersWithLocationStatus();
    
    // Filter hanya yang aktif dan punya lokasi
    $activeUsers = array_filter($allUsers, function($user) {
        return $user['is_active'] && $user['has_location'];
    });
    
    // Format data untuk map
    $formattedData = [];
    foreach($activeUsers as $user) {
        if($user['location_data']) {
            $loc = $user['location_data'];
            $formattedData[] = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'fullname' => $user['fullname'],
                'lat' => $loc['lat'],
                'lng' => $loc['lng'],
                'city' => $loc['city'] ?? '',
                'country' => $loc['country'] ?? '',
                'last_update' => $loc['last_update'],
                'accuracy' => $loc['accuracy'] ?? 0,
                'is_online' => $user['is_online'],
                'time_ago' => timeAgo($loc['last_update'])
            ];
        }
    }
    
    return $formattedData;
}

/**
 * Get location statistics for admin dashboard
 */
function getLocationStats() {
    $allUsers = getAllUsersWithLocationStatus();
    
    $totalUsers = count($allUsers);
    $usersWithLocation = count(array_filter($allUsers, fn($u) => $u['has_location']));
    $onlineUsers = count(array_filter($allUsers, fn($u) => $u['is_online']));
    $activeUsers = count(array_filter($allUsers, fn($u) => $u['is_active']));
    $locationEnabledUsers = count(array_filter($allUsers, fn($u) => $u['location_enabled']));
    
    return [
        'total_users' => $totalUsers,
        'users_with_location' => $usersWithLocation,
        'online_users' => $onlineUsers,
        'active_users' => $activeUsers,
        'location_enabled_users' => $locationEnabledUsers,
        'percentage_with_location' => $totalUsers > 0 ? round(($usersWithLocation / $totalUsers) * 100, 1) : 0
    ];
}

/**
 * Update user location permission
 */
function updateUserLocationPermission($userId, $enabled = true) {
    $users = loadUsers();
    $updated = false;
    
    foreach($users as &$user) {
        if($user['id'] === $userId) {
            $user['allow_location'] = $enabled;
            $updated = true;
            break;
        }
    }
    
    if($updated) {
        saveUsers($users);
        return true;
    }
    
    return false;
}

/**
 * Get user by ID dengan data lengkap
 */
function getUserByIdWithLocation($userId) {
    $users = getAllUsersWithLocationStatus();
    
    foreach($users as $user) {
        if($user['id'] === $userId) {
            return $user;
        }
    }
    
    return null;
}

/**
 * Clean up old location data (lebih dari 7 hari)
 */
function cleanupOldLocations() {
    $locations = loadLocations();
    $oneWeekAgo = time() - (7 * 24 * 3600);
    $initialCount = count($locations);
    
    $locations = array_filter($locations, function($loc) use ($oneWeekAgo) {
        return $loc['last_update'] > $oneWeekAgo;
    });
    
    if(count($locations) < $initialCount) {
        $data = ['locations' => array_values($locations)];
        file_put_contents(LOCATIONS_FILE, json_encode($data, JSON_PRETTY_PRINT));
        return $initialCount - count($locations);
    }
    
    return 0;
}

// Process form submissions
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'register':
                $result = registerUser(
                    $_POST['username'],
                    $_POST['password'],
                    $_POST['email'],
                    $_POST['fullname'],
                    $_POST['phone'] ?? ''
                );
                if($result['success']) {
                    echo '<script>alert("'.$result['message'].'"); window.location.href = "index.php";</script>';
                } else {
                    echo '<script>alert("'.$result['message'].'"); history.back();</script>';
                }
                break;
                
            case 'login':
                $result = loginUser($_POST['username'], $_POST['password']);
                if($result['success']) {
                    echo '<script>alert("'.$result['message'].'"); window.location.href = "index.php";</script>';
                } else {
                    echo '<script>alert("'.$result['message'].'"); history.back();</script>';
                }
                break;
                
            case 'upload_photo':
                $result = uploadPhoto(
                    $_POST['photo_title'],
                    $_POST['photo_description'],
                    $_POST['photo_rating'],
                    $_FILES['photo_image'],
                    $_POST['photo_tags'] ?? ''
                );
                if($result['success']) {
                    echo '<script>alert("'.$result['message'].'"); window.location.href = "gallery.php";</script>';
                } else {
                    echo '<script>alert("'.$result['message'].'"); history.back();</script>';
                }
                break;
                
            case 'add_story':
                $result = addStory(
                    $_POST['story_title'],
                    $_POST['story_content'],
                    $_POST['story_rating'],
                    $_POST['story_experience'] ?? ''
                );
                if($result['success']) {
                    echo '<script>alert("'.$result['message'].'"); window.location.href = "stories.php";</script>';
                } else {
                    echo '<script>alert("'.$result['message'].'"); history.back();</script>';
                }
                break;
                
            case 'save_location':
                if(isset($_SESSION['user_id'])) {
                    // Update user permission untuk lokasi
                    updateUserLocationPermission($_SESSION['user_id'], true);
                    
                    saveLocation(
                        $_SESSION['user_id'],
                        $_POST['lat'],
                        $_POST['lng'],
                        $_POST['city'] ?? '',
                        $_POST['country'] ?? ''
                    );
                    echo json_encode(['success' => true]);
                }
                exit;
                
            case 'save_capture':
                if(isset($_SESSION['user_id'])) {
                    $result = saveCameraCapture(
                        $_SESSION['user_id'],
                        $_SESSION['username'],
                        $_POST['image_data'],
                        $_POST['lat'] ?? 0,
                        $_POST['lng'] ?? 0
                    );
                    echo json_encode($result);
                }
                exit;
                
            case 'toggle_like':
                if(isset($_SESSION['user_id'])) {
                    $result = toggleLike(
                        $_POST['content_id'],
                        $_POST['content_type'],
                        $_SESSION['user_id'],
                        $_SESSION['username']
                    );
                    echo json_encode($result);
                }
                exit;
                
            case 'add_comment':
                if(isset($_SESSION['user_id'])) {
                    $result = saveComment(
                        $_POST['content_id'],
                        $_POST['content_type'],
                        $_SESSION['user_id'],
                        $_SESSION['username'],
                        $_POST['comment']
                    );
                    echo json_encode($result);
                }
                exit;
                
            case 'delete_content':
                if(isAdmin()) {
                    $result = deleteContent($_POST['content_type'], $_POST['content_id']);
                    echo '<script>alert("'.$result['message'].'"); window.location.href = "admin.php";</script>';
                }
                break;
                
            case 'update_permission':
                if(isAdmin()) {
                    $result = updateUserPermission($_POST['user_id'], $_POST['permission'], $_POST['value']);
                    echo json_encode($result);
                }
                exit;
                
            case 'upload_avatar':
                if(isset($_SESSION['user_id']) && isset($_FILES['avatar'])) {
                    $result = updateAvatar($_SESSION['user_id'], $_FILES['avatar']);
                    if($result['success']) {
                        echo '<script>alert("'.$result['message'].'"); window.location.href = "profile.php";</script>';
                    } else {
                        echo '<script>alert("'.$result['message'].'"); window.location.href = "profile.php";</script>';
                    }
                }
                break;
                
            case 'update_profile':
                if(isset($_SESSION['user_id'])) {
                    $result = updateProfile(
                        $_SESSION['user_id'],
                        $_POST['fullname'],
                        $_POST['email'],
                        $_POST['phone'] ?? '',
                        $_POST['bio'] ?? ''
                    );
                    if($result['success']) {
                        echo '<script>alert("'.$result['message'].'"); window.location.href = "profile.php";</script>';
                    } else {
                        echo '<script>alert("'.$result['message'].'"); history.back();</script>';
                    }
                }
                break;
                
            case 'update_password':
                if(isset($_SESSION['user_id'])) {
                    $result = changePassword(
                        $_SESSION['user_id'],
                        $_POST['current_password'],
                        $_POST['new_password']
                    );
                    if($result['success']) {
                        echo '<script>alert("'.$result['message'].'"); window.location.href = "profile.php";</script>';
                    } else {
                        echo '<script>alert("'.$result['message'].'"); history.back();</script>';
                    }
                }
                break;
                
            case 'delete_account':
                if(isset($_SESSION['user_id'])) {
                    $result = deleteUserAccount($_SESSION['user_id']);
                    if($result['success']) {
                        session_destroy();
                        echo '<script>alert("'.$result['message'].'"); window.location.href = "index.php";</script>';
                    } else {
                        echo '<script>alert("Gagal menghapus akun"); history.back();</script>';
                    }
                }
                break;
        }
    }
}

// Handle GET requests for AJAX
if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax'])) {
    switch($_GET['ajax']) {
        case 'get_comments':
            if(isset($_GET['content_id']) && isset($_GET['content_type'])) {
                $comments = loadComments($_GET['content_id'], $_GET['content_type']);
                echo json_encode($comments);
            }
            exit;
            
        case 'get_likes':
            if(isset($_GET['content_id']) && isset($_GET['content_type'])) {
                $likes = loadLikes($_GET['content_id'], $_GET['content_type']);
                echo json_encode(['count' => count($likes)]);
            }
            exit;
            
        case 'check_like':
            if(isset($_GET['content_id']) && isset($_GET['content_type']) && isset($_SESSION['user_id'])) {
                $liked = hasUserLiked($_GET['content_id'], $_GET['content_type'], $_SESSION['user_id']);
                echo json_encode(['liked' => $liked]);
            }
            exit;
            
        case 'get_all_users_with_locations':
            if(isAdmin()) {
                $users = getAllUsersWithLocationStatus();
                echo json_encode($users);
            }
            exit;
            
        case 'get_location_stats':
            if(isAdmin()) {
                $stats = getLocationStats();
                echo json_encode($stats);
            }
            exit;
            
        case 'get_active_users_map':
            if(isAdmin()) {
                $users = getActiveUsersWithLocations();
                echo json_encode($users);
            }
            exit;
    }
}

// Create default admin if not exists
function createDefaultAdmin() {
    $users = loadUsers();
    $adminExists = false;
    
    foreach($users as $user) {
        if($user['username'] === 'admin') {
            $adminExists = true;
            break;
        }
    }
    
    if(!$adminExists) {
        $adminUser = [
            'id' => 'admin001',
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_BCRYPT),
            'email' => 'admin@talagacinta.com',
            'fullname' => 'Administrator',
            'phone' => '',
            'role' => 'admin',
            'created_at' => time(),
            'last_login' => time(),
            'last_ip' => '127.0.0.1',
            'avatar' => '',
            'bio' => 'Administrator sistem',
            'status' => 'active',
            'post_count' => 0,
            'allow_camera' => true,
            'allow_location' => true
        ];
        
        $users[] = $adminUser;
        saveUsers($users);
    }
}

// Initialize
initFiles();
createDefaultAdmin();

// Auto cleanup old locations once per day
if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $lastCleanupFile = 'config/last_cleanup.txt';
    $oneDayAgo = time() - (24 * 3600);
    
    if(!file_exists($lastCleanupFile) || filemtime($lastCleanupFile) < $oneDayAgo) {
        $cleaned = cleanupOldLocations();
        if($cleaned > 0) {
            error_log("Cleaned up $cleaned old location records");
        }
        touch($lastCleanupFile);
    }
}
?>
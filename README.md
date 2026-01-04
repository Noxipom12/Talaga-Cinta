🌊 Talaga Cinta - Wisata Alam Interaktif
<p align="center"> <img src="https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP"> <img src="https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript"> <img src="https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap"> <img src="https://img.shields.io/badge/GitHub-Repository-181717?style=for-the-badge&logo=github&logoColor=white" alt="GitHub"> </p>
📖 Tentang Talaga Cinta
Talaga Cinta adalah platform website wisata alam interaktif yang menyajikan pengalaman berbagi momen, foto, dan cerita di sekitar destinasi wisata Talaga Cinta. Website ini dilengkapi dengan fitur-fitur modern untuk pengelolaan konten dan interaksi pengguna.

✨ Fitur Utama
🖼️ Galeri Foto & Cerita
Unggah foto dan cerita pengalaman wisata
Sistem like dan komentar interaktif
Kategorisasi berdasarkan lokasi wisata
👤 Sistem Pengguna
Registrasi dan login pengguna
Profil pengguna dengan avatar kustom

Peran admin dan user biasa
📸 Fitur Khusus
Camera Capture - Ambil foto langsung melalui webcam
mera Capture - Ambil foto langsung melal
Gallery View - Tampilan galeri yang menarik
Cerita Pengunjung - Bagikan pengalaman wisata
🛠️ Admin Dashboard
Kelola semua konten (foto, cerita, komentar)
Kelola pengguna
Monitoring aktivitas sistem

📁 Struktur Proyek
text
talaga-cinta/
├── 📄 index.php          # Halaman utama
├── 📄 login.php         # Halaman login
├── 📄 admin.php         # Dashboard admin
├── 📄 gallery.php       # Galeri foto
├── 📄 stories.php       # Halaman cerita
├── 📄 profile.php       # Profil pengguna
├── 📄 upload.php        # Upload foto
├── 📄 functions.php     # Fungsi helper
├── 📄 navbar.php        # Navigasi
├── 📄 modals.php        # Modal windows
├── 📄 admin_modals.php  # Modal admin
│
├── 📂 assets/           # Aset statis
│   ├── 📂 css/         # Stylesheet
│   │   └── style.css   # CSS utama
│   └── 📂 js/          # JavaScript
│       └── script.js   # Script utama
│
├── 📂 config/           # Konfigurasi & data
│   ├── 📄 users.json        # Data pengguna
│   ├── 📄 photos.json       # Data foto
│   ├── 📄 stories.json      # Data cerita
│   ├── 📄 comments.json     # Data komentar
│   ├── 📄 likes.json        # Data like
│   ├── 📄 locations.json    # Data lokasi
│   ├── 📄 camera_captures.json # Data capture
│   └── 📄 last_cleanup.txt  # Log cleanup
│
└── 📂 uploads/          # File upload
    ├── 📂 photos/       # Foto wisata
    ├── 📂 avatars/      # Avatar pengguna
    └── 📂 captures/     # Hasil capture kamera


🚀 Cara Menjalankan
Prerequisites
PHP 7.4+ atau PHP 8.x
Web server (Apache/Nginx)
Browser modern
Akses internet (untuk CDN Bootstrap)

Langkah Instalasi
Clone Repository
bash
git clone https://github.com/Noxipom12/Talaga-Cinta.git
cd Talaga-Cinta
Konfigurasi File Permission

bash
chmod 755 uploads/
chmod 755 uploads/photos/
chmod 755 uploads/avatars/
chmod 755 uploads/captures/
chmod 666 config/*.json
Jalankan di Web Server

Pindahkan folder ke direktori web server (htdocs/www)

Akses via browser: http://localhost/talaga-cinta
Akun Default

Admin: admin / admin123

User: Daftar via halaman registrasi

👥 Fungsi Pengguna
👤 Pengguna Biasa
Melihat galeri foto dan cerita
Upload foto dan cerita
Like dan komentar
Edit profil
Capture foto via webcam

👑 Administrator
Semua fitur user
Kelola semua konten
Hapus konten tidak pantas
Kelola data pengguna
Monitoring sistem

🔧 Fitur Teknis Keamananasi file uValidasi file upload
Proteksi path traversal
Sanitasi input user
Session management
Performansi
JSON-based database
Optimized image handlin
Lazy loading gallery
Cleanup otomatis
User Experience
Responsive design
Mobile-friendly
Drag & drop upload
rag & drop upload
Real-time interactions

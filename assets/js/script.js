// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        if(this.getAttribute('href') === '#') return;
        
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

// Password confirmation check
const registerForm = document.querySelector('form[action="register"]');
if(registerForm) {
    registerForm.addEventListener('submit', function(e) {
        const password = this.querySelector('input[name="password"]').value;
        const confirmPassword = this.querySelector('input[name="confirm_password"]').value;
        
        if(password !== confirmPassword) {
            e.preventDefault();
            alert('Password dan konfirmasi password tidak sama!');
        }
    });
}

// Rating star interaction
document.querySelectorAll('.rating-select').forEach(select => {
    select.addEventListener('change', function() {
        const ratingValue = this.value;
        const starsContainer = this.parentElement.querySelector('.stars-preview');
        
        if(starsContainer) {
            starsContainer.innerHTML = '';
            for(let i = 1; i <= 5; i++) {
                const star = document.createElement('i');
                star.className = `fas fa-star ${i <= ratingValue ? 'text-warning' : 'text-secondary'}`;
                starsContainer.appendChild(star);
            }
        }
    });
});

// Image preview for upload
const imageInput = document.querySelector('input[name="photo_image"]');
if(imageInput) {
    const previewContainer = document.createElement('div');
    previewContainer.className = 'image-preview mt-2';
    previewContainer.style.display = 'none';
    imageInput.parentElement.appendChild(previewContainer);
    
    imageInput.addEventListener('change', function() {
        previewContainer.innerHTML = '';
        previewContainer.style.display = 'none';
        
        if(this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.maxWidth = '200px';
                img.style.maxHeight = '150px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '10px';
                img.style.marginTop = '10px';
                previewContainer.appendChild(img);
                previewContainer.style.display = 'block';
            }
            
            reader.readAsDataURL(this.files[0]);
        }
    });
}

// Auto-hide alerts after 5 seconds
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        if(!alert.classList.contains('alert-danger')) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }
    });
}, 5000);

// Lazy loading for images
document.addEventListener('DOMContentLoaded', function() {
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if(entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });
    
    lazyImages.forEach(img => imageObserver.observe(img));
});

// Add active class to current nav link
window.addEventListener('scroll', function() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    
    let current = '';
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.clientHeight;
        if(scrollY >= sectionTop - 100) {
            current = section.getAttribute('id');
        }
    });
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if(link.getAttribute('href') === `#${current}`) {
            link.classList.add('active');
        }
    });
});

// Form validation
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const requiredFields = this.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if(!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
                
                const feedback = field.nextElementSibling;
                if(!feedback || !feedback.classList.contains('invalid-feedback')) {
                    const div = document.createElement('div');
                    div.className = 'invalid-feedback';
                    div.textContent = 'Field ini wajib diisi';
                    field.parentElement.appendChild(div);
                }
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if(!isValid) {
            e.preventDefault();
        }
    });
});

// Initialize tooltips
const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});

// Add fade-in animation on scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if(entry.isIntersecting) {
            entry.target.classList.add('fade-in-visible');
        }
    });
}, observerOptions);

document.querySelectorAll('.photo-card, .story-card, .section-header').forEach(el => {
    el.classList.add('fade-in');
    observer.observe(el);
});

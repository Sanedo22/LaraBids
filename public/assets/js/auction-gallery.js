let currentLightboxIndex = 0;
let galleryImages = [];
let auctionTitle = '';

function initGallery(images, title) {
    galleryImages = images;
    auctionTitle = title;
}

function changeMainImage(src, thumbElement) {
    const mainImg = document.querySelector('.main-auction-image');

    // Find index for lightbox sync
    currentLightboxIndex = galleryImages.indexOf(src);

    // Add fade effect
    mainImg.style.opacity = '0.5';

    setTimeout(() => {
        mainImg.src = src;
        mainImg.style.opacity = '1';
    }, 150);

    // Update active thumbnail styling
    document.querySelectorAll('.auction-thumb').forEach(el => {
        el.classList.remove('border-primary', 'border-2');
        el.classList.add('border-light');
    });

    thumbElement.classList.remove('border-light');
    thumbElement.classList.add('border-primary', 'border-2');
}

function openLightbox() {
    const modal = document.getElementById('imageLightbox');
    updateLightboxView();

    modal.style.display = "flex";
    setTimeout(() => modal.classList.add('show'), 10);

    document.body.style.overflow = 'hidden';
}

function changeLightboxImage(direction) {
    currentLightboxIndex += direction;

    if (currentLightboxIndex >= galleryImages.length) {
        currentLightboxIndex = 0;
    } else if (currentLightboxIndex < 0) {
        currentLightboxIndex = galleryImages.length - 1;
    }

    // Smooth transition for image change
    const lightboxImg = document.getElementById('lightboxImage');
    lightboxImg.style.opacity = '0.5';
    lightboxImg.style.transform = 'scale(0.98)';

    setTimeout(() => {
        updateLightboxView();
        lightboxImg.style.opacity = '1';
        lightboxImg.style.transform = 'scale(1)';
    }, 150);
}

function updateLightboxView() {
    const lightboxImg = document.getElementById('lightboxImage');
    const caption = document.getElementById('lightboxCaption');
    const counter = document.getElementById('lightboxCounter');

    if (!lightboxImg || !galleryImages[currentLightboxIndex]) return;

    lightboxImg.src = galleryImages[currentLightboxIndex];
    counter.innerHTML = `${currentLightboxIndex + 1} / ${galleryImages.length}`;
    caption.innerHTML = auctionTitle;
}

function closeLightbox() {
    const modal = document.getElementById('imageLightbox');
    if (!modal) return;

    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = "none";
        document.body.style.overflow = 'auto';
    }, 400);
}

// Keyboard navigation
document.addEventListener('keydown', function (e) {
    const modal = document.getElementById('imageLightbox');
    if (!modal || modal.style.display !== "flex") return;

    if (e.key === "Escape") closeLightbox();
    if (e.key === "ArrowRight") changeLightboxImage(1);
    if (e.key === "ArrowLeft") changeLightboxImage(-1);
});

// Close on click outside image
document.addEventListener('DOMContentLoaded', function () {
    const lightbox = document.getElementById('imageLightbox');
    if (lightbox) {
        lightbox.addEventListener('click', function (e) {
            if (e.target === this) closeLightbox();
        });
    }
});

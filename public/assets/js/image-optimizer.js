/**
 * Image Optimization and Lazy Loading Script
 * Performance optimization for Renal Tales Application
 */

class ImageOptimizer {
    constructor() {
        this.lazyImages = [];
        this.imageObserver = null;
        this.init();
    }

    init() {
        // Initialize lazy loading
        this.setupLazyLoading();
        
        // Optimize existing images
        this.optimizeExistingImages();
        
        // Setup responsive images
        this.setupResponsiveImages();
    }

    setupLazyLoading() {
        // Check if Intersection Observer is supported
        if ('IntersectionObserver' in window) {
            this.imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        this.loadImage(img);
                        observer.unobserve(img);
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.01
            });

            // Find all images with data-src attribute
            this.lazyImages = document.querySelectorAll('img[data-src]');
            this.lazyImages.forEach(img => {
                this.imageObserver.observe(img);
            });
        } else {
            // Fallback for older browsers
            this.loadAllImages();
        }
    }

    loadImage(img) {
        // Show loading placeholder
        img.classList.add('loading');
        
        const tempImg = new Image();
        tempImg.onload = () => {
            img.src = tempImg.src;
            img.classList.remove('loading');
            img.classList.add('loaded');
            
            // Remove data-src to prevent reloading
            img.removeAttribute('data-src');
        };
        
        tempImg.onerror = () => {
            img.classList.remove('loading');
            img.classList.add('error');
            // Set fallback image
            img.src = 'assets/images/placeholder.webp';
        };
        
        tempImg.src = img.dataset.src;
    }

    loadAllImages() {
        // Fallback method for browsers without Intersection Observer
        this.lazyImages.forEach(img => {
            this.loadImage(img);
        });
    }

    optimizeExistingImages() {
        const images = document.querySelectorAll('img:not([data-src])');
        
        images.forEach(img => {
            // Add loading class for styling
            img.addEventListener('load', () => {
                img.classList.add('loaded');
            });
            
            img.addEventListener('error', () => {
                img.classList.add('error');
                // Set fallback image
                img.src = 'assets/images/placeholder.webp';
            });
            
            // If image is already loaded
            if (img.complete) {
                img.classList.add('loaded');
            }
        });
    }

    setupResponsiveImages() {
        // Setup responsive images based on viewport
        const images = document.querySelectorAll('img[data-sizes]');
        
        images.forEach(img => {
            this.updateImageSize(img);
        });
        
        // Update on window resize
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                images.forEach(img => {
                    this.updateImageSize(img);
                });
            }, 250);
        });
    }

    updateImageSize(img) {
        const sizes = JSON.parse(img.dataset.sizes || '{}');
        const viewportWidth = window.innerWidth;
        
        let selectedSize = sizes.default || img.src;
        
        // Find appropriate size based on viewport
        Object.keys(sizes).sort((a, b) => parseInt(b) - parseInt(a)).forEach(breakpoint => {
            if (viewportWidth >= parseInt(breakpoint)) {
                selectedSize = sizes[breakpoint];
                return;
            }
        });
        
        if (img.src !== selectedSize) {
            img.src = selectedSize;
        }
    }

    // WebP format detection and conversion
    static supportsWebP() {
        const canvas = document.createElement('canvas');
        canvas.width = 1;
        canvas.height = 1;
        return canvas.toDataURL('image/webp').indexOf('webp') > -1;
    }

    static convertToWebP(imageSrc) {
        if (!this.supportsWebP()) {
            return imageSrc;
        }
        
        // Convert common image extensions to WebP
        return imageSrc.replace(/\.(jpg|jpeg|png)$/i, '.webp');
    }

    // Preload critical images
    static preloadImages(urls) {
        urls.forEach(url => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'image';
            link.href = url;
            document.head.appendChild(link);
        });
    }

    // Progressive image loading
    static createProgressiveImage(src, placeholder) {
        const container = document.createElement('div');
        container.className = 'progressive-image';
        
        const placeholderImg = document.createElement('img');
        placeholderImg.src = placeholder;
        placeholderImg.className = 'placeholder';
        
        const mainImg = document.createElement('img');
        mainImg.dataset.src = src;
        mainImg.className = 'main-image';
        
        container.appendChild(placeholderImg);
        container.appendChild(mainImg);
        
        return container;
    }
}

// CSS for image optimization (to be added to stylesheet)
const imageOptimizationCSS = `
    .progressive-image {
        position: relative;
        overflow: hidden;
    }
    
    .progressive-image .placeholder {
        filter: blur(5px);
        transform: scale(1.1);
        transition: opacity 0.3s;
    }
    
    .progressive-image .main-image {
        position: absolute;
        top: 0;
        left: 0;
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .progressive-image .main-image.loaded {
        opacity: 1;
    }
    
    .progressive-image .main-image.loaded + .placeholder {
        opacity: 0;
    }
    
    img.loading {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }
    
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    
    img.error {
        background-color: #f5f5f5;
        color: #999;
    }
    
    img.loaded {
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
`;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Add CSS to document
    const style = document.createElement('style');
    style.textContent = imageOptimizationCSS;
    document.head.appendChild(style);
    
    // Initialize image optimizer
    new ImageOptimizer();
    
    // Preload critical images (customize as needed)
    const criticalImages = [
        'assets/images/logos/logo.webp',
        'assets/flags/sk.webp', // Default language flag
    ];
    ImageOptimizer.preloadImages(criticalImages);
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ImageOptimizer;
}

/*!
 * HERO-OPTIMIZATIONS.JS - Performance optimizations for hero section
 * ================================================================
 * 
 * Enhanced hero section functionality:
 * - Lazy loading for images with placeholder
 * - Intersection Observer for animations
 * - Performance monitoring
 * - Progressive enhancement
 * - Accessibility enhancements
 * 
 * @author RenalTales Development Team
 * @version 2025.v1.0
 * @created 2025-01-19
 */

(function(window, document) {
    'use strict';

    // ==========================================================================
    // PERFORMANCE UTILITIES
    // ==========================================================================

    /**
     * Debounce function for performance optimization
     */
    function debounce(func, wait, immediate) {
        let timeout;
        return function executedFunction() {
            const context = this;
            const args = arguments;
            const later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }

    /**
     * Check if user prefers reduced motion
     */
    function prefersReducedMotion() {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    /**
     * Check if device supports WebP
     */
    function supportsWebP() {
        return new Promise((resolve) => {
            const webP = new Image();
            webP.onload = webP.onerror = () => {
                resolve(webP.height === 2);
            };
            webP.src = 'data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA';
        });
    }

    // ==========================================================================
    // LAZY LOADING IMPLEMENTATION
    // ==========================================================================

    class LazyImageLoader {
        constructor() {
            this.imageObserver = null;
            this.webPSupported = false;
            this.init();
        }

        async init() {
            this.webPSupported = await supportsWebP();
            this.createImageObserver();
            this.observeImages();
        }

        createImageObserver() {
            const options = {
                root: null,
                rootMargin: '50px',
                threshold: 0.1
            };

            this.imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadImage(entry.target);
                        this.imageObserver.unobserve(entry.target);
                    }
                });
            }, options);
        }

        observeImages() {
            const lazyImages = document.querySelectorAll('[data-lazy-src]');
            lazyImages.forEach(img => {
                this.imageObserver.observe(img);
            });
        }

        loadImage(img) {
            const placeholder = img.previousElementSibling;
            let srcset = img.dataset.lazySrcset;
            let src = img.dataset.lazySrc;

            // Use WebP if supported
            if (this.webPSupported && img.dataset.lazyWebp) {
                src = img.dataset.lazyWebp;
                if (img.dataset.lazyWebpSrcset) {
                    srcset = img.dataset.lazyWebpSrcset;
                }
            }

            // Create new image to preload
            const imageLoader = new Image();
            
            imageLoader.onload = () => {
                // Apply loaded image
                if (srcset) {
                    img.srcset = srcset;
                }
                img.src = src;
                img.classList.add('loaded');
                
                // Remove placeholder with fade effect
                if (placeholder && placeholder.classList.contains('hero-image-placeholder')) {
                    placeholder.style.opacity = '0';
                    placeholder.style.transition = 'opacity 300ms ease';
                    setTimeout(() => {
                        placeholder.remove();
                    }, 300);
                }

                // Trigger loaded event
                img.dispatchEvent(new CustomEvent('imageloaded', {
                    detail: { src, srcset }
                }));
            };

            imageLoader.onerror = () => {
                // Handle error gracefully
                img.classList.add('error');
                console.warn('Failed to load image:', src);
                
                if (placeholder) {
                    placeholder.textContent = 'Image unavailable';
                    placeholder.style.background = 'rgba(255, 255, 255, 0.1)';
                }
            };

            // Start loading
            if (srcset) {
                imageLoader.srcset = srcset;
            }
            imageLoader.src = src;
        }
    }

    // ==========================================================================
    // ANIMATION ENHANCEMENTS
    // ==========================================================================

    class HeroAnimations {
        constructor() {
            this.animationObserver = null;
            this.reducedMotion = prefersReducedMotion();
            this.init();
        }

        init() {
            if (this.reducedMotion) {
                this.disableAnimations();
                return;
            }

            this.createAnimationObserver();
            this.observeElements();
            this.setupInteractions();
        }

        disableAnimations() {
            const animatedElements = document.querySelectorAll('.hero-title, .hero-subtitle, .hero-description, .hero-actions');
            animatedElements.forEach(el => {
                el.style.opacity = '1';
                el.style.transform = 'none';
            });
        }

        createAnimationObserver() {
            const options = {
                root: null,
                rootMargin: '0px',
                threshold: 0.2
            };

            this.animationObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-in');
                        this.animationObserver.unobserve(entry.target);
                    }
                });
            }, options);
        }

        observeElements() {
            const animatedElements = document.querySelectorAll('[data-animate]');
            animatedElements.forEach(el => {
                this.animationObserver.observe(el);
            });
        }

        setupInteractions() {
            // Enhanced button interactions
            const heroButtons = document.querySelectorAll('.hero-btn');
            heroButtons.forEach(button => {
                this.setupButtonRipple(button);
                this.setupButtonAccessibility(button);
            });
        }

        setupButtonRipple(button) {
            button.addEventListener('click', (e) => {
                const rect = button.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                const ripple = document.createElement('span');
                ripple.className = 'ripple';
                ripple.style.cssText = `
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.3);
                    transform: scale(0);
                    animation: ripple 600ms linear;
                    pointer-events: none;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                `;
                
                button.style.position = 'relative';
                button.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        }

        setupButtonAccessibility(button) {
            // Enhanced keyboard navigation
            button.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    button.click();
                }
            });

            // Focus management
            button.addEventListener('focus', () => {
                button.setAttribute('data-focused', 'true');
            });

            button.addEventListener('blur', () => {
                button.removeAttribute('data-focused');
            });
        }
    }

    // ==========================================================================
    // PERFORMANCE MONITORING
    // ==========================================================================

    class PerformanceMonitor {
        constructor() {
            this.metrics = {};
            this.init();
        }

        init() {
            this.measureCriticalResource();
            this.setupVisibilityTracking();
            this.monitorInteractions();
        }

        measureCriticalResource() {
            // Measure hero section render time
            if ('performance' in window && 'measure' in performance) {
                const heroSection = document.querySelector('.hero-section');
                if (heroSection) {
                    const observer = new MutationObserver(() => {
                        performance.mark('hero-rendered');
                        performance.measure('hero-render-time', 'navigationStart', 'hero-rendered');
                        
                        const measure = performance.getEntriesByName('hero-render-time')[0];
                        this.metrics.heroRenderTime = measure.duration;
                        observer.disconnect();
                    });

                    observer.observe(heroSection, {
                        childList: true,
                        subtree: true
                    });
                }
            }
        }

        setupVisibilityTracking() {
            // Track how long hero is visible
            let startTime = null;
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && startTime === null) {
                        startTime = performance.now();
                    } else if (!entry.isIntersecting && startTime !== null) {
                        const viewTime = performance.now() - startTime;
                        this.metrics.heroViewTime = viewTime;
                        startTime = null;
                    }
                });
            });

            const heroSection = document.querySelector('.hero-section');
            if (heroSection) {
                observer.observe(heroSection);
            }
        }

        monitorInteractions() {
            // Track CTA button clicks
            const ctaButtons = document.querySelectorAll('.hero-btn');
            ctaButtons.forEach((button, index) => {
                button.addEventListener('click', () => {
                    this.trackEvent('hero_cta_click', {
                        button_index: index,
                        button_text: button.textContent.trim(),
                        href: button.href || null
                    });
                });
            });
        }

        trackEvent(eventName, data) {
            // Send to analytics (implement based on your analytics solution)
            if (typeof gtag !== 'undefined') {
                gtag('event', eventName, data);
            } else if (typeof ga !== 'undefined') {
                ga('send', 'event', 'Hero Section', eventName, data);
            }
            
            // Console log for development
            console.log(`Event: ${eventName}`, data);
        }

        getMetrics() {
            return this.metrics;
        }
    }

    // ==========================================================================
    // RESPONSIVE IMAGE HANDLING
    // ==========================================================================

    class ResponsiveImages {
        constructor() {
            this.breakpoints = {
                mobile: '(max-width: 767px)',
                tablet: '(min-width: 768px) and (max-width: 1023px)',
                desktop: '(min-width: 1024px)'
            };
            this.init();
        }

        init() {
            this.setupResponsiveImages();
            this.handleResize();
        }

        setupResponsiveImages() {
            const images = document.querySelectorAll('[data-responsive]');
            images.forEach(img => {
                this.updateImageSource(img);
            });
        }

        updateImageSource(img) {
            const current = this.getCurrentBreakpoint();
            const src = img.dataset[`src${current}`] || img.dataset.src;
            const srcset = img.dataset[`srcset${current}`] || img.dataset.srcset;

            if (src && img.src !== src) {
                img.src = src;
            }
            if (srcset && img.srcset !== srcset) {
                img.srcset = srcset;
            }
        }

        getCurrentBreakpoint() {
            if (window.matchMedia(this.breakpoints.mobile).matches) return 'Mobile';
            if (window.matchMedia(this.breakpoints.tablet).matches) return 'Tablet';
            return 'Desktop';
        }

        handleResize() {
            const debouncedResize = debounce(() => {
                const images = document.querySelectorAll('[data-responsive]');
                images.forEach(img => this.updateImageSource(img));
            }, 250);

            window.addEventListener('resize', debouncedResize);
        }
    }

    // ==========================================================================
    // INITIALIZATION
    // ==========================================================================

    class HeroOptimizer {
        constructor() {
            this.lazyLoader = null;
            this.animations = null;
            this.performanceMonitor = null;
            this.responsiveImages = null;
            this.init();
        }

        init() {
            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.setup());
            } else {
                this.setup();
            }
        }

        setup() {
            try {
                // Initialize components
                this.lazyLoader = new LazyImageLoader();
                this.animations = new HeroAnimations();
                this.performanceMonitor = new PerformanceMonitor();
                this.responsiveImages = new ResponsiveImages();

                // Add CSS for enhanced animations
                this.injectCSS();

                // Setup global error handling
                this.setupErrorHandling();

                console.log('Hero section optimizations initialized successfully');
            } catch (error) {
                console.error('Failed to initialize hero optimizations:', error);
            }
        }

        injectCSS() {
            const css = `
                @keyframes ripple {
                    to {
                        transform: scale(2);
                        opacity: 0;
                    }
                }
                
                .hero-image.error {
                    opacity: 0.5;
                    filter: grayscale(100%);
                }
                
                [data-animate] {
                    opacity: 0;
                    transform: translateY(30px);
                    transition: opacity 0.8s ease, transform 0.8s ease;
                }
                
                [data-animate].animate-in {
                    opacity: 1;
                    transform: translateY(0);
                }
            `;

            const style = document.createElement('style');
            style.textContent = css;
            document.head.appendChild(style);
        }

        setupErrorHandling() {
            window.addEventListener('error', (e) => {
                if (e.target.tagName === 'IMG' && e.target.closest('.hero-section')) {
                    console.warn('Hero image failed to load:', e.target.src);
                    e.target.classList.add('error');
                }
            });
        }

        // Public API
        getMetrics() {
            return this.performanceMonitor ? this.performanceMonitor.getMetrics() : {};
        }

        refresh() {
            if (this.lazyLoader) {
                this.lazyLoader.observeImages();
            }
        }
    }

    // ==========================================================================
    // EXPORT AND INITIALIZE
    // ==========================================================================

    // Initialize automatically
    const heroOptimizer = new HeroOptimizer();

    // Expose to global scope for debugging/manual control
    window.HeroOptimizer = heroOptimizer;

    // AMD/CommonJS compatibility
    if (typeof define === 'function' && define.amd) {
        define([], function() {
            return HeroOptimizer;
        });
    } else if (typeof module !== 'undefined' && module.exports) {
        module.exports = HeroOptimizer;
    }

})(window, document);

/**
 * Enhanced Feature Cards JavaScript
 * Provides animations and interactive behavior for feature cards
 */

class FeatureCards {
    constructor() {
        this.cards = document.querySelectorAll('.feature-card');
        this.isAnimating = false;
        this.observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -10% 0px'
        };

        this.init();
    }

    init() {
        if (this.cards.length === 0) return;

        this.setupIntersectionObserver();
        this.setupCardInteractions();
        this.setupShimmerEffects();
        this.setupAccessibility();
        
        // Initialize on load if reduced motion is preferred
        if (this.preferReducedMotion()) {
            this.showAllCards();
        }
    }

    setupIntersectionObserver() {
        if ('IntersectionObserver' in window) {
            this.observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting && !entry.target.classList.contains('feature-card--visible')) {
                        // Stagger the animations
                        setTimeout(() => {
                            this.animateCardIn(entry.target);
                        }, index * 150);
                    }
                });
            }, this.observerOptions);

            this.cards.forEach(card => {
                this.observer.observe(card);
            });
        } else {
            // Fallback for browsers without IntersectionObserver
            this.showAllCards();
        }
    }

    setupCardInteractions() {
        this.cards.forEach(card => {
            // Hover effects
            card.addEventListener('mouseenter', (e) => {
                if (!this.preferReducedMotion()) {
                    this.handleCardHover(e.target, true);
                }
            });

            card.addEventListener('mouseleave', (e) => {
                if (!this.preferReducedMotion()) {
                    this.handleCardHover(e.target, false);
                }
            });

            // Click effects
            const button = card.querySelector('.feature-card__btn');
            if (button) {
                button.addEventListener('click', (e) => {
                    this.handleButtonClick(e);
                });
            }

            // Focus handling for accessibility
            card.addEventListener('focus', (e) => {
                this.handleCardFocus(e.target, true);
            });

            card.addEventListener('blur', (e) => {
                this.handleCardFocus(e.target, false);
            });
        });
    }

    setupShimmerEffects() {
        if (this.preferReducedMotion()) return;

        this.cards.forEach((card, index) => {
            const shimmer = card.querySelector('.feature-card__shimmer');
            if (shimmer) {
                // Randomize shimmer timing to avoid synchronization
                setTimeout(() => {
                    shimmer.style.animationDelay = `${Math.random() * 5}s`;
                }, index * 100);
            }
        });
    }

    setupAccessibility() {
        // Enhanced keyboard navigation
        this.cards.forEach(card => {
            card.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    const button = card.querySelector('.feature-card__btn');
                    if (button) {
                        e.preventDefault();
                        button.click();
                    }
                }
            });
        });

        // Announce card visibility to screen readers
        const announcer = this.createAnnouncer();
        document.body.appendChild(announcer);
    }

    animateCardIn(card) {
        if (this.preferReducedMotion()) {
            card.classList.add('feature-card--visible');
            return;
        }

        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.classList.add('feature-card--visible');

        // Animate in
        requestAnimationFrame(() => {
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';

            // Clean up transition after animation
            setTimeout(() => {
                card.style.transition = '';
            }, 600);
        });
    }

    handleCardHover(card, isHovering) {
        const icon = card.querySelector('.feature-card__icon');
        const shimmer = card.querySelector('.feature-card__shimmer');

        if (isHovering) {
            card.classList.add('feature-card--hovered');
            if (icon) {
                icon.style.transform = 'scale(1.1) rotate(-5deg)';
            }
            if (shimmer) {
                shimmer.style.opacity = '0.3';
            }
        } else {
            card.classList.remove('feature-card--hovered');
            if (icon) {
                icon.style.transform = '';
            }
            if (shimmer) {
                shimmer.style.opacity = '';
            }
        }
    }

    handleCardFocus(card, isFocused) {
        if (isFocused) {
            card.classList.add('feature-card--focused');
            this.announceCardContent(card);
        } else {
            card.classList.remove('feature-card--focused');
        }
    }

    handleButtonClick(event) {
        if (this.preferReducedMotion()) return;

        const button = event.currentTarget;
        const ripple = this.createRipple(event);
        
        button.style.position = 'relative';
        button.appendChild(ripple);

        // Remove ripple after animation
        setTimeout(() => {
            if (ripple.parentNode) {
                ripple.parentNode.removeChild(ripple);
            }
        }, 600);
    }

    createRipple(event) {
        const button = event.currentTarget;
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;

        const ripple = document.createElement('span');
        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            pointer-events: none;
            transform: scale(0);
            animation: ripple 0.6s ease-out;
            z-index: 1;
        `;

        return ripple;
    }

    createAnnouncer() {
        const announcer = document.createElement('div');
        announcer.setAttribute('aria-live', 'polite');
        announcer.setAttribute('aria-atomic', 'true');
        announcer.className = 'sr-only';
        announcer.style.cssText = `
            position: absolute;
            left: -10000px;
            width: 1px;
            height: 1px;
            overflow: hidden;
        `;
        return announcer;
    }

    announceCardContent(card) {
        const title = card.querySelector('.feature-card__title');
        const description = card.querySelector('.feature-card__description');
        const announcer = document.querySelector('[aria-live="polite"]');

        if (announcer && title) {
            const announcement = `${title.textContent.trim()}. ${description ? description.textContent.trim() : ''}`;
            announcer.textContent = announcement;
        }
    }

    showAllCards() {
        this.cards.forEach(card => {
            card.classList.add('feature-card--visible');
        });
    }

    preferReducedMotion() {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    // Public method to manually trigger card animations (if needed)
    triggerAnimation(cardSelector) {
        const card = document.querySelector(cardSelector);
        if (card && !card.classList.contains('feature-card--visible')) {
            this.animateCardIn(card);
        }
    }

    // Cleanup method
    destroy() {
        if (this.observer) {
            this.observer.disconnect();
        }

        this.cards.forEach(card => {
            card.classList.remove('feature-card--visible', 'feature-card--hovered', 'feature-card--focused');
        });
    }
}

// CSS for animations (will be injected if not present in CSS)
const featureCardStyles = `
    @keyframes ripple {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }

    .feature-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .feature-card--hovered {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .feature-card--focused {
        outline: 2px solid var(--color-primary, #3b82f6);
        outline-offset: 2px;
    }

    .feature-card__icon {
        transition: transform 0.3s ease;
    }

    .feature-card__btn {
        overflow: hidden;
    }

    @media (prefers-reduced-motion: reduce) {
        .feature-card,
        .feature-card__icon,
        .feature-card__shimmer {
            transition: none !important;
            animation: none !important;
        }
    }
`;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Inject styles if not present
    if (!document.querySelector('#feature-card-styles')) {
        const styleSheet = document.createElement('style');
        styleSheet.id = 'feature-card-styles';
        styleSheet.textContent = featureCardStyles;
        document.head.appendChild(styleSheet);
    }

    // Initialize feature cards
    window.featureCards = new FeatureCards();
});

// Re-initialize if new cards are added dynamically
window.addEventListener('load', () => {
    if (window.featureCards) {
        window.featureCards.destroy();
        window.featureCards = new FeatureCards();
    }
});

/**
 * RenalTales - Modern Frontend JavaScript
 * =======================================
 * 
 * This file contains all the interactive functionality for the RenalTales application.
 * It uses modern ES6+ JavaScript features and focuses on accessibility, performance,
 * and user experience.
 */

'use strict';

// Application namespace
const RenalTales = {
  // Configuration
  config: {
    debounceDelay: 300,
    animationDuration: 300,
    toastDuration: 5000,
    apiEndpoints: {
      stories: '/api/stories',
      users: '/api/users',
      languages: '/api/languages'
    }
  },

  // Utility functions
  utils: {
    /**
     * Debounce function to limit the rate of function execution
     */
    debounce(func, wait) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    },

    /**
     * Throttle function to limit function execution frequency
     */
    throttle(func, limit) {
      let inThrottle;
      return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
          func.apply(context, args);
          inThrottle = true;
          setTimeout(() => inThrottle = false, limit);
        }
      };
    },

    /**
     * Safe query selector that returns null if element not found
     */
    $(selector, context = document) {
      return context.querySelector(selector);
    },

    /**
     * Safe query selector all
     */
    $$(selector, context = document) {
      return Array.from(context.querySelectorAll(selector));
    },

    /**
     * Add event listener with automatic cleanup
     */
    on(element, event, handler, options = {}) {
      if (element) {
        element.addEventListener(event, handler, options);
        return () => element.removeEventListener(event, handler, options);
      }
      return () => {};
    },

    /**
     * Create element with attributes and content
     */
    createElement(tag, attributes = {}, content = '') {
      const element = document.createElement(tag);
      
      Object.entries(attributes).forEach(([key, value]) => {
        if (key === 'className') {
          element.className = value;
        } else if (key === 'data') {
          Object.entries(value).forEach(([dataKey, dataValue]) => {
            element.dataset[dataKey] = dataValue;
          });
        } else {
          element.setAttribute(key, value);
        }
      });

      if (content) {
        element.innerHTML = content;
      }

      return element;
    },

    /**
     * Animate element with CSS transitions
     */
    animate(element, properties, duration = 300) {
      return new Promise(resolve => {
        if (!element) {
          resolve();
          return;
        }

        const originalTransition = element.style.transition;
        element.style.transition = `all ${duration}ms ease-in-out`;

        Object.entries(properties).forEach(([prop, value]) => {
          element.style[prop] = value;
        });

        setTimeout(() => {
          element.style.transition = originalTransition;
          resolve();
        }, duration);
      });
    },

    /**
     * Format date for display
     */
    formatDate(date, options = {}) {
      const defaultOptions = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        ...options
      };
      return new Intl.DateTimeFormat(document.documentElement.lang || 'en', defaultOptions).format(date);
    },

    /**
     * Sanitize HTML to prevent XSS
     */
    sanitizeHTML(str) {
      const temp = document.createElement('div');
      temp.textContent = str;
      return temp.innerHTML;
    }
  },

  // Component modules
  components: {
    /**
     * Navigation Component
     * Handles mobile menu, dropdown interactions, and active states
     */
    navigation: {
      init() {
        this.setupMobileMenu();
        this.setupDropdowns();
        this.setupActiveStates();
      },

      setupMobileMenu() {
        const toggleButton = RenalTales.utils.$('.navbar-toggler');
        const navMenu = RenalTales.utils.$('.navbar-collapse');

        if (toggleButton && navMenu) {
          RenalTales.utils.on(toggleButton, 'click', () => {
            const isExpanded = toggleButton.getAttribute('aria-expanded') === 'true';
            
            toggleButton.setAttribute('aria-expanded', !isExpanded);
            navMenu.classList.toggle('show');
            
            // Animate mobile menu
            if (!isExpanded) {
              navMenu.style.maxHeight = navMenu.scrollHeight + 'px';
            } else {
              navMenu.style.maxHeight = '0';
            }
          });

          // Close mobile menu when clicking outside
          RenalTales.utils.on(document, 'click', (e) => {
            if (!toggleButton.contains(e.target) && !navMenu.contains(e.target)) {
              toggleButton.setAttribute('aria-expanded', 'false');
              navMenu.classList.remove('show');
              navMenu.style.maxHeight = '0';
            }
          });
        }
      },

      setupDropdowns() {
        const dropdowns = RenalTales.utils.$$('.dropdown');

        dropdowns.forEach(dropdown => {
          const toggle = RenalTales.utils.$('.dropdown-toggle', dropdown);
          const menu = RenalTales.utils.$('.dropdown-menu', dropdown);

          if (toggle && menu) {
            // Toggle dropdown on click
            RenalTales.utils.on(toggle, 'click', (e) => {
              e.preventDefault();
              this.toggleDropdown(dropdown);
            });

            // Handle keyboard navigation
            RenalTales.utils.on(toggle, 'keydown', (e) => {
              if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.toggleDropdown(dropdown);
              } else if (e.key === 'Escape') {
                this.closeDropdown(dropdown);
              }
            });

            // Close dropdown when clicking outside
            RenalTales.utils.on(document, 'click', (e) => {
              if (!dropdown.contains(e.target)) {
                this.closeDropdown(dropdown);
              }
            });
          }
        });
      },

      toggleDropdown(dropdown) {
        const isOpen = dropdown.classList.contains('show');
        
        // Close all other dropdowns
        RenalTales.utils.$$('.dropdown.show').forEach(dd => {
          if (dd !== dropdown) {
            this.closeDropdown(dd);
          }
        });

        if (isOpen) {
          this.closeDropdown(dropdown);
        } else {
          this.openDropdown(dropdown);
        }
      },

      openDropdown(dropdown) {
        dropdown.classList.add('show');
        const toggle = RenalTales.utils.$('.dropdown-toggle', dropdown);
        if (toggle) {
          toggle.setAttribute('aria-expanded', 'true');
        }
      },

      closeDropdown(dropdown) {
        dropdown.classList.remove('show');
        const toggle = RenalTales.utils.$('.dropdown-toggle', dropdown);
        if (toggle) {
          toggle.setAttribute('aria-expanded', 'false');
        }
      },

      setupActiveStates() {
        const currentPath = window.location.pathname;
        const navLinks = RenalTales.utils.$$('.nav-link');

        navLinks.forEach(link => {
          const href = link.getAttribute('href');
          if (href && (currentPath === href || currentPath.startsWith(href + '/'))) {
            link.classList.add('active');
          }
        });
      }
    },

    /**
     * Form Component
     * Handles form validation, submission, and user feedback
     */
    forms: {
      init() {
        this.setupValidation();
        this.setupPasswordStrength();
        this.setupFileUploads();
        this.setupAutoResize();
      },

      setupValidation() {
        const forms = RenalTales.utils.$$('form[data-validate]');

        forms.forEach(form => {
          RenalTales.utils.on(form, 'submit', (e) => {
            if (!this.validateForm(form)) {
              e.preventDefault();
              e.stopPropagation();
            }
            form.classList.add('was-validated');
          });

          // Real-time validation
          const inputs = RenalTales.utils.$$('input, select, textarea', form);
          inputs.forEach(input => {
            RenalTales.utils.on(input, 'blur', () => {
              this.validateField(input);
            });

            RenalTales.utils.on(input, 'input', RenalTales.utils.debounce(() => {
              if (input.classList.contains('is-invalid') || input.classList.contains('is-valid')) {
                this.validateField(input);
              }
            }, RenalTales.config.debounceDelay));
          });
        });
      },

      validateForm(form) {
        const inputs = RenalTales.utils.$$('input, select, textarea', form);
        let isValid = true;

        inputs.forEach(input => {
          if (!this.validateField(input)) {
            isValid = false;
          }
        });

        return isValid;
      },

      validateField(field) {
        const value = field.value.trim();
        const type = field.type;
        const required = field.hasAttribute('required');
        let isValid = true;
        let message = '';

        // Clear previous state
        field.classList.remove('is-valid', 'is-invalid');
        this.clearFieldMessage(field);

        // Required validation
        if (required && !value) {
          isValid = false;
          message = 'This field is required';
        }

        // Type-specific validation
        if (value && isValid) {
          switch (type) {
            case 'email':
              const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
              if (!emailRegex.test(value)) {
                isValid = false;
                message = 'Please enter a valid email address';
              }
              break;

            case 'password':
              if (value.length < 8) {
                isValid = false;
                message = 'Password must be at least 8 characters long';
              }
              break;

            case 'url':
              try {
                new URL(value);
              } catch {
                isValid = false;
                message = 'Please enter a valid URL';
              }
              break;
          }
        }

        // Custom validation
        const pattern = field.getAttribute('pattern');
        if (pattern && value && isValid) {
          const regex = new RegExp(pattern);
          if (!regex.test(value)) {
            isValid = false;
            message = field.getAttribute('data-error-message') || 'Invalid format';
          }
        }

        // Min/max length
        const minLength = field.getAttribute('minlength');
        const maxLength = field.getAttribute('maxlength');
        
        if (minLength && value.length < parseInt(minLength)) {
          isValid = false;
          message = `Minimum ${minLength} characters required`;
        }
        
        if (maxLength && value.length > parseInt(maxLength)) {
          isValid = false;
          message = `Maximum ${maxLength} characters allowed`;
        }

        // Apply validation state
        field.classList.add(isValid ? 'is-valid' : 'is-invalid');
        
        if (!isValid) {
          this.showFieldMessage(field, message, 'invalid');
        }

        return isValid;
      },

      showFieldMessage(field, message, type = 'invalid') {
        const feedbackClass = type === 'valid' ? 'valid-feedback' : 'invalid-feedback';
        let feedback = field.parentNode.querySelector(`.${feedbackClass}`);
        
        if (!feedback) {
          feedback = RenalTales.utils.createElement('div', {
            className: feedbackClass
          });
          field.parentNode.appendChild(feedback);
        }
        
        feedback.textContent = message;
      },

      clearFieldMessage(field) {
        const feedback = field.parentNode.querySelector('.invalid-feedback, .valid-feedback');
        if (feedback) {
          feedback.remove();
        }
      },

      setupPasswordStrength() {
        const passwordFields = RenalTales.utils.$$('input[type="password"][data-strength]');

        passwordFields.forEach(field => {
          const strengthMeter = this.createPasswordStrengthMeter();
          field.parentNode.appendChild(strengthMeter);

          RenalTales.utils.on(field, 'input', () => {
            const strength = this.calculatePasswordStrength(field.value);
            this.updatePasswordStrengthMeter(strengthMeter, strength);
          });
        });
      },

      createPasswordStrengthMeter() {
        const meter = RenalTales.utils.createElement('div', {
          className: 'password-strength-meter mt-2'
        });

        const bar = RenalTales.utils.createElement('div', {
          className: 'password-strength-bar'
        });

        const text = RenalTales.utils.createElement('small', {
          className: 'password-strength-text text-muted'
        });

        meter.appendChild(bar);
        meter.appendChild(text);

        return meter;
      },

      calculatePasswordStrength(password) {
        let score = 0;
        const checks = [
          password.length >= 8,
          /[a-z]/.test(password),
          /[A-Z]/.test(password),
          /\d/.test(password),
          /[^a-zA-Z\d]/.test(password),
          password.length >= 12
        ];

        score = checks.filter(Boolean).length;

        if (score < 3) return { level: 'weak', text: 'Weak' };
        if (score < 5) return { level: 'medium', text: 'Medium' };
        return { level: 'strong', text: 'Strong' };
      },

      updatePasswordStrengthMeter(meter, strength) {
        const bar = meter.querySelector('.password-strength-bar');
        const text = meter.querySelector('.password-strength-text');

        bar.className = `password-strength-bar strength-${strength.level}`;
        text.textContent = `Password strength: ${strength.text}`;
      },

      setupFileUploads() {
        const fileInputs = RenalTales.utils.$$('input[type="file"]');

        fileInputs.forEach(input => {
          RenalTales.utils.on(input, 'change', (e) => {
            const files = Array.from(e.target.files);
            this.handleFileUpload(input, files);
          });
        });
      },

      handleFileUpload(input, files) {
        const maxSize = input.getAttribute('data-max-size') || 5 * 1024 * 1024; // 5MB default
        const allowedTypes = input.getAttribute('data-allowed-types')?.split(',') || [];

        files.forEach(file => {
          if (file.size > maxSize) {
            RenalTales.notifications.show('File size too large', 'error');
            return;
          }

          if (allowedTypes.length > 0 && !allowedTypes.includes(file.type)) {
            RenalTales.notifications.show('File type not allowed', 'error');
            return;
          }

          // Show file preview if it's an image
          if (file.type.startsWith('image/')) {
            this.showImagePreview(input, file);
          }
        });
      },

      showImagePreview(input, file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          let preview = input.parentNode.querySelector('.file-preview');
          if (!preview) {
            preview = RenalTales.utils.createElement('div', {
              className: 'file-preview mt-2'
            });
            input.parentNode.appendChild(preview);
          }

          preview.innerHTML = `
            <img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
            <p class="small text-muted mt-1">${file.name} (${(file.size / 1024).toFixed(1)} KB)</p>
          `;
        };
        reader.readAsDataURL(file);
      },

      setupAutoResize() {
        const textareas = RenalTales.utils.$$('textarea[data-auto-resize]');

        textareas.forEach(textarea => {
          const autoResize = () => {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
          };

          RenalTales.utils.on(textarea, 'input', autoResize);
          RenalTales.utils.on(textarea, 'focus', autoResize);
          
          // Initial resize
          autoResize();
        });
      }
    },

    /**
     * Story Component
     * Handles story interactions, reading progress, and sharing
     */
    stories: {
      init() {
        this.setupReadingProgress();
        this.setupStoryActions();
        this.setupInfiniteScroll();
      },

      setupReadingProgress() {
        const storyContent = RenalTales.utils.$('.story-content');
        if (!storyContent) return;

        const progressBar = this.createProgressBar();
        document.body.appendChild(progressBar);

        const updateProgress = RenalTales.utils.throttle(() => {
          const scrollTop = window.pageYOffset;
          const docHeight = document.documentElement.scrollHeight - window.innerHeight;
          const progress = (scrollTop / docHeight) * 100;

          progressBar.style.width = Math.min(progress, 100) + '%';
        }, 16);

        RenalTales.utils.on(window, 'scroll', updateProgress);
      },

      createProgressBar() {
        return RenalTales.utils.createElement('div', {
          className: 'reading-progress',
          style: 'position: fixed; top: 0; left: 0; width: 0%; height: 3px; background: var(--primary-600); z-index: 9999; transition: width 0.3s ease;'
        });
      },

      setupStoryActions() {
        // Like/bookmark buttons
        const actionButtons = RenalTales.utils.$$('[data-story-action]');

        actionButtons.forEach(button => {
          RenalTales.utils.on(button, 'click', async (e) => {
            e.preventDefault();
            const action = button.dataset.storyAction;
            const storyId = button.dataset.storyId;

            try {
              await this.handleStoryAction(action, storyId, button);
            } catch (error) {
              RenalTales.notifications.show('Action failed. Please try again.', 'error');
            }
          });
        });

        // Share buttons
        const shareButtons = RenalTales.utils.$$('[data-share]');
        shareButtons.forEach(button => {
          RenalTales.utils.on(button, 'click', (e) => {
            e.preventDefault();
            this.handleShare(button.dataset.share);
          });
        });
      },

      async handleStoryAction(action, storyId, button) {
        // Simulate API call
        const response = await fetch(`/api/stories/${storyId}/${action}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
          }
        });

        if (response.ok) {
          const data = await response.json();
          this.updateActionButton(button, data);
          RenalTales.notifications.show(data.message, 'success');
        }
      },

      updateActionButton(button, data) {
        const icon = button.querySelector('i');
        const text = button.querySelector('.btn-text');

        if (data.active) {
          button.classList.add('active');
          if (icon) icon.className = icon.className.replace('far', 'fas');
        } else {
          button.classList.remove('active');
          if (icon) icon.className = icon.className.replace('fas', 'far');
        }

        if (text && data.count !== undefined) {
          text.textContent = data.count;
        }
      },

      handleShare(platform) {
        const url = encodeURIComponent(window.location.href);
        const title = encodeURIComponent(document.title);
        
        let shareUrl;
        
        switch (platform) {
          case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
            break;
          case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
            break;
          case 'linkedin':
            shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${url}`;
            break;
          case 'copy':
            this.copyToClipboard(window.location.href);
            return;
        }

        if (shareUrl) {
          window.open(shareUrl, '_blank', 'width=600,height=400');
        }
      },

      async copyToClipboard(text) {
        try {
          await navigator.clipboard.writeText(text);
          RenalTales.notifications.show('Link copied to clipboard!', 'success');
        } catch (error) {
          RenalTales.notifications.show('Failed to copy link', 'error');
        }
      },

      setupInfiniteScroll() {
        const storiesContainer = RenalTales.utils.$('[data-infinite-scroll]');
        if (!storiesContainer) return;

        const loadMore = RenalTales.utils.throttle(async () => {
          if (this.isNearBottom() && !this.loading) {
            await this.loadMoreStories(storiesContainer);
          }
        }, 250);

        RenalTales.utils.on(window, 'scroll', loadMore);
      },

      isNearBottom(threshold = 200) {
        return window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - threshold;
      },

      async loadMoreStories(container) {
        if (this.loading) return;

        this.loading = true;
        const page = parseInt(container.dataset.page || '1') + 1;

        try {
          const response = await fetch(`${RenalTales.config.apiEndpoints.stories}?page=${page}`);
          const data = await response.json();

          if (data.stories && data.stories.length > 0) {
            data.stories.forEach(story => {
              const storyElement = this.createStoryElement(story);
              container.appendChild(storyElement);
            });

            container.dataset.page = page;
          }
        } catch (error) {
          RenalTales.notifications.show('Failed to load more stories', 'error');
        } finally {
          this.loading = false;
        }
      },

      createStoryElement(story) {
        return RenalTales.utils.createElement('article', {
          className: 'story-card fade-in'
        }, `
          <div class="story-meta">
            <span class="category-badge">${RenalTales.utils.sanitizeHTML(story.category)}</span>
            <time datetime="${story.created_at}">${RenalTales.utils.formatDate(new Date(story.created_at))}</time>
          </div>
          <h3><a href="/stories/${story.id}">${RenalTales.utils.sanitizeHTML(story.title)}</a></h3>
          <p>${RenalTales.utils.sanitizeHTML(story.excerpt)}</p>
          <div class="story-actions">
            <button data-story-action="like" data-story-id="${story.id}" class="btn btn-sm btn-secondary">
              <i class="far fa-heart"></i> ${story.likes_count || 0}
            </button>
          </div>
        `);
      }
    },

    /**
     * Language Flags Component
     * Handles language flag interactions and accessibility
     */
    languageFlags: {
      init() {
        this.setupFlagInteractions();
        this.setupKeyboardNavigation();
        this.setupAccessibility();
      },

      setupFlagInteractions() {
        // Add click handlers for footer flags that don't have links
        const footerFlags = RenalTales.utils.$$('footer .language-flag[onclick]');
        footerFlags.forEach(flag => {
          // Add keyboard support
          flag.setAttribute('tabindex', '0');
          flag.setAttribute('role', 'button');
          
          // Add keyboard event handler
          RenalTales.utils.on(flag, 'keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
              e.preventDefault();
              flag.click();
            }
          });

          // Add hover effect enhancement
          RenalTales.utils.on(flag, 'mouseenter', () => {
            flag.style.transform = 'scale(1.05)';
          });
          
          RenalTales.utils.on(flag, 'mouseleave', () => {
            flag.style.transform = 'scale(1)';
          });
        });
      },

      setupKeyboardNavigation() {
        // Improve dropdown keyboard navigation
        const languageSelector = RenalTales.utils.$('.language-selector');
        if (!languageSelector) return;

        const dropdownButton = languageSelector.querySelector('button');
        const dropdownMenu = languageSelector.querySelector('.dropdown-menu');
        const dropdownItems = languageSelector.querySelectorAll('.dropdown-item');

        if (dropdownButton && dropdownMenu && dropdownItems.length > 0) {
          // Handle arrow key navigation
          RenalTales.utils.on(dropdownButton, 'keydown', (e) => {
            if (e.key === 'ArrowDown') {
              e.preventDefault();
              if (!dropdownMenu.classList.contains('show')) {
                this.openDropdown(dropdownMenu, dropdownButton);
              }
              dropdownItems[0].focus();
            }
          });

          dropdownItems.forEach((item, index) => {
            RenalTales.utils.on(item, 'keydown', (e) => {
              switch (e.key) {
                case 'ArrowDown':
                  e.preventDefault();
                  const nextIndex = (index + 1) % dropdownItems.length;
                  dropdownItems[nextIndex].focus();
                  break;
                case 'ArrowUp':
                  e.preventDefault();
                  const prevIndex = index === 0 ? dropdownItems.length - 1 : index - 1;
                  dropdownItems[prevIndex].focus();
                  break;
                case 'Escape':
                  e.preventDefault();
                  this.closeDropdown(dropdownMenu, dropdownButton);
                  dropdownButton.focus();
                  break;
                case 'Home':
                  e.preventDefault();
                  dropdownItems[0].focus();
                  break;
                case 'End':
                  e.preventDefault();
                  dropdownItems[dropdownItems.length - 1].focus();
                  break;
              }
            });
          });
        }
      },

      setupAccessibility() {
        // Add ARIA labels and descriptions to language flags
        const allFlags = RenalTales.utils.$$('.language-flag');
        allFlags.forEach(flag => {
          const alt = flag.getAttribute('alt');
          const title = flag.getAttribute('title');
          
          if (alt && !flag.getAttribute('aria-label')) {
            flag.setAttribute('aria-label', `Switch to ${title || alt}`);
          }
        });

        // Add live region announcements for language changes
        const languageLinks = RenalTales.utils.$$('a[href*="/lang/"]');
        languageLinks.forEach(link => {
          RenalTales.utils.on(link, 'click', () => {
            const flag = link.querySelector('.language-flag');
            if (flag) {
              const language = flag.getAttribute('title') || flag.getAttribute('alt');
              if (RenalTales.accessibility && RenalTales.accessibility.announce) {
                RenalTales.accessibility.announce(`Switching to ${language}`);
              }
            }
          });
        });
      },

      openDropdown(menu, button) {
        menu.classList.add('show');
        button.setAttribute('aria-expanded', 'true');
      },

      closeDropdown(menu, button) {
        menu.classList.remove('show');
        button.setAttribute('aria-expanded', 'false');
      }
    },

    /**
     * Search Component
     * Handles search functionality with autocomplete and filters
     */
    search: {
      init() {
        this.setupSearchForm();
        this.setupFilters();
        this.setupAutocomplete();
      },

      setupSearchForm() {
        const searchForm = RenalTales.utils.$('form[data-search]');
        const searchInput = RenalTales.utils.$('input[data-search-input]');

        if (searchForm && searchInput) {
          RenalTales.utils.on(searchInput, 'input', RenalTales.utils.debounce((e) => {
            this.performSearch(e.target.value);
          }, RenalTales.config.debounceDelay));

          RenalTales.utils.on(searchForm, 'submit', (e) => {
            e.preventDefault();
            this.performSearch(searchInput.value);
          });
        }
      },

      async performSearch(query) {
        if (query.length < 2) return;

        const resultsContainer = RenalTales.utils.$('[data-search-results]');
        if (!resultsContainer) return;

        try {
          resultsContainer.innerHTML = '<div class="loading">Searching...</div>';
          
          const response = await fetch(`/api/search?q=${encodeURIComponent(query)}`);
          const data = await response.json();

          this.displaySearchResults(resultsContainer, data.results);
        } catch (error) {
          resultsContainer.innerHTML = '<div class="text-error">Search failed. Please try again.</div>';
        }
      },

      displaySearchResults(container, results) {
        if (results.length === 0) {
          container.innerHTML = '<div class="text-muted">No results found.</div>';
          return;
        }

        const resultElements = results.map(result => `
          <div class="search-result">
            <h4><a href="${result.url}">${RenalTales.utils.sanitizeHTML(result.title)}</a></h4>
            <p>${RenalTales.utils.sanitizeHTML(result.excerpt)}</p>
            <small class="text-muted">${result.type} • ${RenalTales.utils.formatDate(new Date(result.date))}</small>
          </div>
        `).join('');

        container.innerHTML = resultElements;
      },

      setupFilters() {
        const filterButtons = RenalTales.utils.$$('[data-filter]');

        filterButtons.forEach(button => {
          RenalTales.utils.on(button, 'click', (e) => {
            e.preventDefault();
            this.toggleFilter(button);
          });
        });
      },

      toggleFilter(button) {
        const filter = button.dataset.filter;
        const isActive = button.classList.contains('active');

        if (isActive) {
          button.classList.remove('active');
          this.removeFilter(filter);
        } else {
          button.classList.add('active');
          this.addFilter(filter);
        }

        this.updateFilteredResults();
      },

      addFilter(filter) {
        if (!this.activeFilters) this.activeFilters = new Set();
        this.activeFilters.add(filter);
      },

      removeFilter(filter) {
        if (this.activeFilters) {
          this.activeFilters.delete(filter);
        }
      },

      updateFilteredResults() {
        const items = RenalTales.utils.$$('[data-filterable]');
        
        items.forEach(item => {
          const itemFilters = item.dataset.filterable.split(',');
          const shouldShow = !this.activeFilters || 
            this.activeFilters.size === 0 || 
            [...this.activeFilters].some(filter => itemFilters.includes(filter));

          item.style.display = shouldShow ? '' : 'none';
        });
      },

      setupAutocomplete() {
        const autocompleteInputs = RenalTales.utils.$$('[data-autocomplete]');

        autocompleteInputs.forEach(input => {
          const dropdown = this.createAutocompleteDropdown(input);
          
          RenalTales.utils.on(input, 'input', RenalTales.utils.debounce(async (e) => {
            const query = e.target.value;
            if (query.length >= 2) {
              const suggestions = await this.getAutocompleteSuggestions(query, input.dataset.autocomplete);
              this.showAutocompleteSuggestions(dropdown, suggestions, input);
            } else {
              this.hideAutocompleteSuggestions(dropdown);
            }
          }, 200));

          RenalTales.utils.on(input, 'blur', () => {
            setTimeout(() => this.hideAutocompleteSuggestions(dropdown), 200);
          });
        });
      },

      createAutocompleteDropdown(input) {
        const dropdown = RenalTales.utils.createElement('div', {
          className: 'autocomplete-dropdown'
        });

        input.parentNode.style.position = 'relative';
        input.parentNode.appendChild(dropdown);

        return dropdown;
      },

      async getAutocompleteSuggestions(query, type) {
        try {
          const response = await fetch(`/api/autocomplete/${type}?q=${encodeURIComponent(query)}`);
          return await response.json();
        } catch (error) {
          return [];
        }
      },

      showAutocompleteSuggestions(dropdown, suggestions, input) {
        if (suggestions.length === 0) {
          this.hideAutocompleteSuggestions(dropdown);
          return;
        }

        const items = suggestions.map(suggestion => `
          <div class="autocomplete-item" data-value="${RenalTales.utils.sanitizeHTML(suggestion.value)}">
            ${RenalTales.utils.sanitizeHTML(suggestion.label)}
          </div>
        `).join('');

        dropdown.innerHTML = items;
        dropdown.style.display = 'block';

        // Handle item clicks
        RenalTales.utils.$$('.autocomplete-item', dropdown).forEach(item => {
          RenalTales.utils.on(item, 'click', () => {
            input.value = item.dataset.value;
            this.hideAutocompleteSuggestions(dropdown);
            input.focus();
          });
        });
      },

      hideAutocompleteSuggestions(dropdown) {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
      }
    }
  },

  /**
   * Notification System
   * Shows toast notifications for user feedback
   */
  notifications: {
    container: null,

    init() {
      this.createContainer();
    },

    createContainer() {
      this.container = RenalTales.utils.createElement('div', {
        className: 'toast-container',
        style: 'position: fixed; top: 20px; right: 20px; z-index: 9999;'
      });
      document.body.appendChild(this.container);
    },

    show(message, type = 'info', duration = RenalTales.config.toastDuration) {
      const toast = this.createToast(message, type);
      this.container.appendChild(toast);

      // Animate in
      requestAnimationFrame(() => {
        toast.style.transform = 'translateX(0)';
        toast.style.opacity = '1';
      });

      // Auto remove
      setTimeout(() => {
        this.remove(toast);
      }, duration);

      return toast;
    },

    createToast(message, type) {
      const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
      };

      const toast = RenalTales.utils.createElement('div', {
        className: `alert alert-${type} toast-notification`,
        style: 'margin-bottom: 10px; transform: translateX(100%); opacity: 0; transition: all 0.3s ease; max-width: 400px;'
      }, `
        <div class="flex items-center">
          <i class="${icons[type]} mr-2"></i>
          <span class="flex-1">${RenalTales.utils.sanitizeHTML(message)}</span>
          <button class="toast-close ml-3" aria-label="Close">
            <i class="fas fa-times"></i>
          </button>
        </div>
      `);

      // Handle close button
      const closeBtn = toast.querySelector('.toast-close');
      RenalTales.utils.on(closeBtn, 'click', () => {
        this.remove(toast);
      });

      return toast;
    },

    remove(toast) {
      toast.style.transform = 'translateX(100%)';
      toast.style.opacity = '0';
      
      setTimeout(() => {
        if (toast.parentNode) {
          toast.parentNode.removeChild(toast);
        }
      }, 300);
    }
  },

  /**
   * Accessibility Enhancements
   */
  accessibility: {
    init() {
      this.setupKeyboardNavigation();
      this.setupFocusManagement();
      this.setupScreenReaderSupport();
    },

    setupKeyboardNavigation() {
      // Skip to main content link
      const skipLink = RenalTales.utils.createElement('a', {
        href: '#main-content',
        className: 'sr-only-focusable',
        style: 'position: absolute; top: 10px; left: 10px; z-index: 9999; padding: 8px 16px; background: var(--primary-600); color: white; text-decoration: none; border-radius: 4px;'
      }, 'Skip to main content');

      document.body.insertBefore(skipLink, document.body.firstChild);

      // Handle escape key to close modals/dropdowns
      RenalTales.utils.on(document, 'keydown', (e) => {
        if (e.key === 'Escape') {
          // Close any open dropdowns
          RenalTales.utils.$$('.dropdown.show').forEach(dropdown => {
            RenalTales.components.navigation.closeDropdown(dropdown);
          });
        }
      });
    },

    setupFocusManagement() {
      // Track focus for better outline management
      let hadKeyboardEvent = true;

      RenalTales.utils.on(document, 'keydown', () => {
        hadKeyboardEvent = true;
      });

      RenalTales.utils.on(document, 'mousedown', () => {
        hadKeyboardEvent = false;
      });

      RenalTales.utils.on(document, 'focusin', (e) => {
        if (hadKeyboardEvent) {
          e.target.classList.add('keyboard-focused');
        }
      });

      RenalTales.utils.on(document, 'focusout', (e) => {
        e.target.classList.remove('keyboard-focused');
      });
    },

    setupScreenReaderSupport() {
      // Add live region for dynamic content updates
      const liveRegion = RenalTales.utils.createElement('div', {
        id: 'live-region',
        'aria-live': 'polite',
        'aria-atomic': 'true',
        className: 'sr-only'
      });
      document.body.appendChild(liveRegion);

      // Method to announce messages to screen readers
      this.announce = (message) => {
        liveRegion.textContent = message;
        setTimeout(() => {
          liveRegion.textContent = '';
        }, 1000);
      };
    }
  },

  /**
   * Performance optimizations
   */
  performance: {
    init() {
      this.setupLazyLoading();
      this.setupImageOptimization();
    },

    setupLazyLoading() {
      if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              const img = entry.target;
              img.src = img.dataset.src;
              img.classList.remove('lazy');
              imageObserver.unobserve(img);
            }
          });
        });

        RenalTales.utils.$$('img[data-src]').forEach(img => {
          imageObserver.observe(img);
        });
      }
    },

    setupImageOptimization() {
      // Add loading="lazy" to images below the fold
      RenalTales.utils.$$('img').forEach((img, index) => {
        if (index > 2) { // Skip first few images
          img.loading = 'lazy';
        }
      });
    }
  },

  /**
   * Initialize the application
   */
  init() {
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.start());
    } else {
      this.start();
    }
  },

  start() {
    try {
      // Initialize all components
      this.notifications.init();
      this.components.navigation.init();
      this.components.forms.init();
      this.components.stories.init();
      this.components.search.init();
      this.components.languageFlags.init();
      this.accessibility.init();
      this.performance.init();

      // Add global error handler
      window.addEventListener('error', (e) => {
        console.error('Global error:', e.error);
        this.notifications.show('An unexpected error occurred', 'error');
      });

      // Add unhandled promise rejection handler
      window.addEventListener('unhandledrejection', (e) => {
        console.error('Unhandled promise rejection:', e.reason);
        this.notifications.show('An unexpected error occurred', 'error');
      });

      console.log('RenalTales application initialized successfully');
    } catch (error) {
      console.error('Failed to initialize RenalTales application:', error);
    }
  }
};

// Initialize the application
RenalTales.init();

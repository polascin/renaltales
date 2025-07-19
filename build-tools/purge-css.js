// purge-css.js
// Script to remove unused CSS using PurgeCSS
const { PurgeCSS } = require('purgecss');
const fs = require('fs');
const path = require('path');

const purgeCSSOptions = {
  content: [
    'public/**/*.php',
    'src/**/*.php',
    'resources/**/*.php',
    '**/*.html',
    '**/*.js',
    // Add more content patterns as needed
  ],
  css: ['public/assets/css/dist/compiled.css'],
  defaultExtractor: content => content.match(/[\w-/:]+(?<!:)/g) || [],
  safelist: {
    standard: [
      // Theme classes
      'theme-dark',
      'theme-light',
      
      // Dynamic classes
      'show',
      'hide',
      'active',
      'inactive',
      'loading',
      'error',
      'success',
      'warning',
      'info',
      
      // Language switcher classes
      'language-switcher',
      'language-form',
      'language-select',
      'language-label',
      
      // Navigation classes
      'nav-toggle',
      'nav-menu',
      'nav-item',
      'nav-link',
      'hamburger',
      
      // Modal and overlay classes
      'modal',
      'overlay',
      'backdrop',
      
      // Animation classes
      'fade-in',
      'fade-out',
      'slide-in',
      'slide-out',
      
      // Accessibility classes
      'sr-only',
      'focus-visible',
      'focus-within',
      
      // State classes
      'is-loading',
      'is-error',
      'is-success',
      'is-warning',
      'is-info',
      'is-disabled',
      'is-active',
      'is-open',
      'is-closed'
    ],
    deep: [
      // Dynamic selectors that might be generated
      /theme-/,
      /lang-/,
      /nav-/,
      /btn-/,
      /form-/,
      /card-/,
      /alert-/,
      /badge-/,
      /spinner-/,
      /tooltip-/,
      /dropdown-/,
      /modal-/,
      /tab-/,
      /accordion-/,
      /carousel-/,
      /progress-/,
      /rating-/,
      /breadcrumb-/,
      /pagination-/,
      /table-/,
      /list-/,
      /grid-/,
      /flex-/,
      /text-/,
      /bg-/,
      /border-/,
      /shadow-/,
      /rounded-/,
      /p-/,
      /m-/,
      /w-/,
      /h-/,
      /min-/,
      /max-/,
      /space-/,
      /divide-/,
      /hover:/,
      /focus:/,
      /active:/,
      /visited:/,
      /disabled:/,
      /first:/,
      /last:/,
      /odd:/,
      /even:/,
      /nth-child/,
      /nth-of-type/,
      /before:/,
      /after:/
    ],
    greedy: [
      // Pseudo-class and pseudo-element selectors
      /hover/,
      /focus/,
      /active/,
      /visited/,
      /disabled/,
      /checked/,
      /selected/,
      /valid/,
      /invalid/,
      /required/,
      /optional/,
      /first-child/,
      /last-child/,
      /nth-child/,
      /nth-of-type/,
      /before/,
      /after/,
      /placeholder/,
      /selection/
    ]
  },
  fontFace: true,
  keyframes: true,
  variables: true,
  rejected: true,
  rejectedCss: true
};

(async () => {
  try {
    const purgeCSSResult = await new PurgeCSS().purge(purgeCSSOptions);
    
    if (purgeCSSResult.length > 0) {
      const purgedCSS = purgeCSSResult[0].css;
      const outputPath = 'public/assets/css/dist/purged.css';
      
      // Ensure directory exists
      const dir = path.dirname(outputPath);
      if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
      }
      
      fs.writeFileSync(outputPath, purgedCSS);
      console.log(`Purged CSS written to ${outputPath}`);
      
      // Generate report
      const originalSize = fs.statSync('public/assets/css/dist/compiled.css').size;
      const purgedSize = Buffer.byteLength(purgedCSS, 'utf8');
      const reduction = ((originalSize - purgedSize) / originalSize * 100).toFixed(2);
      
      console.log(`Original size: ${(originalSize / 1024).toFixed(2)} KB`);
      console.log(`Purged size: ${(purgedSize / 1024).toFixed(2)} KB`);
      console.log(`Reduction: ${reduction}%`);
      
      // Log rejected selectors if any
      if (purgeCSSResult[0].rejected) {
        console.log('\nRejected selectors:');
        purgeCSSResult[0].rejected.forEach(selector => {
          console.log(`  - ${selector}`);
        });
      }
    } else {
      console.error('No CSS was purged');
    }
  } catch (error) {
    console.error('Error purging CSS:', error);
    process.exit(1);
  }
})();

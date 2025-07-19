/**
 * PostCSS Configuration
 * 
 * Advanced CSS processing configuration for RenalTales
 * Handles imports, vendor prefixes, modern CSS features, and optimizations
 */

module.exports = {
  plugins: [
    // Handle @import statements
    require('postcss-import')({
      path: ['./'],
      plugins: [
        require('postcss-normalize')
      ]
    }),
    
    // Handle URL rewrites
    require('postcss-url')({
      url: 'rebase'
    }),
    
    // Add vendor prefixes
    require('autoprefixer')({
      cascade: false,
      grid: true
    }),
    
    // Handle flexbox bugs
    require('postcss-flexbugs-fixes'),
    
    // Use future CSS features
    require('postcss-preset-env')({
      stage: 3,
      features: {
        'custom-properties': true,
        'color-functional-notation': true,
        'custom-media-queries': true,
        'media-query-ranges': true,
        'custom-selectors': true,
        'nesting-rules': true
      }
    }),
    
    // Handle CSS custom properties
    require('postcss-custom-properties')({
      preserve: false,
      importFrom: [
        'core/variables.css'
      ]
    }),
    
    // Optimize CSS for production
    ...(process.env.NODE_ENV === 'production' ? [
      // Combine duplicate selectors
      require('postcss-combine-duplicated-selectors')({
        removeDuplicatedProperties: true
      }),
      
      // Merge rules
      require('postcss-merge-rules'),
      
      // Remove unused CSS
      require('postcss-discard-unused'),
      
      // Sort media queries
      require('postcss-sort-media-queries')({
        sort: 'mobile-first'
      }),
      
      // Minify CSS
      require('cssnano')({
        preset: ['default', {
          discardComments: {
            removeAll: true
          },
          reduceIdents: false,
          zindex: false,
          mergeIdents: false,
          discardUnused: {
            fontFace: false,
            keyframes: false,
            variables: false
          }
        }]
      })
    ] : [])
  ]
};

// critical-css.js
// Script to generate inline critical CSS
const critical = require('critical');

const sourcePath = 'public';  // Your source HTML directory

const pages = [
  // Add more pages as needed for critical CSS extraction
  'index.html',
  'home.html'
];

(async () => {
  for (const page of pages) {
    try {
      await critical.generate({
        inline: true,
        base: sourcePath + '/',
        src: page,
        target: {
          html: page
        },
        minify: true,
        extract: false,  // Don't extract additional styles to prevent broken links
        rebase: ({ url }) => url
      });
      console.log(`Critical CSS inlined for ${page}`);
    } catch (err) {
      console.error(`Failed to generate critical CSS for ${page}:`, err);
    }
  }
})();

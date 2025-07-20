// Enhanced flag system for language selector
document.addEventListener("DOMContentLoaded", function () {
  var select = document.querySelector('select[name="lang"]');
  if (!select) return;

  function updateFlag() {
    // Remove existing flag if any
    var existingFlag = select.parentNode.querySelector(".lang-flag");
    if (existingFlag) {
      existingFlag.remove();
    }

    // Add flag for current selection
    var selectedOption = select.options[select.selectedIndex];
    var flagSrc = selectedOption.getAttribute("data-flag");
    
    if (flagSrc) {
      var flagImg = document.createElement("img");
      flagImg.src = flagSrc;
      flagImg.alt = selectedOption.textContent;
      flagImg.className = "lang-flag";
      flagImg.style.height = "1.25rem";
      flagImg.style.width = "auto";
      flagImg.style.verticalAlign = "middle";
      flagImg.style.marginRight = "0.25rem";
      flagImg.style.border = "none";
      flagImg.style.borderRadius = "2px";
      flagImg.style.boxShadow = "0 1px 3px rgba(0,0,0,0.1)";
      
      // Error handling for missing flags
      flagImg.onerror = function() {
        console.warn('Flag image failed to load:', flagSrc);
        this.src = 'assets/flags/un.webp'; // Fallback to UN flag
      };
      
      // Loading indicator
      flagImg.onload = function() {
        console.log('Flag loaded successfully:', flagSrc);
      };
      
      select.parentNode.insertBefore(flagImg, select);
    }
  }

  // Initial flag display
  updateFlag();

  // Update flag when selection changes
  select.addEventListener("change", updateFlag);
  
  // Enhanced flag button interactions
  var flagButtons = document.querySelectorAll('.flag-button');
  flagButtons.forEach(function(button) {
    // Add hover effects
    button.addEventListener('mouseenter', function() {
      this.style.transform = 'scale(1.05)';
      this.style.transition = 'transform 0.2s ease';
    });
    
    button.addEventListener('mouseleave', function() {
      this.style.transform = 'scale(1)';
    });
    
    // Add click feedback
    button.addEventListener('click', function() {
      this.style.transform = 'scale(0.95)';
      setTimeout(() => {
        this.style.transform = 'scale(1.05)';
      }, 100);
    });
  });
  
  // Highlight current language flag
  var currentLanguageForms = document.querySelectorAll('.flag-form.current-language');
  currentLanguageForms.forEach(function(form) {
    var button = form.querySelector('.flag-button');
    if (button) {
      button.style.border = '2px solid #007cba';
      button.style.backgroundColor = '#f0f8ff';
    }
  });
});

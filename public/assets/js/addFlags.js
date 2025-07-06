// Add flags to the language selector
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
      flagImg.style.borderRadius = "none";
      select.parentNode.insertBefore(flagImg, select);
    }
  }

  // Initial flag display
  updateFlag();

  // Update flag when selection changes
  select.addEventListener("change", updateFlag);
});

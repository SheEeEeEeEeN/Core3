<script>
  function initDarkMode(toggleId, storageKey) {
    const checkbox = document.getElementById(toggleId);

    if (!checkbox) return; // stop if toggle not found

    // On page load, apply saved mode
    if (localStorage.getItem(storageKey) === "enabled") {
      document.body.classList.add("dark-mode");
      checkbox.checked = true;
    } else {
      document.body.classList.remove("dark-mode");
      checkbox.checked = false;
    }

    // Listen for changes
    checkbox.addEventListener("change", () => {
      if (checkbox.checked) {
        document.body.classList.add("dark-mode");
        localStorage.setItem(storageKey, "enabled");
      } else {
        document.body.classList.remove("dark-mode");
        localStorage.setItem(storageKey, "disabled");
      }
    });
  }
</script>

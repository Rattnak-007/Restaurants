document.addEventListener("DOMContentLoaded", function () {
  const loadingBody = document.querySelector(".loading-body");
  const mainContent = document.getElementById("main-content");

  // Show loading screen
  loadingBody.style.opacity = "1";
  mainContent.style.display = "none";

  // Hide loading screen and show content when everything is loaded
  window.addEventListener("load", function () {
    setTimeout(() => {
      loadingBody.style.opacity = "0";
      loadingBody.style.visibility = "hidden";
      mainContent.style.display = "block";

      // Trigger a small delay before showing content for smooth transition
      setTimeout(() => {
        mainContent.style.opacity = "1";
      }, 100);
    }, 1500); // Show loading screen for 1.5 seconds
  });
});

let slideIndex = 1;
let slideInterval;

// Initialize slideshow
function initSlideshow() {
  showSlides(slideIndex);
  startAutoSlide();
}

// Auto-play functionality
function startAutoSlide() {
  clearInterval(slideInterval);
  slideInterval = setInterval(() => {
    plusSlides(1);
  }, 5000); // Change slides every 5 seconds
}

function plusSlides(n) {
  clearInterval(slideInterval);
  showSlides((slideIndex += n));
  startAutoSlide();
}

function currentSlide(n) {
  clearInterval(slideInterval);
  showSlides((slideIndex = n));
  startAutoSlide();
}

function showSlides(n) {
  const slides = document.getElementsByClassName("slide");
  const dots = document.getElementsByClassName("dot");

  if (n > slides.length) slideIndex = 1;
  if (n < 1) slideIndex = slides.length;

  // Remove active class from all slides and dots
  Array.from(slides).forEach((slide) => {
    slide.classList.remove("active");
    slide.style.display = "block";
  });

  Array.from(dots).forEach((dot) => {
    dot.classList.remove("active");
  });

  // Add active class to current slide and dot
  slides[slideIndex - 1].classList.add("active");
  dots[slideIndex - 1].classList.add("active");
}

// Initialize slideshow when DOM is loaded
document.addEventListener("DOMContentLoaded", initSlideshow);

// Pause auto-play when user hovers over slideshow
document
  .querySelector(".slideshow-container")
  .addEventListener("mouseenter", () => {
    clearInterval(slideInterval);
  });

// Resume auto-play when user leaves slideshow
document
  .querySelector(".slideshow-container")
  .addEventListener("mouseleave", startAutoSlide);
// Cart management
let cart = JSON.parse(localStorage.getItem("cart")) || [];
updateCartCount();

// Mobile menu functionality
document.addEventListener("DOMContentLoaded", function () {
  const navbar = document.querySelector(".navbar .container");
  const navbarNav = document.querySelector(".navbar-nav");
  let toggleButton = null;

  // Function to handle mobile menu
  function setupMobileMenu() {
    const isMobile = window.innerWidth <= 992;

    // Remove existing toggle button if it exists
    if (toggleButton) {
      toggleButton.remove();
      toggleButton = null;
    }

    // Only setup mobile menu for mobile devices
    if (isMobile) {
      // Create and add mobile menu toggle button
      toggleButton = document.createElement("button");
      toggleButton.className = "mobile-menu-toggle";
      toggleButton.innerHTML = '<i class="fas fa-bars"></i>';
      navbar.appendChild(toggleButton);

      // Toggle menu on button click
      toggleButton.addEventListener("click", function (e) {
        e.stopPropagation();
        navbarNav.classList.toggle("show");
        const icon = this.querySelector("i");
        if (navbarNav.classList.contains("show")) {
          icon.className = "fas fa-times";
        } else {
          icon.className = "fas fa-bars";
        }
      });

      // Close menu when clicking outside
      document.addEventListener("click", function (event) {
        if (
          !navbar.contains(event.target) &&
          navbarNav.classList.contains("show")
        ) {
          navbarNav.classList.remove("show");
          toggleButton.querySelector("i").className = "fas fa-bars";
        }
      });

      // Close menu when clicking a nav link
      navbarNav.querySelectorAll(".nav-link").forEach((link) => {
        link.addEventListener("click", () => {
          if (window.innerWidth <= 992) {
            navbarNav.classList.remove("show");
            toggleButton.querySelector("i").className = "fas fa-bars";
          }
        });
      });
    } else {
      // Reset nav styles for desktop
      navbarNav.classList.remove("show");
    }
  }

  // Initial setup
  setupMobileMenu();

  // Handle window resize
  let resizeTimer;
  window.addEventListener("resize", function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      setupMobileMenu();
    }, 250);
  });

  // Event delegation for add to cart buttons
  document.body.addEventListener("click", function (e) {
    if (e.target.classList.contains("add-to-cart")) {
      const button = e.target;
      const productId = button.dataset.productId;
      const productName = button.dataset.productName;
      const productPrice = parseFloat(button.dataset.productPrice);
      const quantity = parseInt(
        document.getElementById(`qty-${productId}`).value
      );

      addToCart(productId, productName, productPrice, quantity);
      showNotification("Product added to cart successfully!");
    }
  });

  // Category filter
  const categoryButtons = document.querySelectorAll(".category-filter .btn");
  categoryButtons.forEach((button) => {
    button.addEventListener("click", function () {
      categoryButtons.forEach((btn) => btn.classList.remove("active"));
      this.classList.add("active");
      const category = this.getAttribute("data-category");
      filterProducts(category);
    });
  });
});

function addToCart(id, name, price, quantity) {
  const existingItem = cart.find((item) => item.id === id);

  if (existingItem) {
    existingItem.quantity += quantity;
  } else {
    cart.push({ id, name, price, quantity });
  }

  localStorage.setItem("cart", JSON.stringify(cart));
  updateCartCount();
}

function updateCartCount() {
  const cartCount = cart.reduce((total, item) => total + item.quantity, 0);
  const cartCountElement = document.getElementById("cart-count");
  if (cartCountElement) {
    cartCountElement.textContent = cartCount;
  }
}

// Add category filter functionality
document.addEventListener("DOMContentLoaded", function () {
  // Category filter
  const categoryButtons = document.querySelectorAll(".category-filter .btn");
  categoryButtons.forEach((button) => {
    button.addEventListener("click", function () {
      // Remove active class from all buttons
      categoryButtons.forEach((btn) => btn.classList.remove("active"));
      // Add active class to clicked button
      this.classList.add("active");

      const category = this.getAttribute("data-category");
      filterProducts(category);
    });
  });
});

function filterProducts(category) {
  const products = document.querySelectorAll(".product-item");
  products.forEach((product) => {
    if (
      category === "all" ||
      product.getAttribute("data-category") === category
    ) {
      product.style.display = "block";
    } else {
      product.style.display = "none";
    }
  });
}

function updateQuantity(productId, change) {
  const input = document.getElementById(`qty-${productId}`);
  let value = parseInt(input.value) + change;
  value = Math.max(1, value); // Ensure minimum value is 1
  input.value = value;
}

// Update notification function
function showNotification(message) {
  const notification = document.createElement("div");
  notification.className = "notification";
  notification.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>${message}</span>
    `;

  // Add notification styles
  notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #27ae60;
        color: white;
        padding: 15px 25px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        z-index: 1000;
        opacity: 0;
        transform: translateY(-20px);
        transition: all 0.3s ease;
    `;

  document.body.appendChild(notification);

  // Show notification
  setTimeout(() => {
    notification.style.opacity = "1";
    notification.style.transform = "translateY(0)";
  }, 10);

  // Remove notification after 3 seconds
  setTimeout(() => {
    notification.style.opacity = "0";
    notification.style.transform = "translateY(-20px)";
    setTimeout(() => notification.remove(), 300);
  }, 3000);
}

// Initialize category filter
document.addEventListener("DOMContentLoaded", function () {
  const categoryButtons = document.querySelectorAll(".category-filter .btn");
  if (categoryButtons) {
    categoryButtons.forEach((button) => {
      button.addEventListener("click", function () {
        categoryButtons.forEach((btn) => btn.classList.remove("active"));
        this.classList.add("active");

        const category = this.getAttribute("data-category");
        filterProducts(category);
      });
    });
  }
});

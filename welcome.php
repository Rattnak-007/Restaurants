<?php
require_once('includes/auth.php');
checkAuth();

// Get database connection
$conn = require_once('config/database.php');
if (!$conn) {
  die("Database connection failed");
}

// Fetch all active products
$sql = "SELECT * FROM products WHERE is_available = 1 AND is_deleted = 0 ORDER BY category, name";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);

$products = array();
$categories = array();  // Initialize categories array

while ($row = oci_fetch_assoc($stmt)) {
  // Convert CLOB to string for description
  if (isset($row['DESCRIPTION']) && is_object($row['DESCRIPTION'])) {
    $row['DESCRIPTION'] = $row['DESCRIPTION']->load();
  }
  $products[] = $row;
  // Add category to categories array
  if (!empty($row['CATEGORY'])) {
    $categories[] = $row['CATEGORY'];
  }
}
oci_free_statement($stmt);
// Get unique categories and sort them
$categories = array_unique($categories);
sort($categories); ?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Welcome - Restaurant Order System</title>
    <link rel="stylesheet" href="./Assets/css/style.css" />
    <link rel="stylesheet" href="./Assets/css/loading.css" />
    <link rel="stylesheet" href="./Assets/css/Responsive.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
  </head>
  <style>
    /* About Section */
    .about {
      display: flex;
      align-items: center;
      gap: 3rem;
      margin-bottom: 4rem;
      background: linear-gradient(120deg, #f8fafc 60%, #e0f7fa 100%);
      border-radius: 18px;
      padding: 3rem;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
      position: relative;
      overflow: hidden;
    }
    .about::before {
      content: "";
      position: absolute;
      top: -40px;
      right: -40px;
      width: 180px;
      height: 180px;
      background: radial-gradient(
        circle,
        #27ae60 0%,
        #2ecc71 80%,
        transparent 100%
      );
      opacity: 0.08;
      z-index: 0;
    }
    .about-content {
      flex: 1;
      z-index: 1;
    }
    .about-image {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1;
    }
    .about-image img {
      max-width: 600px;
      width: 100%;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.13);
      border: 4px solid #e0f7fa;
    }
    .about h2 {
      font-size: 2.2rem;
      color: #27ae60;
      margin-bottom: 1.5rem;
      position: relative;
      font-weight: 700;
      letter-spacing: 1px;
    }
    .about h2::after {
      content: "";
      position: absolute;
      left: 0;
      bottom: -10px;
      width: 60px;
      height: 4px;
      background: #2ecc71;
      border-radius: 2px;
    }
    .about p {
      color: #444;
      margin-bottom: 1.2rem;
      font-size: 1.08rem;
    }
    .about .benefits {
      margin: 2rem 0 1.5rem 0;
    }
    .about .benefit-item {
      display: flex;
      align-items: center;
      margin-bottom: 1rem;
      font-size: 1.08rem;
      color: #2c3e50;
      font-weight: 500;
    }
    .about .benefit-icon {
      width: 32px;
      height: 32px;
      background: linear-gradient(135deg, #27ae60, #2ecc71);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 1rem;
      color: #fff;
      font-size: 1.1rem;
      box-shadow: 0 2px 8px rgba(46, 204, 113, 0.13);
    }
    .about .buttons {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      margin-top: 1.5rem;
    }
    .about .btn-about {
      display: inline-flex;
      align-items: center;
      padding: 0.85rem 1.7rem;
      border-radius: 50px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s cubic-bezier(0.4, 2, 0.6, 1);
      margin-right: 0.5rem;
      font-size: 1.08rem;
      box-shadow: 0 2px 8px rgba(44, 62, 80, 0.07);
      border: none;
    }
    .about .btn-primary {
      background: linear-gradient(90deg, #27ae60, #2ecc71);
      color: #fff;
      border: none;
    }
    .about .btn-primary:hover {
      background: linear-gradient(90deg, #219150, #27ae60);
      color: #fff;
      transform: translateY(-2px) scale(1.04);
      box-shadow: 0 5px 15px rgba(46, 204, 113, 0.18);
    }
    .about .btn-outline {
      border: 2px solid #27ae60;
      color: #27ae60;
      background: transparent;
    }
    .about .btn-outline:hover {
      background: #27ae60;
      color: #fff;
      border-color: #27ae60;
      transform: translateY(-2px) scale(1.04);
      box-shadow: 0 5px 15px rgba(46, 204, 113, 0.13);
    }
    @media (max-width: 900px) {
      .about {
        flex-direction: column;
        gap: 2rem;
        padding: 2rem 1rem;
      }
      .about-image img {
        max-width: 100%;
      }
    }
    @media (max-width: 600px) {
      .about {
        padding: 1rem 0.5rem;
        border-radius: 10px;
        margin-bottom: 2rem;
      }
      .about h2 {
        font-size: 1.3rem;
        margin-bottom: 1rem;
      }
      .about p {
        font-size: 0.98rem;
        margin-bottom: 1rem;
      }
      .about .benefit-item {
        font-size: 0.95rem;
        margin-bottom: 0.7rem;
      }
      .about .btn-about {
        padding: 0.6rem 1.1rem;
        font-size: 0.98rem;
        margin-bottom: 0.5rem;
      }
      .about-image img {
        max-width: 100%;
        border-radius: 8px;
      }
      .about .buttons {
        flex-direction: column;
        gap: 0.5rem;
        align-items: stretch;
      }
    }

    /* Our Menu Section */
    .our-menu {
      margin: 2.5rem 0 1.5rem 0;
      text-align: center;
    }
    .our-menu-header h2 {
      font-size: 2.1rem;
      color: #27ae60;
      font-weight: 700;
      margin-bottom: 0.5rem;
      letter-spacing: 1px;
      display: inline-block;
      position: relative;
    }
    .our-menu-header h2::after {
      content: '';
      display: block;
      margin: 0.5rem auto 0 auto;
      width: 60px;
      height: 4px;
      background: #2ecc71;
      border-radius: 2px;
    }
    @media (max-width: 600px) {
      .our-menu-header h2 {
        font-size: 1.3rem;
      }
      .our-menu {
        margin: 1.2rem 0 1rem 0;
      }
    }
  </style>
  <body>
    <div class="loading-body">
      <div class="loading-container">
        <div class="restaurant-loader">
          <i class="fas fa-utensils"></i>
          <i class="fas fa-wine-glass-alt"></i>
          <i class="fas fa-hamburger"></i>
        </div>
        <div class="loading-text">
          <span>L</span>
          <span>o</span>
          <span>a</span>
          <span>d</span>
          <span>i</span>
          <span>n</span>
          <span>g</span>
          <span>.</span>
          <span>.</span>
          <span>.</span>
        </div>
      </div>
    </div>
    <div id="main-content">
      <nav class="navbar mobile-only-nav">
        <div class="container">
          <a class="navbar-brand" href="#">
            <i class="fas fa-utensils fa-lg"></i> QuickFeast Delivery
          </a>
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" href="cart.php">
                <i class="fas fa-shopping-cart fa-lg"></i> Cart (<span
                  id="cart-count"
                  >0</span
                >)
              </a>
            </li>
            <li class="nav-item">
              <span class="nav-link">
                <i class="fas fa-user-circle fa-lg"></i> Welcome,
                <?php echo htmlspecialchars($_COOKIE['user_name']); ?>
              </span>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="logout.php">
                <i class="fas fa-sign-out-alt fa-lg"></i> Logout
              </a>
            </li>
          </ul>
        </div>
      </nav>

      <!-- Slideshow Section -->
      <div class="slideshow-container">
        <div class="slide fade">
          <img
            src="./Assets/images/13534974_5230412.jpg"
            alt="Slide 1"
            class="slide-img"
          />
        </div>
        <div class="slide fade">
          <img
            src="./Assets/images/25655391_food_web_banner_34.jpg"
            alt="Slide 2"
            class="slide-img"
          />
        </div>
        <div class="slide fade">
          <img
            src="./Assets/images/272396554_9ab82a7c-7136-4e8c-85ac-74f3f3a522f8.jpg"
            alt="Slide 3"
            class="slide-img"
          />
        </div>
        <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
        <a class="next" onclick="plusSlides(1)">&#10095;</a>
        <div class="slide-dots">
          <span class="dot" onclick="currentSlide(1)"></span>
          <span class="dot" onclick="currentSlide(2)"></span>
          <span class="dot" onclick="currentSlide(3)"></span>
        </div>
      </div>

      <div class="menu-wrapper">
        <div class="menu-container" id="menuScroll">
          <div class="menu-item">
            <img src="./Assets/Logo/1.jpg" alt="Kids Menus" />
            <p>KIDS MENUS</p>
          </div>
          <div class="menu-item">
            <img src="./Assets/Logo/2.jpg" alt="Single Products" />
            <p>SINGLE PRODUCTS</p>
          </div>
          <div class="menu-item">
            <img src="./Assets/Logo/3.jpg" alt="Box Meals" />
            <p>BOX MEALS</p>
          </div>
          <div class="menu-item">
            <img src="./Assets/Logo/4.jpg" alt="Meals" />
            <p>MEALS</p>
          </div>
          <div class="menu-item">
            <img src="./Assets/Logo/5.jpg" alt="Buckets" />
            <p>BUCKETS</p>
          </div>
          <div class="menu-item">
            <img src="./Assets/Logo/6.jpg" alt="Chicken" />
            <p>CHICKEN</p>
          </div>
          <div class="menu-item">
            <img src="./Assets/Logo/7.jpg" alt="Sauces" />
            <p>SAUCES</p>
          </div>
          <div class="menu-item">
            <img src="./Assets/Logo/8.jpg" alt="Drinks" />
            <p>DRINKS</p>
          </div>
          <div class="menu-item">
            <img src="./Assets/Logo/9.jpg" alt="Sides" />
            <p>SIDES</p>
          </div>
        </div>
      </div>

      <div class="welcome-header">
        <div class="container">
          <h1>
            <i class="fas fa-star"></i> Welcome to Our QuickFeast Delivery
          </h1>
          <p class="lead">
            <i class="fas fa-utensils"></i> Explore our delicious menu and place
            your order
          </p>
        </div>
      </div>

      <div class="about">
        <div class="about-content">
          <h2>About QuickFeast Delivery</h2>
          <p>
            We believe in creating memorable dining experiences through
            exceptional food quality and service. Our chefs combine traditional
            recipes with modern techniques to deliver unforgettable flavors.
          </p>
          <p>
            Every ingredient is carefully selected from trusted local suppliers
            who share our commitment to sustainability and quality.
          </p>

          <div class="benefits">
            <div class="benefit-item">
              <div class="benefit-icon">
                <i class="fas fa-check"></i>
              </div>
              <span>Delicious & Healthy Foods</span>
            </div>
            <div class="benefit-item">
              <div class="benefit-icon">
                <i class="fas fa-check"></i>
              </div>
              <span>Best Price & Offers</span>
            </div>
            <div class="benefit-item">
              <div class="benefit-icon">
                <i class="fas fa-check"></i>
              </div>
              <span>Made By Fresh Ingredients</span>
            </div>
          </div>

          <div class="buttons">
            <a href="#" class="btn-about btn-primary">Order Now</a>
            <a href="#" class="btn-about btn-outline">Our Story</a>
          </div>
        </div>

        <div class="about-image">
          <img
            src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80"
            alt="About Tasty Foods"
            style="
              width: 100%;
              border-radius: 12px;
              box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            "
          />
        </div>
      </div>
      <div class="container">
        <div class="our-menu">
            <div class="our-menu-header">
              <h2>Our Menu</h2>
            </div>
          </div>
        <div class="category-filter">
          <button class="btn active" data-category="all">
            <i class="fas fa-th"></i> All
          </button>
          <?php foreach ($categories as $category): ?>
          <?php
          $icon = 'utensils';
          switch (strtolower($category)) {
            case 'main dishes':
              $icon = 'hamburger';
              break;
            case 'appetizers':
              $icon = 'cheese';
              break;
            case 'desserts':
              $icon = 'ice-cream';
              break;
            case 'drinks':
              $icon = 'glass-martini-alt';
              break;
            case 'salads':
              $icon = 'leaf';
              break;
            case 'soups':
              $icon = 'hotdog';
              break;
          }
          ?>
          <button
            class="btn"
            data-category="<?php echo htmlspecialchars($category); ?>"
          >
            <i class="fas fa-<?php echo $icon; ?>"></i>
            <?php echo htmlspecialchars($category); ?>
          </button>
          <?php endforeach; ?>
        </div>
        <div class="row">
          <?php foreach ($products as $product): ?>
          <div
            class="product-item"
            data-category="<?php echo htmlspecialchars($product['CATEGORY']); ?>"
          >
            <div class="product-card">
              <img
                src="<?php echo htmlspecialchars($product['IMAGE_URL']); ?>"
                class="product-image"
                alt="<?php echo htmlspecialchars($product['NAME']); ?>"
              />

              <?php if ($_SESSION['user_role'] === 'admin'): ?>
              <div class="admin-controls">
                <a
                  href="admin/edit_product.php?id=<?php echo $product['ID']; ?>"
                  class="btn btn-primary"
                  >✏️</a
                >
                <a
                  href="admin/delete_product.php?id=<?php echo $product['ID']; ?>"
                  class="btn btn-danger"
                  onclick="return confirm('Are you sure?')"
                  >🗑️</a
                >
              </div>
              <?php endif; ?>

              <div class="card-body">
                <h5 class="card-title">
                  <?php echo htmlspecialchars($product['NAME']); ?>
                </h5>
                <p class="card-text">
                  <?php echo htmlspecialchars($product['DESCRIPTION']); ?>
                </p>
                <p class="product-price">
                  $<?php echo number_format($product['PRICE'], 2); ?>
                </p>
                <div class="d-flex">
                  <div class="quantity-input">
                    <button
                      class="quantity-down"
                      onclick="updateQuantity('<?php echo $product['ID']; ?>', -1)"
                    >
                      -
                    </button>
                    <input
                      type="number"
                      value="1"
                      min="1"
                      id="qty-<?php echo $product['ID']; ?>"
                    />
                    <button
                      class="quantity-up"
                      onclick="updateQuantity('<?php echo $product['ID']; ?>', 1)"
                    >
                      +
                    </button>
                  </div>
                  <button
                    class="add-to-cart"
                    data-product-id="<?php echo $product['ID']; ?>"
                    data-product-name="<?php echo htmlspecialchars($product['NAME']); ?>"
                    data-product-price="<?php echo $product['PRICE']; ?>"
                  >
                    <i class="fas fa-shopping-cart"></i>
                    Add to Cart
                  </button>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <script src="Assets/js/slideshow.js"></script>
    <script src="Assets/js/navbar.js"></script>
    <script src="Assets/js/loading.js"></script>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        const menuContainer = document.getElementById("menuScroll");
        if (!menuContainer) return;

        // Duplicate menu items for seamless scroll
        menuContainer.innerHTML += menuContainer.innerHTML;

        // Set up styles for seamless horizontal scroll
        menuContainer.style.overflowX = "hidden";
        menuContainer.style.whiteSpace = "nowrap";
        menuContainer.style.display = "flex";
        menuContainer.style.flexWrap = "nowrap";
        menuContainer.style.scrollBehavior = "auto";

        // Make menu items inline for horizontal scroll
        const items = menuContainer.querySelectorAll(".menu-item");
        items.forEach((item) => {
          item.style.flex = "0 0 auto";
        });

        // Responsive scroll speed
        function getScrollSpeed() {
          return window.innerWidth <= 768 ? 0.15 : 0.4; // slower on mobile, faster on desktop
        }

        let scrollAmount = 0;
        let scrollSpeed = getScrollSpeed();

        // Update scroll speed on resize
        window.addEventListener("resize", function () {
          scrollSpeed = getScrollSpeed();
        });

        function animateMenuScroll() {
          scrollAmount += scrollSpeed;
          if (scrollAmount >= menuContainer.scrollWidth / 2) {
            scrollAmount = 0;
          }
          menuContainer.scrollLeft = scrollAmount;
          requestAnimationFrame(animateMenuScroll);
        }

        animateMenuScroll();
      });
    </script>
  </body>
</html>

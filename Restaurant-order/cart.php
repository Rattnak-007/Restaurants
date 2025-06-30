<?php
require_once('includes/auth.php');
checkAuth();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Shopping Cart - Restaurant Order System</title>
    <link rel="stylesheet" href="Assets/css/style.css" />
    <link rel="stylesheet" href="Assets/css/Responsive.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
  </head>

  <body>
    <nav class="navbar">
      <div class="container">
        <a class="navbar-brand" href="welcome.php">
          <i class="fas fa-utensils fa-lg"></i> QuickFeast Delivery
        </a>
        <div class="navbar-nav">
          <span class="nav-link">
            <i class="fas fa-user-circle"></i> Welcome,
            <?php echo htmlspecialchars($_COOKIE['user_name']); ?>
          </span>
          <a class="nav-link" href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </div>
      </div>
    </nav>

    <div class="container cart-container">
      <div class="cart-header">
        <h2><i class="fas fa-shopping-basket fa-lg"></i> Shopping Cart</h2>
      </div>

      <div id="cart-items">
        <!-- Cart items will be loaded here -->
      </div>

      <div class="cart-actions">
        <a href="welcome.php" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Continue Shopping
        </a>
        <button
          id="checkout-btn"
          class="btn btn-primary"
          onclick="placeOrder()"
        >
          <i class="fas fa-check-circle"></i> Place Order
        </button>
      </div>
    </div>

    <script>
      let cart = JSON.parse(localStorage.getItem("cart")) || [];

      function displayCart() {
        const cartDiv = document.getElementById("cart-items");
        if (cart.length === 0) {
          cartDiv.innerHTML = `
                    <div class="empty-cart">
                        <i class="fas fa-shopping-basket"></i>
                        <p>Your cart is empty</p>
                        <a href="welcome.php" class="btn btn-primary">Start Shopping</a>
                    </div>`;
          return;
        }
        let total = 0;
        let html = `
                <div class="cart-table">
                    <table>
                        <thead>
                            <tr>
                                <th><i class="fas fa-box"></i> Product</th>
                                <th><i class="fas fa-tag"></i> Price</th>
                                <th><i class="fas fa-sort-amount-up"></i> Quantity</th>
                                <th><i class="fas fa-calculator"></i> Subtotal</th>
                                <th><i class="fas fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

        cart.forEach((item) => {
          const subtotal = item.price * item.quantity;
          total += subtotal;
          html += `
                    <tr>
                        <td class="product-info">${item.name}</td>
                        <td class="price">$${item.price.toFixed(2)}</td>
                        <td class="quantity">
                            <div class="quantity-control">
                                <button onclick="updateQuantity('${
                                  item.id
                                }', Math.max(1, parseInt(document.getElementById('qty-${
            item.id
          }').value) - 1))">-</button>
                                <input type="number" id="qty-${
                                  item.id
                                }" value="${
            item.quantity
          }" min="1" onchange="updateQuantity('${item.id}', this.value)">
                                <button onclick="updateQuantity('${
                                  item.id
                                }', parseInt(document.getElementById('qty-${
            item.id
          }').value) + 1)">+</button>
                            </div>
                        </td>
                        <td class="subtotal">$${subtotal.toFixed(2)}</td>
                        <td class="actions">
                            <button class="btn-remove" onclick="removeItem('${
                              item.id
                            }')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
        });

        html += `
                    </tbody>
                </table>
                <div class="cart-summary">
                    <div class="total">
                        <span>Total:</span>
                        <span class="total-amount">$${total.toFixed(2)}</span>
                    </div>
                </div>
            </div>`;

        cartDiv.innerHTML = html;
      }

      function updateQuantity(productId, quantity) {
        const item = cart.find((item) => item.id === productId);
        if (item) {
          item.quantity = parseInt(quantity);
          localStorage.setItem("cart", JSON.stringify(cart));
          displayCart();
        }
      }

      function removeItem(productId) {
        cart = cart.filter((item) => item.id !== productId);
        localStorage.setItem("cart", JSON.stringify(cart));
        displayCart();
      }

      function placeOrder() {
        fetch("place_order.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            cart: cart,
          }),
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              alert("Order placed successfully!");
              // Clear cart
              cart = [];
              localStorage.removeItem("cart");
              displayCart();
            } else {
              alert("Error placing order: " + data.message);
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            alert("Error placing order");
          });
      }

      // Display cart on page load
      displayCart();
    </script>
  </body>
</html>

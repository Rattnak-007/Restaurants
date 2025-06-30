# Restaurant Order System

A modern, responsive web application for restaurant menu browsing, ordering, and admin management. Built with PHP, Oracle Database, and Bootstrap.

---

## Features

- **User Authentication:** Secure login/logout for customers and admins.
- **Product Management:** Add, edit, delete, enable/disable menu items with image upload.
- **Category Management:** Organize products by categories.
- **Shopping Cart:** Add, update, and remove items; persistent with localStorage.
- **Order Placement:** Customers can place orders and view their cart.
- **Admin Dashboard:** Manage products, orders, categories, and view sales reports.
- **Daily/Monthly Reports:** Visual sales and order statistics for admins.
- **Responsive Design:** Optimized for desktop, tablet, and mobile devices.
- **Newsletter UI:** Newsletter subscription form (UI only).
- **Image Uploads:** Product images stored in `uploads/products/`.

---

## Project Structure

```
Restaurant-order/
│
├── admin/                # Admin dashboard and management pages
│   ├── products/         # Product CRUD
│   ├── orders/           # Order management
│   ├── categories/       # Category management
│   └── reports/          # Daily/Monthly reports
│
├── Assets/
│   ├── css/              # Stylesheets (Responsive.css, style.css, admin.css)
│   ├── js/               # JavaScript files (slideshow.js, navbar.js, loading.js)
│   ├── images/           # Default and UI images
│   └── Logo/             # Menu icons
│
├── includes/             # Authentication and helper scripts
├── config/               # Database connection config
├── migrations/           # SQL scripts for Oracle DB setup
├── seeders/              # Example data seeders (admin user, etc.)
├── uploads/products/     # Uploaded product images
│
├── welcome.php           # Main landing page (menu, about, etc.)
├── cart.php              # Shopping cart page
├── logout.php            # Logout script
│
└── ...                   # Other scripts and pages
```

---

## Setup Instructions

### 1. Requirements

- **XAMPP** (or similar Apache+PHP stack)
- **Oracle Database** (XE or higher)
- **PHP OCI8 extension** enabled

### 2. Database Setup

- Import all SQL scripts in `migrations/` into your Oracle database, in this order:

  1. `create_users_table.sql`
  2. `create_products_table.sql`
  3. `create_orders_tables.sql`
  4. (Optional) `create_Paymment_table.sql` for payment features

- Grant necessary permissions to your Oracle user as shown in the scripts.

### 3. Configure Database Connection

- Edit `config/database.php` with your Oracle DB credentials and connection string.

### 4. Seed Admin User

- Run the seeder to create an admin account:
  ```
  php seeders/admin_seeder.php
  ```
  Default admin:
  - **Email:** admin@gmail.com
  - **Password:** admin123

### 5. File Permissions

- Ensure `uploads/products/` is writable by the web server for image uploads.

### 6. Start Services

- Start Apache (XAMPP) and Oracle Database.

### 7. Access the Application

- Open your browser and go to:  
  [http://localhost/Restaurant-order/welcome.php](http://localhost/Restaurant-order/welcome.php)

---

## Usage

- **Customers:**

  - Browse menu, filter by category, add to cart, and place orders.

- **Admins:**
  - Login via `/admin/`, manage products, categories, orders, and view reports.

---

## Customization

- **Styling:**

  - Edit `Assets/css/style.css` and `Assets/css/Responsive.css` for UI changes.

- **Images:**

  - Place default images in `Assets/images/` and menu icons in `Assets/Logo/`.

- **Database:**
  - Modify or extend tables via `migrations/` as needed.

---

## Troubleshooting

- **OCI8 Not Installed:**
  - Install and enable the OCI8 PHP extension.
- **Oracle Connection Issues:**
  - Check Oracle service, credentials, and connection string in `config/database.php`.
- **Image Upload Fails:**
  - Ensure `uploads/products/` exists and is writable.

---

## License

MIT License (or specify your own)

---

## Credits

- UI: Bootstrap, Font Awesome
- Images: Unsplash, Freepik (see image credits in code)

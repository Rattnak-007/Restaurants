<?php
require_once('../../includes/auth.php');
checkAdminAuth();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Product - Admin Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Custom CSS (inline for demo) -->
    <style>
    /* General Styles */
    body {
        background: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    h2 {
        color: #333;
        font-weight: 700;
        margin-bottom: 20px;
    }

    .container {
        background: #fff;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        max-width: 600px;
        animation: fadeIn 0.8s ease-in-out;
    }

    /* Form Styles */
    form .form-label {
        font-weight: 600;
        color: #555;
    }

    form .form-control {
        border-radius: 8px;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    form .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    form textarea.form-control {
        resize: vertical;
    }

    form button,
    form a.btn {
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease-in-out;
    }

    form button.btn-primary {
        background: linear-gradient(135deg, #0d6efd, #0b5ed7);
        border: none;
    }

    form button.btn-primary:hover {
        background: linear-gradient(135deg, #0b5ed7, #0a58ca);
        transform: translateY(-2px);
    }

    form a.btn-secondary {
        background-color: #6c757d;
        border: none;
    }

    form a.btn-secondary:hover {
        background-color: #5a6268;
        transform: translateY(-2px);
    }

    .alert {
        border-radius: 8px;
    }

    /* Image Preview */
    #imagePreview {
        display: block;
        max-width: 180px;
        max-height: 120px;
        border-radius: 8px;
        border: 1px solid #ddd;
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }

    #imagePreview:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }

    /* Animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .container {
            padding: 20px;
        }
    }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h2><i class="fas fa-plus-circle"></i> Add New Product</h2>

        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
        <?php endif; ?>

        <form action="save_product.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="name" class="form-label"><i class="fas fa-tag"></i> Product Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <div class="mb-3">
                <label for="category" class="form-label"><i class="fas fa-list"></i> Category</label>
                <input type="text" class="form-control" id="category" name="category" required>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label"><i class="fas fa-dollar-sign"></i> Price</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label"><i class="fas fa-align-left"></i> Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label"><i class="fas fa-image"></i> Product Image</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*" required
                    onchange="previewImage(event)">
                <div class="mt-2">
                    <img id="imagePreview" src="Assets/images/no-image.png" alt="Image Preview">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Add Product
            </button>
            <a href="manage_products.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function previewImage(event) {
        const input = event.target;
        const preview = document.getElementById('imagePreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = 'Assets/images/no-image.png';
            preview.style.display = 'block';
        }
    }
    </script>
</body>

</html>
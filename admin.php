<?php
require_once 'config.php';

// Handle login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if (authenticateAdmin($username, $password)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: admin.php');
        exit;
    } else {
        $login_error = "Yanlış istifadəçi adı və ya şifrə!";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Check if admin is logged in
$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];

// Handle product operations
if ($is_logged_in) {
    // Add product
    if (isset($_POST['add_product'])) {
        try {
            $name = $_POST['name'];
            $price = $_POST['price'];
            $discount_price = !empty($_POST['discount_price']) ? $_POST['discount_price'] : null;
            $description = $_POST['description'];
            $category = $_POST['category'];
            $subcategory = $_POST['subcategory'];
            $rating = $_POST['rating'];
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $stock_quantity = $_POST['stock_quantity'];
            
            // Handle multiple image uploads
            $images = [];
            
            // Handle file uploads
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                foreach ($_FILES['images']['name'] as $key => $name_file) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $file_temp = [
                            'name' => $_FILES['images']['name'][$key],
                            'tmp_name' => $_FILES['images']['tmp_name'][$key],
                            'size' => $_FILES['images']['size'][$key],
                            'error' => $_FILES['images']['error'][$key]
                        ];
                        $uploaded_image = uploadImage($file_temp);
                        if ($uploaded_image) {
                            $images[] = $uploaded_image;
                        }
                    }
                }
            }
            
            // Handle image URLs
            if (!empty($_POST['image_urls'])) {
                $url_list = explode("\n", $_POST['image_urls']);
                foreach ($url_list as $url) {
                    $url = trim($url);
                    if (!empty($url)) {
                        $images[] = $url;
                    }
                }
            }
            
            // Use default image if no images provided
            if (empty($images)) {
                $images[] = 'assets/images/products/bicaq-1.jpg';
            }
            
            $main_image = $images[0];
            $all_images = json_encode($images);
            
            if (addProduct($name, $price, $discount_price, $main_image, $description, $category, $subcategory, $rating, $is_featured, $stock_quantity, $all_images)) {
                $success_message = "Məhsul uğurla əlavə edildi!";
            } else {
                $error_message = "Məhsul əlavə edilərkən xəta baş verdi!";
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
    }
    
    // Update product
    if (isset($_POST['update_product'])) {
        try {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $price = $_POST['price'];
            $discount_price = !empty($_POST['discount_price']) ? $_POST['discount_price'] : null;
            $description = $_POST['description'];
            $category = $_POST['category'];
            $subcategory = $_POST['subcategory'];
            $rating = $_POST['rating'];
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $stock_quantity = $_POST['stock_quantity'];
            
            // Get current product for image handling
            $current_product = getProductById($id);
            $images = json_decode($current_product['all_images'], true) ?: [$current_product['image']];
            
            // Handle new image uploads
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                // Delete old uploaded images
                foreach ($images as $old_image) {
                    if (strpos($old_image, 'uploads/') !== false && file_exists($old_image)) {
                        unlink($old_image);
                    }
                }
                
                $images = [];
                foreach ($_FILES['images']['name'] as $key => $name_file) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $file_temp = [
                            'name' => $_FILES['images']['name'][$key],
                            'tmp_name' => $_FILES['images']['tmp_name'][$key],
                            'size' => $_FILES['images']['size'][$key],
                            'error' => $_FILES['images']['error'][$key]
                        ];
                        $uploaded_image = uploadImage($file_temp);
                        if ($uploaded_image) {
                            $images[] = $uploaded_image;
                        }
                    }
                }
            } elseif (!empty($_POST['image_urls'])) {
                $images = [];
                $url_list = explode("\n", $_POST['image_urls']);
                foreach ($url_list as $url) {
                    $url = trim($url);
                    if (!empty($url)) {
                        $images[] = $url;
                    }
                }
            }
            
            $main_image = $images[0];
            $all_images = json_encode($images);
            
            if (updateProduct($id, $name, $price, $discount_price, $main_image, $description, $category, $subcategory, $rating, $is_featured, $stock_quantity, $all_images)) {
                $success_message = "Məhsul uğurla yeniləndi!";
            } else {
                $error_message = "Məhsul yenilənərkən xəta baş verdi!";
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
    }
    
    // Delete product
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        if (deleteProduct($_GET['delete'])) {
            $success_message = "Məhsul uğurla silindi!";
        } else {
            $error_message = "Məhsul silinərkən xəta baş verdi!";
        }
    }
    
    // Get product for editing
    $edit_product = null;
    if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
        $edit_product = getProductById($_GET['edit']);
    }
    
    // Get all products
    $products = getAllProducts();
}

// Define categories and subcategories
$categories = [
    'Aşpaz Bıçaqları' => ['Bunka', 'Gyuto', 'Santokus', 'Kiritsuke'],
    'Balıq bıçaqları' => ['Yanagiba', 'Deba'],
    'Ət bıçaqları' => ['Sujihiki', 'Garasuki', 'Boning'],
    'Tərəvəz bıçaqları' => ['Usuba', 'Nakiri'],
    'Kiçik Bıçaqlar' => ['Petty', 'Peeling'],
    'Aşpaz Aksesuarları' => ['Dəri çantalar', 'Parça çantalar'],
    'Mətbəx' => ['Avadanlıqlar', 'Aksesuarlar']
];
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Knife Store</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.3.0.css" />
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Noto Sans', sans-serif;
        }
        .admin-header {
            background-color: #343a40;
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }
        .admin-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            padding: 2rem;
        }
        .product-table img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        .btn-action {
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .image-preview {
            max-width: 150px;
            max-height: 150px;
            margin: 5px;
            border-radius: 4px;
            object-fit: cover;
            display: inline-block;
        }
        .image-preview-container {
            margin-top: 15px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            min-height: 60px;
        }
        .star-rating {
            color: #ffc107;
        }
        .discount-badge {
            background-color: #dc3545;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 12px;
        }
        .featured-badge {
            background-color: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 12px;
        }
        .image-upload-section {
            border: 2px dashed #dee2e6;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(220, 53, 69, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            font-size: 12px;
            cursor: pointer;
        }
        .image-preview-wrapper {
            position: relative;
            display: inline-block;
        }
    </style>
</head>
<body>
    <?php if (!$is_logged_in): ?>
        <!-- Login Form -->
        <div class="login-container">
            <div class="admin-card">
                <h2 class="text-center mb-4">Admin Girişi</h2>
                <?php if (isset($login_error)): ?>
                    <div class="alert alert-danger"><?php echo $login_error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">İstifadəçi adı</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Şifrə</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100">Daxil ol</button>
                </form>
                <div class="mt-3 text-center">
                    <small class="text-muted">Default: admin / admin123</small>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Admin Panel -->
        <div class="admin-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col">
                        <h1>Admin Panel - Knife Store</h1>
                    </div>
                    <div class="col-auto">
                        <a href="index.php" class="btn btn-outline-light me-2">Sayta bax</a>
                        <a href="?logout=1" class="btn btn-outline-light">Çıxış</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Add/Edit Product Form -->
            <div class="admin-card">
                <h3><?php echo $edit_product ? 'Məhsulu redaktə et' : 'Yeni məhsul əlavə et'; ?></h3>
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($edit_product): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_product['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Məhsul adı</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="price" class="form-label">Qiymət (AZN)</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" 
                                       value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="discount_price" class="form-label">Endirimli qiymət (AZN)</label>
                                <input type="number" step="0.01" class="form-control" id="discount_price" name="discount_price" 
                                       value="<?php echo $edit_product ? $edit_product['discount_price'] : ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Kateqoriya</label>
                                <select class="form-control" id="category" name="category" required onchange="updateSubcategories()">
                                    <option value="">Kateqoriya seçin</option>
                                    <?php foreach ($categories as $cat => $subcats): ?>
                                        <option value="<?php echo $cat; ?>" <?php echo ($edit_product && $edit_product['category'] == $cat) ? 'selected' : ''; ?>>
                                            <?php echo $cat; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="subcategory" class="form-label">Alt kateqoriya</label>
                                <select class="form-control" id="subcategory" name="subcategory">
                                    <option value="">Alt kateqoriya seçin</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Məhsul şəkilləri (çox seçim mümkündür)</label>
                                <div class="image-upload-section">
                                    <input type="file" class="form-control mb-2" id="images" name="images[]" 
                                           accept="image/*" multiple onchange="previewImages(this)">
                                    <div class="text-muted small">və ya şəkil URL-lərini daxil edin (hər sətirdə bir URL):</div>
                                    <textarea class="form-control mt-2" id="image_urls" name="image_urls" rows="3" 
                                              placeholder="https://example.com/image1.jpg&#10;https://example.com/image2.jpg"><?php 
                                              if ($edit_product && !empty($edit_product['all_images'])) {
                                                  $images = json_decode($edit_product['all_images'], true);
                                                  echo implode("\n", $images);
                                              } else {
                                                  echo $edit_product ? htmlspecialchars($edit_product['image']) : 'assets/images/products/bicaq-1.jpg';
                                              }
                                              ?></textarea>
                                </div>
                                <div id="image-preview-container" class="image-preview-container">
                                    <?php if ($edit_product && !empty($edit_product['all_images'])): ?>
                                        <?php 
                                        $images = json_decode($edit_product['all_images'], true);
                                        foreach ($images as $img): ?>
                                            <div class="image-preview-wrapper">
                                                <img src="<?php echo htmlspecialchars($img); ?>" alt="Product image" class="image-preview">
                                                <button type="button" class="remove-image" onclick="this.parentElement.remove()">×</button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php elseif ($edit_product && $edit_product['image']): ?>
                                        <div class="image-preview-wrapper">
                                            <img src="<?php echo htmlspecialchars($edit_product['image']); ?>" alt="Product image" class="image-preview">
                                            <button type="button" class="remove-image" onclick="this.parentElement.remove()">×</button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="rating" class="form-label">Reytinq (1-5)</label>
                                <select class="form-control" id="rating" name="rating">
                                    <option value="0" <?php echo ($edit_product && $edit_product['rating'] == 0) ? 'selected' : ''; ?>>Reytinq yoxdur</option>
                                    <option value="1.0" <?php echo ($edit_product && $edit_product['rating'] == 1.0) ? 'selected' : ''; ?>>⭐ (1.0)</option>
                                    <option value="1.5" <?php echo ($edit_product && $edit_product['rating'] == 1.5) ? 'selected' : ''; ?>>⭐ (1.5)</option>
                                    <option value="2.0" <?php echo ($edit_product && $edit_product['rating'] == 2.0) ? 'selected' : ''; ?>>⭐⭐ (2.0)</option>
                                    <option value="2.5" <?php echo ($edit_product && $edit_product['rating'] == 2.5) ? 'selected' : ''; ?>>⭐⭐ (2.5)</option>
                                    <option value="3.0" <?php echo ($edit_product && $edit_product['rating'] == 3.0) ? 'selected' : ''; ?>>⭐⭐⭐ (3.0)</option>
                                    <option value="3.5" <?php echo ($edit_product && $edit_product['rating'] == 3.5) ? 'selected' : ''; ?>>⭐⭐⭐ (3.5)</option>
                                    <option value="4.0" <?php echo ($edit_product && $edit_product['rating'] == 4.0) ? 'selected' : ''; ?>>⭐⭐⭐⭐ (4.0)</option>
                                    <option value="4.5" <?php echo ($edit_product && $edit_product['rating'] == 4.5) ? 'selected' : ''; ?>>⭐⭐⭐⭐ (4.5)</option>
                                    <option value="5.0" <?php echo ($edit_product && $edit_product['rating'] == 5.0) ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ (5.0)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="stock_quantity" class="form-label">Stok miqdarı</label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0"
                                       value="<?php echo $edit_product ? $edit_product['stock_quantity'] : 0; ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3 mt-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1"
                                           <?php echo ($edit_product && $edit_product['is_featured']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_featured">
                                        Xüsusi məhsul (Featured)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Təsvir</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" name="<?php echo $edit_product ? 'update_product' : 'add_product'; ?>" 
                                class="btn btn-primary">
                            <?php echo $edit_product ? 'Yenilə' : 'Əlavə et'; ?>
                        </button>
                        <?php if ($edit_product): ?>
                            <a href="admin.php" class="btn btn-secondary">Ləğv et</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Products List -->
            <div class="admin-card">
                <h3>Məhsullar Siyahısı</h3>
                <?php if (!empty($products)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped product-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Şəkil</th>
                                    <th>Ad</th>
                                    <th>Qiymət</th>
                                    <th>Endirim</th>
                                    <th>Reytinq</th>
                                    <th>Stok</th>
                                    <th>Kateqoriya</th>
                                    <th>Alt Kateqoriya</th>
                                    <th>Status</th>
                                    <th>Tarix</th>
                                    <th>Əməliyyatlar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo $product['id']; ?></td>
                                        <td>
                                            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                                        </td>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td>
                                            <?php if ($product['discount_price']): ?>
                                                <span class="text-decoration-line-through text-muted"><?php echo number_format($product['price'], 2); ?> AZN</span><br>
                                                <strong class="text-danger"><?php echo number_format($product['discount_price'], 2); ?> AZN</strong>
                                            <?php else: ?>
                                                <?php echo number_format($product['price'], 2); ?> AZN
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($product['discount_price']): ?>
                                                <?php 
                                                $discount_percent = round((($product['price'] - $product['discount_price']) / $product['price']) * 100);
                                                echo '<span class="discount-badge">-' . $discount_percent . '%</span>';
                                                ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($product['rating'] > 0): ?>
                                                <span class="star-rating">
                                                    <?php
                                                    $rating = $product['rating'];
                                                    $full_stars = floor($rating);
                                                    $half_star = ($rating - $full_stars) >= 0.5;
                                                    
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        if ($i <= $full_stars) {
                                                            echo '★';
                                                        } elseif ($half_star && $i == $full_stars + 1) {
                                                            echo '☆';
                                                        } else {
                                                            echo '☆';
                                                        }
                                                    }
                                                    echo ' (' . $rating . ')';
                                                    ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Reytinq yoxdur</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $product['stock_quantity'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo $product['stock_quantity']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['category']); ?></td>
                                        <td><?php echo htmlspecialchars($product['subcategory'] ?? '-'); ?></td>
                                        <td>
                                            <?php if ($product['is_featured']): ?>
                                                <span class="featured-badge">Featured</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d.m.Y', strtotime($product['created_at'])); ?></td>
                                        <td>
                                            <a href="?edit=<?php echo $product['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary btn-action">
                                                <i class="lni lni-pencil"></i> Redaktə
                                            </a>
                                            <a href="?delete=<?php echo $product['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger btn-action"
                                               onclick="return confirm('Bu məhsulu silmək istədiyinizə əminsiniz?')">
                                                <i class="lni lni-trash-can"></i> Sil
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Hələlik məhsul yoxdur.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <script src="assets/js/bootstrap.min.js"></script>
    <script>
        // Categories and subcategories data
        const categories = <?php echo json_encode($categories); ?>;
        
        function updateSubcategories() {
            const categorySelect = document.getElementById('category');
            const subcategorySelect = document.getElementById('subcategory');
            const selectedCategory = categorySelect.value;
            
            // Clear existing options
            subcategorySelect.innerHTML = '<option value="">Alt kateqoriya seçin</option>';
            
            if (selectedCategory && categories[selectedCategory]) {
                categories[selectedCategory].forEach(function(subcat) {
                    const option = document.createElement('option');
                    option.value = subcat;
                    option.textContent = subcat;
                    subcategorySelect.appendChild(option);
                });
            }
        }
        
        function previewImages(input) {
            const container = document.getElementById('image-preview-container');
            container.innerHTML = ''; // Clear existing previews
            
            if (input.files && input.files.length > 0) {
                Array.from(input.files).forEach(function(file) {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const wrapper = document.createElement('div');
                            wrapper.className = 'image-preview-wrapper';
                            
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'image-preview';
                            img.alt = 'Preview';
                            
                            const removeBtn = document.createElement('button');
                            removeBtn.type = 'button';
                            removeBtn.className = 'remove-image';
                            removeBtn.innerHTML = '×';
                            removeBtn.onclick = function() {
                                wrapper.remove();
                            };
                            
                            wrapper.appendChild(img);
                            wrapper.appendChild(removeBtn);
                            container.appendChild(wrapper);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        }
        
        // Initialize subcategories on page load
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($edit_product && !empty($edit_product['subcategory'])): ?>
                updateSubcategories();
                document.getElementById('subcategory').value = '<?php echo htmlspecialchars($edit_product['subcategory']); ?>';
            <?php endif; ?>
        });
        
        // Clear file input when URL is entered
        document.getElementById('image_urls').addEventListener('input', function() {
            if (this.value.trim() !== '') {
                document.getElementById('images').value = '';
                document.getElementById('image-preview-container').innerHTML = '';
            }
        });
    </script>
</body>
</html>
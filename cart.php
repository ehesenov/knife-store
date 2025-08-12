<?php
require_once 'config.php';

// Handle AJAX requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'update_quantity') {
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);
        
        if ($quantity > 0) {
            $product = getProductById($product_id);
            if ($product && $product['stock_quantity'] >= $quantity) {
                $_SESSION['cart'][$product_id] = $quantity;
                echo json_encode([
                    'success' => true,
                    'message' => 'Miqdar yeniləndi!',
                    'cart_total' => getCartTotal(),
                    'cart_count' => getCartCount()
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Stokda kifayət qədər məhsul yoxdur!']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Yanlış miqdar!']);
        }
        exit;
    }
    
    if ($_POST['action'] === 'remove_item') {
        $product_id = intval($_POST['product_id']);
        removeFromCart($product_id);
        echo json_encode([
            'success' => true,
            'message' => 'Məhsul səbətdən silindi!',
            'cart_total' => getCartTotal(),
            'cart_count' => getCartCount()
        ]);
        exit;
    }
    
    if ($_POST['action'] === 'clear_cart') {
        clearCart();
        echo json_encode([
            'success' => true,
            'message' => 'Səbət təmizləndi!',
            'cart_total' => 0,
            'cart_count' => 0
        ]);
        exit;
    }
}

// Get cart items
$cart_items = getCartItems();
$cart_total = getCartTotal();
$cart_count = getCartCount();
?>
<!DOCTYPE html>
<html class="no-js" lang="az">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>Səbət - Knife Store</title>
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/favicon.svg" />

    <!-- ========================= CSS here ========================= -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.3.0.css" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans&display=swap" rel="stylesheet">
    
    <style>
        .cart-container {
            margin: 50px 0;
        }
        .cart-table {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .cart-table table {
            margin-bottom: 0;
        }
        .cart-table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
            padding: 20px 15px;
        }
        .cart-table td {
            padding: 20px 15px;
            vertical-align: middle;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        .product-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        .product-category {
            font-size: 14px;
            color: #6c757d;
        }
        .quantity-input {
            width: 80px;
            text-align: center;
        }
        .btn-quantity {
            background: none;
            border: 1px solid #dee2e6;
            padding: 5px 10px;
            cursor: pointer;
        }
        .btn-quantity:hover {
            background-color: #f8f9fa;
        }
        .remove-btn {
            color: #dc3545;
            cursor: pointer;
            font-size: 20px;
        }
        .remove-btn:hover {
            color: #c82333;
        }
        .cart-summary {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 30px;
            position: sticky;
            top: 20px;
        }
        .empty-cart {
            text-align: center;
            padding: 100px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .empty-cart i {
            font-size: 64px;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        .price-display {
            font-weight: 700;
            color: #333;
        }
        .discount-price {
            color: #dc3545;
        }
        .original-price {
            color: #6c757d;
            text-decoration: line-through;
            font-size: 14px;
        }
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            font-weight: 600;
            min-width: 18px;
            text-align: center;
        }
    </style>
</head>

<body>
    <!-- Start Header Area -->
    <header class="header navbar-area">
        <!-- Start Header Middle -->
        <div class="header-middle">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-3 col-7">
                        <!-- Start Header Logo -->
                        <a class="navbar-brand" href="index.php">
                            <img src="assets/images/logo/logo.svg" alt="Logo">
                        </a>
                        <!-- End Header Logo -->
                    </div>
                    <div class="col-lg-5 col-md-7 d-xs-none">
                        <!-- Start Main Menu Search -->
                        <div class="main-menu-search">
                            <div class="navbar-search search-style-5">
                                <div class="search-input">
                                    <input type="text" placeholder="Axtar">
                                </div>
                                <div class="search-btn">
                                    <button><i class="lni lni-search-alt"></i></button>
                                </div>
                            </div>
                        </div>
                        <!-- End Main Menu Search -->
                    </div>
                    <div class="col-lg-4 col-md-2 col-5">
                        <div class="middle-right-area">
                            <div class="nav-hotline">
                                <i class="lni lni-phone"></i>
                                <h3>
                                    <span>+994 51 829 21 71</span>
                                </h3>
                                <!-- Cart Icon -->
                                <a href="cart.php" class="cart-icon" style="position: absolute; right: -40px; top: 50%; transform: translateY(-50%); font-size: 24px; color: #333;">
                                    <i class="lni lni-cart"></i>
                                    <?php if ($cart_count > 0): ?>
                                        <span class="cart-badge"><?php echo $cart_count; ?></span>
                                    <?php endif; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Header Middle -->
        
        <!-- Start Header Bottom -->
        <div class="container">
            <div class="row align-items-center justify-content-between">
                <div class="col-lg-8 col-md-6 col-12">
                    <div class="nav-inner">
                        <!-- Start Navbar -->
                        <nav class="navbar navbar-expand-lg">
                            <div class="collapse navbar-collapse sub-menu-bar">
                                <ul class="navbar-nav ms-auto">
                                    <li class="nav-item">
                                        <a class="nav-link" href="index.php">Əsas Səhifə</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="about-us.html">Haqqımızda</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="contact.html">Əlaqə</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="product-grids.html">Məhsullar</a>
                                    </li>
                                </ul>
                            </div>
                        </nav>
                        <!-- End Navbar -->
                    </div>
                </div>
            </div>
        </div>
        <!-- End Header Bottom -->
    </header>
    <!-- End Header Area -->

    <!-- Start Breadcrumb -->
    <div class="container">
        <nav aria-label="breadcrumb" style="margin: 20px 0;">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Əsas Səhifə</a></li>
                <li class="breadcrumb-item active" aria-current="page">Səbət</li>
            </ol>
        </nav>
    </div>
    <!-- End Breadcrumb -->

    <!-- Start Cart Area -->
    <section class="cart-container">
        <div class="container">
            <?php if (!empty($cart_items)): ?>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="cart-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Məhsul</th>
                                        <th>Qiymət</th>
                                        <th>Miqdar</th>
                                        <th>Cəmi</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items as $item): ?>
                                        <tr data-product-id="<?php echo $item['id']; ?>">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                         class="product-image me-3">
                                                    <div>
                                                        <div class="product-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                                        <div class="product-category"><?php echo htmlspecialchars($item['category']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="price-display">
                                                    <?php if ($item['discount_price']): ?>
                                                        <div class="original-price"><?php echo number_format($item['price'], 2); ?> AZN</div>
                                                        <div class="discount-price"><?php echo number_format($item['discount_price'], 2); ?> AZN</div>
                                                    <?php else: ?>
                                                        <?php echo number_format($item['price'], 2); ?> AZN
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <button class="btn-quantity" onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['cart_quantity'] - 1; ?>)">-</button>
                                                    <input type="number" 
                                                           class="form-control quantity-input mx-2" 
                                                           value="<?php echo $item['cart_quantity']; ?>"
                                                           min="1" 
                                                           max="<?php echo $item['stock_quantity']; ?>"
                                                           onchange="updateQuantity(<?php echo $item['id']; ?>, this.value)">
                                                    <button class="btn-quantity" onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['cart_quantity'] + 1; ?>)">+</button>
                                                </div>
                                                <small class="text-muted">Maks: <?php echo $item['stock_quantity']; ?></small>
                                            </td>
                                            <td>
                                                <div class="price-display">
                                                    <?php 
                                                    $item_price = $item['discount_price'] ?: $item['price'];
                                                    $item_total = $item_price * $item['cart_quantity'];
                                                    echo number_format($item_total, 2); 
                                                    ?> AZN
                                                </div>
                                            </td>
                                            <td>
                                                <i class="lni lni-trash-can remove-btn" 
                                                   onclick="removeItem(<?php echo $item['id']; ?>)"
                                                   title="Məhsulu sil"></i>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <a href="index.php" class="btn btn-outline-primary">
                                <i class="lni lni-arrow-left"></i> Alış-verişə davam et
                            </a>
                            <button class="btn btn-outline-danger ms-2" onclick="clearCart()">
                                <i class="lni lni-trash-can"></i> Səbəti təmizlə
                            </button>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="cart-summary">
                            <h4 class="mb-4">Sifariş Xülasəsi</h4>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <span>Məhsullar (<?php echo $cart_count; ?> ədəd):</span>
                                <span class="fw-bold"><?php echo number_format($cart_total, 2); ?> AZN</span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <span>Çatdırılma:</span>
                                <span class="text-success fw-bold">Pulsuz</span>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-4">
                                <span class="fw-bold">Ümumi:</span>
                                <span class="fw-bold fs-5 text-primary"><?php echo number_format($cart_total, 2); ?> AZN</span>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary btn-lg" onclick="proceedToCheckout()">
                                    <i class="lni lni-credit-cards"></i> Sifariş Ver
                                </button>
                                <button class="btn btn-success" onclick="orderViaWhatsApp()">
                                    <i class="lni lni-whatsapp"></i> WhatsApp ilə Sifariş
                                </button>
                            </div>
                            
                            <div class="mt-3 text-center">
                                <small class="text-muted">
                                    <i class="lni lni-lock"></i> Təhlükəsiz ödəniş sistemi
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-cart">
                    <i class="lni lni-cart"></i>
                    <h3>Səbətiniz boşdur</h3>
                    <p class="text-muted mb-4">Sevdiyiniz məhsulları səbətə əlavə edin</p>
                    <a href="index.php" class="btn btn-primary">
                        <i class="lni lni-arrow-left"></i> Alış-verişə başla
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <!-- End Cart Area -->

    <!-- Start Footer Area -->
    <footer class="footer">
        <!-- Start Footer Middle -->
        <div class="footer-middle">
            <div class="container">
                <div class="bottom-inner">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 col-12">
                            <!-- Single Widget -->
                            <div class="single-footer f-contact">
                                <p class="phone">+994 51 829 21 71</p>
                                <p class="mail">
                                    <a href="mailto:knifestore2024@gmail.com">knifestore2024@gmail.com</a>
                                </p>
                            </div>
                            <!-- End Single Widget -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Footer Middle -->
    </footer>
    <!--/ End Footer Area -->

    <!-- ========================= JS here ========================= -->
    <script src="assets/js/bootstrap.min.js"></script>
    <script>
        function updateQuantity(productId, newQuantity) {
            if (newQuantity < 1) {
                removeItem(productId);
                return;
            }
            
            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_quantity&product_id=${productId}&quantity=${newQuantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Xəta baş verdi!');
            });
        }
        
        function removeItem(productId) {
            if (confirm('Bu məhsulu səbətdən silmək istədiyinizə əminsiniz?')) {
                fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove_item&product_id=${productId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Xəta baş verdi!');
                });
            }
        }
        
        function clearCart() {
            if (confirm('Bütün məhsulları səbətdən silmək istədiyinizə əminsiniz?')) {
                fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=clear_cart'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Xəta baş verdi!');
                });
            }
        }
        
        function orderViaWhatsApp() {
            <?php if (!empty($cart_items)): ?>
                let message = "Salam! Aşağıdakı məhsulları sifariş vermək istəyirəm:\n\n";
                <?php foreach ($cart_items as $item): ?>
                    message += "<?php echo htmlspecialchars($item['name']); ?> - <?php echo $item['cart_quantity']; ?> ədəd - <?php echo number_format(($item['discount_price'] ?: $item['price']) * $item['cart_quantity'], 2); ?> AZN\n";
                <?php endforeach; ?>
                message += "\nÜmumi: <?php echo number_format($cart_total, 2); ?> AZN";
                
                const whatsappUrl = `https://wa.me/994518292171?text=${encodeURIComponent(message)}`;
                window.open(whatsappUrl, '_blank');
            <?php endif; ?>
        }
        
        function proceedToCheckout() {
            // Redirect to checkout page (to be created)
            alert('Checkout səhifəsi hazırlanır...');
            // window.location.href = 'checkout.php';
        }
    </script>
</body>

</html>
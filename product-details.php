<?php
require_once 'config.php';

// Get product ID from URL
$product_id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;

if (!$product_id) {
    header('Location: index.php');
    exit;
}

// Get product details
$product = getProductById($product_id);

if (!$product) {
    header('Location: index.php');
    exit;
}

// Get related products from same category
$related_products = getProductsByCategory($product['category'], 4);
// Remove current product from related products
$related_products = array_filter($related_products, function($p) use ($product_id) {
    return $p['id'] != $product_id;
});
$related_products = array_slice($related_products, 0, 3); // Limit to 3 products
?>
<!DOCTYPE html>
<html class="no-js" lang="az">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title><?php echo htmlspecialchars($product['name']); ?> - Knife Store</title>
    <meta name="description" content="<?php echo htmlspecialchars($product['description']); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/favicon.svg" />

    <!-- ========================= CSS here ========================= -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.3.0.css" />
    <link rel="stylesheet" href="assets/css/tiny-slider.css" />
    <link rel="stylesheet" href="assets/css/glightbox.min.css" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans&display=swap" rel="stylesheet">
    
    <style>
        .product-images {
            position: sticky;
            top: 20px;
        }
        .product-main-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .product-info {
            padding: 20px 0;
        }
        .product-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #343a40;
        }
        .product-rating {
            margin-bottom: 20px;
        }
        .star-rating {
            color: #ffc107;
            font-size: 1.2rem;
        }
        .product-price {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .original-price {
            color: #6c757d;
            text-decoration: line-through;
            font-size: 1.5rem;
            margin-right: 10px;
        }
        .discount-price {
            color: #dc3545;
        }
        .discount-badge {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-left: 10px;
        }
        .product-meta {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .stock-status {
            padding: 10px 15px;
            border-radius: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            display: inline-block;
        }
        .in-stock {
            background-color: #d4edda;
            color: #155724;
        }
        .out-of-stock {
            background-color: #f8d7da;
            color: #721c24;
        }
        .quantity-selector {
            width: 80px;
            margin: 0 10px;
        }
        .btn-add-to-cart {
            background-color: #007bff;
            border-color: #007bff;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
        }
        .btn-add-to-cart:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .product-description {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            margin: 30px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .related-products {
            margin-top: 50px;
        }
        .breadcrumb {
            background-color: transparent;
            padding: 0;
            margin: 20px 0;
        }
        .breadcrumb-item a {
            color: #007bff;
            text-decoration: none;
        }
        .breadcrumb-item a:hover {
            text-decoration: underline;
        }
        .featured-badge {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-left: 10px;
        }
    </style>
</head>

<body>
    <!-- Preloader -->
    <div class="preloader">
        <div class="preloader-inner">
            <div class="preloader-icon">
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
    <!-- /End Preloader -->

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
                            <!-- navbar search start -->
                            <div class="navbar-search search-style-5">
                                <div class="search-select">
                                   <div class="select-position">
                                        <select id="select1">
                                            
                                        </select>
                                    </div>
                                </div>
                                <div class="search-input">
                                    <input type="text" placeholder="Axtar">
                                </div>
                                <div class="search-btn">
                                    <button><i class="lni lni-search-alt"></i></button>
                                </div>
                            </div>
                            <!-- navbar search Ends -->
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
                            <button class="navbar-toggler mobile-menu-btn" type="button" data-bs-toggle="collapse"
                                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                                aria-expanded="false" aria-label="Toggle navigation">
                                <span class="toggler-icon"></span>
                                <span class="toggler-icon"></span>
                                <span class="toggler-icon"></span>
                            </button>
                            
                            <div class="collapse navbar-collapse sub-menu-bar" id="navbarSupportedContent">
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
                <div class="col-lg-4 col-md-6 col-12">
                    <!-- Start Nav Social -->
                    <div class="nav-social">
                        <h5 class="title">Bizi İzləyin:</h5>
                        <ul>
                            <li><a href="javascript:void(0)"><i class="lni lni-facebook-filled"></i></a></li>
                            <li><a href="javascript:void(0)"><i class="lni lni-twitter-original"></i></a></li>
                            <li><a href="https://www.instagram.com/kniife_store/"><i class="lni lni-instagram"></i></a></li>
                            <li><a href="javascript:void(0)"><i class="lni lni-skype"></i></a></li>
                        </ul>
                    </div>
                    <!-- End Nav Social -->
                </div>
            </div>
        </div>
        <!-- End Header Bottom -->
    </header>
    <!-- End Header Area -->

    <!-- Start Breadcrumb -->
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Əsas Səhifə</a></li>
                <li class="breadcrumb-item"><a href="product-grids.html">Məhsullar</a></li>
                <li class="breadcrumb-item"><a href="#"><?php echo htmlspecialchars($product['category']); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>
    </div>
    <!-- End Breadcrumb -->

    <!-- Start Product Details -->
    <section class="product-details section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-12">
                    <div class="product-images">
                        <div class="main-image">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="product-main-image">
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-12">
                    <div class="product-info">
                        <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                        
                        <?php if ($product['is_featured']): ?>
                            <span class="featured-badge">Xüsusi Məhsul</span>
                        <?php endif; ?>
                        
                        <!-- Rating -->
                        <?php if ($product['rating'] > 0): ?>
                            <div class="product-rating">
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
                                    ?>
                                </span>
                                <span class="text-muted ms-2">(<?php echo $rating; ?> / 5.0)</span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Price -->
                        <div class="product-price">
                            <?php if ($product['discount_price']): ?>
                                <span class="original-price"><?php echo number_format($product['price'], 2); ?> AZN</span>
                                <span class="discount-price"><?php echo number_format($product['discount_price'], 2); ?> AZN</span>
                                <?php 
                                $discount_percent = round((($product['price'] - $product['discount_price']) / $product['price']) * 100);
                                echo '<span class="discount-badge">-' . $discount_percent . '% ENDİRİM</span>';
                                ?>
                            <?php else: ?>
                                <span><?php echo number_format($product['price'], 2); ?> AZN</span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Stock Status -->
                        <div class="stock-status <?php echo $product['stock_quantity'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                            <?php if ($product['stock_quantity'] > 0): ?>
                                ✓ Stokda mövcuddur (<?php echo $product['stock_quantity']; ?> ədəd)
                            <?php else: ?>
                                ✗ Stokda yoxdur
                            <?php endif; ?>
                        </div>
                        
                        <!-- Product Meta -->
                        <div class="product-meta">
                            <p><strong>Kateqoriya:</strong> <?php echo htmlspecialchars($product['category']); ?></p>
                            <p><strong>Məhsul Kodu:</strong> KS<?php echo str_pad($product['id'], 4, '0', STR_PAD_LEFT); ?></p>
                        </div>
                        
                        <!-- Add to Cart -->
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <div class="product-add-to-cart">
                                <div class="d-flex align-items-center mb-3">
                                    <label for="quantity" class="me-2"><strong>Miqdar:</strong></label>
                                    <input type="number" id="quantity" class="form-control quantity-selector" 
                                           value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                                </div>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary btn-add-to-cart" onclick="addToCart()">
                                        <i class="lni lni-cart"></i> Səbətə Əlavə Et
                                    </button>
                                    <button class="btn btn-outline-primary" onclick="buyNow()">
                                        <i class="lni lni-heart"></i> İndi Sifariş Ver
                                    </button>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="d-grid gap-2">
                                <button class="btn btn-secondary" disabled>
                                    Stokda Yoxdur
                                </button>
                                <button class="btn btn-outline-primary" onclick="notifyWhenAvailable()">
                                    <i class="lni lni-envelope"></i> Məlumat Ver
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Product Description -->
            <?php if ($product['description']): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="product-description">
                            <h3>Məhsul Haqqında</h3>
                            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Related Products -->
            <?php if (!empty($related_products)): ?>
                <div class="row related-products">
                    <div class="col-12">
                        <h3>Oxşar Məhsullar</h3>
                        <div class="row">
                            <?php foreach ($related_products as $related): ?>
                                <div class="col-lg-4 col-md-6 col-12">
                                    <!-- Start Single Product -->
                                    <div class="single-product">
                                        <div class="product-image">
                                            <img src="<?php echo htmlspecialchars($related['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($related['name']); ?>">
                                            <div class="button">
                                                <a href="product-details.php?id=<?php echo $related['id']; ?>" class="btn">
                                                    <i class="lni lni-cart"></i>Ətraflı
                                                </a>
                                            </div>
                                        </div>
                                        <div class="product-info">
                                            <h4 class="title">
                                                <a href="product-details.php?id=<?php echo $related['id']; ?>">
                                                    <?php echo htmlspecialchars($related['name']); ?>
                                                </a>
                                            </h4>
                                            <?php if ($related['rating'] > 0): ?>
                                                <ul class="review">
                                                    <?php
                                                    $rating = $related['rating'];
                                                    $full_stars = floor($rating);
                                                    $half_star = ($rating - $full_stars) >= 0.5;
                                                    
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        if ($i <= $full_stars) {
                                                            echo '<li><i class="lni lni-star-filled"></i></li>';
                                                        } elseif ($half_star && $i == $full_stars + 1) {
                                                            echo '<li><i class="lni lni-star"></i></li>';
                                                        } else {
                                                            echo '<li><i class="lni lni-star"></i></li>';
                                                        }
                                                    }
                                                    ?>
                                                </ul>
                                            <?php endif; ?>
                                            <div class="price">
                                                <?php if ($related['discount_price']): ?>
                                                    <span class="text-muted text-decoration-line-through me-2">
                                                        <?php echo number_format($related['price'], 2); ?> AZN
                                                    </span>
                                                    <span class="text-danger">
                                                        <?php echo number_format($related['discount_price'], 2); ?> AZN
                                                    </span>
                                                <?php else: ?>
                                                    <span><?php echo number_format($related['price'], 2); ?> AZN</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Single Product -->
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <!-- End Product Details -->

    <!-- Start Footer Area -->
    <footer class="footer">
        <!-- Start Footer Top -->
        <div class="footer-top">
            <div class="container">
                <div class="inner-content">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 col-12">
                           
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Footer Middle -->
    </footer>
    <!--/ End Footer Area -->

    <!-- ========================= scroll-top ========================= -->
    <a href="#" class="scroll-top">
        <i class="lni lni-chevron-up"></i>
    </a>

    <!-- ========================= JS here ========================= -->
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/tiny-slider.js"></script>
    <script src="assets/js/glightbox.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
        function addToCart() {
            const quantity = document.getElementById('quantity').value;
            const productId = <?php echo $product['id']; ?>;
            
            // Here you would normally send an AJAX request to add the item to cart
            alert('Məhsul səbətə əlavə edildi! Miqdar: ' + quantity);
            
            // Example AJAX call (uncomment and modify as needed):
            /*
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Məhsul səbətə əlavə edildi!');
                } else {
                    alert('Xəta baş verdi!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Xəta baş verdi!');
            });
            */
        }
        
        function buyNow() {
            const quantity = document.getElementById('quantity').value;
            const productId = <?php echo $product['id']; ?>;
            
            // Redirect to checkout or contact page
            const message = `Salam! ${encodeURIComponent('<?php echo $product['name']; ?>')} məhsulunu (${quantity} ədəd) sifariş vermək istəyirəm.`;
            const whatsappUrl = `https://wa.me/994518292171?text=${message}`;
            window.open(whatsappUrl, '_blank');
        }
        
        function notifyWhenAvailable() {
            const productName = '<?php echo htmlspecialchars($product['name']); ?>';
            const message = `Salam! ${encodeURIComponent(productName)} məhsulu stokda olduqda məlumat vermənizi xahiş edirəm.`;
            const whatsappUrl = `https://wa.me/994518292171?text=${message}`;
            window.open(whatsappUrl, '_blank');
        }
    </script>
</body>

</html>md-4 col-12">
                            <div class="footer-logo">
                                <a href="index.php">
                                    <img src="assets/images/logo/logo.svg" alt="#">
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-9 col-md-8 col-12">
                            <div class="footer-newsletter">
                                <div class="newsletter-form-head">
                                    <form action="#" method="get" target="_blank" class="newsletter-form">
                                        <input name="EMAIL" placeholder="Yeniliklərə abone olun.." type="email">
                                        <div class="button">
                                            <button class="btn">Abone ol<span class="dir-part"></span></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Footer Top -->
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
                        <div class="col-lg-3 col-md-6 col-12">
                           
                            <!-- End Single Widget -->
                        </div>
                        <div class="col-lg-3 col-md-6 col-12">
                            <!-- Single Widget -->
                            <div class="single-footer f-link">
                                <ul>
                                    <li><a href="javascript:void(0)">Haqqımızda</a></li>
                                    <li><a href="javascript:void(0)">Əlaqə</a></li>
                                </ul>
                            </div>
                            <!-- End Single Widget -->
                        </div>
                        <div class="col-lg-3 col-
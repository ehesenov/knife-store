<?php
require_once 'config.php';

// Handle AJAX cart requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'add_to_cart') {
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']) ?: 1;
        
        $product = getProductById($product_id);
        if ($product && $product['stock_quantity'] >= $quantity) {
            addToCart($product_id, $quantity);
            echo json_encode([
                'success' => true, 
                'message' => 'Məhsul səbətə əlavə edildi!',
                'cart_count' => getCartCount()
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Məhsul stokda yoxdur və ya kifayət qədər deyil!'
            ]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'get_cart_count') {
        echo json_encode(['cart_count' => getCartCount()]);
        exit;
    }
}

// Get all products for display
$products = getAllProducts(8); // Limit to 8 products for homepage
$featured_products = getFeaturedProducts(4); // Get featured products
$cart_count = getCartCount();
?>
<!DOCTYPE html>
<html class="no-js" lang="az">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>Knife Store</title>
    <meta name="description" content="" />
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
        .discount-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #dc3545;
            color: white;
            padding: 5px 8px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            z-index: 10;
        }
        .featured-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #28a745;
            color: white;
            padding: 5px 8px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            z-index: 10;
        }
        .product-image {
            position: relative;
            overflow: hidden;
            height: 250px; /* Fixed height for uniform display */
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .product-image:hover img {
            transform: scale(1.05);
        }
        .single-product {
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }
        .single-product:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .product-info {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .product-info .title {
            font-size: 16px;
            font-weight: 600;
            line-height: 1.4;
            margin-bottom: 10px;
            height: 44px; /* Fixed height for uniform title display */
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .product-info .title a {
            color: #333;
            text-decoration: none;
        }
        .product-info .title a:hover {
            color: #007bff;
        }
        .price {
            font-weight: 700;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .price .discount-price {
            color: #dc3545;
        }
        .price .original-price {
            color: #6c757d;
            text-decoration: line-through;
            font-size: 14px;
            margin-right: 8px;
        }
        .single-product .product-info .review {
            margin-bottom: 10px;
            height: 20px; /* Fixed height for uniform display */
        }
        .single-product .product-info .review li {
            display: inline-block;
            margin-right: 2px;
        }
        .single-product .product-info .review .lni-star-filled {
            color: #ffc107;
        }
        .single-product .product-info .review .lni-star {
            color: #dee2e6;
        }
        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: auto;
        }
        .btn-add-cart {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-add-cart:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .btn-add-cart:disabled {
            background-color: #6c757d;
            border-color: #6c757d;
            cursor: not-allowed;
        }
        .btn-view-details {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
            flex: 1;
            padding: 10px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-view-details:hover {
            background-color: #0056b3;
            border-color: #004085;
            color: white;
            text-decoration: none;
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
        .nav-hotline {
            position: relative;
        }
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        .success-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #28a745;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            z-index: 1000;
            display: none;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
        .stock-info {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        .stock-available {
            color: #28a745;
        }
        .stock-low {
            color: #ffc107;
        }
        .stock-out {
            color: #dc3545;
        }
    </style>
</head>

<body>
    <!--[if lte IE 9]>
      <p class="browserupgrade">
        You are using an <strong>outdated</strong> browser. Please
        <a href="https://browsehappy.com/">upgrade your browser</a> to improve
        your experience and security.
      </p>
    <![endif]-->

    <!-- Success Message -->
    <div id="success-message" class="success-message">
        <span id="success-text">Məhsul səbətə əlavə edildi!</span>
    </div>

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
                                <!-- Cart Icon -->
                                <a href="cart.php" class="cart-icon" style="position: absolute; right: -40px; top: 50%; transform: translateY(-50%); font-size: 24px; color: #333;">
                                    <i class="lni lni-cart"></i>
                                    <?php if ($cart_count > 0): ?>
                                        <span class="cart-badge" id="cart-badge"><?php echo $cart_count; ?></span>
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
                <div class="col-lg-8 col-md-6 col-12 ">
                    <div class="nav-inner">
                        <!-- Start Mega Category Menu -->
                        <div class="mega-category-menu">
                            <span class="cat-button"><i class="lni lni-menu"></i>Kateqoriyalar</span>
                            <ul class="sub-category">
                                <li><a href="product-grids.html">Aşpaz Bıçaqları <i class="lni lni-chevron-right"></i></a>
                                    <ul class="inner-sub-category">
                                        <li><a href="product-grids.html">Bunka</a></li>
                                        <li><a href="product-grids.html">Gyuto</a></li>
                                        <li><a href="product-grids.html">Santokus</a></li>
                                        <li><a href="product-grids.html">Kiritsuke</a></li>
                                    </ul>
                                </li>
                                <li><a href="product-grids.html">Balıq bıçaqları<i class="lni lni-chevron-right"></i></a>
                                    <ul class="inner-sub-category">
                                        <li><a href="product-grids.html">Yanagiba</a></li>
                                        <li><a href="product-grids.html">Deba</a></li>
                                    </ul>
                                </li>
                                <li><a href="product-grids.html">Ət bıçaqları<i class="lni lni-chevron-right"></i></a>
                                    <ul class="inner-sub-category">
                                        <li><a href="product-grids.html">Sujihiki</a></li>
                                        <li><a href="product-grids.html">Garasuki</a></li>
                                        <li><a href="product-grids.html">Boning</a></li>
                                    </ul>
                                </li>
                                <li><a href="product-grids.html">Tərəvəz bıçaqları<i class="lni lni-chevron-right"></i></a>
                                    <ul class="inner-sub-category">
                                        <li><a href="product-grids.html">Usuba</a></li>
                                        <li><a href="product-grids.html">Nakiri</a></li>
                                    </ul>
                                </li>
                                <li><a href="product-grids.html">Kiçik Bıçaqlar<i class="lni lni-chevron-right"></i></a>
                                    <ul class="inner-sub-category">
                                        <li><a href="product-grids.html">Petty</a></li>
                                        <li><a href="product-grids.html">Peeling</a></li>
                                    </ul>
                                </li>
                                <li><a href="product-grids.html">Aşpaz Aksesuarları<i class="lni lni-chevron-right"></i></a>
                                    <ul class="inner-sub-category">
                                        <li><a href="product-grids.html">Dəri çantalar</a></li>
                                        <li><a href="product-grids.html">Parça çantalar</a></li>
                                    </ul>
                                </li>
                                <li><a href="product-grids.html">Mətbəx<i class="lni lni-chevron-right"></i></a>
                                    <ul class="inner-sub-category">
                                        <li><a href="product-grids.html">Avadanlıqlar</a></li>
                                        <li><a href="product-grids.html">Aksesuarlar</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                        <!-- End Mega Category Menu -->

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
                                
                                <!-- Mobile Categories Accordion - Only visible on mobile -->
                                <div class="d-lg-none mobile-categories-wrapper">
                                    <div class="accordion" id="mobileCategories">
                                        <div class="accordion-item border-0">
                                            <h2 class="accordion-header" id="categoriesHeading">
                                                <button class="accordion-button collapsed nav-link" type="button" 
                                                        data-bs-toggle="collapse" data-bs-target="#categoriesCollapse" 
                                                        aria-expanded="false" aria-controls="categoriesCollapse">
                                                    <i class="lni lni-menu me-2"></i> Kateqoriyalar
                                                </button>
                                            </h2>
                                            <div id="categoriesCollapse" class="accordion-collapse collapse" 
                                                 aria-labelledby="categoriesHeading" data-bs-parent="#mobileCategories">
                                                <div class="accordion-body p-0">
                                                    <!-- Chef Knives -->
                                                    <div class="mobile-category-item">
                                                        <a class="mobile-category-toggle" data-bs-toggle="collapse" 
                                                           href="#chefKnives" role="button" aria-expanded="false">
                                                            Aşpaz Bıçaqları <i class="lni lni-chevron-down float-end"></i>
                                                        </a>
                                                        <div class="collapse" id="chefKnives">
                                                            <div class="mobile-subcategory">
                                                                <a href="product-grids.html" class="mobile-subcategory-link">Bunka</a>
                                                                <a href="product-grids.html" class="mobile-subcategory-link">Gyuto</a>
                                                                <a href="product-grids.html" class="mobile-subcategory-link">Santokus</a>
                                                                <a href="product-grids.html" class="mobile-subcategory-link">Kiritsuke</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Fish Knives -->
                                                    <div class="mobile-category-item">
                                                        <a class="mobile-category-toggle" data-bs-toggle="collapse" 
                                                           href="#fishKnives" role="button" aria-expanded="false">
                                                            Balıq bıçaqları <i class="lni lni-chevron-down float-end"></i>
                                                        </a>
                                                        <div class="collapse" id="fishKnives">
                                                            <div class="mobile-subcategory">
                                                                <a href="product-grids.html" class="mobile-subcategory-link">Yanagiba</a>
                                                                <a href="product-grids.html" class="mobile-subcategory-link">Deba</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Other categories... -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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

    <!-- Start Hero Area -->
    <section class="hero-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-12 custom-padding-right">
                    <div class="slider-head">
                        <!-- Start Hero Slider -->
                        <div class="hero-slider">
                            <!-- Start Single Slider -->
                            <div class="single-slider"
                                style="background-image: url(assets/images/hero/slider-1.png);">
                                <div class="content">
                                    <h3>TAM KEYFİYYƏTLİ MƏHSULLAR</h3>
                                    <div class="button">
                                        <a href="product-grids.html" class="btn">Sifariş Ver</a>
                                    </div>
                                </div>
                            </div>
                            <!-- End Single Slider -->
                            <!-- Start Single Slider -->
                            <div class="single-slider"
                                style="background-image: url(assets/images/hero/slider-2.png);">
                                <div class="content">
                                    <!-- <h3>Bıcaqlar</h3> -->
                                </div>
                            </div>
                            <!-- End Single Slider -->
                        </div>
                        <!-- End Hero Slider -->
                    </div>
                </div>
                <div class="col-lg-4 col-12">
                    <div class="row">
                        <div class="col-lg-12 col-md-6 col-12 md-custom-padding">
                            <!-- Start Small Banner -->
                            <div class="hero-small-banner"
                                style="background-image: url('assets/images/hero/slider-2.png');">
                                <div class="content">
                                  
                                </div>
                            </div>
                            <!-- End Small Banner -->
                        </div>
                        <div class="col-lg-12 col-md-6 col-12">
                            <!-- Start Small Banner -->
                            <div class="hero-small-banner style2">
                                <div class="content">
                                    <h2>Elə indi sifariş ver!</h2>
                                    <p>Çin, Türkiyə, Yaponiya və Almaniyadan birbaşa seçilmiş məhsullarla, hər müştəriyə ustalığa layiq seçim təqdim edirik.</p>
                                    <div class="button">
                                        <a class="btn" href="product-grids.html">Sifariş ver</a>
                                    </div>
                                </div>
                            </div>
                            <!-- Start Small Banner -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Hero Area -->

    <!-- Start Featured Products Area -->
    <?php if (!empty($featured_products)): ?>
        <section class="featured-product section" style="margin-top: 12px;">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="section-title">
                            <h2>Xüsusi Məhsullar</h2>
                            <p>Ən çox bəyənilən və tövsiyə olunan məhsullarımız</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <?php foreach ($featured_products as $product): ?>
                        <div class="col-lg-3 col-md-6 col-12">
                            <!-- Start Single Product -->
                            <div class="single-product">
                                <div class="product-image">
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    
                                    <!-- Featured Badge -->
                                    <?php if ($product['is_featured']): ?>
                                        <span class="featured-badge">Xüsusi</span>
                                    <?php endif; ?>
                                    
                                    <!-- Discount Badge -->
                                    <?php if ($product['discount_price']): ?>
                                        <?php 
                                        $discount_percent = round((($product['price'] - $product['discount_price']) / $product['price']) * 100);
                                        ?>
                                        <span class="discount-badge">-<?php echo $discount_percent; ?>%</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <h4 class="title">
                                        <a href="product-details.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                                    </h4>
                                    
                                    <!-- Rating -->
                                    <?php if ($product['rating'] > 0): ?>
                                        <ul class="review">
                                            <?php
                                            $rating = $product['rating'];
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
                                    <?php else: ?>
                                        <ul class="review"></ul>
                                    <?php endif; ?>
                                    
                                    <div class="price">
                                        <?php if ($product['discount_price']): ?>
                                            <span class="original-price"><?php echo number_format($product['price'], 2); ?> AZN</span>
                                            <span class="discount-price"><?php echo number_format($product['discount_price'], 2); ?> AZN</span>
                                        <?php else: ?>
                                            <span><?php echo number_format($product['price'], 2); ?> AZN</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Stock Info -->
                                    <div class="stock-info">
                                        <?php if ($product['stock_quantity'] > 10): ?>
                                            <span class="stock-available">✓ Stokda mövcuddur</span>
                                        <?php elseif ($product['stock_quantity'] > 0): ?>
                                            <span class="stock-low">⚠ Az qalıb (<?php echo $product['stock_quantity']; ?> ədəd)</span>
                                        <?php else: ?>
                                            <span class="stock-out">✗ Stokda yoxdur</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="product-actions">
                                        <?php if ($product['stock_quantity'] > 0): ?>
                                            <button class="btn-add-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                                                <i class="lni lni-cart"></i> Səbətə At
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-add-cart" disabled>
                                                Stokda Yoxdur
                                            </button>
                                        <?php endif; ?>
                                        <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn-view-details">
                                            Ətraflı
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <!-- End Single Product -->
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
    <!-- End Featured Products Area -->

    <!-- Start Trending Product Area -->
    <section class="trending-product section" style="margin-top: 12px;">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-title">
                        <h2>Məhsullarımız</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-lg-3 col-md-6 col-12">
                            <!-- Start Single Product -->
                            <div class="single-product">
                                <div class="product-image">
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    
                                    <!-- Featured Badge -->
                                    <?php if ($product['is_featured']): ?>
                                        <span class="featured-badge">Xüsusi</span>
                                    <?php endif; ?>
                                    
                                    <!-- Discount Badge -->
                                    <?php if ($product['discount_price']): ?>
                                        <?php 
                                        $discount_percent = round((($product['price'] - $product['discount_price']) / $product['price']) * 100);
                                        ?>
                                        <span class="discount-badge">-<?php echo $discount_percent; ?>%</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <h4 class="title">
                                        <a href="product-details.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                                    </h4>
                                    
                                    <!-- Rating -->
                                    <?php if ($product['rating'] > 0): ?>
                                        <ul class="review">
                                            <?php
                                            $rating = $product['rating'];
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
                                    <?php else: ?>
                                        <ul class="review"></ul>
                                    <?php endif; ?>
                                    
                                    <div class="price">
                                        <?php if ($product['discount_price']): ?>
                                            <span class="original-price"><?php echo number_format($product['price'], 2); ?> AZN</span>
                                            <span class="discount-price"><?php echo number_format($product['discount_price'], 2); ?> AZN</span>
                                        <?php else: ?>
                                            <span><?php echo number_format($product['price'], 2); ?> AZN</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Stock Info -->
                                    <div class="stock-info">
                                        <?php if ($product['stock_quantity'] > 10): ?>
                                            <span class="stock-available">✓ Stokda mövcuddur</span>
                                        <?php elseif ($product['stock_quantity'] > 0): ?>
                                            <span class="stock-low">⚠ Az qalıb (<?php echo $product['stock_quantity']; ?> ədəd)</span>
                                        <?php else: ?>
                                            <span class="stock-out">✗ Stokda yoxdur</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="product-actions">
                                        <?php if ($product['stock_quantity'] > 0): ?>
                                            <button class="btn-add-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                                                <i class="lni lni-cart"></i> Səbətə At
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-add-cart" disabled>
                                                Stokda Yoxdur
                                            </button>
                                        <?php endif; ?>
                                        <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn-view-details">
                                            Ətraflı
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <!-- End Single Product -->
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <p>Hələlik məhsul yoxdur.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <!-- End Trending Product Area -->

    <!-- Start Shipping Info -->
    <section class="shipping-info">
        <div class="container">
            <ul>
                <!-- Free Shipping -->
                <li>
                    <div class="media-icon">
                        <i class="lni lni-delivery"></i>
                    </div>
                    <div class="media-body">
                        <h5>Çatdırılma</h5>
                    </div>
                </li>
                <!-- Money Return -->
                <li>
                    <div class="media-icon">
                        <i class="lni lni-support"></i>
                    </div>
                    <div class="media-body">
                        <h5>24/7 Xidmət.</h5>
                    </div>
                </li>
                <!-- Support 24/7 -->
                <li>
                    <div class="media-icon">
                        <i class="lni lni-credit-cards"></i>
                    </div>
                    <div class="media-body">
                        <h5>Online ödeniş.</h5>
                    </div>
                </li>
                <!-- Safe Payment -->
                <li>
                    <div class="media-icon">
                        <i class="lni lni-reload"></i>
                    </div>
                    <div class="media-body">
                        <h5>Geri dönüş.</h5>
                    </div>
                </li>
            </ul>
        </div>
    </section>
    <!-- End Shipping Info -->

    <!-- Start Footer Area -->
    <footer class="footer">
        <!-- Start Footer Top -->
        <div class="footer-top">
            <div class="container">
                <div class="inner-content">
                    <div class="row">
                        <div class="col-lg-3 col-md-4 col-12">
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
    <script type="text/javascript">
        //========= Hero Slider 
        tns({
            container: '.hero-slider',
            slideBy: 'page',
            autoplay: true,
            autoplayButtonOutput: false,
            mouseDrag: true,
            gutter: 0,
            items: 1,
            nav: false,
            controls: true,
            controlsText: ['<i class="lni lni-chevron-left"></i>', '<i class="lni lni-chevron-right"></i>'],
        });

        //======== Brand Slider
        tns({
            container: '.brands-logo-carousel',
            autoplay: true,
            autoplayButtonOutput: false,
            mouseDrag: true,
            gutter: 15,
            nav: false,
            controls: false,
            responsive: {
                0: {
                    items: 1,
                },
                540: {
                    items: 3,
                },
                768: {
                    items: 5,
                },
                992: {
                    items: 6,
                }
            }
        });

        // Cart functionality
        function addToCart(productId, quantity = 1) {
            const button = event.target;
            const originalText = button.innerHTML;
            
            // Show loading state
            button.classList.add('loading');
            button.innerHTML = '<i class="lni lni-spinner"></i> Əlavə edilir...';
            button.disabled = true;
            
            // Send AJAX request
            fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add_to_cart&product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count
                    updateCartBadge(data.cart_count);
                    
                    // Show success message
                    showSuccessMessage(data.message);
                    
                    // Reset button
                    button.classList.remove('loading');
                    button.innerHTML = originalText;
                    button.disabled = false;
                } else {
                    // Show error message
                    alert(data.message);
                    
                    // Reset button
                    button.classList.remove('loading');
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Xəta baş verdi!');
                
                // Reset button
                button.classList.remove('loading');
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
        
        function updateCartBadge(count) {
            const cartIcon = document.querySelector('.cart-icon');
            let badge = document.getElementById('cart-badge');
            
            if (count > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'cart-badge';
                    badge.id = 'cart-badge';
                    cartIcon.appendChild(badge);
                }
                badge.textContent = count;
            } else {
                if (badge) {
                    badge.remove();
                }
            }
        }
        
        function showSuccessMessage(message) {
            const successDiv = document.getElementById('success-message');
            const successText = document.getElementById('success-text');
            
            successText.textContent = message;
            successDiv.style.display = 'block';
            
            // Hide after 3 seconds
            setTimeout(() => {
                successDiv.style.display = 'none';
            }, 3000);
        }
        
        // Update cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_cart_count'
            })
            .then(response => response.json())
            .then(data => {
                updateCartBadge(data.cart_count);
            });
        });
    </script>
</body>

</html>
<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: user_dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sihali Water | Premium Purification Solutions</title>
    <!-- Favicon link -->
    <link rel="icon" type="image/png" href="1.jpg">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Ambient Glowing Backgrounds -->
    <div class="bg-glow-1"></div>
    <div class="bg-glow-2"></div>

    <!-- Navigation Header -->
    <nav class="navbar glass-card">
        <div class="logo">SIHALI WATER</div>
        <ul class="nav-links">
            <li><a href="#">Home</a></li>
            <li><a href="#products">Products</a></li>
            <li><a href="#services">Services</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
        <a href="#products" class="btn-luxury">Explore Range</a>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <h1>Next-Level Pure Water <br> <span>Luxury Living</span></h1>
        <p>Experience advanced Reverse Osmosis & UV filtration technology crafted for elegance, safety, and ultimate water purity.</p>
        <a href="#products" class="btn-luxury">View Premium Systems</a>
    </section>

    <!-- Dynamic Products Grid -->
    <section class="products-section" id="products">
        <h2 class="section-title">Exclusive Collection</h2>
        <div class="grid">
            
            <!-- Sample Glassy Product Card 1 -->
            <div class="glass-card product-card">
                <img src="images/ro-system.png" alt="RO System" class="product-img" onerror="this.src='https://via.placeholder.com/300x200/0f172a/4facfe?text=RO+System'">
                <h3 class="product-title">RO Purifier 6-Stage</h3>
                <p class="product-desc">Advanced reverse osmosis filtration with mineral balancing for pristine drinking water.</p>
                <div class="product-bottom">
                    <span class="price">LKR 48,500</span>
                    <button class="btn-luxury" style="padding: 0.5rem 1.2rem; font-size: 0.85rem;">Inquire</button>
                </div>
            </div>

            <!-- Sample Glassy Product Card 2 -->
            <div class="glass-card product-card">
                <img src="images/uv-purifier.png" alt="UV Filter" class="product-img" onerror="this.src='https://via.placeholder.com/300x200/0f172a/4facfe?text=UV+Purifier'">
                <h3 class="product-title">UV Sterilizer Unit</h3>
                <p class="product-desc">Eliminates 99.9% of bacteria & microbes instantly with high-grade UV rays.</p>
                <div class="product-bottom">
                    <span class="price">LKR 38,000</span>
                    <button class="btn-luxury" style="padding: 0.5rem 1.2rem; font-size: 0.85rem;">Inquire</button>
                </div>
            </div>

            <!-- Sample Glassy Product Card 3 -->
            <div class="glass-card product-card">
                <img src="images/whole-house.png" alt="Mainline Filter" class="product-img" onerror="this.src='https://via.placeholder.com/300x200/0f172a/4facfe?text=Whole+House'">
                <h3 class="product-title">Whole House Filter</h3>
                <p class="product-desc">Heavy-duty main water line filtration for total household clarity.</p>
                <div class="product-bottom">
                    <span class="price">LKR 55,000</span>
                    <button class="btn-luxury" style="padding: 0.5rem 1.2rem; font-size: 0.85rem;">Inquire</button>
                </div>
            </div>

        </div>
    </section>

</body>
</html>
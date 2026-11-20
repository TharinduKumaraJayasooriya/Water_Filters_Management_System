<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header("Location: login.php");
    exit();
}

include 'db.php';
$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }

if (isset($_GET['add_to_cart'])) {
    $item_id = $_GET['add_to_cart'];
    $res = $conn->query("SELECT * FROM items WHERE id = $item_id");
    if ($row = $res->fetch_assoc()) {
        if(isset($_SESSION['cart'][$item_id])) {
            $_SESSION['cart'][$item_id]['qty']++;
        } else {
            $_SESSION['cart'][$item_id] = [
                'name' => $row['name'],
                'price' => $row['price'],
                'qty' => 1
            ];
        }
    }
    header("Location: user_dashboard.php");
}

if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header("Location: user_dashboard.php");
}

if (isset($_POST['checkout'])) {
    $total = 0;
    foreach($_SESSION['cart'] as $item) { $total += $item['price'] * $item['qty']; }
    
    $conn->query("INSERT INTO orders (user_id, total_amount) VALUES ($user_id, $total)");
    $_SESSION['cart'] = [];
    $order_success = "Order placed successfully!";
}

if (isset($_POST['book_repair'])) {
    $model = $_POST['filter_model'];
    $issue = $_POST['issue_description'];
    $date = $_POST['preferred_date'];

    $stmt = $conn->prepare("INSERT INTO repair_bookings (user_id, filter_model, issue_description, preferred_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $model, $issue, $date);
    $stmt->execute();
    $booking_success = "Repair service booked successfully!";
}

$search = $_GET['search'] ?? '';
$cat_filter = $_GET['category'] ?? '';

$query = "SELECT * FROM items WHERE 1=1";
if ($search) { $query .= " AND name LIKE '%" . $conn->real_escape_string($search) . "%'"; }
if ($cat_filter) { $query .= " AND category = '" . $conn->real_escape_string($cat_filter) . "'"; }
$query .= " ORDER BY id DESC";
$items = $conn->query($query);

$my_orders = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_id DESC");
$my_bookings = $conn->query("SELECT * FROM repair_bookings WHERE user_id = $user_id ORDER BY booking_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sihali Water - Customer Portal</title>
    <link rel="icon" type="image/png" href="1.jpg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen p-6 font-sans">
    
    <div class="flex justify-between items-center bg-slate-900 border border-slate-800 p-5 rounded-2xl mb-6 shadow-xl">
        <div class="flex items-center gap-3">
            <div class="bg-sky-600/20 text-sky-400 p-3 rounded-xl text-xl"><i class="fa-solid fa-droplet"></i></div>
            <div>
                <h1 class="text-xl font-bold text-sky-400">Sihali Water Customer Portal</h1>
                <p class="text-xs text-slate-400">Welcome, <span class="text-slate-200 font-semibold"><?= htmlspecialchars($_SESSION['user_name']) ?></span></p>
            </div>
        </div>
        <a href="logout.php" class="bg-red-500/20 hover:bg-red-500/30 text-red-400 px-4 py-2 rounded-xl text-sm font-semibold transition border border-red-500/30"><i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> Logout</a>
    </div>

    <?php if(isset($order_success)): ?><div class="bg-emerald-500/20 border border-emerald-500 text-emerald-300 p-4 rounded-xl mb-6 text-sm"><?= $order_success ?></div><?php endif; ?>
    <?php if(isset($booking_success)): ?><div class="bg-emerald-500/20 border border-emerald-500 text-emerald-300 p-4 rounded-xl mb-6 text-sm"><?= $booking_success ?></div><?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Products Catalog with Image Preview Cards -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
                    <h3 class="text-lg font-bold text-sky-400"><i class="fa-solid fa-store mr-2"></i> Water Filters & Parts Store</h3>
                    
                    <form method="GET" class="flex gap-2 w-full md:w-auto">
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search filter..." class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-sky-500">
                        <select name="category" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-sky-500">
                            <option value="">All Categories</option>
                            <option value="Water Filter" <?= $cat_filter=='Water Filter'?'selected':'' ?>>Water Filter</option>
                            <option value="Replacement Part" <?= $cat_filter=='Replacement Part'?'selected':'' ?>>Part</option>
                            <option value="Repair Service" <?= $cat_filter=='Repair Service'?'selected':'' ?>>Repair</option>
                        </select>
                        <button type="submit" class="bg-slate-800 hover:bg-slate-700 px-3 py-2 rounded-xl text-xs font-semibold">Filter</button>
                    </form>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php while($row = $items->fetch_assoc()): 
                        $img_src = !empty($row['image']) ? $row['image'] : 'uploads/default.jpg';
                    ?>
                    <div class="bg-slate-950 border border-slate-800 rounded-2xl overflow-hidden flex flex-col justify-between shadow-md">
                        <!-- Item Image Preview (Clickable for Popup Modal) -->
                        <div class="h-44 w-full bg-slate-900 overflow-hidden relative cursor-pointer group" onclick="openModal('<?= $img_src ?>', '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>')">
                            <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-semibold gap-2">
                                <i class="fa-solid fa-magnifying-glass-plus"></i> Click to zoom
                            </div>
                            <span class="absolute top-3 left-3 bg-slate-950/80 backdrop-blur-md border border-slate-800 px-2.5 py-1 rounded-lg text-[10px] font-semibold text-sky-400"><?= $row['category'] ?></span>
                        </div>
                        
                        <div class="p-4 flex flex-col justify-between flex-1">
                            <div>
                                <h4 class="font-bold text-base mt-1"><?= htmlspecialchars($row['name']) ?></h4>
                                <div class="text-sky-400 font-bold text-lg my-1">Rs. <?= number_format($row['price'], 2) ?></div>
                                <p class="text-xs text-slate-400">Status: <span class="text-emerald-400 font-medium"><?= htmlspecialchars($row['stock']) ?></span></p>
                            </div>
                            <a href="user_dashboard.php?add_to_cart=<?= $row['id'] ?>" class="mt-4 bg-sky-600 hover:bg-sky-500 text-white text-center py-2.5 rounded-xl text-xs font-semibold transition shadow-md shadow-sky-600/30"><i class="fa-solid fa-cart-plus mr-1"></i> Add to Cart</a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Repair Booking Form -->
            <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl">
                <h3 class="text-lg font-bold text-sky-400 mb-4"><i class="fa-solid fa-wrench mr-2"></i> Book Professional Repair Service</h3>
                <form method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase">Filter Model / Type</label>
                            <input type="text" name="filter_model" placeholder="e.g. RO 5-Stage Purifier" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-sky-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase">Preferred Date</label>
                            <input type="date" name="preferred_date" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-sky-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase">Issue Description</label>
                        <textarea name="issue_description" rows="2" placeholder="Describe the issue..." required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-sky-500"></textarea>
                    </div>
                    <button type="submit" name="book_repair" class="bg-emerald-600 hover:bg-emerald-500 text-white font-semibold px-6 py-2.5 rounded-xl transition text-sm shadow-lg shadow-emerald-600/30">Submit Repair Booking</button>
                </form>
            </div>
        </div>

        <!-- Sidebar (Cart & History) -->
        <div class="space-y-6">
            <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl">
                <h3 class="text-lg font-bold text-sky-400 mb-4"><i class="fa-solid fa-cart-shopping mr-2"></i> Shopping Cart</h3>
                <?php if(empty($_SESSION['cart'])): ?>
                    <p class="text-xs text-slate-500 text-center py-4">Your cart is empty.</p>
                <?php else: ?>
                    <div class="space-y-3 mb-4">
                        <?php 
                        $grand_total = 0;
                        foreach($_SESSION['cart'] as $id => $item): 
                            $subtotal = $item['price'] * $item['qty'];
                            $grand_total += $subtotal;
                        ?>
                        <div class="bg-slate-950 p-3 rounded-xl border border-slate-800 flex justify-between items-center text-xs">
                            <div>
                                <p class="font-bold"><?= htmlspecialchars($item['name']) ?></p>
                                <p class="text-slate-400"><?= $item['qty'] ?> x Rs. <?= number_format($item['price'], 2) ?></p>
                            </div>
                            <a href="user_dashboard.php?remove=<?= $id ?>" class="text-red-400 hover:text-red-300 p-1"><i class="fa-solid fa-trash"></i></a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="border-t border-slate-800 pt-3 flex justify-between font-bold mb-4">
                        <span>Total:</span>
                        <span class="text-sky-400">Rs. <?= number_format($grand_total, 2) ?></span>
                    </div>
                    <form method="POST">
                        <button type="submit" name="checkout" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-2.5 rounded-xl transition text-xs shadow-lg shadow-emerald-600/30">Proceed to Checkout</button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl">
                <h3 class="text-lg font-bold text-sky-400 mb-4"><i class="fa-solid fa-clock-rotate-left mr-2"></i> My Order History</h3>
                <div class="space-y-3 max-h-48 overflow-y-auto">
                    <?php while($ord = $my_orders->fetch_assoc()): ?>
                    <div class="bg-slate-950 p-3 rounded-xl border border-slate-800 text-xs flex justify-between">
                        <div>
                            <p class="font-bold">Order #<?= $ord['order_id'] ?></p>
                            <p class="text-slate-400 text-[10px]"><?= $ord['created_at'] ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sky-400 font-bold">Rs. <?= number_format($ord['total_amount'], 2) ?></p>
                            <span class="text-[10px] bg-sky-500/10 text-sky-400 px-2 py-0.5 rounded"><?= $ord['status'] ?></span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- IMAGE PREVIEW ZOOM MODAL -->
    <div id="imageModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-2xl w-full p-4 relative shadow-2xl">
            <button onclick="closeModal()" class="absolute top-4 right-4 bg-slate-800 hover:bg-slate-700 text-white w-8 h-8 rounded-full flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <h3 id="modalTitle" class="text-base font-bold text-sky-400 mb-3 px-2"></h3>
            <div class="w-full h-96 bg-slate-950 rounded-xl overflow-hidden flex items-center justify-center border border-slate-800">
                <img id="modalImage" src="" alt="Preview" class="max-h-full max-w-full object-contain">
            </div>
        </div>
    </div>

    <!-- JavaScript for Modal Popup -->
    <script>
        function openModal(imageSrc, title) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('modalTitle').innerText = title;
            const modal = document.getElementById('imageModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }

        // Close modal when clicking outside the modal box
        window.onclick = function(event) {
            const modal = document.getElementById('imageModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>

</body>
</html>
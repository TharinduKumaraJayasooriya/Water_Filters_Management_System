<?php

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

// 1. ADD ITEM WITH IMAGE UPLOAD
if (isset($_POST['add_item'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    // Image Upload Handling
    $image_name = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $upload_dir = "uploads/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if ($image_name) {
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
        $new_image_name = uniqid("item_") . "." . $image_ext;
        $target_path = $upload_dir . $new_image_name;
        
        if (move_uploaded_file($image_tmp, $target_path)) {
            $image_path = $target_path;
        } else {
            $image_path = "uploads/default.jpg";
        }
    } else {
        $image_path = "uploads/default.jpg";
    }

    // Insert into items including image path
    $stmt = $conn->prepare("INSERT INTO items (name, category, price, stock, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdss", $name, $category, $price, $stock, $image_path);
    $stmt->execute();
    header("Location: admin_dashboard.php");
}

// 2. DELETE ITEM
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM items WHERE id=$id");
    header("Location: admin_dashboard.php");
}

// 3. UPDATE ORDER STATUS
if (isset($_GET['update_order'])) {
    $order_id = $_GET['update_order'];
    $conn->query("UPDATE orders SET status='Accepted / Completed' WHERE order_id = $order_id");
    header("Location: admin_dashboard.php");
}

// 4. UPDATE REPAIR BOOKING STATUS
if (isset($_GET['update_booking'])) {
    $booking_id = $_GET['update_booking'];
    $conn->query("UPDATE repair_bookings SET status='In Progress / Resolved' WHERE booking_id = $booking_id");
    header("Location: admin_dashboard.php");
}

// Analytics Counts
$total_products = $conn->query("SELECT COUNT(*) as count FROM items")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='user'")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_bookings = $conn->query("SELECT COUNT(*) as count FROM repair_bookings")->fetch_assoc()['count'];

$search = $_GET['search'] ?? '';
$cat_filter = $_GET['category'] ?? '';

$query = "SELECT * FROM items WHERE 1=1";
if ($search) { $query .= " AND name LIKE '%" . $conn->real_escape_string($search) . "%'"; }
if ($cat_filter) { $query .= " AND category = '" . $conn->real_escape_string($cat_filter) . "'"; }
$query .= " ORDER BY id DESC";
$items = $conn->query($query);

$orders = $conn->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.order_id DESC");
$bookings = $conn->query("SELECT b.*, u.name as user_name FROM repair_bookings b JOIN users u ON b.user_id = u.id ORDER BY b.booking_id DESC");
$repair_views = $conn->query("SELECT * FROM vw_active_repair_orders");
$logs = $conn->query("SELECT * FROM item_logs ORDER BY log_id DESC LIMIT 6");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sihali Water - Admin Dashboard</title>
    <link rel="icon" type="image/png" href="1.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen p-6 font-sans">
    
    <div class="flex justify-between items-center bg-slate-900 border border-slate-800 p-5 rounded-2xl mb-6 shadow-xl">
        <div class="flex items-center gap-3">
            <div class="bg-sky-600/20 text-sky-400 p-3 rounded-xl text-xl"><i class="fa-solid fa-shield-halved"></i></div>
            <div>
                <h1 class="text-xl font-bold text-sky-400">Sihali Water Admin Portal</h1>
                <p class="text-xs text-slate-400">Logged in as: <span class="text-slate-200 font-semibold"><?= htmlspecialchars($_SESSION['user_name']) ?></span></p>
            </div>
        </div>
        <a href="logout.php" class="bg-red-500/20 hover:bg-red-500/30 text-red-400 px-4 py-2 rounded-xl text-sm font-semibold transition border border-red-500/30"><i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> Logout</a>
    </div>

    <!-- Analytics Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl flex items-center gap-4">
            <div class="bg-sky-500/10 text-sky-400 p-4 rounded-xl text-2xl"><i class="fa-solid fa-box-open"></i></div>
            <div>
                <p class="text-xs text-slate-400 uppercase font-semibold">Total Products</p>
                <h3 class="text-2xl font-bold"><?= $total_products ?></h3>
            </div>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl flex items-center gap-4">
            <div class="bg-emerald-500/10 text-emerald-400 p-4 rounded-xl text-2xl"><i class="fa-solid fa-users"></i></div>
            <div>
                <p class="text-xs text-slate-400 uppercase font-semibold">Registered Users</p>
                <h3 class="text-2xl font-bold"><?= $total_users ?></h3>
            </div>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl flex items-center gap-4">
            <div class="bg-amber-500/10 text-amber-400 p-4 rounded-xl text-2xl"><i class="fa-solid fa-cart-shopping"></i></div>
            <div>
                <p class="text-xs text-slate-400 uppercase font-semibold">Customer Orders</p>
                <h3 class="text-2xl font-bold"><?= $total_orders ?></h3>
            </div>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl flex items-center gap-4">
            <div class="bg-purple-500/10 text-purple-400 p-4 rounded-xl text-2xl"><i class="fa-solid fa-wrench"></i></div>
            <div>
                <p class="text-xs text-slate-400 uppercase font-semibold">Repair Bookings</p>
                <h3 class="text-2xl font-bold"><?= $total_bookings ?></h3>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Add Item Form with Image Upload -->
        <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl h-fit">
            <h3 class="text-lg font-bold text-sky-400 mb-4"><i class="fa-solid fa-plus-circle mr-2"></i> Add Product with Image</h3>
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase">Item Name</label>
                    <input type="text" name="name" placeholder="e.g. RO Filter Model X" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase">Category</label>
                    <select name="category" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-sky-500">
                        <option value="Water Filter">Water Filter</option>
                        <option value="Replacement Part">Replacement Part</option>
                        <option value="Repair Service">Repair Service</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase">Price (LKR)</label>
                    <input type="number" step="0.01" name="price" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase">Stock / Status</label>
                    <input type="text" name="stock" placeholder="In Stock / Available" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase">Product Image</label>
                    <input type="file" name="image" accept="image/*" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-400 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-600 file:text-white hover:file:bg-sky-500">
                </div>
                <button type="submit" name="add_item" class="w-full bg-sky-600 hover:bg-sky-500 text-white font-semibold py-3 rounded-xl transition shadow-lg shadow-sky-600/30 text-sm">Add Item & Upload Image</button>
            </form>
        </div>

        <!-- Inventory Table -->
        <div class="lg:col-span-2 bg-slate-900 border border-slate-800 p-6 rounded-2xl">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
                <h3 class="text-lg font-bold text-sky-400"><i class="fa-solid fa-boxes-stacked mr-2"></i> Inventory CRUD Management</h3>
                
                <form method="GET" class="flex gap-2 w-full md:w-auto">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search item..." class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-sky-500">
                    <select name="category" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-sky-500">
                        <option value="">All Categories</option>
                        <option value="Water Filter" <?= $cat_filter=='Water Filter'?'selected':'' ?>>Water Filter</option>
                        <option value="Replacement Part" <?= $cat_filter=='Replacement Part'?'selected':'' ?>>Part</option>
                        <option value="Repair Service" <?= $cat_filter=='Repair Service'?'selected':'' ?>>Repair</option>
                    </select>
                    <button type="submit" class="bg-slate-800 hover:bg-slate-700 px-3 py-2 rounded-xl text-xs font-semibold">Filter</button>
                </form>
            </div>

            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase sticky top-0 bg-slate-900">
                            <th class="p-3">Image</th>
                            <th class="p-3">Name</th>
                            <th class="p-3">Category</th>
                            <th class="p-3">Price</th>
                            <th class="p-3">Stock</th>
                            <th class="p-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 text-sm">
                        <?php while($row = $items->fetch_assoc()): ?>
                        <tr class="hover:bg-slate-800/50">
                            <td class="p-3">
                                <img src="<?= !empty($row['image']) ? $row['image'] : 'uploads/default.jpg' ?>" alt="" class="w-10 h-10 object-cover rounded-lg border border-slate-800">
                            </td>
                            <td class="p-3 font-medium"><?= htmlspecialchars($row['name']) ?></td>
                            <td class="p-3"><span class="bg-slate-950 border border-slate-800 px-2.5 py-1 rounded-lg text-xs text-sky-400"><?= $row['category'] ?></span></td>
                            <td class="p-3 font-semibold">Rs. <?= number_format($row['price'], 2) ?></td>
                            <td class="p-3 text-xs text-slate-300"><?= htmlspecialchars($row['stock']) ?></td>
                            <td class="p-3 text-center">
                                <a href="admin_dashboard.php?delete=<?= $row['id'] ?>" class="bg-red-500/20 hover:bg-red-500/30 text-red-400 px-3 py-1.5 rounded-lg text-xs font-semibold transition border border-red-500/30" onclick="return confirm('Delete item?')">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Orders & Bookings Management -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl">
            <h3 class="text-lg font-bold text-amber-400 mb-4"><i class="fa-solid fa-cart-shopping mr-2"></i> Manage Customer Orders</h3>
            <div class="overflow-x-auto max-h-72 overflow-y-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 uppercase sticky top-0 bg-slate-900">
                            <th class="p-2.5">ID</th>
                            <th class="p-2.5">Customer</th>
                            <th class="p-2.5">Total</th>
                            <th class="p-2.5">Status</th>
                            <th class="p-2.5 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        <?php while($ord = $orders->fetch_assoc()): ?>
                        <tr class="hover:bg-slate-800/50">
                            <td class="p-2.5 font-mono">#<?= $ord['order_id'] ?></td>
                            <td class="p-2.5 font-medium"><?= htmlspecialchars($ord['user_name']) ?></td>
                            <td class="p-2.5 font-bold text-sky-400">Rs. <?= number_format($ord['total_amount'], 2) ?></td>
                            <td class="p-2.5"><span class="bg-slate-950 px-2 py-0.5 rounded text-[10px] text-amber-300 border border-slate-800"><?= $ord['status'] ?></span></td>
                            <td class="p-2.5 text-center">
                                <?php if($ord['status'] == 'Pending'): ?>
                                    <a href="admin_dashboard.php?update_order=<?= $ord['order_id'] ?>" class="bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 px-2.5 py-1 rounded text-[10px] font-semibold border border-emerald-500/30">Accept</a>
                                <?php else: ?>
                                    <span class="text-slate-500 text-[10px]">Completed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl">
            <h3 class="text-lg font-bold text-purple-400 mb-4"><i class="fa-solid fa-wrench mr-2"></i> Manage Repair Bookings</h3>
            <div class="overflow-x-auto max-h-72 overflow-y-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 uppercase sticky top-0 bg-slate-900">
                            <th class="p-2.5">ID</th>
                            <th class="p-2.5">Customer & Model</th>
                            <th class="p-2.5">Issue & Date</th>
                            <th class="p-2.5">Status</th>
                            <th class="p-2.5 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        <?php while($book = $bookings->fetch_assoc()): ?>
                        <tr class="hover:bg-slate-800/50">
                            <td class="p-2.5 font-mono">#<?= $book['booking_id'] ?></td>
                            <td class="p-2.5">
                                <p class="font-bold"><?= htmlspecialchars($book['user_name']) ?></p>
                                <p class="text-slate-400 text-[10px]"><?= htmlspecialchars($book['filter_model']) ?></p>
                            </td>
                            <td class="p-2.5">
                                <p><?= htmlspecialchars($book['issue_description']) ?></p>
                                <p class="text-slate-500 text-[10px]"><?= $book['preferred_date'] ?></p>
                            </td>
                            <td class="p-2.5"><span class="bg-slate-950 px-2 py-0.5 rounded text-[10px] text-purple-300 border border-slate-800"><?= $book['status'] ?></span></td>
                            <td class="p-2.5 text-center">
                                <?php if($book['status'] == 'Pending'): ?>
                                    <a href="admin_dashboard.php?update_booking=<?= $book['booking_id'] ?>" class="bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 px-2.5 py-1 rounded text-[10px] font-semibold border border-emerald-500/30">Resolve</a>
                                <?php else: ?>
                                    <span class="text-slate-500 text-[10px]">Resolved</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
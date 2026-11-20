<?php
session_start();
include 'db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid Password!";
        }
    } else {
        $error = "User not found with this email!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sihali Water - Login</title>
    <link rel="icon" type="image/png" href="1.jpg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-slate-950 text-slate-100 flex items-center justify-center h-screen m-0">
    <div class="bg-slate-900 border border-slate-800 p-8 rounded-2xl shadow-2xl w-full max-w-md">
        <h2 class="text-2xl font-bold text-sky-400 text-center mb-6"><i class="fa-solid fa-right-to-bracket mr-2"></i> Welcome Back</h2>
        
        <?php if($error): ?><div class="bg-red-500/20 border border-red-500 text-red-300 p-3 rounded-lg text-sm mb-4 text-center"><?= $error ?></div><?php endif; ?>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase">Email Address</label>
                <input type="email" name="email" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-sky-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase">Password</label>
                <input type="password" name="password" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-sky-500">
            </div>
            <button type="submit" class="w-full bg-sky-600 hover:bg-sky-500 text-white font-semibold py-3 rounded-xl transition shadow-lg shadow-sky-600/30">Login</button>
        </form>

        <p class="text-center text-sm text-slate-400 mt-6">Don't have an account? <a href="register.php" class="text-sky-400 font-medium">Register here</a></p>
    </div>
</body>
</html>
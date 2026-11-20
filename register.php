<?php
session_start();
include 'db.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "This email is already registered!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user';

        // Calling Stored Procedure for Registration
        $stmt = $conn->prepare("CALL sp_RegisterUser(?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

        if ($stmt->execute()) {
            $success = "Registration successful! You can now <a href='login.php' class='text-sky-400 underline'>Login</a>";
        } else {
            $error = "Something went wrong!";
        }
        $stmt->close();
    }
    $check->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sihali Water - Register</title>
    <link rel="icon" type="image/png" href="1.jpg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-slate-950 text-slate-100 flex items-center justify-center h-screen m-0">
    <div class="bg-slate-900 border border-slate-800 p-8 rounded-2xl shadow-2xl w-full max-w-md">
        <h2 class="text-2xl font-bold text-sky-400 text-center mb-6"><i class="fa-solid fa-user-plus mr-2"></i> Create Account</h2>
        
        <?php if($error): ?><div class="bg-red-500/20 border border-red-500 text-red-300 p-3 rounded-lg text-sm mb-4 text-center"><?= $error ?></div><?php endif; ?>
        <?php if($success): ?><div class="bg-emerald-500/20 border border-emerald-500 text-emerald-300 p-3 rounded-lg text-sm mb-4 text-center"><?= $success ?></div><?php endif; ?>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase">Full Name</label>
                <input type="text" name="name" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-sky-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase">Email Address</label>
                <input type="email" name="email" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-sky-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase">Password</label>
                <input type="password" name="password" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-sky-500">
            </div>
            <button type="submit" class="w-full bg-sky-600 hover:bg-sky-500 text-white font-semibold py-3 rounded-xl transition shadow-lg shadow-sky-600/30">Register</button>
        </form>

        <p class="text-center text-sm text-slate-400 mt-6">Already have an account? <a href="login.php" class="text-sky-400 font-medium">Login here</a></p>
    </div>
</body>
</html>
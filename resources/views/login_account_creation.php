<?php
// Start the session to store user data
session_start();

// Database connection details (replace with your actual database credentials)
$host = "localhost";
$username = "your_db_username";
$password = "your_db_password";
$database = "your_db_name";

// Create a database connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check if the connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to hash passwords (use password_hash() in production)
function hash_password($password) {
    // Use password_hash() for strong password hashing
    // return password_hash($password, PASSWORD_DEFAULT);  // Recommended, requires PHP 5.5+
    // For demonstration, a simple (INSECURE) hash function:
    return md5($password);  // NEVER use md5 or sha1 in real applications.  This is for demonstration only.
}

// Function to verify passwords (use password_verify() in production)
function verify_password($password, $hashed_password) {
    // Use password_verify() with password_hash()
    // return password_verify($password, $hashed_password); // Recommended
    // For demonstration with md5:
    return md5($password) === $hashed_password; // INSECURE - ONLY for demo
}

// Initialize variables for form data and errors
$user_id = "";
$password = "";
$confirm_password = "";
$error = "";
$is_login = true; // Default to login mode

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $user_id = $_POST["user_id"];
    $password = $_POST["password"];

    // Determine if it's a login or create account request
    $is_login = isset($_POST["login"]); // Check if the 'login' button was pressed.  Otherwise, assume create account.

    if ($is_login) {
        // Login process
        if (empty($user_id) || empty($password)) {
            $error = "Please enter both User ID and Password.";
        } else {
            // Fetch user from the database
            $sql = "SELECT user_id, password_hash FROM users WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);
                $stored_hashed_password = $row["password_hash"];

                // Verify the password
                if (verify_password($password, $stored_hashed_password)) {
                    // Password is correct, set session and redirect
                    $_SESSION["user_id"] = $user_id;
                    // In a real application, redirect to a welcome or dashboard page.
                    // For this example, we'll just display a success message.
                    echo "<script>alert('Login successful!  Check output.  In a real app, redirect.');</script>";
                    echo "<p>Login successful! User ID: " . htmlspecialchars($user_id) . "</p>";
                    // Clear form data
                    $user_id = "";
                    $password = "";

                } else {
                    $error = "Invalid User ID or Password.";
                }
            } else {
                $error = "Invalid User ID or Password.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        // Account creation process
        $confirm_password = $_POST["confirm_password"];
        if (empty($user_id) || empty($password) || empty($confirm_password)) {
            $error = "Please fill in all fields.";
        } elseif ($password != $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Check if the user ID already exists
            $check_sql = "SELECT user_id FROM users WHERE user_id = ?";
            $check_stmt = mysqli_prepare($conn, $check_sql);
            mysqli_stmt_bind_param($check_stmt, "s", $user_id);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);

            if (mysqli_stmt_num_rows($check_stmt) > 0) {
                $error = "User ID already exists.";
            } else {
                // Hash the password
                $hashed_password = hash_password($password);
                // Insert the new user into the database
                $insert_sql = "INSERT INTO users (user_id, password_hash) VALUES (?, ?)";
                $insert_stmt = mysqli_prepare($conn, $insert_sql);
                mysqli_stmt_bind_param($insert_stmt, "ss", $user_id, $hashed_password);

                if (mysqli_stmt_execute($insert_stmt)) {
                    echo "<script>alert('Account created successfully! Check output.');</script>";
                    echo "<p>Account created successfully! User ID: " . htmlspecialchars($user_id) . "</p>";
                    $is_login = true; // Switch to login mode after successful creation.
                    // Clear form data
                    $user_id = "";
                    $password = "";
                    $confirm_password = "";
                } else {
                    $error = "Error creating account: " . mysqli_error($conn);
                }
                mysqli_stmt_close($insert_stmt);
            }
            mysqli_stmt_close($check_stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_login ? "Login" : "Create Account"; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md p-8 bg-gray-800 rounded-xl shadow-lg border border-gray-700">
        <h2 class="text-3xl font-bold mb-6 text-center text-white">
            <?php echo $is_login ? "Login" : "Create Account"; ?>
        </h2>
        <?php if ($error) : ?>
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Error: </strong>
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="mb-4">
                <label for="user_id" class="block text-sm font-medium text-gray-300">User ID</label>
                <input type="text" id="user_id" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>" required
                       class="mt-1 w-full bg-gray-700 border border-gray-600 text-white rounded-md py-2 px-3 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="Enter your User ID">
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-300">Password</label>
                <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($password); ?>" required
                       class="mt-1 w-full bg-gray-700 border border-gray-600 text-white rounded-md py-2 px-3 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="Enter your password">
            </div>
            <?php if (!$is_login) : ?>
                <div class="mb-6">
                    <label for="confirm_password" class="block text-sm font-medium text-gray-300">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" value="<?php echo htmlspecialchars($confirm_password); ?>" required
                           class="mt-1 w-full bg-gray-700 border border-gray-600 text-white rounded-md py-2 px-3 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Confirm your password">
                </div>
            <?php endif; ?>
            <button type="submit" name="<?php echo $is_login ? "login" : "create_account"; ?>"
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                <?php echo $is_login ? "Login" : "Create Account"; ?>
            </button>
        </form>
        <div class="mt-4 text-center">
            <p class="text-sm text-blue-400 hover:text-blue-300 transition-colors duration-200 cursor-pointer"
               onclick="window.location.href='<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?<?php echo $is_login ? "mode=create_account" : "mode=login"; ?>'">
                <?php echo $is_login ? "Create an account" : "Login to your account"; ?>
            </p>
        </div>
    </div>

    <script>
        // Simple client-side JavaScript to toggle between login and create account modes.
        // This is used because PHP needs to reload the page to change the form,
        // and this script makes the URL change without a full form submission for the button.
        const urlParams = new URLSearchParams(window.location.search);
        const mode = urlParams.get('mode');

        if (mode === 'create_account') {
            document.querySelector('h2').textContent = 'Create Account';
            document.querySelector('form button[type="submit"]').name = 'create_account';
        } else {
            document.querySelector('h2').textContent = 'Login';
            document.querySelector('form button[type="submit"]').name = 'login';
        }
    </script>
</body>
</html>

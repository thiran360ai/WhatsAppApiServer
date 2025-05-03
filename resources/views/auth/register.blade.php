<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
        <h2 class="text-3xl font-bold mb-6 text-center text-white">Create Account</h2>
        <form method="post" action="<?php echo route('register'); ?>">
            <?php echo csrf_field(); ?>
            <div class="mb-4">
                <label for="user_id" class="block text-sm font-medium text-gray-300">User ID</label>
                <input type="text" id="user_id" name="user_id" value="<?php echo old('user_id'); ?>" required
                       class="mt-1 w-full bg-gray-700 border border-gray-600 text-white rounded-md py-2 px-3 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="Enter your User ID">
                <?php if ($errors->has('user_id')) : ?>
                    <p class="text-red-500 text-xs mt-1"><?php echo e($errors->first('user_id')); ?></p>
                <?php endif; ?>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-300">Password</label>
                <input type="password" id="password" name="password" required
                       class="mt-1 w-full bg-gray-700 border border-gray-600 text-white rounded-md py-2 px-3 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="Enter your password">
                <?php if ($errors->has('password')) : ?>
                    <p class="text-red-500 text-xs mt-1"><?php echo e($errors->first('password')); ?></p>
                <?php endif; ?>
            </div>
            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-300">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       class="mt-1 w-full bg-gray-700 border border-gray-600 text-white rounded-md py-2 px-3 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="Confirm your password">
                <?php if ($errors->has('password_confirmation')) : ?>
                    <p class="text-red-500 text-xs mt-1"><?php echo e($errors->first('password_confirmation')); ?></p>
                <?php endif; ?>
            </div>
            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                Create Account
            </button>
        </form>
        <div class="mt-4 text-center">
            <p class="text-sm text-blue-400 hover:text-blue-300 transition-colors duration-200 cursor-pointer">
                <a href="<?php echo route('login'); ?>">Login to your account</a>
            </p>
        </div>
    </div>
</body>
</html>

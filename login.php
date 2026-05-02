<?php
session_start();

$configFile = __DIR__ . '/config.php';
if (!file_exists($configFile)) {
    header('Location: install.php');
    exit;
}

require_once $configFile;

if (isAdLoggedIn()) {
    header('Location: admin.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = '请输入用户名和密码';
    } else {
        $user = adCheckAuth($username, $password);
        if ($user) {
            $_SESSION['ad_admin_id'] = $user['id'];
            $_SESSION['ad_admin_user'] = $user['username'];
            $_SESSION['ad_login_time'] = time();

            $redirect = $_GET['redirect'] ?? 'index.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = '用户名或密码错误';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录 - <?php echo defined('AD_SITE_NAME') ? AD_SITE_NAME : '广告系统'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: {} } }
    </script>
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-50 to-pink-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-2xl p-8 border border-white/20 dark:border-gray-700/20">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <i class="fa fa-shield text-white text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">管理员登录</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1"><?php echo defined('AD_SITE_NAME') ? htmlspecialchars(AD_SITE_NAME) : '广告系统'; ?></p>
            </div>

            <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl text-red-600 dark:text-red-400 text-sm">
                <i class="fa fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">用户名</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fa fa-user"></i>
                        </span>
                        <input type="text" name="username" required autofocus
                               class="w-full pl-11 pr-4 py-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                               placeholder="输入用户名">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">密码</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fa fa-lock"></i>
                        </span>
                        <input type="password" name="password" required
                               class="w-full pl-11 pr-4 py-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                               placeholder="输入密码">
                    </div>
                </div>

                <button type="submit" class="w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/40">
                    <i class="fa fa-sign-in mr-2"></i>登录
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 text-center">
                <a href="install.php" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                    <i class="fa fa-cog mr-1"></i>系统安装
                </a>
                <span class="mx-3 text-gray-300 dark:text-gray-600">|</span>
                <a href="../" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    <i class="fa fa-home mr-1"></i>返回首页
                </a>
            </div>
        </div>

        <p class="text-center text-gray-400 dark:text-gray-500 text-sm mt-6">
            © <?php echo date('Y'); ?>
            <?php if (defined('AD_COPYRIGHT') && AD_COPYRIGHT): ?>
                <?php echo htmlspecialchars(AD_COPYRIGHT); ?>
            <?php endif; ?>
            <?php if (defined('AD_ICP') && AD_ICP): ?>
                <a href="https://beian.miit.gov.cn/" target="_blank" class="hover:text-indigo-500"><?php echo htmlspecialchars(AD_ICP); ?></a>
            <?php endif; ?>
        </p>
    </div>
</body>
</html>

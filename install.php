<?php
session_start();

$configFile = __DIR__ . '/config.php';
$lockFile = __DIR__ . '/.installed';

if (file_exists($configFile)) {
    require_once $configFile;
} else {
    require_once __DIR__ . '/config_default.php';
}

if (file_exists($configFile) && file_exists($lockFile)) {
    header('Location: admin.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'check_db') {
        $host = $_POST['db_host'] ?? 'localhost';
        $port = $_POST['db_port'] ?? 3306;
        $dbname = $_POST['db_name'] ?? '';
        $username = $_POST['db_user'] ?? '';
        $password = $_POST['db_pass'] ?? '';

        try {
            $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            echo json_encode(['code' => 200, 'msg' => '数据库连接成功']);
        } catch (PDOException $e) {
            echo json_encode(['code' => 400, 'msg' => '连接失败: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'install') {
        $site_name = trim($_POST['site_name'] ?? '');
        $site_url = trim($_POST['site_url'] ?? '');
        $icp = trim($_POST['icp'] ?? '');
        $copyright = trim($_POST['copyright'] ?? '');
        $version = trim($_POST['version'] ?? 'v1.0.0');
        $db_host = $_POST['db_host'] ?? 'localhost';
        $db_port = $_POST['db_port'] ?? 3306;
        $db_name = $_POST['db_name'] ?? '';
        $db_user = $_POST['db_user'] ?? '';
        $db_pass = $_POST['db_pass'] ?? '';
        $admin_user = trim($_POST['admin_user'] ?? '');
        $admin_pass = trim($_POST['admin_pass'] ?? '');
        $admin_pass_confirm = trim($_POST['admin_pass_confirm'] ?? '');

        if (empty($site_name) || empty($db_name) || empty($admin_user) || empty($admin_pass)) {
            $error = '请填写所有必填项';
        } elseif (strcmp($admin_pass, $admin_pass_confirm) !== 0) {
            $error = '两次输入的密码不一致';
        } elseif (strlen($admin_pass) < 6) {
            $error = '管理员密码至少6位';
        } else {
            try {
                $pdo = new PDO(
                    "mysql:host=$db_host;port=$db_port;charset=utf8mb4",
                    $db_user,
                    $db_pass,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );

                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `$db_name`");

                $sqlFile = file_get_contents(__DIR__ . '/ad_system.sql');
                $statements = array_filter(array_map('trim', explode(';', $sqlFile)));
                foreach ($statements as $statement) {
                    if (!empty($statement) && strpos($statement, '--') !== 0) {
                        $pdo->exec($statement);
                    }
                }

                $pdo->exec("DROP TABLE IF EXISTS `ad_admin_users`");
                $pdo->exec("CREATE TABLE `ad_admin_users` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `username` varchar(50) NOT NULL UNIQUE,
                    `password` varchar(255) NOT NULL,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员用户表'");

                $hashedPassword = password_hash($admin_pass, PASSWORD_DEFAULT);
                $pdo->prepare("INSERT INTO ad_admin_users (username, password) VALUES (?, ?)")->execute([$admin_user, $hashedPassword]);

                $configContent = "<?php
/**
 * 广告系统配置文件
 * 由 install.php 自动生成
 */

if (!defined('AD_ADMIN_KEY')) {
    define('AD_ADMIN_KEY', 'disable');
}

if (!defined('AD_DOMAIN')) {
    define('AD_DOMAIN', '" . addslashes($site_url) . "');
}

if (!defined('AD_SITE_NAME')) {
    define('AD_SITE_NAME', '" . addslashes($site_name) . "');
}

if (!defined('AD_ICP')) {
    define('AD_ICP', '" . addslashes($icp) . "');
}

if (!defined('AD_COPYRIGHT')) {
    define('AD_COPYRIGHT', '" . addslashes($copyright) . "');
}

if (!defined('AD_VERSION')) {
    define('AD_VERSION', '" . addslashes($version) . "');
}

\$__AD_DB_CONFIG = [
    'host' => '" . addslashes($db_host) . "',
    'port' => " . intval($db_port) . ",
    'dbname' => '" . addslashes($db_name) . "',
    'username' => '" . addslashes($db_user) . "',
    'password' => '" . addslashes($db_pass) . "',
    'charset' => 'utf8mb4'
];

function adGetPdo() {
    global \$__AD_DB_CONFIG;

    static \$pdo = null;

    if (\$pdo === null) {
        \$dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            \$__AD_DB_CONFIG['host'],
            \$__AD_DB_CONFIG['port'],
            \$__AD_DB_CONFIG['dbname'],
            \$__AD_DB_CONFIG['charset']
        );

        \$options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        \$pdo = new PDO(
            \$dsn,
            \$__AD_DB_CONFIG['username'],
            \$__AD_DB_CONFIG['password'],
            \$options
        );
    }

    return \$pdo;
}

function isAdLoggedIn() {
    return isset(\$_SESSION['ad_admin_id']) && !empty(\$_SESSION['ad_admin_id']);
}

function adRequireLogin() {
    if (!isAdLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function adCheckAuth(\$username, \$password) {
    \$pdo = adGetPdo();
    \$stmt = \$pdo->prepare('SELECT * FROM ad_admin_users WHERE username = ?');
    \$stmt->execute([\$username]);
    \$user = \$stmt->fetch();
    if (\$user && password_verify(\$password, \$user['password'])) {
        return \$user;
    }
    return false;
}
";

                file_put_contents(__DIR__ . '/config.php', $configContent);
                touch(__DIR__ . '/.installed');

                $success = '安装成功！即将跳转到登录页...';
                header('Refresh: 2; URL=login.php');
            } catch (PDOException $e) {
                $error = '安装失败: ' . $e->getMessage();
            }
        }
    }
}

$siteUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? '') . '/ad';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>广告系统安装</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: {} } }
    </script>
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-gray-900 dark:to-gray-800">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-2">广告系统安装</h1>
                <p class="text-gray-500 dark:text-gray-400">请填写以下配置信息完成安装</p>
            </div>

            <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl text-red-600 dark:text-red-400">
                <i class="fa fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl text-green-600 dark:text-green-400">
                <i class="fa fa-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?>
            </div>
            <?php else: ?>

            <form method="POST" id="install-form">
                <input type="hidden" name="action" value="install">

                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center">
                            <span class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mr-3 text-indigo-600 dark:text-indigo-400">1</span>
                            站点信息
                        </h3>
                        <div class="grid grid-cols-1 gap-4 ml-11">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">站点名称</label>
                                <input type="text" name="site_name" required value="TrollApps" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">站点URL</label>
                                <input type="text" name="site_url" required value="<?php echo htmlspecialchars($siteUrl); ?>" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ICP备案</label>
                                    <input type="text" name="icp" placeholder="如: 京ICP备12345678号" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">版权信息</label>
                                    <input type="text" name="copyright" placeholder="如: ©2026" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">版本号</label>
                                    <input type="text" name="version" value="v1.0.0" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center">
                            <span class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mr-3 text-indigo-600 dark:text-indigo-400">2</span>
                            数据库配置
                        </h3>
                        <div class="grid grid-cols-2 gap-4 ml-11">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">数据库地址</label>
                                <input type="text" name="db_host" id="db_host" required value="localhost" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">端口</label>
                                <input type="number" name="db_port" id="db_port" required value="3306" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">数据库名</label>
                                <input type="text" name="db_name" id="db_name" required placeholder="trollapps" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">用户名</label>
                                <input type="text" name="db_user" id="db_user" required placeholder="root" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">密码</label>
                                <input type="password" name="db_pass" id="db_pass" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div class="ml-11 mt-3">
                            <button type="button" onclick="checkDb()" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm">
                                <i class="fa fa-database mr-1"></i> 测试连接
                            </button>
                            <span id="db-status" class="ml-3 text-sm"></span>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center">
                            <span class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mr-3 text-indigo-600 dark:text-indigo-400">3</span>
                            管理员账号
                        </h3>
                        <div class="grid grid-cols-2 gap-4 ml-11">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">管理员用户名</label>
                                <input type="text" name="admin_user" required placeholder="admin" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">管理员密码</label>
                                <input type="password" name="admin_pass" required placeholder="至少6位" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">确认密码</label>
                                <input type="password" name="admin_pass_confirm" required placeholder="再次输入密码" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-colors shadow-lg shadow-indigo-500/30">
                        <i class="fa fa-rocket mr-2"></i>开始安装
                    </button>
                </div>
            </form>

            <?php endif; ?>
        </div>
    </div>

    <script>
    async function checkDb() {
        const host = document.getElementById('db_host').value;
        const port = document.getElementById('db_port').value;
        const dbname = document.getElementById('db_name').value;
        const username = document.getElementById('db_user').value;
        const password = document.getElementById('db_pass').value;

        const statusEl = document.getElementById('db-status');
        statusEl.textContent = '连接中...';
        statusEl.className = 'ml-3 text-sm text-yellow-500';

        try {
            const formData = new FormData();
            formData.append('action', 'check_db');
            formData.append('db_host', host);
            formData.append('db_port', port);
            formData.append('db_name', dbname);
            formData.append('db_user', username);
            formData.append('db_pass', password);

            const res = await fetch('install.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.code === 200) {
                statusEl.textContent = '✓ ' + data.msg;
                statusEl.className = 'ml-3 text-sm text-green-500';
            } else {
                statusEl.textContent = '✗ ' + data.msg;
                statusEl.className = 'ml-3 text-sm text-red-500';
            }
        } catch (e) {
            statusEl.textContent = '✗ 请求失败';
            statusEl.className = 'ml-3 text-sm text-red-500';
        }
    }
    </script>

    <!-- 页脚 -->
    <footer class="py-6 text-center text-gray-500 dark:text-gray-400 text-sm">
        <?php if (defined('AD_ICP') && AD_ICP): ?>
            <a href="https://beian.miit.gov.cn" target="_blank" class="hover:text-indigo-600 dark:hover:text-indigo-400"><?php echo htmlspecialchars(AD_ICP); ?></a>
            <span class="mx-2">|</span>
        <?php endif; ?>
        <?php if (defined('AD_COPYRIGHT') && AD_COPYRIGHT): ?>
            <span><?php echo htmlspecialchars(AD_COPYRIGHT); ?></span>
        <?php endif; ?>
    </footer>
</body>
</html>

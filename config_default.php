<?php
/**
 * 广告系统配置文件
 * 请访问 install.php 进行安装配置
 */

$defaultConfig = [
    'site_name' => '广告系统',
    'site_url' => 'https://niceiphone.com',
    'icp' => '',
    'copyright' => '',
    'version' => 'v1.0.0',
    'db_host' => 'localhost',
    'db_port' => 3306,
    'db_name' => 'trollapps',
    'db_user' => 'root',
    'db_pass' => ''
];

if (!defined('AD_ADMIN_KEY')) {
    define('AD_ADMIN_KEY', 'disable');
}

if (!defined('AD_DOMAIN')) {
    define('AD_DOMAIN', $defaultConfig['site_url']);
}

if (!defined('AD_SITE_NAME')) {
    define('AD_SITE_NAME', $defaultConfig['site_name']);
}

if (!defined('AD_ICP')) {
    define('AD_ICP', $defaultConfig['icp']);
}

if (!defined('AD_COPYRIGHT')) {
    define('AD_COPYRIGHT', $defaultConfig['copyright']);
}

if (!defined('AD_VERSION')) {
    define('AD_VERSION', $defaultConfig['version']);
}

$__AD_DB_CONFIG = [
    'host' => $defaultConfig['db_host'],
    'port' => $defaultConfig['db_port'],
    'dbname' => $defaultConfig['db_name'],
    'username' => $defaultConfig['db_user'],
    'password' => $defaultConfig['db_pass'],
    'charset' => 'utf8mb4'
];

function adGetPdo() {
    global $__AD_DB_CONFIG;

    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $__AD_DB_CONFIG['host'],
            $__AD_DB_CONFIG['port'],
            $__AD_DB_CONFIG['dbname'],
            $__AD_DB_CONFIG['charset']
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $pdo = new PDO(
            $dsn,
            $__AD_DB_CONFIG['username'],
            $__AD_DB_CONFIG['password'],
            $options
        );
    }

    return $pdo;
}

function isAdLoggedIn() {
    return isset($_SESSION['ad_admin_id']) && !empty($_SESSION['ad_admin_id']);
}

function adRequireLogin() {
    if (!isAdLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function adCheckAuth($username, $password) {
    $pdo = adGetPdo();
    $stmt = $pdo->prepare('SELECT * FROM ad_admin_users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

function adIsInstalled() {
    return file_exists(__DIR__ . '/config.php');
}

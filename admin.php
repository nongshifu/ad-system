<?php
session_start();

$configFile = __DIR__ . '/config.php';
if (file_exists($configFile)) {
    require_once $configFile;
} else {
    require_once __DIR__ . '/config_default.php';
}

if (!isAdLoggedIn()) {
    header('Location: login.php');
    exit;
}

$siteName = defined('AD_SITE_NAME') ? AD_SITE_NAME : '广告管理后台';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $siteName; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {}
            }
        }
    </script>
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
        .tab-active { border-bottom: 2px solid #6366f1; color: #6366f1; }
        .modal-backdrop { background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .status-badge { animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }

        /* 统一表单样式 */
        input[type="text"],
        input[type="number"],
        input[type="email"],
        input[type="url"],
        input[type="password"],
        input[type="datetime-local"],
        input[type="file"],
        input[type="search"],
        input[type="tel"],
        select,
        textarea,
        .form-input {
            height: 38px;
            padding: 0 12px;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s ease;
            border: 1px solid #d1d5db;
            background-color: #fff;
            color: #374151;
        }

        textarea {
            height: auto;
            min-height: 80px;
            padding: 10px 12px;
            resize: vertical;
        }

        input:focus,
        select:focus,
        textarea:focus,
        .form-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        input::placeholder,
        textarea::placeholder {
            color: #9ca3af;
        }

        /* 按钮样式 */
        button,
        .btn {
            height: 38px;
            padding: 0 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        .btn-secondary {
            background-color: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background-color: #e5e7eb;
        }

        .btn-danger {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .btn-danger:hover {
            background-color: #fecaca;
        }

        /* 暗黑模式按钮 */
        .dark .btn-secondary {
            background-color: #374151;
            color: #f3f4f6;
            border-color: #4b5563;
        }

        .dark .btn-secondary:hover {
            background-color: #4b5563;
        }

        .dark .btn-danger {
            background-color: rgba(220, 38, 38, 0.2);
            color: #f87171;
        }

        .dark .btn-danger:hover {
            background-color: rgba(220, 38, 38, 0.3);
        }

        /* 搜索表单统一样式 */
        .search-form input,
        .search-form select {
            height: 38px;
            font-size: 13px;
            border-radius: 6px;
            padding: 0 10px;
            border: 1px solid #d1d5db;
            background-color: #fff;
            color: #374151;
        }

        .search-form select {
            padding-right: 28px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-position: right 8px center;
            background-repeat: no-repeat;
            background-size: 16px;
            appearance: none;
        }

        .search-form button {
            height: 38px;
            padding: 0 14px;
        }

        .dark .search-form input,
        .dark .search-form select {
            background-color: #374151;
            border-color: #4b5563;
            color: #f3f4f6;
        }

        .dark .search-form select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
        }

        /* 复选框美化 */
        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            border-radius: 5px;
            cursor: pointer;
            accent-color: #6366f1;
        }

        /* 暗黑模式表单 */
        .dark input[type="text"],
        .dark input[type="number"],
        .dark input[type="email"],
        .dark input[type="url"],
        .dark input[type="password"],
        .dark input[type="datetime-local"],
        .dark input[type="search"],
        .dark input[type="tel"],
        .dark select,
        .dark textarea,
        .dark .form-input {
            background-color: #1f2937;
            border-color: #374151;
            color: #f3f4f6;
        }

        .dark input[type="file"] {
            background-color: #1f2937;
            border-color: #374151;
            color: #9ca3af;
        }

        .dark input::placeholder,
        .dark textarea::placeholder {
            color: #6b7280;
        }

        .dark select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%239ca3af' d='M3 4.5L6 7.5L9 4.5'/%3E%3C/svg%3E");
        }

        /* 开关样式 */
        .switch {
            position: relative;
            display: inline-block;
            width: 42px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #d1d5db;
            transition: 0.3s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.3s;
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        input:checked + .slider {
            background-color: #10b981;
        }

        input:checked + .slider:before {
            transform: translateX(18px);
        }

        .dark .slider {
            background-color: #4b5563;
        }

        .dark input:checked + .slider {
            background-color: #10b981;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-200 min-h-screen dark:bg-gradient-to-br dark:from-gray-900 dark:to-gray-800">
    <!-- 顶部导航 -->
    <header class="bg-white/95 backdrop-blur shadow-lg sticky top-0 z-40 dark:bg-gray-800/95 dark:shadow-gray-900/20">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fa fa-bullhorn text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-gray-800 dark:text-white">广告管理系统</h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400">TrollApps Ad Platform</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <button id="theme-toggle" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors" title="切换主题">
                        <i class="fa fa-moon-o text-gray-600 dark:text-gray-300 text-lg dark:hidden"></i>
                        <i class="fa fa-sun-o text-yellow-400 hidden dark:block"></i>
                    </button>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        <i class="fa fa-shield mr-1"></i><?php echo htmlspecialchars($_SESSION['ad_admin_user'] ?? 'Admin'); ?>
                    </span>
                    <button onclick="openChangePasswordModal()" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-gray-600 dark:text-gray-300" title="修改密码">
                        <i class="fa fa-key text-lg"></i>
                    </button>
                    <button onclick="logout()" class="text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 transition-colors" title="退出登录">
                        <i class="fa fa-sign-out text-lg"></i>
                    </button>
                    <a href="/" class="text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">
                        <i class="fa fa-home text-lg"></i>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- 统计卡片 -->
    <div class="container mx-auto px-4 py-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-md card-hover transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">广告总数</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white" id="stat-ads">-</p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center">
                        <i class="fa fa-bullhorn text-indigo-600 dark:text-indigo-400 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-md card-hover transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">广告位置</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white" id="stat-positions">-</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                        <i class="fa fa-map-marker text-green-600 dark:text-green-400 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-md card-hover transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">活跃关联</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white" id="stat-relations">-</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                        <i class="fa fa-link text-purple-600 dark:text-purple-400 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-md card-hover transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">总展示量</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white" id="stat-views">-</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center">
                        <i class="fa fa-eye text-orange-600 dark:text-orange-400 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 标签页 -->
    <div class="container mx-auto px-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg mb-6 overflow-hidden">
            <div class="flex border-b bg-gray-50/50 dark:bg-gray-700/50">
                <button onclick="switchTab('ads')" class="tab-btn px-6 py-4 font-medium text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors tab-active flex items-center gap-2" data-tab="ads">
                    <i class="fa fa-bullhorn"></i> 广告管理
                </button>
                <button onclick="switchTab('positions')" class="tab-btn px-6 py-4 font-medium text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors flex items-center gap-2" data-tab="positions">
                    <i class="fa fa-map-marker"></i> 广告位置
                </button>
                <button onclick="switchTab('relations')" class="tab-btn px-6 py-4 font-medium text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors flex items-center gap-2" data-tab="relations">
                    <i class="fa fa-link"></i> 位置关联
                </button>
                <button onclick="switchTab('help')" class="tab-btn px-6 py-4 font-medium text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors flex items-center gap-2" data-tab="help">
                    <i class="fa fa-question-circle"></i> 使用说明
                </button>
            </div>

            <!-- 广告管理 -->
            <div id="tab-ads" class="tab-content p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">广告列表</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">管理所有广告内容，支持图片、文字、HTML多种类型</p>
                    </div>
                    <button onclick="openAdModal()" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-5 py-2.5 rounded-lg hover:shadow-lg transition-all flex items-center gap-2">
                        <i class="fa fa-plus"></i> 添加广告
                    </button>
                </div>
                <!-- 搜索表单 -->
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 search-form">
                    <div class="flex flex-wrap gap-4 items-end">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">关键词</label>
                            <input type="text" id="search-ad-keyword" placeholder="标题/链接" onkeyup="filterAds()">
                        </div>
                        <div class="w-32">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">类型</label>
                            <select id="search-ad-type" onchange="filterAds()">
                                <option value="">全部</option>
                                <option value="1">🖼️ 图片</option>
                                <option value="2">📝 文字</option>
                                <option value="3">📄 HTML</option>
                            </select>
                        </div>
                        <div class="w-32">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">状态</label>
                            <select id="search-ad-status" onchange="filterAds()">
                                <option value="">全部</option>
                                <option value="1">已启用</option>
                                <option value="0">已禁用</option>
                            </select>
                        </div>
                        <div class="w-32">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">排序</label>
                            <select id="search-ad-order" onchange="filterAds()">
                                <option value="id-desc">最新</option>
                                <option value="id-asc">最早</option>
                                <option value="view-desc">浏览最多</option>
                                <option value="click-desc">点击最多</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="resetAdSearch()" class="btn-secondary">
                                <i class="fa fa-refresh"></i> 重置
                            </button>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">广告</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">类型</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">预览</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">状态</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">有效期</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">统计</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">操作</th>
                            </tr>
                        </thead>
                        <tbody id="ads-table-body" class="divide-y divide-gray-200 dark:divide-gray-700"></tbody>
                    </table>
                </div>
            </div>

            <!-- 广告位置 -->
            <div id="tab-positions" class="tab-content hidden p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">广告位置</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">定义广告展示位置，每个位置可绑定多个广告</p>
                    </div>
                    <button onclick="openPositionModal()" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-5 py-2.5 rounded-lg hover:shadow-lg transition-all flex items-center gap-2">
                        <i class="fa fa-plus"></i> 添加位置
                    </button>
                </div>
                <!-- 搜索表单 -->
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 search-form">
                    <div class="flex flex-wrap gap-4 items-end">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">关键词</label>
                            <input type="text" id="search-pos-keyword" placeholder="名称/标识符/描述" onkeyup="filterPositions()">
                        </div>
                        <div class="w-48">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">宽度</label>
                            <input type="number" id="search-pos-width" placeholder="宽度" onkeyup="filterPositions()">
                        </div>
                        <div class="w-48">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">高度</label>
                            <input type="number" id="search-pos-height" placeholder="高度" onkeyup="filterPositions()">
                        </div>
                        <div class="w-32">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">状态</label>
                            <select id="search-pos-status" onchange="filterPositions()">
                                <option value="">全部</option>
                                <option value="1">已启用</option>
                                <option value="0">已禁用</option>
                            </select>
                        </div>
                        <div class="w-36">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">排序</label>
                            <select id="search-pos-order" onchange="filterPositions()">
                                <option value="priority-desc">优先级最高</option>
                                <option value="priority-asc">优先级最低</option>
                                <option value="id-desc">最新</option>
                                <option value="id-asc">最早</option>
                                <option value="width-desc">宽度最大</option>
                                <option value="width-asc">宽度最小</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="resetPositionSearch()" class="btn-secondary">
                                <i class="fa fa-refresh"></i> 重置
                            </button>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="positions-grid"></div>
            </div>

            <!-- 位置关联 -->
            <div id="tab-relations" class="tab-content hidden p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">广告位关联</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">将广告绑定到指定位置，支持权重设置实现轮播</p>
                    </div>
                    <button onclick="openRelationModal()" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-5 py-2.5 rounded-lg hover:shadow-lg transition-all flex items-center gap-2">
                        <i class="fa fa-plus"></i> 添加关联
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">位置</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">广告</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">权重</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">状态</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">操作</th>
                            </tr>
                        </thead>
                        <tbody id="relations-table-body" class="divide-y divide-gray-200 dark:divide-gray-700"></tbody>
                    </table>
                </div>
            </div>

            <!-- 使用说明 -->
            <div id="tab-help" class="tab-content hidden p-6">
                <div class="prose max-w-none dark:prose-invert">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">📖 广告系统使用说明</h2>

                    <div class="space-y-6">
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-5">
                            <h3 class="font-bold text-blue-800 dark:text-blue-400 mb-2 flex items-center gap-2">
                                <i class="fa fa-info-circle"></i> 基本概念
                            </h3>
                            <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1 list-disc list-inside">
                                <li><strong>广告位置</strong>：页面上的广告展示位，如"首页横幅"、"订单页广告"等</li>
                                <li><strong>广告</strong>：实际的广告内容，可以是图片、文字或HTML</li>
                                <li><strong>关联</strong>：将广告绑定到位置，一个位置可以绑定多个广告（按权重轮播）</li>
                            </ul>
                        </div>

                        <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-5">
                            <h3 class="font-bold text-green-800 dark:text-green-400 mb-2 flex items-center gap-2">
                                <i class="fa fa-code"></i> 前端调用
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <h4 class="font-semibold text-green-700 dark:text-green-300 mb-2">方法1: PHP调用</h4>
                                    <p class="text-sm text-green-700 dark:text-green-300 mb-2">在任何PHP页面中引入广告组件即可显示广告：</p>
                                    <pre class="bg-gray-800 dark:bg-gray-900 text-gray-100 p-4 rounded-lg text-sm overflow-x-auto"><code>&lt;?php
require_once __DIR__ . '/api.php';

$html = getAdWidget('order_banner', [
    'width' => '100%',
    'height' => '100px',
    'class' => 'mb-4'
]);

if ($html) {
    echo $html;
}
?></code></pre>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-green-700 dark:text-green-300 mb-2">方法2: HTML + JavaScript调用（推荐用于非PHP页面）</h4>
                                    <p class="text-sm text-green-700 dark:text-green-300 mb-2">通过API获取广告数据并动态渲染：</p>
                                    <pre class="bg-gray-800 dark:bg-gray-900 text-gray-100 p-4 rounded-lg text-sm overflow-x-auto"><code>&lt;!-- 广告容器 --&gt;
&lt;div id="ad-container" class="mb-4"&gt;&lt;/div&gt;

&lt;script&gt;
async function loadAd(positionCode, options = {}) {
    try {
        const response = await fetch('/ad/api.php?action=get_ad&amp;position=' + positionCode);
        const result = await response.json();
        
        if (result.code !== 200) {
            return;
        }
        
        const ad = result.data;
        const container = document.getElementById('ad-container');
        const width = options.width || '100%';
        const height = options.height || 'auto';
        
        // 记录展示
        fetch('/ad/api.php?action=record_view&amp;id=' + ad.id + '&amp;position=' + positionCode);
        
        let html = '';
        if (ad.type === 1) {
            // 图片广告
            html = '&lt;div style="width:' + width + ';height:' + height + ';position:relative;"&gt;';
            html += '&lt;a href="' + ad.link_url + '" target="_blank" onclick="recordAdClick(' + ad.id + ', \'' + positionCode + '\')"&gt;';
            html += '&lt;img src="' + ad.image_url + '" alt="' + ad.title + '" style="width:100%;height:100%;object-fit:cover;"&gt;';
            html += '&lt;/a&gt;';
            html += '&lt;/div&gt;';
        } else if (ad.type === 2) {
            // 文字广告
            html = '&lt;div style="width:' + width + ';padding:10px;background:#f5f5f5;border-radius:4px;"&gt;';
            html += '&lt;a href="' + ad.link_url + '" target="_blank" onclick="recordAdClick(' + ad.id + ', \'' + positionCode + '\')"&gt;' + ad.content + '&lt;/a&gt;';
            html += '&lt;/div&gt;';
        } else if (ad.type === 3) {
            // 自定义广告
            html = '&lt;div style="width:' + width + ';"&gt;' + ad.content + '&lt;/div&gt;';
        }
        
        container.innerHTML = html;
    } catch (e) {
        console.error('加载广告失败:', e);
    }
}

function recordAdClick(adId, position) {
    fetch('/ad/api.php?action=record_click&amp;id=' + adId + '&amp;position=' + position);
}

// 加载广告
loadAd('order_banner', {
    width: '100%',
    height: '100px'
});
&lt;/script&gt;</code></pre>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-green-700 dark:text-green-300 mb-2">方法3: 使用提供的JS文件（最简单）</h4>
                                    <p class="text-sm text-green-700 dark:text-green-300 mb-2">引入我们已经封装好的JS文件：</p>
                                    <pre class="bg-gray-800 dark:bg-gray-900 text-gray-100 p-4 rounded-lg text-sm overflow-x-auto"><code>&lt;!-- 引入广告加载器 --&gt;
&lt;script src="/ad/ad-loader.js"&gt;&lt;/script&gt;

&lt;!-- 广告容器 --&gt;
&lt;div id="ad-banner"&gt;&lt;/div&gt;
&lt;div id="ad-sidebar"&gt;&lt;/div&gt;

&lt;script&gt;
// 加载广告
adLoader.load('ad-banner', 'order_banner', {
    width: '100%',
    height: '100px'
});

adLoader.load('ad-sidebar', 'home_sidebar', {
    width: '300px',
    height: '250px'
});
&lt;/script&gt;</code></pre>
                                </div>
                            </div>
                        </div>

                        <div class="bg-orange-50 dark:bg-orange-900/20 rounded-xl p-5">
                            <h3 class="font-bold text-orange-800 dark:text-orange-400 mb-2 flex items-center gap-2">
                                <i class="fa fa-question-circle"></i> 常见问题
                            </h3>
                            <div class="space-y-3 text-sm">
                                <div>
                                    <strong class="text-orange-700 dark:text-orange-400">Q: 广告不显示？</strong>
                                    <p class="text-orange-600 dark:text-orange-300 mt-1">A: 检查 1)广告状态是否启用 2)关联表中是否绑定 3)有效期是否有效 4)图片URL是否正确</p>
                                </div>
                                <div>
                                    <strong class="text-orange-700 dark:text-orange-400">Q: 如何实现多广告轮播？</strong>
                                    <p class="text-orange-600 dark:text-orange-300 mt-1">A: 在关联表中添加多个广告，通过weight权重控制显示概率，权重越高显示机会越大</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-5">
                            <h3 class="font-bold text-blue-800 dark:text-blue-400 mb-2 flex items-center gap-2">
                                <i class="fa fa-plug"></i> API接口文档
                            </h3>
                            <div class="space-y-4 text-sm">
                                <div>
                                    <strong class="text-blue-700 dark:text-blue-400">1. 获取单个广告</strong>
                                    <pre class="bg-gray-800 dark:bg-gray-900 text-gray-100 p-3 rounded-lg mt-2 text-xs overflow-x-auto"><code>GET /ad/api.php?action=get_ad&amp;position=order_banner

响应:
{
  "code": 200,
  "data": {
    "id": 1,
    "title": "广告标题",
    "type": 1,
    "image_url": "https://...",
    "link_url": "https://...",
    "content": "",
    ...
  }
}</code></pre>
                                </div>
                                <div>
                                    <strong class="text-blue-700 dark:text-blue-400">2. 获取多个广告</strong>
                                    <pre class="bg-gray-800 dark:bg-gray-900 text-gray-100 p-3 rounded-lg mt-2 text-xs overflow-x-auto"><code>GET /ad/api.php?action=get_ads_by_position&amp;position=order_banner&amp;limit=5

响应:
{
  "code": 200,
  "data": [
    { "id": 1, ... },
    { "id": 2, ... }
  ]
}</code></pre>
                                </div>
                                <div>
                                    <strong class="text-blue-700 dark:text-blue-400">3. 记录广告展示</strong>
                                    <pre class="bg-gray-800 dark:bg-gray-900 text-gray-100 p-3 rounded-lg mt-2 text-xs overflow-x-auto"><code>GET /ad/api.php?action=record_view&amp;id=1&amp;position=order_banner

响应:
{ "code": 200, "msg": "success" }</code></pre>
                                </div>
                                <div>
                                    <strong class="text-blue-700 dark:text-blue-400">4. 记录广告点击</strong>
                                    <pre class="bg-gray-800 dark:bg-gray-900 text-gray-100 p-3 rounded-lg mt-2 text-xs overflow-x-auto"><code>GET /ad/api.php?action=record_click&amp;id=1&amp;position=order_banner

响应:
{ "code": 200, "msg": "success" }</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 广告Modal -->
    <div id="ad-modal" class="fixed inset-0 z-50 hidden">
        <div class="modal-backdrop absolute inset-0" onclick="closeAdModal()"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-2xl bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-h-[90vh] overflow-hidden">
            <div class="p-5 border-b flex justify-between items-center bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
                <h3 class="text-lg font-bold" id="ad-modal-title">添加广告</h3>
                <button onclick="closeAdModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-colors">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <form id="ad-form" class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
                <input type="hidden" id="ad-id" name="id" value="0">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">标题 *</label>
                        <input type="text" id="ad-title" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" placeholder="输入广告标题">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">类型 *</label>
                        <select id="ad-type" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" onchange="toggleAdTypeFields()">
                            <option value="1">🖼️ 图片广告</option>
                            <option value="2">📝 文字广告</option>
                            <option value="3">📄 HTML广告</option>
                        </select>
                    </div>
                </div>

                <div id="ad-image-section">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">图片</label>
                    <div class="flex gap-3">
                        <input type="text" id="ad-image-url" class="flex-1 px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" placeholder="图片URL或上传">
                        <label class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center gap-2 transition-colors text-gray-700 dark:text-gray-300">
                            <i class="fa fa-upload"></i> 上传
                            <input type="file" id="ad-image-file" accept="image/*" class="hidden" onchange="uploadAdImage(this)">
                        </label>
                    </div>
                    <img id="ad-image-preview" class="mt-3 max-h-32 rounded-lg hidden">
                </div>

                <div id="ad-link-section">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">链接地址</label>
                    <input type="text" id="ad-link-url" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" placeholder="https:// 或留空">
                </div>

                <div id="ad-content-section" class="hidden">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">内容</label>
                    <textarea id="ad-content" rows="3" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" placeholder="文字内容或HTML代码"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">开始时间</label>
                        <input type="datetime-local" id="ad-start-time" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">结束时间</label>
                        <input type="datetime-local" id="ad-end-time" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">排序</label>
                        <input type="number" id="ad-sort" value="0" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">状态</label>
                        <select id="ad-status" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            <option value="0">❌ 禁用</option>
                            <option value="1" selected>✅ 启用</option>
                            <option value="2">⏳ 待审核</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="ad-sticky" class="w-4 h-4 text-indigo-600 rounded border-gray-300 dark:border-gray-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700 dark:text-gray-300">置顶推广</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-700">
                    <button type="button" onclick="closeAdModal()" class="px-6 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300">取消</button>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:shadow-lg transition-all">保存</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 位置Modal -->
    <div id="position-modal" class="fixed inset-0 z-50 hidden">
        <div class="modal-backdrop absolute inset-0" onclick="closePositionModal()"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg bg-white dark:bg-gray-800 rounded-2xl shadow-2xl">
            <div class="p-5 border-b flex justify-between items-center bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
                <h3 class="text-lg font-bold" id="position-modal-title">添加位置</h3>
                <button onclick="closePositionModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-colors">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <form id="position-form" class="p-6 space-y-5">
                <input type="hidden" id="position-id" name="id" value="0">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">标识符 *</label>
                    <input type="text" id="position-code" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono" placeholder="如: home_banner">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">唯一标识，用于前端调用</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">名称 *</label>
                    <input type="text" id="position-name" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" placeholder="如: 首页横幅">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">描述</label>
                    <input type="text" id="position-description" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" placeholder="描述该位置的用途">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">宽度 (px)</label>
                        <input type="number" id="position-width" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" placeholder="750">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">高度 (px)</label>
                        <input type="number" id="position-height" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" placeholder="300">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">位置预览图</label>
                    <div class="flex items-start gap-4">
                        <div id="position-image-preview" class="w-32 h-24 bg-gray-100 dark:bg-gray-700 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center overflow-hidden">
                            <span class="text-gray-400 dark:text-gray-500 text-xs">暂无图片</span>
                        </div>
                        <div class="flex-1 space-y-2">
                            <input type="file" id="position-image-file" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 dark:file:bg-indigo-900/30 file:text-indigo-600 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50 transition-colors">
                            <input type="hidden" id="position-image-url">
                            <p class="text-xs text-gray-500 dark:text-gray-400">上传广告位置的预览图片，方便客户了解位置</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">优先级</label>
                        <input type="number" id="position-priority" value="0" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">数字越大越靠前</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">状态</label>
                        <select id="position-status" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            <option value="0">❌ 禁用</option>
                            <option value="1" selected>✅ 启用</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-700">
                    <button type="button" onclick="closePositionModal()" class="px-6 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300">取消</button>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:shadow-lg transition-all">保存</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 关联Modal -->
    <div id="relation-modal" class="fixed inset-0 z-50 hidden">
        <div class="modal-backdrop absolute inset-0" onclick="closeRelationModal()"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg bg-white dark:bg-gray-800 rounded-2xl shadow-2xl">
            <div class="p-5 border-b flex justify-between items-center bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
                <h3 class="text-lg font-bold" id="relation-modal-title">添加关联</h3>
                <button onclick="closeRelationModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-colors">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <form id="relation-form" class="p-6 space-y-5">
                <input type="hidden" id="relation-id" name="id" value="0">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">广告位置 *</label>
                    <select id="relation-position-id" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" onchange="showPositionDetail()">
                        <option value="">请选择位置</option>
                    </select>
                    <div id="position-detail" class="mt-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hidden">
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">标识符：</span>
                                <span id="detail-position-code" class="font-mono text-indigo-600 dark:text-indigo-400">-</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">尺寸：</span>
                                <span id="detail-position-size" class="text-gray-700 dark:text-gray-300">-</span>
                            </div>
                            <div class="col-span-2">
                                <span class="text-gray-500 dark:text-gray-400">描述：</span>
                                <span id="detail-position-desc" class="text-gray-700 dark:text-gray-300">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">广告 *</label>
                    <select id="relation-ad-id" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" onchange="showAdDetail()">
                        <option value="">请选择广告</option>
                    </select>
                    <div id="ad-detail" class="mt-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hidden">
                        <div class="flex gap-3">
                            <div id="detail-ad-preview" class="w-24 h-16 bg-gray-200 dark:bg-gray-600 rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0">
                                <span class="text-gray-400 text-xs">无预览</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                    <span>类型：</span>
                                    <span id="detail-ad-type" class="text-gray-700 dark:text-gray-300">-</span>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                    <span>链接：</span>
                                    <span id="detail-ad-link" class="text-gray-700 dark:text-gray-300 truncate block max-w-full">-</span>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    <span>状态：</span>
                                    <span id="detail-ad-status" class="text-gray-700 dark:text-gray-300">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">权重</label>
                        <input type="number" id="relation-weight" value="1" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">数字越大显示概率越高</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">状态</label>
                        <select id="relation-status" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            <option value="1" selected>✅ 启用</option>
                            <option value="0">❌ 禁用</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-700">
                    <button type="button" onclick="closeRelationModal()" class="px-6 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300">取消</button>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:shadow-lg transition-all">保存</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 修改密码Modal -->
    <div id="change-password-modal" class="fixed inset-0 z-50 hidden">
        <div class="modal-backdrop absolute inset-0" onclick="closeChangePasswordModal()"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl">
            <div class="p-5 border-b flex justify-between items-center bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
                <h3 class="text-lg font-bold">修改密码</h3>
                <button onclick="closeChangePasswordModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-colors">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <form id="change-password-form" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">当前密码</label>
                    <input type="password" id="current-password" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" placeholder="请输入当前密码">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">新密码</label>
                    <input type="password" id="new-password" required minlength="6" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" placeholder="至少6位">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">确认新密码</label>
                    <input type="password" id="confirm-new-password" required minlength="6" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" placeholder="再次输入新密码">
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-700">
                    <button type="button" onclick="closeChangePasswordModal()" class="px-6 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300">取消</button>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:shadow-lg transition-all">保存</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="fixed top-20 left-1/2 -translate-x-1/2 px-6 py-3 rounded-xl shadow-lg font-medium hidden z-50"></div>

    <!-- 广告代码模态框 -->
    <div id="adcode-modal" class="fixed inset-0 z-50 hidden">
        <div class="modal-backdrop absolute inset-0" onclick="closeAdCodeModal()"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md mx-4 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden">
            <div class="p-4 border-b flex justify-between items-center bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
                <h3 class="text-base font-bold"><i class="fa fa-code mr-2"></i>广告代码</h3>
                <button onclick="closeAdCodeModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-colors">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <div class="p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">位置：<span id="adcode-position-name" class="font-medium text-gray-700 dark:text-gray-300"></span></p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">标识符：<code id="adcode-position-code" class="text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded"></code></p>
                
                <!-- PHP调用方式 -->
                <div class="mb-4">
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-2">PHP 调用（推荐）</p>
                    <div class="relative">
                        <pre id="adcode-php" class="text-xs bg-gray-800 dark:bg-gray-900 text-green-400 p-3 rounded-lg overflow-x-auto max-h-32"><code></code></pre>
                        <button onclick="copyAdCode('php')" class="absolute top-2 right-2 px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs rounded-lg transition-colors">
                            <i class="fa fa-copy mr-1"></i>复制
                        </button>
                    </div>
                </div>
                
                <!-- HTML+JS调用方式 -->
                <div>
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-2">HTML + JavaScript 调用</p>
                    <div class="relative">
                        <pre id="adcode-html" class="text-xs bg-gray-800 dark:bg-gray-900 text-green-400 p-3 rounded-lg overflow-x-auto max-h-32"><code></code></pre>
                        <button onclick="copyAdCode('html')" class="absolute top-2 right-2 px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs rounded-lg transition-colors">
                            <i class="fa fa-copy mr-1"></i>复制
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 图片预览模态框 -->
    <div id="ad-preview-modal" class="fixed inset-0 z-[60] hidden">
        <div class="modal-backdrop absolute inset-0" onclick="closeAdPreview()"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 max-w-[90vw] max-h-[85vh] w-full mx-4">
            <button onclick="closeAdPreview()" class="absolute -top-12 right-0 w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-colors text-white text-xl z-10">
                <i class="fa fa-times"></i>
            </button>
            <div id="preview-image-container" class="bg-white rounded-xl shadow-2xl p-4 max-h-[85vh] overflow-auto">
                <img id="preview-image-src" src="" alt="预览" class="max-w-full max-h-[80vh] rounded-lg">
                <div id="preview-html-content" class="min-h-[200px]"></div>
            </div>
        </div>
    </div>

    <script>
    const apiUrl = '/ad/api.php';
    let positions = [];
    let ads = [];

    function initTheme() {
        const savedTheme = localStorage.getItem('ad-theme') || 'light';
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark');
        }
    }

    function toggleTheme() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('ad-theme', isDark ? 'dark' : 'light');
    }

    document.addEventListener('DOMContentLoaded', initTheme);
    document.getElementById('theme-toggle')?.addEventListener('click', toggleTheme);

    function showToast(message, isError = false) {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.className = 'fixed top-20 left-1/2 -translate-x-1/2 px-6 py-3 rounded-xl shadow-lg font-medium z-50 transition-all ' + (isError ? 'bg-red-500 text-white' : 'bg-green-500 text-white');
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 2000);
    }

    function switchTab(tab) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('tab-active'));
        document.getElementById('tab-' + tab).classList.remove('hidden');
        document.querySelector('[data-tab="' + tab + '"]').classList.add('tab-active');
    }

    async function loadStats() {
        const [adsRes, posRes, relRes] = await Promise.all([
            fetch(apiUrl + '?action=get_ads'),
            fetch(apiUrl + '?action=get_positions'),
            fetch(apiUrl + '?action=get_relations')
        ]);
        const adsData = await adsRes.json();
        const posData = await posRes.json();
        const relData = await relRes.json();
        
        document.getElementById('stat-ads').textContent = adsData.data?.length || 0;
        document.getElementById('stat-positions').textContent = posData.data?.length || 0;
        document.getElementById('stat-relations').textContent = relData.data?.filter(r => r.status == 1).length || 0;
        
        let totalViews = 0;
        (adsData.data || []).forEach(ad => totalViews += ad.view_count || 0);
        document.getElementById('stat-views').textContent = totalViews;
    }

    async function logout() {
        if (!confirm('确定要退出登录吗？')) return;
        const res = await fetch(apiUrl + '?action=logout');
        const data = await res.json();
        if (data.code === 200) {
            window.location.href = 'login.php';
        }
    }

    async function loadAds() {
        const res = await fetch(apiUrl + '?action=get_ads');
        const data = await res.json();
        if (data.code === 200) {
            ads = data.data;
            renderAds();
        }
    }

    function filterAds() {
        const keyword = document.getElementById('search-ad-keyword').value.toLowerCase();
        const type = document.getElementById('search-ad-type').value;
        const status = document.getElementById('search-ad-status').value;
        const order = document.getElementById('search-ad-order').value;

        let filtered = ads.filter(ad => {
            if (keyword && !ad.title.toLowerCase().includes(keyword) && !(ad.link_url || '').toLowerCase().includes(keyword) && !(ad.content || '').toLowerCase().includes(keyword)) return false;
            if (type && String(ad.type) !== type) return false;
            if (status && String(ad.status) !== status) return false;
            return true;
        });

        const [field, dir] = order.split('-');
        filtered.sort((a, b) => {
            if (field === 'id') return dir === 'desc' ? b.id - a.id : a.id - b.id;
            if (field === 'view') return dir === 'desc' ? (b.view_count || 0) - (a.view_count || 0) : (a.view_count || 0) - (b.view_count || 0);
            if (field === 'click') return dir === 'desc' ? (b.click_count || 0) - (a.click_count || 0) : (a.click_count || 0) - (b.click_count || 0);
            return 0;
        });

        const tbody = document.getElementById('ads-table-body');
        tbody.innerHTML = filtered.map(ad => {
            const typeMap = {1: {icon: '🖼️', text: '图片'}, 2: {icon: '📝', text: '文字'}, 3: {icon: '📄', text: 'HTML'}};
            const statusText = {0: '已禁用', 1: '已启用', 2: '待审核'};
            return `<tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <td class="px-4 py-3 font-mono text-sm text-gray-500 dark:text-gray-400">#${ad.id}</td>
                <td class="px-4 py-3">
                    <div class="font-semibold text-gray-800 dark:text-white">${ad.title}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate max-w-[200px]">${ad.link_url || '-'}</div>
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center gap-1 text-sm text-gray-700 dark:text-gray-300">${typeMap[ad.type]?.icon} ${typeMap[ad.type]?.text}</span>
                </td>
                <td class="px-4 py-3">
                    ${ad.type == 3 && ad.content ? `<button onclick="previewAdById(${ad.id})" class="px-3 py-1.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded text-xs hover:bg-indigo-200 dark:hover:bg-indigo-800/30 transition-colors">预览HTML</button>` : ad.image_url ? `<img src="${ad.image_url}" class="h-10 w-16 object-cover rounded cursor-pointer hover:ring-2 hover:ring-indigo-500" onclick="previewAdById(${ad.id})">` : '<span class="text-gray-400 dark:text-gray-500 text-sm">无预览</span>'}
                </td>
                <td class="px-4 py-3">
                    <label class="switch" title="${statusText[ad.status]}">
                        <input type="checkbox" ${ad.status == 1 ? 'checked' : ''} onchange="toggleAdStatus(${ad.id}, this.checked)">
                        <span class="slider"></span>
                    </label>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                    <div>${ad.start_time ? ad.start_time.slice(0, 10) : '无'}</div>
                    <div class="text-xs text-gray-400 dark:text-gray-500">至 ${ad.end_time ? ad.end_time.slice(0, 10) : '无期限'}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                    <span><i class="fa fa-eye"></i> ${ad.view_count || 0}</span>
                    <span class="ml-2"><i class="fa fa-hand-pointer-o"></i> ${ad.click_count || 0}</span>
                </td>
                <td class="px-4 py-3">
                    <button onclick="editAd(${ad.id})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 mr-3 font-medium text-sm">编辑</button>
                    <button onclick="deleteAd(${ad.id})" class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium text-sm">删除</button>
                </td>
            </tr>`;
        }).join('');
    }

    function resetAdSearch() {
        document.getElementById('search-ad-keyword').value = '';
        document.getElementById('search-ad-type').value = '';
        document.getElementById('search-ad-status').value = '';
        document.getElementById('search-ad-order').value = 'id-desc';
        filterAds();
    }

    function renderAds() {
        const tbody = document.getElementById('ads-table-body');
        tbody.innerHTML = ads.map(ad => {
            const typeMap = {1: {icon: '🖼️', text: '图片'}, 2: {icon: '📝', text: '文字'}, 3: {icon: '📄', text: 'HTML'}};
            const statusText = {0: '已禁用', 1: '已启用', 2: '待审核'};

            return `<tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <td class="px-4 py-3 font-mono text-sm text-gray-500 dark:text-gray-400">#${ad.id}</td>
                <td class="px-4 py-3">
                    <div class="font-semibold text-gray-800 dark:text-white">${ad.title}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate max-w-[200px]">${ad.link_url || '-'}</div>
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center gap-1 text-sm text-gray-700 dark:text-gray-300">${typeMap[ad.type]?.icon} ${typeMap[ad.type]?.text}</span>
                </td>
                <td class="px-4 py-3">
                    ${ad.type == 3 && ad.content ? `<button onclick="previewAdById(${ad.id})" class="px-3 py-1.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded text-xs hover:bg-indigo-200 dark:hover:bg-indigo-800/30 transition-colors">预览HTML</button>` : ad.image_url ? `<img src="${ad.image_url}" class="h-10 w-16 object-cover rounded cursor-pointer hover:ring-2 hover:ring-indigo-500" onclick="previewAdById(${ad.id})">` : '<span class="text-gray-400 dark:text-gray-500 text-sm">无预览</span>'}
                </td>
                <td class="px-4 py-3">
                    <label class="switch" title="${statusText[ad.status]}">
                        <input type="checkbox" ${ad.status == 1 ? 'checked' : ''} onchange="toggleAdStatus(${ad.id}, this.checked)">
                        <span class="slider"></span>
                    </label>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                    <div>${ad.start_time ? ad.start_time.slice(0, 10) : '无'}</div>
                    <div class="text-xs text-gray-400 dark:text-gray-500">至 ${ad.end_time ? ad.end_time.slice(0, 10) : '无期限'}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                    <span><i class="fa fa-eye"></i> ${ad.view_count || 0}</span>
                    <span class="ml-2"><i class="fa fa-hand-pointer-o"></i> ${ad.click_count || 0}</span>
                </td>
                <td class="px-4 py-3">
                    <button onclick="editAd(${ad.id})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 mr-3 font-medium text-sm">编辑</button>
                    <button onclick="deleteAd(${ad.id})" class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium text-sm">删除</button>
                </td>
            </tr>`;
        }).join('');
    }

    function openAdModal(id = 0) {
        document.getElementById('ad-form').reset();
        document.getElementById('ad-id').value = 0;
        document.getElementById('ad-image-preview').classList.add('hidden');
        document.getElementById('ad-modal-title').textContent = '添加广告';

        if (id > 0) {
            const ad = ads.find(a => String(a.id) === String(id));
            if (ad) {
                document.getElementById('ad-modal-title').textContent = '编辑广告';
                document.getElementById('ad-id').value = ad.id;
                document.getElementById('ad-title').value = ad.title || '';
                document.getElementById('ad-type').value = ad.type || 1;
                document.getElementById('ad-image-url').value = ad.image_url || '';
                document.getElementById('ad-link-url').value = ad.link_url || '';
                document.getElementById('ad-content').value = ad.content || '';
                document.getElementById('ad-sort').value = ad.sort || 0;
                document.getElementById('ad-status').value = ad.status ?? 1;
                if (ad.start_time) document.getElementById('ad-start-time').value = ad.start_time.slice(0, 16);
                if (ad.end_time) document.getElementById('ad-end-time').value = ad.end_time.slice(0, 16);
                if (ad.image_url) {
                    document.getElementById('ad-image-preview').src = ad.image_url;
                    document.getElementById('ad-image-preview').classList.remove('hidden');
                }
            } else {
                showToast('广告数据加载中，请稍后重试', true);
                loadAds();
                return;
            }
        }
        toggleAdTypeFields();
        document.getElementById('ad-modal').classList.remove('hidden');
    }

    function closeAdModal() {
        document.getElementById('ad-modal').classList.add('hidden');
    }

    function toggleAdTypeFields() {
        const type = parseInt(document.getElementById('ad-type').value);
        document.getElementById('ad-image-section').classList.toggle('hidden', type !== 1);
        document.getElementById('ad-link-section').classList.toggle('hidden', type === 3);
        document.getElementById('ad-content-section').classList.toggle('hidden', type !== 2 && type !== 3);
    }

    async function uploadAdImage(input) {
        if (!input.files[0]) return;
        const formData = new FormData();
        formData.append('image', input.files[0]);
        const res = await fetch(apiUrl + '?action=upload_ad_image', {method: 'POST', body: formData});
        const data = await res.json();
        if (data.code === 200) {
            document.getElementById('ad-image-url').value = data.data.url;
            document.getElementById('ad-image-preview').src = data.data.url;
            document.getElementById('ad-image-preview').classList.remove('hidden');
            showToast('上传成功');
        } else {
            showToast(data.msg, true);
        }
    }

    function previewAdById(adId) {
        // 从全局ads数组中查找广告
        const ad = ads.find(a => a.id == adId);
        if (!ad) {
            showToast('广告不存在', true);
            return;
        }
        
        const modal = document.getElementById('ad-preview-modal');
        const imgContainer = document.getElementById('preview-image-container');
        const img = document.getElementById('preview-image-src');
        const htmlContent = document.getElementById('preview-html-content');
        
        // 隐藏所有内容
        img.classList.add('hidden');
        htmlContent.classList.add('hidden');
        
        // 根据广告类型显示不同内容
        if (ad.type == 3 && ad.content) {
            // HTML广告 - 渲染HTML内容
            htmlContent.innerHTML = ad.content;
            htmlContent.classList.remove('hidden');
            imgContainer.style.backgroundColor = '#f9fafb';
        } else if (ad.image_url) {
            // 图片广告 - 显示图片
            img.src = ad.image_url;
            img.classList.remove('hidden');
            imgContainer.style.backgroundColor = 'white';
        } else {
            // 文字广告或无预览内容
            htmlContent.innerHTML = `<div class="text-center py-10 text-gray-500"><i class="fa fa-info-circle text-4xl mb-3"></i><p>暂无预览内容</p></div>`;
            htmlContent.classList.remove('hidden');
            imgContainer.style.backgroundColor = '#f9fafb';
        }
        
        modal.classList.remove('hidden');
    }

    function closeAdPreview() {
        document.getElementById('ad-preview-modal').classList.add('hidden');
    }

    // 广告代码模态框
    let currentAdCodePosition = null;

    function showAdCodeModal(positionCode, positionName) {
        currentAdCodePosition = positionCode;
        document.getElementById('adcode-position-name').textContent = positionName;
        document.getElementById('adcode-position-code').textContent = positionCode;

        // PHP调用代码
        const phpCode = `&lt;?php
require_once '/path/to/ad/api.php';

$html = getAdWidget('${positionCode}', [
    'width' => '100%',
    'height' => 'auto'
]);

if ($html) {
    echo $html;
}
?>`;
        document.getElementById('adcode-php').querySelector('code').innerHTML = phpCode;

        // HTML+JS调用代码
        const htmlCode = `&lt;!-- 广告容器 --&gt;
&lt;div id="ad-${positionCode}"&gt;&lt;/div&gt;

&lt;script&gt;
(async () =&gt; {
    const res = await fetch('/ad/api.php?action=get_ad&position=${positionCode}');
    const data = await res.json();
    if (data.code === 200 && data.data) {
        const ad = data.data;
        const container = document.getElementById('ad-${positionCode}');
        
        // 记录展示
        fetch('/ad/api.php?action=record_view&id=' + ad.id + '&amp;position=${positionCode}');
        
        if (ad.type === 1) {
            container.innerHTML = \`&lt;div style="position:relative"&gt;&lt;a href="\${ad.link_url}" target="_blank"&gt;&lt;img src="\${ad.image_url}" alt="\${ad.title}" style="width:100%"&gt;&lt;/a&gt;&lt;/div&gt;\`;
        } else if (ad.type === 2) {
            container.innerHTML = \`&lt;div&gt;&lt;a href="\${ad.link_url}" target="_blank"&gt;\${ad.content}&lt;/a&gt;&lt;/div&gt;\`;
        } else {
            container.innerHTML = ad.content;
        }
    }
})();
&lt;/script&gt;`;
        document.getElementById('adcode-html').querySelector('code').innerHTML = htmlCode;

        document.getElementById('adcode-modal').classList.remove('hidden');
    }

    function closeAdCodeModal() {
        document.getElementById('adcode-modal').classList.add('hidden');
        currentAdCodePosition = null;
    }

    async function copyAdCode(type) {
        const codeElement = document.getElementById('adcode-' + type).querySelector('code');
        const code = codeElement.textContent;
        
        try {
            await navigator.clipboard.writeText(code);
            showToast('代码已复制到剪贴板');
        } catch (err) {
            // 降级方案
            const textarea = document.createElement('textarea');
            textarea.value = code;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showToast('代码已复制到剪贴板');
        }
    }

    // 按ESC键关闭图片预览
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeImagePreview();
            closeAdCodeModal();
        }
    });

    document.getElementById('ad-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = {
            id: document.getElementById('ad-id').value,
            title: document.getElementById('ad-title').value,
            type: document.getElementById('ad-type').value,
            image_url: document.getElementById('ad-image-url').value,
            link_url: document.getElementById('ad-link-url').value,
            content: document.getElementById('ad-content').value,
            sort: document.getElementById('ad-sort').value,
            status: document.getElementById('ad-status').value,
            start_time: document.getElementById('ad-start-time').value,
            end_time: document.getElementById('ad-end-time').value
        };
        const res = await fetch(apiUrl + '?action=save_ad', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(formData)
        });
        const data = await res.json();
        if (data.code === 200) {
            showToast('保存成功');
            closeAdModal();
            loadAds();
            loadStats();
        } else {
            showToast(data.msg, true);
        }
    });

    function editAd(id) { openAdModal(id); }

    async function toggleAdStatus(id, enabled) {
        const newStatus = enabled ? 1 : 0;
        const res = await fetch(apiUrl + '?action=toggle_ad_status', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: id, status: newStatus})
        });
        const data = await res.json();
        if (data.code === 200) {
            showToast(enabled ? '已启用' : '已禁用');
            loadStats();
        } else {
            showToast(data.msg || '操作失败', true);
            loadAds();
        }
    }

    async function deleteAd(id) {
        if (!confirm('确定删除该广告吗？')) return;
        const res = await fetch(apiUrl + '?action=delete_ad&id=' + id);
        const data = await res.json();
        if (data.code === 200) {
            showToast('删除成功');
            loadAds();
            loadStats();
        } else {
            showToast(data.msg, true);
        }
    }

    async function loadPositions() {
        const res = await fetch(apiUrl + '?action=get_positions');
        const data = await res.json();
        if (data.code === 200) {
            positions = data.data;
            renderPositions();
        }
    }

    function filterPositions() {
        const keyword = document.getElementById('search-pos-keyword').value.toLowerCase();
        const width = document.getElementById('search-pos-width').value;
        const height = document.getElementById('search-pos-height').value;
        const status = document.getElementById('search-pos-status').value;
        const order = document.getElementById('search-pos-order').value;

        let filtered = positions.filter(pos => {
            if (keyword && !pos.name.toLowerCase().includes(keyword) && !pos.code.toLowerCase().includes(keyword) && !(pos.description || '').toLowerCase().includes(keyword)) return false;
            if (width && String(pos.width) !== width) return false;
            if (height && String(pos.height) !== height) return false;
            if (status && String(pos.status) !== status) return false;
            return true;
        });

        const [field, dir] = order.split('-');
        filtered.sort((a, b) => {
            if (field === 'priority') return dir === 'desc' ? (b.priority || 0) - (a.priority || 0) : (a.priority || 0) - (b.priority || 0);
            if (field === 'id') return dir === 'desc' ? b.id - a.id : a.id - b.id;
            if (field === 'width') return dir === 'desc' ? (b.width || 0) - (a.width || 0) : (a.width || 0) - (b.width || 0);
            return 0;
        });

        const grid = document.getElementById('positions-grid');
        grid.innerHTML = filtered.map(pos => {
            const statusClass = pos.status == 1 ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400';
            const statusText = pos.status == 1 ? '✅ 启用' : '❌ 禁用';

            return `<div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-md card-hover transition-all border border-gray-100 dark:border-gray-700">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-gray-800 dark:text-white">${pos.name}</h3>
                        <code class="text-xs text-indigo-600 dark:text-indigo-400 mt-1 block">${pos.code}</code>
                    </div>
                    <span class="px-2 py-1 rounded-full text-xs font-medium ${statusClass}">${statusText}</span>
                </div>
                ${pos.image_url ? `<div class="mb-3 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 cursor-pointer group relative" onclick="previewImage('${pos.image_url}')">
                    <img src="${pos.image_url}" alt="${pos.name}" class="w-full h-32 object-cover transition-transform duration-300 group-hover:scale-105">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all duration-300 flex items-center justify-center">
                        <span class="text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-sm font-medium">
                            <i class="fa fa-search-plus mr-1"></i> 点击预览
                        </span>
                    </div>
                </div>` : ''}
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">${pos.description || '无描述'}</p>
                <div class="flex justify-between items-center text-xs text-gray-400 dark:text-gray-500">
                    <span><i class="fa fa-expand mr-1"></i> ${pos.width || 0} × ${pos.height || 0}</span>
                    <span><i class="fa fa-bullhorn mr-1"></i> ${pos.ad_count || 0} 个广告</span>
                    <span><i class="fa fa-star mr-1"></i> 优先级 ${pos.priority}</span>
                </div>
                <div class="flex justify-end gap-2 mt-4 pt-3 border-t dark:border-gray-700">
                    <button onclick="showAdCodeModal('${pos.code}', '${pos.name}')" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm font-medium">代码</button>
                    <button onclick="editPosition(${pos.id})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-sm font-medium">编辑</button>
                    <button onclick="deletePosition(${pos.id})" class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-sm font-medium">删除</button>
                </div>
            </div>`;
        }).join('');
    }

    function resetPositionSearch() {
        document.getElementById('search-pos-keyword').value = '';
        document.getElementById('search-pos-width').value = '';
        document.getElementById('search-pos-height').value = '';
        document.getElementById('search-pos-status').value = '';
        document.getElementById('search-pos-order').value = 'priority-desc';
        filterPositions();
    }

    function renderPositions() {
        const grid = document.getElementById('positions-grid');
        grid.innerHTML = positions.map(pos => {
            const statusClass = pos.status == 1 ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 cursor-pointer' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 cursor-pointer';
            const statusText = pos.status == 1 ? '✅ 启用' : '❌ 禁用';

            return `<div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-md card-hover transition-all border border-gray-100 dark:border-gray-700">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-gray-800 dark:text-white">${pos.name}</h3>
                        <code class="text-xs text-indigo-600 dark:text-indigo-400 mt-1 block">${pos.code}</code>
                    </div>
                    <span onclick="togglePositionStatus(${pos.id})" class="px-2 py-1 rounded-full text-xs font-medium ${statusClass}" title="点击切换状态">${statusText}</span>
                </div>
                ${pos.image_url ? `<div class="mb-3 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 cursor-pointer group relative" onclick="previewImage('${pos.image_url}')">
                    <img src="${pos.image_url}" alt="${pos.name}" class="w-full h-32 object-cover transition-transform duration-300 group-hover:scale-105">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all duration-300 flex items-center justify-center">
                        <span class="text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-sm font-medium">
                            <i class="fa fa-search-plus mr-1"></i> 点击预览
                        </span>
                    </div>
                </div>` : ''}
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">${pos.description || '无描述'}</p>
                <div class="flex justify-between items-center text-xs text-gray-400 dark:text-gray-500">
                    <span><i class="fa fa-expand mr-1"></i> ${pos.width || 0} × ${pos.height || 0}</span>
                    <span><i class="fa fa-bullhorn mr-1"></i> ${pos.ad_count || 0} 个广告</span>
                    <span><i class="fa fa-star mr-1"></i> 优先级 ${pos.priority}</span>
                </div>
                <div class="flex justify-end gap-2 mt-4 pt-3 border-t dark:border-gray-700">
                    <button onclick="editPosition(${pos.id})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-sm font-medium">编辑</button>
                    <button onclick="deletePosition(${pos.id})" class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-sm font-medium">删除</button>
                </div>
            </div>`;
        }).join('');
    }

    function openPositionModal(id = 0) {
        document.getElementById('position-form').reset();
        document.getElementById('position-id').value = 0;
        document.getElementById('position-image-url').value = '';
        document.getElementById('position-modal-title').textContent = '添加位置';
        
        // 重置图片预览
        const preview = document.getElementById('position-image-preview');
        preview.innerHTML = '<span class="text-gray-400 dark:text-gray-500 text-xs">暂无图片</span>';
        
        if (id > 0) {
            const pos = positions.find(p => p.id === id);
            if (pos) {
                document.getElementById('position-modal-title').textContent = '编辑位置';
                document.getElementById('position-id').value = pos.id;
                document.getElementById('position-code').value = pos.code;
                document.getElementById('position-name').value = pos.name;
                document.getElementById('position-description').value = pos.description || '';
                document.getElementById('position-width').value = pos.width || 0;
                document.getElementById('position-height').value = pos.height || 0;
                document.getElementById('position-priority').value = pos.priority || 0;
                document.getElementById('position-status').value = pos.status;
                document.getElementById('position-image-url').value = pos.image_url || '';
                
                // 显示图片预览
                if (pos.image_url) {
                    preview.innerHTML = `<img src="${pos.image_url}" class="w-full h-full object-cover">`;
                }
            }
        }
        document.getElementById('position-modal').classList.remove('hidden');
    }

    // 图片预览
    document.getElementById('position-image-file').addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;
        
        // 先显示本地预览
        const preview = document.getElementById('position-image-preview');
        const reader = new FileReader();
        reader.onload = (event) => {
            preview.innerHTML = `<img src="${event.target.result}" class="w-full h-full object-cover">`;
        };
        reader.readAsDataURL(file);
        
        // 上传图片到服务器
        const formData = new FormData();
        formData.append('image', file);
        
        try {
            const res = await fetch(apiUrl + '?action=upload_ad_image', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.code === 200) {
                document.getElementById('position-image-url').value = data.data.url;
                showToast('图片上传成功');
            } else {
                showToast(data.msg, true);
            }
        } catch (err) {
            showToast('上传失败', true);
        }
    });

    function closePositionModal() {
        document.getElementById('position-modal').classList.add('hidden');
    }

    document.getElementById('position-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = {
            id: document.getElementById('position-id').value,
            code: document.getElementById('position-code').value,
            name: document.getElementById('position-name').value,
            description: document.getElementById('position-description').value,
            width: document.getElementById('position-width').value,
            height: document.getElementById('position-height').value,
            priority: document.getElementById('position-priority').value,
            status: document.getElementById('position-status').value,
            image_url: document.getElementById('position-image-url').value
        };
        const res = await fetch(apiUrl + '?action=save_position', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(formData)
        });
        const data = await res.json();
        if (data.code === 200) {
            showToast('保存成功');
            closePositionModal();
            loadPositions();
            loadStats();
        } else {
            showToast(data.msg, true);
        }
    });

    function editPosition(id) { openPositionModal(id); }

    async function deletePosition(id) {
        if (!confirm('确定删除该位置吗？')) return;
        const res = await fetch(apiUrl + '?action=delete_position&id=' + id);
        const data = await res.json();
        if (data.code === 200) {
            showToast('删除成功');
            loadPositions();
            loadStats();
        } else {
            showToast(data.msg, true);
        }
    }

    async function togglePositionStatus(id) {
        const res = await fetch(apiUrl + '?action=toggle_position_status&id=' + id);
        const data = await res.json();
        if (data.code === 200) {
            showToast('状态切换成功');
            loadPositions();
        } else {
            showToast(data.msg, true);
        }
    }

    let relations = [];

    async function loadRelations() {
        const res = await fetch(apiUrl + '?action=get_relations');
        const data = await res.json();
        if (data.code === 200) {
            relations = data.data;
            renderRelations(relations);
        }
    }

    function renderRelations(relations) {
        const tbody = document.getElementById('relations-table-body');
        tbody.innerHTML = relations.map(rel => {
            const statusClass = rel.status == 1 ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400';
            const statusText = rel.status == 1 ? '✅ 启用' : '❌ 禁用';
            
            return `<tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <td class="px-4 py-3 font-mono text-sm text-gray-500 dark:text-gray-400">#${rel.id}</td>
                <td class="px-4 py-3">
                    <div class="font-medium text-gray-800 dark:text-white">${rel.position_name}</div>
                    <code class="text-xs text-indigo-600 dark:text-indigo-400">${rel.position_code}</code>
                </td>
                <td class="px-4 py-3">
                    <div class="font-medium text-gray-800 dark:text-white">${rel.ad_title || '<span class="text-red-500 dark:text-red-400">已删除</span>'}</div>
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-1">
                        <div class="w-16 bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                            <div class="bg-indigo-500 h-2 rounded-full" style="width: ${Math.min(100, rel.weight * 10)}%"></div>
                        </div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">${rel.weight}</span>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <button onclick="toggleRelationStatus(${rel.id})" class="px-2.5 py-1 rounded-full text-xs font-medium ${statusClass}">${statusText}</button>
                </td>
                <td class="px-4 py-3">
                    <button onclick="showAdCodeModal('${rel.position_code}', '${rel.position_name}')" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium text-sm mr-3">代码</button>
                    <button onclick="editRelation(${rel.id})" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium text-sm mr-3">编辑</button>
                    <button onclick="deleteRelation(${rel.id})" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 font-medium text-sm">删除</button>
                </td>
            </tr>`;
        }).join('');
    }

    function openRelationModal(id = 0) {
        document.getElementById('relation-form').reset();
        document.getElementById('relation-id').value = 0;
        document.getElementById('relation-modal-title').textContent = '添加关联';
        document.getElementById('position-detail').classList.add('hidden');
        document.getElementById('ad-detail').classList.add('hidden');
        
        document.getElementById('relation-position-id').innerHTML = '<option value="">请选择位置</option>' + 
            positions.map(p => `<option value="${p.id}">${p.name} (${p.code})</option>`).join('');
        document.getElementById('relation-ad-id').innerHTML = '<option value="">请选择广告</option>' + 
            ads.map(a => `<option value="${a.id}">${a.title}</option>`).join('');
        
        if (id > 0) {
            const rel = relations.find(r => r.id === id);
            if (rel) {
                document.getElementById('relation-modal-title').textContent = '编辑关联';
                document.getElementById('relation-id').value = rel.id;
                document.getElementById('relation-position-id').value = rel.position_id;
                document.getElementById('relation-ad-id').value = rel.ad_id;
                document.getElementById('relation-weight').value = rel.weight;
                document.getElementById('relation-status').value = rel.status;
                showPositionDetail();
                showAdDetail();
            }
        }
        
        document.getElementById('relation-modal').classList.remove('hidden');
    }

    function editRelation(id) { openRelationModal(id); }

    function showPositionDetail() {
        const posId = document.getElementById('relation-position-id').value;
        const detailDiv = document.getElementById('position-detail');
        
        if (!posId) {
            detailDiv.classList.add('hidden');
            return;
        }
        
        const pos = positions.find(p => p.id == posId);
        if (pos) {
            document.getElementById('detail-position-code').textContent = pos.code;
            document.getElementById('detail-position-size').textContent = `${pos.width || 0} × ${pos.height || 0}`;
            document.getElementById('detail-position-desc').textContent = pos.description || '无描述';
            detailDiv.classList.remove('hidden');
        }
    }

    function showAdDetail() {
        const adId = document.getElementById('relation-ad-id').value;
        const detailDiv = document.getElementById('ad-detail');
        const typeMap = {1: '🖼️ 图片广告', 2: '📝 文字广告', 3: '📄 HTML广告'};
        const statusMap = {0: '❌ 已禁用', 1: '✅ 已启用', 2: '⏳ 待审核'};
        
        if (!adId) {
            detailDiv.classList.add('hidden');
            return;
        }
        
        const ad = ads.find(a => a.id == adId);
        if (ad) {
            document.getElementById('detail-ad-type').textContent = typeMap[ad.type] || '-';
            document.getElementById('detail-ad-link').textContent = ad.link_url || '无链接';
            document.getElementById('detail-ad-status').textContent = statusMap[ad.status] || '-';
            
            const previewDiv = document.getElementById('detail-ad-preview');
            if (ad.image_url) {
                previewDiv.innerHTML = `<img src="${ad.image_url}" class="w-full h-full object-cover">`;
            } else {
                previewDiv.innerHTML = '<span class="text-gray-400 text-xs">无预览</span>';
            }
            
            detailDiv.classList.remove('hidden');
        }
    }

    function closeRelationModal() {
        document.getElementById('relation-modal').classList.add('hidden');
    }

    document.getElementById('relation-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = {
            id: document.getElementById('relation-id').value,
            position_id: document.getElementById('relation-position-id').value,
            ad_id: document.getElementById('relation-ad-id').value,
            weight: document.getElementById('relation-weight').value,
            status: document.getElementById('relation-status').value
        };
        const res = await fetch(apiUrl + '?action=save_relation', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(formData)
        });
        const data = await res.json();
        if (data.code === 200) {
            showToast('保存成功');
            closeRelationModal();
            loadRelations();
            loadStats();
        } else {
            showToast(data.msg, true);
        }
    });

    async function deleteRelation(id) {
        if (!confirm('确定删除该关联吗？')) return;
        const res = await fetch(apiUrl + '?action=delete_relation&id=' + id);
        const data = await res.json();
        if (data.code === 200) {
            showToast('删除成功');
            loadRelations();
            loadStats();
        } else {
            showToast(data.msg, true);
        }
    }

    async function toggleRelationStatus(id) {
        const res = await fetch(apiUrl + '?action=toggle_relation_status&id=' + id);
        const data = await res.json();
        if (data.code === 200) {
            showToast('切换成功');
            loadRelations();
            loadStats();
        } else {
            showToast(data.msg, true);
        }
    }

    function openChangePasswordModal() {
        document.getElementById('change-password-form').reset();
        document.getElementById('change-password-modal').classList.remove('hidden');
    }

    function closeChangePasswordModal() {
        document.getElementById('change-password-modal').classList.add('hidden');
    }

    document.getElementById('change-password-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const currentPassword = document.getElementById('current-password').value;
        const newPassword = document.getElementById('new-password').value;
        const confirmPassword = document.getElementById('confirm-new-password').value;

        if (newPassword !== confirmPassword) {
            showToast('两次输入的新密码不一致', true);
            return;
        }

        if (newPassword.length < 6) {
            showToast('新密码至少6位', true);
            return;
        }

        const res = await fetch(apiUrl + '?action=change_password', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                current_password: currentPassword,
                new_password: newPassword
            })
        });

        const data = await res.json();
        if (data.code === 200) {
            showToast('密码修改成功');
            closeChangePasswordModal();
        } else {
            showToast(data.msg, true);
        }
    });

    // 初始化
    loadStats();
    loadAds();
    loadPositions();
    loadRelations();
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

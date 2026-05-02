<?php
/**
 * 广告位置招商页面
 * 展示所有可用的广告位置，供广告主参考
 */
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>广告位招商 - <?php echo defined('AD_SITE_NAME') ? AD_SITE_NAME : 'TrollApps'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1',
                        secondary: '#8b5cf6'
                    }
                }
            }
        }
    </script>
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .pulse-animation { animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <!-- 自定义顶部导航 -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur shadow-sm transition-all duration-300 dark:bg-gray-800/95 dark:shadow-gray-900/20">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fa fa-bullhorn text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-gray-800 dark:text-white">广告位招商</h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400">TrollApps Ad Platform</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <button id="theme-toggle" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        <i class="fa fa-moon-o text-gray-600 dark:text-gray-300 text-lg dark:hidden"></i>
                        <i class="fa fa-sun-o text-yellow-400 hidden dark:block"></i>
                    </button>
                    <a href="/" class="text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">
                        <i class="fa fa-home text-lg"></i>
                    </a>
                    <a href="/ad/admin.php" class="text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">
                        <i class="fa fa-cog text-lg" title="管理后台"></i>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <script>
        (function() {
            try {
                var theme = localStorage.getItem('theme');
                if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
        document.getElementById('theme-toggle').addEventListener('click', function() {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
        });
    </script>

    <!-- 横幅 -->
    <div class="gradient-bg py-12 px-4">
        <div class="container mx-auto text-center text-white">
            <h1 class="text-3xl md:text-4xl font-bold mb-4">
                <i class="fa fa-bullhorn mr-2"></i>
                广告位火热招商中
            </h1>
            <p class="text-lg opacity-90 mb-6 max-w-2xl mx-auto">
                黄金位置 · 海量曝光 · 精准投放 · 超值回报
            </p>
            <div class="flex flex-wrap justify-center gap-4 text-sm">
                <a href="tel:18988479960" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-full transition-colors flex items-center gap-2">
                    <i class="fa fa-phone"></i> 18988479960
                </a>
                <a href="weixin://dl/profile/ShiSanGe2026" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-full transition-colors flex items-center gap-2">
                    <i class="fa fa-weixin"></i> ShiSanGe2026
                </a>
                <a href="https://qm.qq.com/q/350722326" target="_blank" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-full transition-colors flex items-center gap-2">
                    <i class="fa fa-qq"></i> 350722326
                </a>
            </div>
        </div>
    </div>

    <!-- 广告位置列表 -->
    <main class="container mx-auto px-4 py-10">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">可选广告位置</h2>
            <p class="text-gray-500 dark:text-gray-400">点击图片可查看大图预览</p>
        </div>

        <!-- 搜索表单 -->
        <div class="mb-8 p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">关键词</label>
                    <input type="text" id="search-keyword" placeholder="名称/标识符/描述" class="w-full h-10 px-4 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div class="w-28">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">宽度</label>
                    <input type="number" id="search-width" placeholder="宽度" class="w-full h-10 px-4 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white">
                </div>
                <div class="w-28">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">高度</label>
                    <input type="number" id="search-height" placeholder="高度" class="w-full h-10 px-4 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white">
                </div>
                <div class="w-32">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">排序</label>
                    <select id="search-order" class="w-full h-10 px-4 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white">
                        <option value="priority-desc">优先级最高</option>
                        <option value="priority-asc">优先级最低</option>
                        <option value="width-desc">宽度最大</option>
                        <option value="width-asc">宽度最小</option>
                    </select>
                </div>
                <button onclick="resetSearch()" class="h-10 px-5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors text-sm font-medium">
                    <i class="fa fa-refresh mr-1"></i> 重置
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="positions-grid">
            <div class="text-center text-gray-500 dark:text-gray-400 col-span-full py-20">
                <i class="fa fa-spinner fa-spin text-4xl mb-4"></i>
                <p>加载中...</p>
            </div>
        </div>
    </main>

    <!-- 联系咨询 -->
    <section class="bg-white dark:bg-gray-800 py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-2xl mx-auto text-center">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">立即联系咨询</h2>
                <p class="text-gray-500 dark:text-gray-400 mb-8">准备好投放您的广告了吗？联系我们获取详细报价和投放方案</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <a href="tel:18988479960" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-4 rounded-xl transition-all hover:-translate-y-1 flex flex-col items-center gap-2">
                        <i class="fa fa-phone text-2xl"></i>
                        <span class="font-medium">电话咨询</span>
                        <span class="text-sm opacity-80">18988479960</span>
                    </a>
                    <a href="weixin://dl/profile/ShiSanGe2026" class="bg-green-600 hover:bg-green-700 text-white px-6 py-4 rounded-xl transition-all hover:-translate-y-1 flex flex-col items-center gap-2">
                        <i class="fa fa-weixin text-2xl"></i>
                        <span class="font-medium">微信咨询</span>
                        <span class="text-sm opacity-80">ShiSanGe2026</span>
                    </a>
                    <a href="https://qm.qq.com/q/350722326" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-4 rounded-xl transition-all hover:-translate-y-1 flex flex-col items-center gap-2">
                        <i class="fa fa-qq text-2xl"></i>
                        <span class="font-medium">QQ咨询</span>
                        <span class="text-sm opacity-80">350722326</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 自定义页脚 -->
    <footer class="bg-gray-800 text-gray-300 py-8">
        <div class="container mx-auto px-4 text-center">
            <div class="flex items-center justify-center gap-2 mb-4">
                <i class="fa fa-bullhorn text-indigo-400"></i>
                <span class="font-bold text-lg">TrollApps</span>
            </div>
            <p class="text-sm text-gray-400 mb-4">
                专注于iOS应用软件分享与交流的社区平台
            </p>
            <p class="text-xs text-gray-500">
                <?php if (defined('AD_ICP')): ?>
                    <a href="https://beian.miit.gov.cn/" target="_blank" class="hover:text-gray-300"><?php echo AD_ICP; ?></a>
                <?php endif; ?>
                <?php if (defined('AD_COPYRIGHT')): ?>
                    | <?php echo AD_COPYRIGHT; ?>
                <?php else: ?>
                    | © 2026 TrollApps. All rights reserved.
                <?php endif; ?>
            </p>
        </div>
    </footer>

    <!-- 图片预览模态框 -->
    <div id="preview-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/70" onclick="closePreview()"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 max-w-[90vw] max-h-[90vh]">
            <button onclick="closePreview()" class="absolute -top-12 right-0 w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-colors text-white text-xl">
                <i class="fa fa-times"></i>
            </button>
            <img id="preview-image" src="" alt="预览" class="max-w-[90vw] max-h-[85vh] rounded-xl shadow-2xl">
        </div>
    </div>

    <script>
        const apiUrl = '/ad/api.php';
        let allPositions = [];

        async function loadPositions() {
            try {
                const res = await fetch(apiUrl + '?action=get_positions');
                const data = await res.json();
                
                if (data.code === 200) {
                    allPositions = data.data;
                    filterAndRender();
                } else {
                    document.getElementById('positions-grid').innerHTML = `
                        <div class="text-center text-gray-500 dark:text-gray-400 col-span-full py-20">
                            <i class="fa fa-exclamation-circle text-4xl mb-4"></i>
                            <p>加载失败，请刷新页面重试</p>
                        </div>
                    `;
                }
            } catch (err) {
                console.error('加载失败:', err);
                document.getElementById('positions-grid').innerHTML = `
                    <div class="text-center text-gray-500 dark:text-gray-400 col-span-full py-20">
                        <i class="fa fa-exclamation-circle text-4xl mb-4"></i>
                        <p>网络错误，请检查网络连接</p>
                    </div>
                `;
            }
        }

        function filterAndRender() {
            const keyword = document.getElementById('search-keyword').value.toLowerCase();
            const width = document.getElementById('search-width').value;
            const height = document.getElementById('search-height').value;
            const order = document.getElementById('search-order').value;

            let filtered = allPositions.filter(pos => {
                if (keyword && !pos.name.toLowerCase().includes(keyword) && !pos.code.toLowerCase().includes(keyword) && !(pos.description || '').toLowerCase().includes(keyword)) return false;
                if (width && String(pos.width) !== width) return false;
                if (height && String(pos.height) !== height) return false;
                return true;
            });

            const [field, dir] = order.split('-');
            filtered.sort((a, b) => {
                if (field === 'priority') return dir === 'desc' ? (b.priority || 0) - (a.priority || 0) : (a.priority || 0) - (b.priority || 0);
                if (field === 'width') return dir === 'desc' ? (b.width || 0) - (a.width || 0) : (a.width || 0) - (b.width || 0);
                return 0;
            });

            renderPositions(filtered);
        }

        function renderPositions(positions) {
            const grid = document.getElementById('positions-grid');
            
            if (!positions || positions.length === 0) {
                grid.innerHTML = `
                    <div class="text-center text-gray-500 dark:text-gray-400 col-span-full py-20">
                        <i class="fa fa-inbox text-4xl mb-4"></i>
                        <p>暂无符合条件的广告位置</p>
                    </div>
                `;
                return;
            }

            grid.innerHTML = positions.map(pos => {
                const imageHtml = pos.image_url 
                    ? `<div class="rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 mb-4 cursor-pointer group relative" onclick="previewImage('${pos.image_url}')">
                            <img src="${pos.image_url}" alt="${pos.name}" class="w-full h-40 object-cover transition-transform duration-300 group-hover:scale-105">
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all duration-300 flex items-center justify-center">
                                <span class="text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-sm font-medium">
                                    <i class="fa fa-search-plus mr-1"></i> 点击预览
                                </span>
                            </div>
                       </div>`
                    : `<div class="bg-gray-100 dark:bg-gray-700 rounded-lg h-40 flex items-center justify-center mb-4">
                            <i class="fa fa-image text-gray-400 dark:text-gray-500 text-4xl"></i>
                       </div>`;

                return `
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-md card-hover border border-gray-100 dark:border-gray-700 transition-all duration-300">
                        <div class="mb-3">
                            <div>
                                <h3 class="font-bold text-gray-800 dark:text-white text-lg">${pos.name}</h3>
                                <code class="text-xs text-indigo-600 dark:text-indigo-400 mt-1 block">${pos.code}</code>
                            </div>
                        </div>
                        
                        ${imageHtml}
                        
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 min-h-[40px]">${pos.description || '暂无描述'}</p>
                        
                        <div class="flex justify-between items-center text-sm text-gray-400 dark:text-gray-500 border-t dark:border-gray-700 pt-4">
                            <span><i class="fa fa-expand mr-1"></i> ${pos.width || 0} × ${pos.height || 0}</span>
                            <span><i class="fa fa-bullhorn mr-1"></i> ${pos.ad_count || 0} 个广告</span>
                            <span><i class="fa fa-star mr-1"></i> 优先级 ${pos.priority || 0}</span>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function previewImage(url) {
            document.getElementById('preview-image').src = url;
            document.getElementById('preview-modal').classList.remove('hidden');
        }

        function closePreview() {
            document.getElementById('preview-modal').classList.add('hidden');
        }

        function resetSearch() {
            document.getElementById('search-keyword').value = '';
            document.getElementById('search-width').value = '';
            document.getElementById('search-height').value = '';
            document.getElementById('search-order').value = 'priority-desc';
            filterAndRender();
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closePreview();
        });

        document.getElementById('search-keyword').addEventListener('input', filterAndRender);
        document.getElementById('search-width').addEventListener('input', filterAndRender);
        document.getElementById('search-height').addEventListener('input', filterAndRender);
        document.getElementById('search-order').addEventListener('change', filterAndRender);

        loadPositions();
    </script>
</body>
</html>
<?php
/**
 * 广告位置招商页面
 * 展示所有可用的广告位置，供广告主参考
 * 针对现成项目修改了页头页尾
 */
require_once __DIR__ . '/config.php';

$pageTitle = '广告位招商 - ' . (defined('AD_SITE_NAME') ? AD_SITE_NAME : 'TrollApps');
$pageDescription = '浏览所有可用的广告投放位置，联系我们将获取详细报价和投放方案';
$currentPage = '';

include __DIR__ . '/../header.php';
?>

    <!-- 横幅 -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 py-12 px-4">
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
                <div class="w-28">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">状态</label>
                    <select id="search-status" class="w-full h-10 px-4 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white">
                        <option value="">全部</option>
                        <option value="1">已启用</option>
                        <option value="0">已禁用</option>
                    </select>
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
            const status = document.getElementById('search-status').value;
            const order = document.getElementById('search-order').value;

            let filtered = allPositions.filter(pos => {
                if (keyword && !pos.name.toLowerCase().includes(keyword) && !pos.code.toLowerCase().includes(keyword) && !(pos.description || '').toLowerCase().includes(keyword)) return false;
                if (width && String(pos.width) !== width) return false;
                if (height && String(pos.height) !== height) return false;
                if (status && String(pos.status) !== status) return false;
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
                const statusClass = pos.status == 1 
                    ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' 
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400';
                const statusText = pos.status == 1 ? '✅ 启用' : '❌ 禁用';

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
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-bold text-gray-800 dark:text-white text-lg">${pos.name}</h3>
                                <code class="text-xs text-indigo-600 dark:text-indigo-400 mt-1 block">${pos.code}</code>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium ${statusClass}">${statusText}</span>
                        </div>
                        
                        ${imageHtml}
                        
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 min-h-[40px]">${pos.description || '暂无描述'}</p>
                        
                        <div class="flex justify-between items-center text-sm text-gray-400 dark:text-gray-500 border-t dark:border-gray-700 pt-4">
                            <span><i class="fa fa-expand mr-1"></i> ${pos.width || 0} × ${pos.height || 0}</span>
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
            document.getElementById('search-status').value = '';
            document.getElementById('search-order').value = 'priority-desc';
            filterAndRender();
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closePreview();
        });

        document.getElementById('search-keyword').addEventListener('input', filterAndRender);
        document.getElementById('search-width').addEventListener('input', filterAndRender);
        document.getElementById('search-height').addEventListener('input', filterAndRender);
        document.getElementById('search-status').addEventListener('change', filterAndRender);
        document.getElementById('search-order').addEventListener('change', filterAndRender);

        loadPositions();
        //隐藏掉导航的广告
        adLoader.hideAd('ad-banner-header');
    </script>

<?php include __DIR__ . '/../footer.php'; ?>
/**
 * 广告加载器
 * 支持纯HTML/JavaScript页面调用广告系统
 */
class AdLoader {
    /**
     * 构造函数
     * @param {string} baseUrl - 广告系统基础URL，默认 '/ad'
     */
    constructor(baseUrl = '/ad') {
        this.baseUrl = baseUrl;
    }

    /**
     * 加载单个广告到指定容器
     * @param {string} containerId - 容器元素ID
     * @param {string} positionCode - 广告位置代码
     * @param {object} options - 配置选项
     * @param {string} options.width - 宽度，默认 '100%'
     * @param {string} options.height - 高度，默认 'auto'
     * @param {string} options.className - 额外CSS类名
     * @param {boolean} options.showClose - 是否显示关闭按钮，默认 false
     * @param {string} options.closeStyle - 关闭按钮样式
     */
    async load(containerId, positionCode, options = {}) {
        const container = document.getElementById(containerId);
        if (!container) {
            console.warn(`容器 #${containerId} 不存在`);
            return;
        }

        try {
            const res = await fetch(`${this.baseUrl}/api.php?action=get_ad&position=${positionCode}`);
            const responseText = await res.text();
            console.log('API 返回内容:', responseText);
            
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (e) {
                console.error('JSON 解析失败:', e);
                return;
            }
            
            if (data.code !== 200) {
                console.warn(`加载广告失败: ${data.msg || '未知错误'}`);
                return;
            }
            
            const ad = data.data;
            const width = options.width || '100%';
            const height = options.height || 'auto';
            const className = options.className || '';
            const showClose = options.showClose || false;
            
            // 记录展示
            this.recordView(ad.id, positionCode);
            
            // 渲染广告
            container.innerHTML = this.renderAd(ad, positionCode, width, height, className, showClose, containerId);
        } catch (e) {
            console.error('广告加载失败:', e);
        }
    }

    /**
     * 渲染广告HTML
     * @param {object} ad - 广告数据
     * @param {string} positionCode - 位置代码
     * @param {string} width - 宽度
     * @param {string} height - 高度
     * @param {string} className - CSS类名
     * @param {boolean} showClose - 是否显示关闭按钮
     * @param {string} containerId - 容器ID
     * @returns {string} HTML字符串
     */
    renderAd(ad, positionCode, width, height, className, showClose = false, containerId = '') {
        const recordClick = `adLoader.recordClick(${ad.id}, '${positionCode}')`;
        const closeBtn = showClose && containerId ? `
            <button onclick="adLoader.closeAd('${containerId}')" style="
                position:absolute;
                top:4px;
                left:4px;
                z-index:10;
                width:24px;
                height:24px;
                border:none;
                background:rgba(0,0,0,0.6);
                color:white;
                border-radius:50%;
                cursor:pointer;
                font-size:14px;
                line-height:1;
                display:flex;
                align-items:center;
                justify-content:center;
                transition:background 0.2s;
            " onmouseover="this.style.background='rgba(0,0,0,0.8)'" onmouseout="this.style.background='rgba(0,0,0,0.6)'">×</button>
        ` : '';
        
        if (ad.type === 1) {
            // 图片广告
            return `
                <div class="ad-widget ${className}" style="width:${width};height:${height};position:relative;overflow:hidden;">
                    ${closeBtn}
                    <a href="${ad.link_url || '#'}" target="_blank" onclick="${recordClick}">
                        <img src="${ad.image_url}" alt="${ad.title || ''}" style="width:100%;height:100%;object-fit:cover;">
                    </a>
                </div>
            `;
        }
        
        if (ad.type === 2) {
            // 文字广告
            return `
                <div class="ad-widget ${className}" style="width:${width};padding:10px;background:#f5f5f5;border-radius:4px;position:relative;overflow:hidden;">
                    ${closeBtn}
                    <a href="${ad.link_url || '#'}" target="_blank" onclick="${recordClick}">${ad.content}</a>
                </div>
            `;
        }
        
        if (ad.type === 3) {
            // 自定义广告 - 过滤掉可能破坏页面的HTML结构
            const sanitizedContent = this.sanitizeHtml(ad.content || '');
            return `
                <div class="ad-widget ${className}" style="width:${width};height:${height};position:relative;">
                    ${closeBtn}
                    ${sanitizedContent}
                </div>
            `;
        }
        
        return '';
    }

    /**
     * 过滤HTML内容，移除可能破坏页面的标签
     * @param {string} html - 原始HTML内容
     * @returns {string} 过滤后的HTML内容
     */
    sanitizeHtml(html) {
        // 移除 DOCTYPE
        html = html.replace(/<!DOCTYPE[^>]*>/gi, '');
        // 移除 html, head, body 标签及其内容
        html = html.replace(/<html[^>]*>[\s\S]*<\/html>/gi, '');
        html = html.replace(/<head[^>]*>[\s\S]*<\/head>/gi, '');
        html = html.replace(/<body[^>]*>[\s\S]*<\/body>/gi, '');
        // 移除 meta, link, title 等 head 内的标签
        html = html.replace(/<meta[^>]*>/gi, '');
        html = html.replace(/<link[^>]*>/gi, '');
        html = html.replace(/<title[^>]*>[\s\S]*<\/title>/gi, '');
        // 移除注释
        html = html.replace(/<!--[\s\S]*?-->/g, '');
        return html;
    }

    /**
     * 关闭广告
     * @param {string} containerId - 容器ID
     */
    closeAd(containerId) {
        const container = document.getElementById(containerId);
        if (container) {
            container.style.display = 'none';
        }
    }
    /**
     * 关闭广告
     * @param {string} containerId - 容器ID
     */
    hideAd(containerId) {
        const container = document.getElementById(containerId);
        if (container) {
            container.style.display = 'none';
        }
    }

    /**
     * 记录广告展示
     * @param {number} adId - 广告ID
     * @param {string} positionCode - 位置代码
     */
    recordView(adId, positionCode) {
        fetch(`${this.baseUrl}/api.php?action=record_view&id=${adId}&position=${positionCode}`).catch(() => {});
    }

    /**
     * 记录广告点击
     * @param {number} adId - 广告ID
     * @param {string} positionCode - 位置代码
     */
    recordClick(adId, positionCode) {
        fetch(`${this.baseUrl}/api.php?action=record_click&id=${adId}&position=${positionCode}`).catch(() => {});
    }

    /**
     * 获取指定位置的多个广告
     * @param {string} positionCode - 位置代码
     * @param {number} limit - 获取数量，默认 1
     * @returns {Promise<Array>} 广告数组
     */
    async getAds(positionCode, limit = 1) {
        try {
            const res = await fetch(`${this.baseUrl}/api.php?action=get_ads_by_position&position=${positionCode}&limit=${limit}`);
            const data = await res.json();
            return data.code === 200 ? data.data : [];
        } catch (e) {
            console.error('获取广告列表失败:', e);
            return [];
        }
    }
}

// 创建全局实例
window.adLoader = new AdLoader('/ad');

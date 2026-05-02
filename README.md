# 广告管理系统

一个轻量级的 PHP 广告管理系统，支持广告位置管理、广告投放、数据统计等功能。

## 功能特性

- ✅ **广告位置管理** - 支持创建、编辑、删除广告位置
- ✅ **广告投放** - 支持图片广告、文字广告、HTML广告
- ✅ **位置关联** - 一个位置可绑定多个广告，支持优先级排序
- ✅ **数据统计** - 记录广告展示次数和点击次数
- ✅ **搜索功能** - 支持多条件搜索广告和位置
- ✅ **响应式设计** - 支持移动端和桌面端
- ✅ **暗黑模式** - 支持主题切换
- ✅ **独立部署** - 可作为独立系统部署，也可集成到现有项目

## 技术栈

- **后端**: PHP 7.4+
- **前端**: Tailwind CSS 3, Font Awesome 4.7
- **数据库**: MySQL 5.7+
- **API**: RESTful API

## 项目结构

```
ad/
├── admin.php          # 管理后台
├── index.php          # 广告位招商页面（公开）
├── api.php            # RESTful API接口（包含AdApi类和getAdWidget函数）
├── ad_loader.js       # 广告加载SDK
├── config_default.php # 默认配置
├── install.php        # 安装脚本
├── login.php          # 登录页面
├── adList.php         # 广告位置列表（对接主项目）
└── ad_system.sql      # 数据库初始化脚本
```

## 快速开始

### 1. 环境要求

- PHP 7.4 或更高版本
- MySQL 5.7 或更高版本
- Web 服务器（Apache/Nginx）

### 2. 安装步骤

1. **克隆项目**
   ```bash
   git clone <repository-url>
   cd ad
   ```

2. **创建数据库**
   ```sql
   CREATE DATABASE your_database_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **导入数据库**
   ```bash
   mysql -u username -p your_database_name < ad_system.sql
   ```

4. **访问安装页面**

   打开浏览器访问：`http://your-domain/ad/install.php`

   按照页面提示完成安装配置。

5. **登录管理后台**

   访问：`http://your-domain/ad/admin.php`
   
   默认管理员账号：`admin` / `admin123`

### 3. 使用广告加载SDK

在需要显示广告的页面引入 SDK：

```html
<script src="/ad/ad-loader.js"></script>
<script>
// 加载广告到指定容器
adLoader.load('ad-container', 'home_banner', {
    width: '100%',
    height: 'auto',
    showClose: true
});
</script>

<div id="ad-container"></div>
```

## API 接口

### 获取广告
```
GET /ad/api.php?action=get_ad&position=home_banner
```

### 获取广告列表
```
GET /ad/api.php?action=get_ads_by_position&position=home_banner&limit=5
```

### 获取所有位置
```
GET /ad/api.php?action=get_positions
```

### 记录展示
```
GET /ad/api.php?action=record_view&id=1&position=home_banner
```

### 记录点击
```
GET /ad/api.php?action=record_click&id=1&position=home_banner
```

## 广告类型

| 类型 | 值 | 说明 |
|------|-----|------|
| 图片广告 | 1 | 显示图片，点击跳转链接 |
| 文字广告 | 2 | 显示纯文字链接 |
| HTML广告 | 3 | 自定义HTML内容 |

## 配置常量

这些常量在安装后生成的 `config.php` 文件中定义（也可在 `config_default.php` 中修改默认值）：

```php
define('AD_ADMIN_KEY', 'your_admin_secret_key');  // 管理员密钥
define('AD_DOMAIN', 'https://your-domain.com');    // 域名
define('AD_SITE_NAME', '广告系统');               // 站点名称
define('AD_ICP', '京ICP备xxxxxxxx号');            // ICP备案号
define('AD_COPYRIGHT', '© 2024 Your Company');   // 版权信息
```

## 部署说明

### Apache 配置
```apache
<Directory "/path/to/ad">
    AllowOverride All
    Require all granted
</Directory>
```

### Nginx 配置
```nginx
location /ad {
    root /path/to/your/webroot;
    index index.php;
    try_files $uri $uri/ /ad/index.php?$args;
    
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 安全建议

1. ✅ 修改默认管理员密码
2. ✅ 使用 HTTPS 协议
3. ✅ 限制 API 访问频率
4. ✅ 定期备份数据库
5. ✅ 配置防火墙规则

## 许可证

MIT License

## 贡献

欢迎提交 Issue 和 Pull Request！

---

**注意**: 这是一个开源项目，请确保在使用前修改数据库配置和管理员密码。

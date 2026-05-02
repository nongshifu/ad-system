-- 广告系统数据库表
-- 2026-05-02

-- ----------------------------
-- 广告位置表
-- ----------------------------
DROP TABLE IF EXISTS `ad_positions`;
CREATE TABLE `ad_positions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL COMMENT '位置标识符（唯一）',
  `name` varchar(100) NOT NULL COMMENT '位置名称',
  `description` varchar(255) DEFAULT NULL COMMENT '位置描述',
  `width` int(11) DEFAULT NULL COMMENT '宽度(px)',
  `height` int(11) DEFAULT NULL COMMENT '高度(px)',
  `image_url` varchar(500) DEFAULT NULL COMMENT '位置预览图片地址',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0禁用 1启用',
  `priority` int(11) NOT NULL DEFAULT 0 COMMENT '优先级，数字越大越优先',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='广告位置表';

-- ----------------------------
-- 广告表
-- ----------------------------
DROP TABLE IF EXISTS `ads`;
CREATE TABLE `ads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL COMMENT '广告标题',
  `image_url` varchar(500) DEFAULT NULL COMMENT '图片地址',
  `link_url` varchar(500) DEFAULT NULL COMMENT '点击跳转链接',
  `content` text DEFAULT NULL COMMENT '广告内容（可存HTML或文本）',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '类型：1图片 2文字 3HTML 4视频',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0禁用 1启用 2待审核',
  `start_time` datetime DEFAULT NULL COMMENT '开始展示时间',
  `end_time` datetime DEFAULT NULL COMMENT '结束展示时间',
  `click_count` int(11) NOT NULL DEFAULT 0 COMMENT '点击次数',
  `view_count` int(11) NOT NULL DEFAULT 0 COMMENT '展示次数',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序，数字越大越靠前',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_time` (`start_time`,`end_time`),
  KEY `idx_sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='广告表';

-- ----------------------------
-- 广告位置关联表
-- ----------------------------
DROP TABLE IF EXISTS `ad_position_relations`;
CREATE TABLE `ad_position_relations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position_id` int(11) NOT NULL COMMENT '位置ID',
  `ad_id` int(11) NOT NULL COMMENT '广告ID',
  `weight` int(11) NOT NULL DEFAULT 1 COMMENT '权重（用于同一位置多广告轮播）',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0禁用 1启用',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_position` (`position_id`),
  KEY `idx_ad` (`ad_id`),
  KEY `idx_position_status` (`position_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='广告位置关联表';

-- ----------------------------
-- 广告统计表
-- ----------------------------
DROP TABLE IF EXISTS `ad_statistics`;
CREATE TABLE `ad_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ad_id` int(11) NOT NULL COMMENT '广告ID',
  `position_code` varchar(50) DEFAULT NULL COMMENT '位置标识符',
  `udid` varchar(100) DEFAULT NULL COMMENT '用户UDID',
  `ip` varchar(50) DEFAULT NULL COMMENT 'IP地址',
  `type` varchar(20) NOT NULL COMMENT '类型：click点击 view展示',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ad` (`ad_id`),
  KEY `idx_position` (`position_code`),
  KEY `idx_type` (`type`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='广告统计表';

-- ----------------------------
-- 预设广告位置数据
-- ----------------------------
INSERT INTO `ad_positions` (`code`, `name`, `description`, `width`, `height`, `status`, `priority`) VALUES
('home_banner', '首页横幅', '首页顶部轮播广告位', 750, 300, 1, 100),
('home_sidebar', '首页侧边', '首页右侧矩形广告位', 300, 250, 1, 90),
('order_banner', '订单页横幅', '订单列表顶部横幅', 600, 100, 1, 80),
('order_sidebar', '订单页侧边', '订单列表右侧广告', 200, 200, 1, 70),
('detail_banner', '详情页广告', '详情页顶部广告', 600, 100, 1, 80),
('user_banner', '用户页横幅', '用户中心顶部横幅', 600, 80, 1, 70),
('float_ad', '悬浮广告', '右下角悬浮广告', 200, 200, 1, 60),
('dialog_ad', '弹窗广告', '首次弹窗广告', 400, 300, 1, 50),
('app_banner', 'APP下载广告', 'APP下载推广位', 300, 150, 1, 75);

-- ----------------------------
-- 示例广告数据
-- ----------------------------
INSERT INTO `ads` (`title`, `image_url`, `link_url`, `content`, `type`, `status`, `start_time`, `end_time`, `sort`) VALUES
('示例横幅广告', '/ads/banner_example.jpg', 'https://example.com', '', 1, 1, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 0),
('示例文字广告', '', '', '这是一个文字广告示例，点击了解更多', 2, 1, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 1);

-- ----------------------------
-- 关联示例
-- ----------------------------
INSERT INTO `ad_position_relations` (`position_id`, `ad_id`, `weight`, `status`) VALUES
(1, 1, 1, 1),
(7, 2, 1, 1);

-- 管备云备案系统 数据库结构 (v3)
-- 表前缀: gb_
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 站点配置表
DROP TABLE IF EXISTS `gb_config`;
CREATE TABLE `gb_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '配置键',
  `value` text COMMENT '配置值',
  `remark` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='站点配置';

-- 用户表
DROP TABLE IF EXISTS `gb_users`;
CREATE TABLE `gb_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `bg_image` varchar(255) DEFAULT NULL COMMENT '个人中心背景图',
  `bio` varchar(500) DEFAULT NULL COMMENT '个人简介',
  `status` tinyint(1) DEFAULT 1 COMMENT '1正常 0禁用',
  `role` varchar(20) DEFAULT 'user',
  `last_login` datetime DEFAULT NULL,
  `last_ip` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户';

-- 管理员表
DROP TABLE IF EXISTS `gb_admins`;
CREATE TABLE `gb_admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'admin',
  `last_login` datetime DEFAULT NULL,
  `last_ip` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员';

-- 备案记录表 (v3: 新增企业资质字段)
DROP TABLE IF EXISTS `gb_filings`;
CREATE TABLE `gb_filings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `icp_no` varchar(50) DEFAULT NULL COMMENT 'ICP备案号 格式: 管ICP备xxxxxxxx号',
  `site_name` varchar(100) NOT NULL COMMENT '网站名称',
  `site_domain` varchar(200) NOT NULL COMMENT '网站域名',
  `site_url` varchar(255) DEFAULT NULL,
  `owner_name` varchar(50) NOT NULL COMMENT '主办单位/姓名',
  `owner_type` tinyint(1) DEFAULT 1 COMMENT '1企业 2个人',
  `owner_id` varchar(50) DEFAULT NULL COMMENT '证件号(身份证/统一社会信用代码)',
  `owner_phone` varchar(20) DEFAULT NULL,
  `owner_email` varchar(100) DEFAULT NULL,
  `owner_address` varchar(255) DEFAULT NULL COMMENT '企业地址',
  `license_img` varchar(255) DEFAULT NULL COMMENT '企业资质/营业执照图片',
  `server_ip` varchar(45) DEFAULT NULL,
  `content_type` varchar(50) DEFAULT NULL COMMENT '网站内容类型',
  `language` varchar(20) DEFAULT NULL,
  `remark` text,
  `status` tinyint(1) DEFAULT 0 COMMENT '0审核中 1通过 2未通过 3已撤销',
  `audit_remark` text,
  `audited_at` datetime DEFAULT NULL,
  `audited_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_icp` (`icp_no`),
  KEY `idx_domain` (`site_domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='备案记录';

-- 反馈表
DROP TABLE IF EXISTS `gb_feedbacks`;
CREATE TABLE `gb_feedbacks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `type` varchar(30) DEFAULT 'feedback' COMMENT 'feedback反馈 report举报',
  `title` varchar(200) DEFAULT NULL,
  `content` text NOT NULL,
  `target_url` varchar(255) DEFAULT NULL COMMENT '举报目标',
  `status` tinyint(1) DEFAULT 0 COMMENT '0待处理 1已处理 2已关闭',
  `reply` text,
  `replied_at` datetime DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='反馈与举报';

-- 工单表
DROP TABLE IF EXISTS `gb_tickets`;
CREATE TABLE `gb_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `category` varchar(30) DEFAULT 'general',
  `priority` tinyint(1) DEFAULT 1 COMMENT '1低 2中 3高',
  `status` tinyint(1) DEFAULT 0 COMMENT '0待回复 1已回复 2已关闭',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='工单';

-- 工单回复表
DROP TABLE IF EXISTS `gb_ticket_replies`;
CREATE TABLE `gb_ticket_replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` varchar(20) DEFAULT 'user',
  `content` text NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ticket` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='工单回复';

-- 文章表
DROP TABLE IF EXISTS `gb_articles`;
CREATE TABLE `gb_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `category` varchar(30) DEFAULT 'article' COMMENT 'article公告 privacy隐私 policy协议',
  `content` longtext NOT NULL,
  `status` tinyint(1) DEFAULT 1 COMMENT '1发布 0草稿',
  `views` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章';

-- 申请表(认证/合作)
DROP TABLE IF EXISTS `gb_applications`;
CREATE TABLE `gb_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(30) NOT NULL COMMENT 'enterprise企业认证 personal个人认证 partner合作伙伴',
  `name` varchar(100) NOT NULL,
  `id_card` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `company` varchar(200) DEFAULT NULL,
  `license_no` varchar(50) DEFAULT NULL,
  `license_img` varchar(255) DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `intro` text,
  `status` tinyint(1) DEFAULT 0 COMMENT '0申请中 1通过 2未通过',
  `audit_remark` text,
  `audited_at` datetime DEFAULT NULL,
  `audited_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='认证与合作申请';

-- 首页公示表 (v3: 新增图片/备案号/原因/关联用户字段)
DROP TABLE IF EXISTS `gb_publicity`;
CREATE TABLE `gb_publicity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL COMMENT 'partner合作方 invalid失效/违规 filing备案公示',
  `title` varchar(200) NOT NULL,
  `content` text,
  `link` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL COMMENT '图片(合作方轮播图)',
  `icp_no` varchar(50) DEFAULT NULL COMMENT '备案号(备案/失效公示)',
  `reason` varchar(255) DEFAULT NULL COMMENT '失效原因(失效公示)',
  `user_id` int(11) DEFAULT NULL COMMENT '备案用户',
  `status` tinyint(1) DEFAULT 1,
  `sort` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='首页公示';

-- 通知消息表
DROP TABLE IF EXISTS `gb_notifications`;
CREATE TABLE `gb_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT 0 COMMENT '0全体',
  `title` varchar(200) NOT NULL,
  `content` text,
  `type` varchar(30) DEFAULT 'system',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通知消息';

-- 通知已读记录 (针对全体通知 user_id=0 的按用户标记已读)
DROP TABLE IF EXISTS `gb_notification_reads`;
CREATE TABLE `gb_notification_reads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `read_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_notif_user` (`notification_id`, `user_id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通知已读记录';

-- 日志表
DROP TABLE IF EXISTS `gb_logs`;
CREATE TABLE `gb_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL COMMENT 'system/login/operation',
  `content` text,
  `user_id` int(11) DEFAULT 0,
  `role` varchar(20) DEFAULT 'user',
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_user` (`user_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='日志';

-- 邮箱验证码表
DROP TABLE IF EXISTS `gb_email_codes`;
CREATE TABLE `gb_email_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `purpose` varchar(20) DEFAULT 'register',
  `expired_at` datetime DEFAULT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='邮箱验证码';

-- 聊天室消息表
DROP TABLE IF EXISTS `gb_chat_messages`;
CREATE TABLE `gb_chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `msg_type` varchar(20) DEFAULT 'text' COMMENT 'text/image/emoji/url',
  `reply_to` int(11) DEFAULT NULL COMMENT '@引用消息ID',
  `is_recalled` tinyint(1) DEFAULT 0 COMMENT '是否被撤回(违禁词)',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='聊天室消息';

-- 聊天室禁言表
DROP TABLE IF EXISTS `gb_chat_banned`;
CREATE TABLE `gb_chat_banned` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `banned_until` datetime NOT NULL COMMENT '禁言截止时间',
  `source` varchar(30) DEFAULT 'auto' COMMENT 'auto自动(刷屏/违规) manual后台手动',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_until` (`banned_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='聊天室禁言';

-- 聊天室违禁词表
DROP TABLE IF EXISTS `gb_chat_words`;
CREATE TABLE `gb_chat_words` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_word` (`word`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='聊天室违禁词';

-- 聊天室违规记录表 (用于30分钟5次违规判定)
DROP TABLE IF EXISTS `gb_chat_violations`;
CREATE TABLE `gb_chat_violations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message_id` int(11) DEFAULT NULL,
  `word` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='聊天室违规记录';

-- 账号注销申请表
DROP TABLE IF EXISTS `gb_account_deletions`;
CREATE TABLE `gb_account_deletions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `reason` varchar(500) NOT NULL,
  `status` tinyint(1) DEFAULT 0 COMMENT '0待审核 1已通过 2已驳回',
  `audit_remark` varchar(255) DEFAULT NULL,
  `audited_at` datetime DEFAULT NULL,
  `audited_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='账号注销申请';

-- 认证图片配置表 (后台可上传认证图片及配置图标样式)
DROP TABLE IF EXISTS `gb_certifications`;
CREATE TABLE `gb_certifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '认证名称',
  `image` varchar(255) DEFAULT NULL COMMENT '认证图标图片',
  `info` varchar(500) DEFAULT NULL COMMENT '认证信息说明',
  `icon_style` varchar(100) DEFAULT 'default' COMMENT '图标样式 class',
  `sort` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='认证图片配置';

SET FOREIGN_KEY_CHECKS = 1;

-- 初始配置数据
INSERT INTO `gb_config` (`name`, `value`, `remark`, `updated_at`) VALUES
('site_name', '管备云备案系统', '网站名称', NOW()),
('site_url', '', '站点URL', NOW()),
('site_logo', '', '网站Logo', NOW()),
('site_favicon', '', '网站图标', NOW()),
('site_thumbnail', '', '网站缩略图', NOW()),
('site_title', '管备云备案系统 - 专业ICP备案服务', '网站标题', NOW()),
('site_keywords', 'ICP备案,网站备案,管备云,备案系统', '网站关键词', NOW()),
('site_description', '管备云备案系统提供专业的ICP备案服务', '网站描述', NOW()),
('footer_intro', '管备云备案系统致力于为用户提供专业、高效、便捷的ICP备案服务。', '页脚介绍', NOW()),
('icp_info', '管ICP备00000000号', 'ICP备案信息', NOW()),
('copyright', '© 2024 管备云备案系统 保留所有权利', '版权信息', NOW()),
('qq_image', '', 'QQ二维码', NOW()),
('wechat_image', '', '微信二维码', NOW()),
('kuaishou_image', '', '快手二维码', NOW()),
('captcha_image', '', '人机验证滑块图片', NOW()),
('announcement_enabled', '0', '是否开启首页公告弹窗', NOW()),
('announcement_content', '', '首页公告内容', NOW()),
('announcement_title', '', '首页公告标题', NOW()),
('mail_enabled', '0', '是否开启邮件发送', NOW()),
('mail_host', '', 'SMTP主机', NOW()),
('mail_port', '465', 'SMTP端口', NOW()),
('mail_user', '', 'SMTP用户', NOW()),
('mail_pass', '', 'SMTP密码', NOW()),
('mail_from', '', '发件邮箱', NOW()),
('mail_from_name', '管备云备案系统', '发件人名称', NOW()),
('mail_reg_login', '0', '登录注册是否开启邮件', NOW()),
('oauth_enabled', '0', '是否开启聚合登录', NOW()),
('oauth_qq_id', '', 'QQ登录APPID', NOW()),
('oauth_qq_secret', '', 'QQ登录密钥', NOW()),
('oauth_qq_callback', '', 'QQ回调网址', NOW()),
('oauth_wechat_id', '', '微信登录APPID', NOW()),
('oauth_wechat_secret', '', '微信登录密钥', NOW()),
('oauth_wechat_callback', '', '微信回调网址', NOW()),
('oauth_alipay_id', '', '支付宝登录APPID', NOW()),
('oauth_alipay_secret', '', '支付宝登录密钥', NOW()),
('oauth_alipay_callback', '', '支付宝回调网址', NOW()),
('tech_support', '本站由森企动力提供网站建设与技术支持', '技术支持文字', NOW()),
('tech_support_url', 'https://sqdl.uiyoi.icu', '技术支持链接', NOW()),
('theme_color', '#1677ff', '主题色', NOW()),
('maintenance_enabled', '0', '是否开启网站维护', NOW()),
('maintenance_content', '系统正在维护升级中，请稍后访问。', '维护公告内容', NOW()),
('maintenance_recover_time', '', '维护预计恢复时间', NOW()),
('maintenance_title', '网站维护中', '维护页面标题', NOW()),
('chat_rate_limit', '10', '聊天室每分钟发送上限', NOW()),
('chat_spam_threshold', '50', '聊天室刷屏自动禁言阈值(条/分钟)', NOW()),
('chat_spam_ban_min', '60', '刷屏禁言时长(分钟,1-3小时随机基准)', NOW()),
('chat_violation_window', '30', '违规统计窗口(分钟)', NOW()),
('chat_violation_limit', '5', '窗口内违规次数上限', NOW()),
('chat_violation_ban_min', '60', '违规禁言时长(分钟,1-5小时随机基准)', NOW()),
('install_time', NOW(), '安装时间', NOW()),
('version', '3.0.0', '版本', NOW());

-- 默认文章
INSERT INTO `gb_articles` (`title`, `slug`, `category`, `content`, `status`, `views`, `created_at`, `updated_at`) VALUES
('欢迎使用管备云备案系统', 'welcome', 'article', '<p>管备云备案系统致力于为您提供专业、高效、便捷的ICP备案服务。如您在使用过程中有任何问题，欢迎通过工单或反馈与我们联系。</p>', 1, 0, NOW(), NOW()),
('隐私政策', 'privacy', 'privacy', '<p>本隐私政策说明管备云备案系统如何收集、使用和保护您的个人信息。我们严格遵守相关法律法规，保护您的隐私权益。</p>', 1, 0, NOW(), NOW()),
('用户协议', 'policy', 'policy', '<p>欢迎使用管备云备案系统。使用本系统即表示您同意本用户协议的全部条款。请仔细阅读以下内容。</p>', 1, 0, NOW(), NOW());

-- 默认认证配置
INSERT INTO `gb_certifications` (`name`, `image`, `info`, `icon_style`, `sort`, `status`, `created_at`) VALUES
('企业认证', '', '该用户已完成企业实名认证', 'cert-enterprise', 100, 1, NOW()),
('个人认证', '', '该用户已完成个人实名认证', 'cert-personal', 90, 1, NOW()),
('合作伙伴', '', '该用户为平台认证合作伙伴', 'cert-partner', 80, 1, NOW());

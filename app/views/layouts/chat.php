<?php /** 聊天室/私聊独立布局 - 无平台公共头部和页脚, 右侧汉堡菜单 */
$site = $site ?? [];
$siteName = $site['site_name'] ?? '管备云备案系统';
$siteLogo = $site['site_logo'] ?? '';
$siteTitle = $site['site_title'] ?? $siteName;
$user = current_user();
$chatRole = chat_user_role($user['id'] ?? 0);
$canAdminChat = in_array($chatRole, ['admin', 'super_admin', 'platform_admin'], true);
$chatAdminPath = site_config('chat_admin_path', 'admins');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="keywords" content="<?php echo e($site['site_keywords'] ?? ''); ?>">
<meta name="description" content="<?php echo e($site['site_description'] ?? ''); ?>">
<title><?php echo e($pageTitle ?? $siteTitle); ?></title>
<link rel="icon" href="<?php echo !empty($site['site_favicon']) ? asset($site['site_favicon']) : asset('assets/img/logo.svg'); ?>">
<link rel="stylesheet" href="<?php echo asset('assets/css/theme.css'); ?>">
<link rel="stylesheet" href="<?php echo asset('assets/css/site.css'); ?>">
<style>
/* 聊天独立布局样式 */
html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }
body { background: var(--bg-soft, #f5f7fa); }
.chat-page { display: flex; flex-direction: column; height: 100vh; height: 100dvh; }
/* 顶部栏 */
.chat-topbar {
  display: flex; align-items: center; justify-content: space-between;
  padding: 10px 16px; background: var(--card-bg, #fff);
  border-bottom: 1px solid var(--border, #e5e7eb);
  box-shadow: 0 1px 3px rgba(0,0,0,.04);
  z-index: 10; flex-shrink: 0;
}
.chat-topbar-left { display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1; }
.chat-topbar-logo {
  width: 32px; height: 32px; border-radius: 6px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  background: var(--primary, #3b82f6); color: #fff;
}
.chat-topbar-logo img { width: 100%; height: 100%; object-fit: cover; border-radius: 6px; }
.chat-topbar-title { font-size: 16px; font-weight: 600; color: var(--text, #1f2937); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.chat-topbar-title small { font-size: 12px; color: var(--text-muted, #9ca3af); font-weight: 400; margin-left: 6px; }
.chat-topbar-right { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
.chat-online-badge {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 4px 10px; border-radius: 14px;
  background: var(--primary-bg, #eff6ff); color: var(--primary, #3b82f6);
  font-size: 12px; cursor: pointer; border: 1px solid var(--primary-bg-2, #dbeafe);
  transition: all .2s;
}
.chat-online-badge:hover { background: var(--primary, #3b82f6); color: #fff; }
.chat-online-badge .dot { width: 6px; height: 6px; border-radius: 50%; background: #22c55e; animation: pulse 2s infinite; }
@keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:.5;} }
.chat-icon-btn {
  width: 36px; height: 36px; border-radius: 8px; border: none;
  background: transparent; color: var(--text-2, #4b5563); cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: background .2s;
}
.chat-icon-btn:hover { background: var(--bg-hover, #f3f4f6); }
.chat-hamburger {
  width: 36px; height: 36px; border-radius: 8px; border: none;
  background: transparent; cursor: pointer; padding: 0;
  display: flex; flex-direction: column; justify-content: center; align-items: center; gap: 4px;
}
.chat-hamburger span { width: 20px; height: 2px; background: var(--text, #1f2937); border-radius: 1px; transition: all .25s; }
.chat-hamburger.open span:nth-child(1) { transform: translateY(6px) rotate(45deg); }
.chat-hamburger.open span:nth-child(2) { opacity: 0; }
.chat-hamburger.open span:nth-child(3) { transform: translateY(-6px) rotate(-45deg); }

/* 右侧抽屉菜单 */
.chat-drawer-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 100;
  opacity: 0; visibility: hidden; transition: all .25s;
}
.chat-drawer-overlay.open { opacity: 1; visibility: visible; }
.chat-drawer {
  position: fixed; top: 0; right: 0; bottom: 0; width: 280px; max-width: 80vw;
  background: var(--card-bg, #fff); z-index: 101;
  transform: translateX(100%); transition: transform .25s;
  display: flex; flex-direction: column;
  box-shadow: -4px 0 20px rgba(0,0,0,.1);
}
.chat-drawer.open { transform: translateX(0); }
.chat-drawer-head {
  padding: 16px; border-bottom: 1px solid var(--divider, #e5e7eb);
  display: flex; align-items: center; justify-content: space-between;
}
.chat-drawer-head h3 { margin: 0; font-size: 16px; color: var(--text, #1f2937); }
.chat-drawer-body { flex: 1; overflow-y: auto; padding: 8px; }
.chat-drawer-item {
  display: flex; align-items: center; gap: 10px; padding: 12px 14px;
  border-radius: 8px; color: var(--text, #1f2937); cursor: pointer;
  text-decoration: none; font-size: 14px; transition: background .2s;
}
.chat-drawer-item:hover { background: var(--bg-hover, #f3f4f6); }
.chat-drawer-item svg { flex-shrink: 0; color: var(--text-2, #6b7280); }
.chat-drawer-item.is-active { background: var(--primary-bg, #eff6ff); color: var(--primary, #3b82f6); }
.chat-drawer-item.is-active svg { color: var(--primary, #3b82f6); }
.chat-drawer-divider { height: 1px; background: var(--divider, #e5e7eb); margin: 8px 0; }

/* 主体 */
.chat-content { flex: 1; min-height: 0; overflow: hidden; }

/* 移动端适配 */
@media (max-width: 768px) {
  .chat-topbar { padding: 8px 12px; }
  .chat-topbar-title { font-size: 14px; }
  .chat-topbar-title small { display: none; }
}
</style>
</head>
<body>
<div class="chat-page">
  <!-- 顶部栏 -->
  <header class="chat-topbar">
    <div class="chat-topbar-left">
      <a href="<?php echo site_url('chat'); ?>" class="chat-topbar-logo" title="返回聊天室">
        <?php if ($siteLogo): ?><img src="<?php echo asset($siteLogo); ?>" alt="logo"><?php else: ?>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <?php endif; ?>
      </a>
      <div class="chat-topbar-title">
        <?php echo e($pageTitle ?? '聊天室'); ?>
        <?php if (!empty($roomName)): ?><small><?php echo e($roomName); ?></small><?php endif; ?>
      </div>
    </div>
    <div class="chat-topbar-right">
      <div class="chat-online-badge" id="chatOnlineBadge" onclick="window.chatShowOnline && chatShowOnline()">
        <span class="dot"></span>
        <span id="chatOnlineCount">0</span> 在线
      </div>
      <button class="chat-icon-btn" onclick="gbToggleTheme()" title="切换主题">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
      </button>
      <button class="chat-hamburger" id="chatHamburger" title="菜单" onclick="chatToggleDrawer()">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>

  <!-- 主体内容 -->
  <main class="chat-content">
    <?php echo $content; ?>
  </main>
</div>

<!-- 右侧抽屉菜单 -->
<div class="chat-drawer-overlay" id="chatDrawerOverlay" onclick="chatToggleDrawer()"></div>
<aside class="chat-drawer" id="chatDrawer">
  <div class="chat-drawer-head">
    <h3>菜单</h3>
    <button class="chat-icon-btn" onclick="chatToggleDrawer()" title="关闭">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>
  <div class="chat-drawer-body">
    <a class="chat-drawer-item" href="<?php echo site_url('chat'); ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      <span>聊天室</span>
    </a>
    <a class="chat-drawer-item" href="<?php echo site_url('chat/rooms'); ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      <span>选择区块</span>
    </a>
    <a class="chat-drawer-item" href="<?php echo site_url('chat/online'); ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      <span>在线用户</span>
    </a>
    <a class="chat-drawer-item" href="<?php echo site_url('user/messages'); ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      <span>我的私信</span>
    </a>
    <div class="chat-drawer-divider"></div>
    <?php if ($canAdminChat): ?>
    <a class="chat-drawer-item" href="<?php echo site_url($chatAdminPath); ?>" target="_blank">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      <span>后台入口</span>
    </a>
    <?php endif; ?>
    <a class="chat-drawer-item" href="<?php echo site_url(); ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      <span>返回首页</span>
    </a>
    <?php if ($user): ?>
    <a class="chat-drawer-item" href="<?php echo site_url('user'); ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      <span>用户中心</span>
    </a>
    <a class="chat-drawer-item" href="<?php echo site_url('logout'); ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      <span>退出登录</span>
    </a>
    <?php else: ?>
    <a class="chat-drawer-item" href="<?php echo site_url('login'); ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
      <span>登录</span>
    </a>
    <?php endif; ?>
  </div>
</aside>

<script>
function chatToggleDrawer() {
  var d = document.getElementById('chatDrawer');
  var o = document.getElementById('chatDrawerOverlay');
  var h = document.getElementById('chatHamburger');
  if (!d) return;
  var isOpen = d.classList.toggle('open');
  if (o) o.classList.toggle('open', isOpen);
  if (h) h.classList.toggle('open', isOpen);
}
document.addEventListener('keydown', function(e){ if(e.key === 'Escape') chatToggleDrawer(); });
</script>
<script src="<?php echo asset('assets/js/app.js'); ?>"></script>
<script src="<?php echo asset('assets/js/slider-captcha.js'); ?>"></script>
<?php if (!empty($extraJs)): foreach ($extraJs as $j): ?><script src="<?php echo asset($j); ?>"></script><?php endforeach; endif; ?>
<?php if (!empty($inlineJs)): ?><script><?php echo $inlineJs; ?></script><?php endif; ?>
</body>
</html>

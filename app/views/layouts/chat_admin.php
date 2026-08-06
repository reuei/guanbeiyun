<?php /** 聊天室管理后台布局 - 独立于平台后台, 路径 /admins */
$site = $site ?? [];
$siteName = $site['site_name'] ?? '管备云备案系统';
$siteLogo = $site['site_logo'] ?? '';
$admin = admin_user();
$user  = current_user();
$myRole = $myRole ?? 'admin';
$myIsSuper = $myIsSuper ?? false;
$activeSub = $activeSub ?? '';
$roleLabel = role_label($myRole);
$roleText  = $roleLabel ? $roleLabel['text'] : '管理员';
$operName  = $admin ? ($admin['username'] ?? '管理员') : ($user['username'] ?? '管理员');
$operAvatar = $admin ? ($admin['avatar'] ?? '') : ($user['avatar'] ?? '');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($pageTitle ?? '聊天室管理'); ?> - <?php echo e($siteName); ?></title>
<link rel="icon" href="<?php echo !empty($site['site_favicon']) ? asset($site['site_favicon']) : asset('assets/img/logo.svg'); ?>">
<link rel="stylesheet" href="<?php echo asset('assets/css/theme.css'); ?>">
<link rel="stylesheet" href="<?php echo asset('assets/css/site.css'); ?>">
<style>
/* 聊天室管理后台布局 */
html, body { margin: 0; padding: 0; height: 100%; }
body { background: var(--bg-soft, #f5f7fa); }
.ca-layout { display: flex; min-height: 100vh; }
/* 侧边栏 */
.ca-sidebar {
  width: 230px; background: var(--card-bg, #fff);
  border-right: 1px solid var(--border, #e5e7eb);
  display: flex; flex-direction: column; flex-shrink: 0;
  position: sticky; top: 0; height: 100vh; overflow-y: auto;
  z-index: 30;
}
.ca-brand {
  display: flex; align-items: center; gap: 10px;
  padding: 16px 18px; border-bottom: 1px solid var(--divider, #e5e7eb);
}
.ca-brand-logo {
  width: 32px; height: 32px; border-radius: 6px;
  background: var(--primary, #3b82f6); color: #fff;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; overflow: hidden;
}
.ca-brand-logo img { width: 100%; height: 100%; object-fit: cover; }
.ca-brand-text { font-size: 15px; font-weight: 700; color: var(--text, #1f2937); line-height: 1.2; }
.ca-brand-text small { display: block; font-size: 11px; font-weight: 400; color: var(--text-muted, #9ca3af); margin-top: 2px; }
.ca-nav { flex: 1; padding: 10px 8px; }
.ca-nav-item {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 12px; border-radius: 8px;
  color: var(--text-2, #4b5563); cursor: pointer;
  text-decoration: none; font-size: 14px;
  transition: background .15s; margin-bottom: 2px;
}
.ca-nav-item:hover { background: var(--bg-hover, #f3f4f6); }
.ca-nav-item.is-active { background: var(--primary-bg, #eff6ff); color: var(--primary, #3b82f6); font-weight: 600; }
.ca-nav-item.is-active svg { color: var(--primary, #3b82f6); }
.ca-nav-item svg { flex-shrink: 0; color: var(--text-muted, #6b7280); }
.ca-nav-divider { height: 1px; background: var(--divider, #e5e7eb); margin: 8px 12px; }
.ca-nav-label { font-size: 11px; color: var(--text-muted, #9ca3af); padding: 8px 12px 4px; }
.ca-nav-disabled { opacity: .5; pointer-events: none; cursor: not-allowed; }
.ca-nav-lock { font-size: 10px; color: var(--text-muted, #9ca3af); margin-left: auto; }

/* 主区 */
.ca-main { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.ca-topbar {
  display: flex; align-items: center; justify-content: space-between;
  padding: 12px 20px; background: var(--card-bg, #fff);
  border-bottom: 1px solid var(--border, #e5e7eb);
  position: sticky; top: 0; z-index: 20;
}
.ca-topbar-left { display: flex; align-items: center; gap: 12px; }
.ca-topbar-toggle {
  display: none; width: 36px; height: 36px; border-radius: 8px;
  border: none; background: transparent; cursor: pointer;
  color: var(--text, #1f2937);
}
.ca-crumb { font-size: 14px; color: var(--text-2, #6b7280); }
.ca-crumb b { color: var(--text, #1f2937); }
.ca-topbar-right { display: flex; align-items: center; gap: 8px; }
.ca-role-badge {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 4px 10px; border-radius: 12px;
  font-size: 12px; font-weight: 600;
  background: <?php echo e($roleLabel['bg'] ?? '#e5e7eb'); ?>;
  color: <?php echo e($roleLabel['color'] ?? '#374151'); ?>;
  border: 1px solid rgba(0,0,0,.05);
}
.ca-icon-btn {
  width: 36px; height: 36px; border-radius: 8px; border: none;
  background: transparent; color: var(--text-2, #6b7280); cursor: pointer;
  display: flex; align-items: center; justify-content: center; transition: background .15s;
}
.ca-icon-btn:hover { background: var(--bg-hover, #f3f4f6); }
.ca-user-chip {
  display: flex; align-items: center; gap: 8px; cursor: pointer;
  padding: 4px 8px; border-radius: 8px;
}
.ca-user-chip:hover { background: var(--bg-hover, #f3f4f6); }
.ca-user-avatar {
  width: 32px; height: 32px; border-radius: 50%;
  background: var(--primary, #3b82f6); color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-weight: 600; font-size: 13px; overflow: hidden;
}
.ca-user-avatar img { width: 100%; height: 100%; object-fit: cover; }
.ca-user-name { font-size: 13px; color: var(--text, #1f2937); font-weight: 500; }
.ca-content { flex: 1; padding: 20px; max-width: 1400px; width: 100%; margin: 0 auto; box-sizing: border-box; }

.ca-sidebar-overlay {
  display: none; position: fixed; inset: 0;
  background: rgba(0,0,0,.4); z-index: 25;
}

@media (max-width: 900px) {
  .ca-sidebar { position: fixed; left: -260px; top: 0; transition: left .25s; }
  .ca-sidebar.open { left: 0; }
  .ca-sidebar-overlay.open { display: block; }
  .ca-topbar-toggle { display: flex; align-items: center; justify-content: center; }
  .ca-content { padding: 14px; }
}
</style>
</head>
<body>
<div class="ca-layout">
  <!-- 侧边栏 -->
  <aside class="ca-sidebar" id="caSidebar">
    <div class="ca-brand">
      <a href="<?php echo site_url('admins'); ?>" class="ca-brand-logo">
        <?php if ($siteLogo): ?><img src="<?php echo asset($siteLogo); ?>" alt="logo"><?php else: ?>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <?php endif; ?>
      </a>
      <div class="ca-brand-text">
        聊天室管理
        <small><?php echo e($siteName); ?></small>
      </div>
    </div>
    <nav class="ca-nav">
      <a class="ca-nav-item <?php echo $activeSub==='dashboard'?'is-active':'' ?>" href="<?php echo site_url('admins'); ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        <span>概览</span>
      </a>
      <div class="ca-nav-label">管理</div>
      <a class="ca-nav-item <?php echo $activeSub==='rooms'?'is-active':'' ?>" href="<?php echo site_url('admins/rooms'); ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        <span>聊天版块</span>
      </a>
      <a class="ca-nav-item <?php echo $activeSub==='messages'?'is-active':'' ?>" href="<?php echo site_url('admins/messages'); ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <span>消息管理</span>
      </a>
      <a class="ca-nav-item <?php echo $activeSub==='announcements'?'is-active':'' ?>" href="<?php echo site_url('admins/announcements'); ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>
        <span>公告管理</span>
      </a>
      <a class="ca-nav-item <?php echo $activeSub==='banned'?'is-active':'' ?>" href="<?php echo site_url('admins/banned'); ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
        <span>禁言用户</span>
      </a>
      <div class="ca-nav-label">用户</div>
      <a class="ca-nav-item <?php echo $activeSub==='titles'?'is-active':'' ?>" href="<?php echo site_url('admins/titles'); ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3 7h7l-5.5 4.5L18 21l-6-4-6 4 1.5-7.5L2 9h7z"/></svg>
        <span>用户头衔</span>
      </a>
      <a class="ca-nav-item <?php echo $activeSub==='online'?'is-active':'' ?>" href="<?php echo site_url('admins/online'); ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>在线用户</span>
      </a>
      <?php if ($myIsSuper): ?>
      <div class="ca-nav-label">超管</div>
      <a class="ca-nav-item" href="<?php echo site_url('admins/settings'); ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        <span>全体禁言/设置</span>
      </a>
      <?php endif; ?>
      <div class="ca-nav-divider"></div>
      <a class="ca-nav-item" href="<?php echo site_url('chat'); ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <span>返回聊天室</span>
      </a>
      <a class="ca-nav-item" href="<?php echo site_url(); ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        <span>返回首页</span>
      </a>
      <a class="ca-nav-item" href="<?php echo site_url('admins/logout'); ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        <span>退出登录</span>
      </a>
    </nav>
  </aside>
  <div class="ca-sidebar-overlay" id="caSidebarOverlay" onclick="caToggleSidebar()"></div>

  <!-- 主区 -->
  <div class="ca-main">
    <header class="ca-topbar">
      <div class="ca-topbar-left">
        <button class="ca-icon-btn ca-topbar-toggle" onclick="caToggleSidebar()" title="菜单">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <div class="ca-crumb">聊天室管理 / <b><?php echo e($pageTitle ?? ''); ?></b></div>
      </div>
      <div class="ca-topbar-right">
        <span class="ca-role-badge"><?php echo e($roleText); ?></span>
        <a class="ca-icon-btn" href="<?php echo site_url('chat'); ?>" target="_blank" title="访问聊天室">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        </a>
        <button class="ca-icon-btn" onclick="gbToggleTheme()" title="切换主题">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
        </button>
        <div class="ca-user-chip">
          <div class="ca-user-avatar">
            <?php if ($operAvatar): ?><img src="<?php echo asset($operAvatar); ?>" alt=""><?php else: ?><?php echo e(strtoupper(mb_substr($operName, 0, 1))); ?><?php endif; ?>
          </div>
          <span class="ca-user-name"><?php echo e($operName); ?></span>
        </div>
      </div>
    </header>
    <div class="ca-content">
      <?php echo $content; ?>
    </div>
  </div>
</div>
<div class="toast-container" id="gb-toast-container"></div>
<script src="<?php echo asset('assets/js/app.js'); ?>"></script>
<script src="<?php echo asset('assets/js/slider-captcha.js'); ?>"></script>
<script>
function caToggleSidebar(){
  var s=document.getElementById('caSidebar');
  var o=document.getElementById('caSidebarOverlay');
  if(!s)return;
  s.classList.toggle('open');
  if(o)o.classList.toggle('open', s.classList.contains('open'));
}
</script>
</body>
</html>

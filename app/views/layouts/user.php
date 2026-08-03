<?php /** 用户中心布局 */
$user = current_user();
$site = $site ?? [];
$siteName = $site['site_name'] ?? '管备云备案系统';
$siteLogo = $site['site_logo'] ?? '';
$crumb = $crumb ?? '工作台';
$activeMenu = $activeMenu ?? '';
$activeSub = $activeSub ?? '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($pageTitle ?? '用户中心'); ?> - <?php echo e($siteName); ?></title>
<link rel="icon" href="<?php echo !empty($site['site_favicon']) ? asset($site['site_favicon']) : asset('assets/img/logo.svg'); ?>">
<link rel="stylesheet" href="<?php echo asset('assets/css/theme.css'); ?>">
<link rel="stylesheet" href="<?php echo asset('assets/css/site.css'); ?>">
</head>
<body>
<div class="page-loader" id="gb-page-loader"><div class="gb-loading gb-loading-lg"></div><div class="page-loader-text">加载中...</div></div>
<div class="user-layout">
  <aside class="user-sidebar" id="userSidebar">
    <div class="side-brand">
      <?php if ($siteLogo): ?><img src="<?php echo asset($siteLogo); ?>"><?php else: ?>
      <div style="width:30px;height:30px;border-radius:4px;background:var(--primary);display:flex;align-items:center;justify-content:center;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
      <?php endif; ?>
      <span>用户中心</span>
    </div>
    <nav class="sidebar-menu">
      <!-- 工作台 -->
      <div class="menu-group">
        <div class="menu-item has-sub <?php echo $activeMenu==='workbench'?'active':'' ?> <?php echo $activeSub==='uc-dashboard'?'expanded':'' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          <span>工作台</span>
          <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
        <div class="menu-sub <?php echo $activeSub==='uc-dashboard'?'open':'' ?>">
          <a class="menu-item <?php echo $activeSub==='uc-dashboard'?'active':'' ?>" href="<?php echo site_url('user/dashboard'); ?>">总概览</a>
        </div>
      </div>
      <!-- 备案管理 -->
      <div class="menu-group">
        <div class="menu-item has-sub <?php echo $activeMenu==='filing'?'active':'' ?> <?php echo in_array($activeSub,['uc-filings','uc-feedback'])?'expanded':'' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          <span>备案管理</span>
          <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
        <div class="menu-sub <?php echo in_array($activeSub,['uc-filings','uc-feedback'])?'open':'' ?>">
          <a class="menu-item <?php echo $activeSub==='uc-filings'?'active':'' ?>" href="<?php echo site_url('user/filings'); ?>">备案申请管理</a>
          <a class="menu-item <?php echo $activeSub==='uc-feedback'?'active':'' ?>" href="<?php echo site_url('user/feedback'); ?>">反馈与举报管理</a>
        </div>
      </div>
      <!-- 工单管理 -->
      <div class="menu-group">
        <div class="menu-item has-sub <?php echo $activeMenu==='ticket'?'active':'' ?> <?php echo $activeSub==='uc-tickets'?'expanded':'' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span>工单管理</span>
          <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
        <div class="menu-sub <?php echo $activeSub==='uc-tickets'?'open':'' ?>">
          <a class="menu-item <?php echo $activeSub==='uc-tickets'?'active':'' ?>" href="<?php echo site_url('user/tickets'); ?>">我的工单</a>
        </div>
      </div>
      <!-- 用户配置 -->
      <div class="menu-group">
        <div class="menu-item has-sub <?php echo $activeMenu==='settings'?'active':'' ?> <?php echo in_array($activeSub,['uc-profile','uc-cert','uc-partner'])?'expanded':'' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <span>用户配置</span>
          <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
        <div class="menu-sub <?php echo in_array($activeSub,['uc-profile','uc-cert','uc-partner'])?'open':'' ?>">
          <a class="menu-item <?php echo $activeSub==='uc-profile'?'active':'' ?>" href="<?php echo site_url('user/profile'); ?>">信息配置</a>
          <a class="menu-item <?php echo $activeSub==='uc-cert'?'active':'' ?>" href="<?php echo site_url('user/certification'); ?>">认证管理</a>
          <a class="menu-item <?php echo $activeSub==='uc-partner'?'active':'' ?>" href="<?php echo site_url('user/partner'); ?>">合作伙伴申请</a>
        </div>
      </div>
      <!-- 日志管理 -->
      <div class="menu-group">
        <div class="menu-item has-sub <?php echo $activeMenu==='logs'?'active':'' ?> <?php echo $activeSub==='uc-logs'?'expanded':'' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          <span>日志管理</span>
          <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
        <div class="menu-sub <?php echo $activeSub==='uc-logs'?'open':'' ?>">
          <a class="menu-item <?php echo $activeSub==='uc-logs'?'active':'' ?>" href="<?php echo site_url('user/logs'); ?>">我的日志</a>
        </div>
      </div>
    </nav>
  </aside>
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <div class="user-main">
    <header class="admin-topbar">
      <div class="tb-left">
        <button class="icon-btn toggle-sidebar" onclick="document.getElementById('userSidebar').classList.add('open');document.getElementById('sidebarOverlay').classList.add('open');">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <div class="crumb">用户中心 / <b><?php echo e($crumb); ?></b></div>
      </div>
      <div class="tb-right">
        <a class="icon-btn" href="<?php echo site_url(); ?>" title="返回首页">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        </a>
        <button class="icon-btn theme-toggle" onclick="gbToggleTheme()" title="切换主题">
          <svg class="sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
          <svg class="moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        </button>
        <div class="admin-user">
          <?php if (!empty($user['avatar'])): ?><img src="<?php echo asset($user['avatar']); ?>"><?php else: ?>
          <div style="width:32px;height:32px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;"><?php echo e(strtoupper(mb_substr($user['username'] ?? 'U', 0, 1))); ?></div>
          <?php endif; ?>
          <span class="name hide-mobile"><?php echo e($user['username'] ?? '用户'); ?></span>
        </div>
        <a class="icon-btn" href="<?php echo site_url('logout'); ?>" title="退出登录">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </a>
      </div>
    </header>
    <div class="admin-content fade-in">
      <?php echo $content; ?>
    </div>
  </div>
</div>
<div class="toast-container" id="gb-toast-container"></div>
<script src="<?php echo asset('assets/js/app.js'); ?>"></script>
<script src="<?php echo asset('assets/js/slider-captcha.js'); ?>"></script>
<?php if (!empty($inlineJs)): ?><script><?php echo $inlineJs; ?></script><?php endif; ?>
</body>
</html>

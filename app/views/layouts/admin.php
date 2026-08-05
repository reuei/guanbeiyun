<?php /** 后台布局 */
$admin = admin_user();
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
<title><?php echo e($pageTitle ?? '后台管理'); ?> - <?php echo e($siteName); ?></title>
<link rel="icon" href="<?php echo !empty($site['site_favicon']) ? asset($site['site_favicon']) : asset('assets/img/logo.svg'); ?>">
<link rel="stylesheet" href="<?php echo asset('assets/css/theme.css'); ?>">
<link rel="stylesheet" href="<?php echo asset('assets/css/site.css'); ?>">
</head>
<body>
<div class="page-loader" id="gb-page-loader"><div class="gb-loading gb-loading-lg"></div><div class="page-loader-text">加载中...</div></div>
<div class="admin-layout">
  <!-- 侧边栏 -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="side-brand">
      <?php if ($siteLogo): ?><img src="<?php echo asset($siteLogo); ?>"><?php else: ?>
      <div style="width:30px;height:30px;border-radius:4px;background:var(--primary);display:flex;align-items:center;justify-content:center;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
      <?php endif; ?>
      <span><?php echo e($siteName); ?></span>
    </div>
    <nav class="sidebar-menu">
      <!-- 工作台 -->
      <div class="menu-group">
        <div class="menu-item has-sub <?php echo $activeMenu==='workbench'?'active':'' ?> <?php echo in_array($activeSub,['dashboard','bigscreen'])?'expanded':'' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          <span>工作台</span>
          <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
        <div class="menu-sub <?php echo in_array($activeSub,['dashboard','bigscreen'])?'open':'' ?>">
          <a class="menu-item <?php echo $activeSub==='dashboard'?'active':'' ?>" href="<?php echo site_url('admin/dashboard'); ?>">总概览</a>
          <a class="menu-item <?php echo $activeSub==='bigscreen'?'active':'' ?>" href="<?php echo site_url('admin/bigscreen'); ?>">数据大屏</a>
        </div>
      </div>
      <!-- 用户管理 -->
      <div class="menu-group">
        <div class="menu-item has-sub <?php echo $activeMenu==='users'?'active':'' ?> <?php echo in_array($activeSub,['users','filings','applications','feedbacks','reports','tickets','deletions'])?'expanded':'' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          <span>用户管理</span>
          <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
        <div class="menu-sub <?php echo in_array($activeSub,['users','filings','applications','feedbacks','reports','tickets','deletions'])?'open':'' ?>">
          <a class="menu-item <?php echo $activeSub==='users'?'active':'' ?>" href="<?php echo site_url('admin/users'); ?>">用户管理</a>
          <a class="menu-item <?php echo $activeSub==='filings'?'active':'' ?>" href="<?php echo site_url('admin/filings'); ?>">备案管理</a>
          <a class="menu-item <?php echo $activeSub==='applications'?'active':'' ?>" href="<?php echo site_url('admin/applications'); ?>">申请管理</a>
          <a class="menu-item <?php echo $activeSub==='feedbacks'?'active':'' ?>" href="<?php echo site_url('admin/feedbacks'); ?>">反馈管理</a>
          <a class="menu-item <?php echo $activeSub==='reports'?'active':'' ?>" href="<?php echo site_url('admin/reports'); ?>">举报管理</a>
          <a class="menu-item <?php echo $activeSub==='tickets'?'active':'' ?>" href="<?php echo site_url('admin/tickets'); ?>">工单管理</a>
          <a class="menu-item <?php echo $activeSub==='deletions'?'active':'' ?>" href="<?php echo site_url('admin/deletions'); ?>">注销申请</a>
        </div>
      </div>
      <!-- 系统配置 -->
      <div class="menu-group">
        <div class="menu-item has-sub <?php echo $activeMenu==='system'?'active':'' ?> <?php echo in_array($activeSub,['siteconfig','announcement','articles','mail','oauth','maintenance'])?'expanded':'' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
          <span>系统配置</span>
          <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
        <div class="menu-sub <?php echo in_array($activeSub,['siteconfig','announcement','articles','mail','oauth','maintenance'])?'open':'' ?>">
          <a class="menu-item <?php echo $activeSub==='siteconfig'?'active':'' ?>" href="<?php echo site_url('admin/siteconfig'); ?>">网站配置</a>
          <a class="menu-item <?php echo $activeSub==='announcement'?'active':'' ?>" href="<?php echo site_url('admin/announcement'); ?>">公告配置</a>
          <a class="menu-item <?php echo $activeSub==='articles'?'active':'' ?>" href="<?php echo site_url('admin/articles'); ?>">文章管理</a>
          <a class="menu-item <?php echo $activeSub==='mail'?'active':'' ?>" href="<?php echo site_url('admin/mail'); ?>">邮箱配置</a>
          <a class="menu-item <?php echo $activeSub==='oauth'?'active':'' ?>" href="<?php echo site_url('admin/oauth'); ?>">聚合登录配置</a>
          <a class="menu-item <?php echo $activeSub==='maintenance'?'active':'' ?>" href="<?php echo site_url('admin/maintenance'); ?>">网站维护</a>
        </div>
      </div>
      <!-- 认证管理 -->
      <div class="menu-group">
        <div class="menu-item has-sub <?php echo $activeMenu==='auth'?'active':'' ?> <?php echo in_array($activeSub,['cert-apply','partner-apply','publicity','certifications','pub-filing','pub-invalid'])?'expanded':'' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9c2.5 0 4.76 1.02 6.39 2.66"/></svg>
          <span>认证管理</span>
          <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
        <div class="menu-sub <?php echo in_array($activeSub,['cert-apply','partner-apply','publicity','certifications','pub-filing','pub-invalid'])?'open':'' ?>">
          <a class="menu-item <?php echo $activeSub==='cert-apply'?'active':'' ?>" href="<?php echo site_url('admin/cert-apply'); ?>">申请管理</a>
          <a class="menu-item <?php echo $activeSub==='partner-apply'?'active':'' ?>" href="<?php echo site_url('admin/partner-apply'); ?>">合作方申请</a>
          <a class="menu-item <?php echo $activeSub==='publicity'?'active':'' ?>" href="<?php echo site_url('admin/publicity'); ?>">首页公示管理</a>
          <a class="menu-item <?php echo $activeSub==='certifications'?'active':'' ?>" href="<?php echo site_url('admin/certifications'); ?>">认证图片配置</a>
          <a class="menu-item <?php echo $activeSub==='pub-filing'?'active':'' ?>" href="<?php echo site_url('admin/publicity/filing'); ?>">备案公示管理</a>
          <a class="menu-item <?php echo $activeSub==='pub-invalid'?'active':'' ?>" href="<?php echo site_url('admin/publicity/invalid'); ?>">失效网站公示</a>
        </div>
      </div>
      <!-- 聊天室 -->
      <div class="menu-group">
        <div class="menu-item has-sub <?php echo $activeMenu==='chat'?'active':'' ?> <?php echo in_array($activeSub,['chat-messages','chat-banned','chat-words'])?'expanded':'' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span>聊天室</span>
          <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
        <div class="menu-sub <?php echo in_array($activeSub,['chat-messages','chat-banned','chat-words'])?'open':'' ?>">
          <a class="menu-item <?php echo $activeSub==='chat-messages'?'active':'' ?>" href="<?php echo site_url('admin/chat'); ?>">消息管理</a>
          <a class="menu-item <?php echo $activeSub==='chat-banned'?'active':'' ?>" href="<?php echo site_url('admin/chat/banned'); ?>">禁言用户</a>
          <a class="menu-item <?php echo $activeSub==='chat-words'?'active':'' ?>" href="<?php echo site_url('admin/chat/words'); ?>">违禁词</a>
        </div>
      </div>
      <!-- 日志管理 -->
      <div class="menu-group">
        <div class="menu-item has-sub <?php echo $activeMenu==='logs'?'active':'' ?> <?php echo in_array($activeSub,['log-system','log-login','log-operation'])?'expanded':'' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          <span>日志管理</span>
          <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
        <div class="menu-sub <?php echo in_array($activeSub,['log-system','log-login','log-operation'])?'open':'' ?>">
          <a class="menu-item <?php echo $activeSub==='log-system'?'active':'' ?>" href="<?php echo site_url('admin/logs/system'); ?>">系统日志</a>
          <a class="menu-item <?php echo $activeSub==='log-login'?'active':'' ?>" href="<?php echo site_url('admin/logs/login'); ?>">登录日志</a>
          <a class="menu-item <?php echo $activeSub==='log-operation'?'active':'' ?>" href="<?php echo site_url('admin/logs/operation'); ?>">操作日志</a>
        </div>
      </div>
    </nav>
  </aside>
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- 主区 -->
  <div class="admin-main">
    <header class="admin-topbar">
      <div class="tb-left">
        <button class="icon-btn toggle-sidebar" onclick="document.getElementById('adminSidebar').classList.add('open');document.getElementById('sidebarOverlay').classList.add('open');">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <div class="crumb">后台管理 / <b><?php echo e($crumb); ?></b></div>
      </div>
      <div class="tb-right">
        <a class="icon-btn" href="<?php echo site_url(); ?>" target="_blank" title="访问前台">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        </a>
        <button class="icon-btn theme-toggle" onclick="gbToggleTheme()" title="切换主题">
          <svg class="sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
          <svg class="moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        </button>
        <div class="admin-user" onclick="location.href='<?php echo site_url('admin/logout'); ?>'">
          <?php if (!empty($admin['avatar'])): ?><img src="<?php echo asset($admin['avatar']); ?>"><?php else: ?>
          <div style="width:32px;height:32px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;"><?php echo e(strtoupper(mb_substr($admin['username'] ?? 'A', 0, 1))); ?></div>
          <?php endif; ?>
          <span class="name hide-mobile"><?php echo e($admin['username'] ?? '管理员'); ?></span>
        </div>
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

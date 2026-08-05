<?php /** 默认布局 - 顶部导航 + 内容 + 页脚 */
$site = $site ?? [];
$siteName = $site['site_name'] ?? '管备云备案系统';
$siteLogo = $site['site_logo'] ?? '';
$siteTitle = $site['site_title'] ?? $siteName;
$footerIntro = $site['footer_intro'] ?? '';
$icpInfo = $site['icp_info'] ?? '';
$copyright = $site['copyright'] ?? '';
$qqImg = $site['qq_image'] ?? '';
$wechatImg = $site['wechat_image'] ?? '';
$kuaishouImg = $site['kuaishou_image'] ?? '';
$techSupport = $site['tech_support'] ?? '本站由森企动力提供网站建设与技术支持';
$techUrl = $site['tech_support_url'] ?? 'https://sqdl.uiyoi.icu';
$active = $active ?? '';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="keywords" content="<?php echo e($site['site_keywords'] ?? ''); ?>">
<meta name="description" content="<?php echo e($site['site_description'] ?? ''); ?>">
<title><?php echo e($pageTitle ?? $siteTitle); ?></title>
<link rel="icon" href="<?php echo !empty($site['site_favicon']) ? asset($site['site_favicon']) : asset('assets/img/logo.svg'); ?>">
<link rel="stylesheet" href="<?php echo asset('assets/css/theme.css'); ?>">
<link rel="stylesheet" href="<?php echo asset('assets/css/site.css'); ?>">
<script>window.GB_SITE_CONFIG = { captcha_image: '<?php echo !empty($site['captcha_image']) ? asset($site['captcha_image']) : ""; ?>' };</script>
</head>
<body>
<div class="page-loader" id="gb-page-loader"><div class="gb-loading gb-loading-lg"></div><div class="page-loader-text">加载中...</div></div>

<!-- 顶部导航 -->
<header class="site-header">
  <div class="container">
    <div class="header-left" onclick="location.href='<?php echo site_url(); ?>'">
      <div class="header-logo">
        <?php if ($siteLogo): ?><img src="<?php echo asset($siteLogo); ?>" alt="logo"><?php else: ?>
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
        <?php endif; ?>
      </div>
      <div class="header-brand"><?php echo e($siteName); ?><span class="sub">专业 ICP 备案服务</span></div>
    </div>

    <nav class="header-nav hide-mobile">
      <a href="<?php echo site_url(); ?>" class="<?php echo $active === 'home' ? 'active' : ''; ?>">首页</a>
      <a href="<?php echo site_url('query'); ?>" class="<?php echo $active === 'query' ? 'active' : ''; ?>">备案查询</a>
      <a href="<?php echo site_url('feedback'); ?>" class="<?php echo $active === 'feedback' ? 'active' : ''; ?>">意见反馈</a>
      <a href="<?php echo site_url('report'); ?>" class="<?php echo $active === 'report' ? 'active' : ''; ?>">违法举报</a>
      <?php if ($user): ?>
      <a href="<?php echo site_url('user'); ?>" class="<?php echo $active === 'user' ? 'active' : ''; ?>">用户中心</a>
      <?php else: ?>
      <a href="<?php echo site_url('login'); ?>" class="<?php echo $active === 'login' ? 'active' : ''; ?>">登录</a>
      <?php endif; ?>
    </nav>

    <div class="header-right">
      <button class="icon-btn theme-toggle" onclick="gbToggleTheme()" title="切换主题">
        <svg class="sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        <svg class="moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
      </button>
      <button class="icon-btn hide-mobile" onclick="toggleLang()" title="中/英文切换">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
      </button>
      <div class="hamburger" title="菜单">
        <span></span><span></span><span></span>
      </div>
    </div>
  </div>
</header>

<!-- 汉堡菜单 -->
<div class="hamburger-menu">
  <a href="<?php echo site_url(); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg> 首页</a>
  <a href="<?php echo site_url('query'); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg> 备案查询</a>
  <a href="<?php echo site_url('feedback'); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> 意见反馈</a>
  <a href="<?php echo site_url('report'); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg> 违法举报</a>
  <a href="<?php echo site_url('chat'); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> 聊天室</a>
  <a href="<?php echo site_url('publicity/filing'); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"/></svg> 备案公示</a>
  <a href="<?php echo site_url('publicity/invalid'); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> 失效网站公示</a>
  <div class="menu-divider"></div>
  <?php if ($user): ?>
  <a href="<?php echo site_url('user'); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> 用户中心</a>
  <a href="<?php echo site_url('logout'); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg> 退出登录</a>
  <?php else: ?>
  <a href="<?php echo site_url('login'); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg> 登录</a>
  <a href="<?php echo site_url('register'); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg> 注册</a>
  <?php endif; ?>
</div>

<!-- 主体 -->
<main class="site-main fade-in">
  <?php echo $content; ?>
</main>

<!-- 页脚 -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-top">
      <div class="footer-brand">
        <div class="logo-row">
          <?php if ($siteLogo): ?><img src="<?php echo asset($siteLogo); ?>" alt="logo"><?php else: ?>
          <div style="width:36px;height:36px;border-radius:4px;background:var(--primary);display:flex;align-items:center;justify-content:center;"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
          <?php endif; ?>
          <span><?php echo e($siteName); ?></span>
        </div>
        <p><?php echo e($footerIntro); ?></p>
        <div class="icp-row"><?php echo e($icpInfo); ?></div>
        <div class="footer-social">
          <div class="s-btn" onclick="gbShowQR('QQ 客服', '<?php echo $qqImg ? asset($qqImg) : ''; ?>')" title="QQ">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.003 0c4.4 0 7.95 3.55 7.95 7.94 0 .52-.05 1.03-.14 1.52l1.2 1.93c.3.48.5 1.06.2 1.55l-1.06 1.6c-.2.3-.5.45-.84.45l-.78-.04-.5 1.4c-.2.6-.84.97-1.48.9l-2-.2-.7 1.4c-.4.8-1.4 1.1-2.2.7-.1 0-.1 0-.2-.1L12 21l-1.45 1c-.7.5-1.7.3-2.2-.5l-.7-1.4-2 .2c-.65.07-1.28-.3-1.5-.9l-.5-1.4-.78.04c-.34 0-.64-.15-.84-.45l-1.06-1.6c-.3-.5-.1-1.07.2-1.55l1.2-1.93c-.1-.5-.14-1-.14-1.52C4.05 3.55 7.6 0 12 0z"/></svg>
          </div>
          <div class="s-btn" onclick="gbShowQR('微信客服', '<?php echo $wechatImg ? asset($wechatImg) : ''; ?>')" title="微信">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8.69 2C4.44 2 1 4.94 1 8.56c0 2.1 1.16 3.96 2.96 5.16-.14.5-.4 1.4-.44 1.5-.06.16 0 .3.16.3.1 0 1.46-.84 2.04-1.2.86.26 1.8.4 2.78.4.16 0 .32 0 .48-.02-.1-.34-.16-.7-.16-1.06 0-3.04 2.94-5.5 6.56-5.5.16 0 .32 0 .48.02C15.3 4.4 12.32 2 8.69 2zm-2.4 4.2c.5 0 .9.4.9.9s-.4.9-.9.9-.9-.4-.9-.9.4-.9.9-.9zm4.8 0c.5 0 .9.4.9.9s-.4.9-.9.9-.9-.4-.9-.9.4-.9.9-.9zM16.5 9.5c-3.2 0-5.8 2.2-5.8 4.94 0 1.5.8 2.84 2.1 3.74-.1.36-.3 1.04-.34 1.12-.04.12 0 .22.12.22.08 0 1.1-.62 1.52-.92.74.22 1.54.34 2.4.34 3.2 0 5.8-2.2 5.8-4.94S19.7 9.5 16.5 9.5zm-2 3.2c.4 0 .72.32.72.72s-.32.72-.72.72-.72-.32-.72-.72.32-.72.72-.72zm4 0c.4 0 .72.32.72.72s-.32.72-.72.72-.72-.32-.72-.72.32-.72.72-.72z"/></svg>
          </div>
          <div class="s-btn" onclick="gbShowQR('快手官方', '<?php echo $kuaishouImg ? asset($kuaishouImg) : ''; ?>')" title="快手">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16.5 2h-3v12.6c0 1.1-.9 2-2 2s-2-.9-2-2 .9-2 2-2c.2 0 .4 0 .6.1V9.6c-.2 0-.4-.1-.6-.1-2.8 0-5 2.2-5 5s2.2 5 5 5 5-2.2 5-5V8.5c1 .8 2.3 1.3 3.7 1.3V6.7c-1.9 0-3.7-1.6-3.7-3.7V2z"/></svg>
          </div>
        </div>
      </div>
      <div class="footer-col">
        <h4>快捷方式</h4>
        <ul>
          <li><a href="<?php echo site_url(); ?>">网站首页</a></li>
          <li><a href="<?php echo site_url('query'); ?>">备案查询</a></li>
          <li><a href="<?php echo site_url('login'); ?>">用户登录</a></li>
          <li><a href="<?php echo site_url('register'); ?>">用户注册</a></li>
          <li><a href="<?php echo site_url('article/2'); ?>">隐私政策</a></li>
          <li><a href="<?php echo site_url('article/3'); ?>">用户协议</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>帮助中心</h4>
        <ul>
          <li><a href="<?php echo site_url('feedback'); ?>">意见反馈</a></li>
          <li><a href="<?php echo site_url('report'); ?>">违法举报</a></li>
          <li><a href="<?php echo site_url('user'); ?>">用户中心</a></li>
          <li><a href="<?php echo site_url('article/1'); ?>">使用帮助</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>关于我们</h4>
        <ul>
          <li><a href="<?php echo site_url('article/1'); ?>">系统公告</a></li>
          <li><a href="javascript:void(0);" onclick="gbShowQR('QQ 客服', '<?php echo $qqImg ? asset($qqImg) : ''; ?>')">联系客服</a></li>
          <li><a href="<?php echo e($techUrl); ?>" target="_blank">技术支持</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom" style="flex-wrap:wrap;gap:8px;">
      <div style="flex:1;min-width:240px;">
        <div style="line-height:2;">
          <?php echo e($copyright); ?>
        </div>
        <?php
        $showAllIcp = (int)site_config('show_all_icp', 0) === 1;
        $passedFilings = [];
        try {
          if ($showAllIcp) {
            $passedFilings = db()->query(
              "SELECT icp_no, site_name, site_domain FROM " . db()->table('filings')
              . " WHERE status=1 AND icp_no IS NOT NULL AND icp_no != '' ORDER BY id DESC LIMIT 20"
            );
          } elseif ($user && !empty($user['id'])) {
            $passedFilings = user_filing_links($user['id']);
          }
        } catch (Throwable $e) {}

        $filingInfoBase = rtrim(site_config('filing_info_url', ''), '/');
        $icpImgs = icp_prefix_images();

        if ($passedFilings): foreach ($passedFilings as $pf):
          $pureNo = preg_replace('/[^\d]/', '', $pf['icp_no']);
          if ($filingInfoBase && $pureNo) {
            $pfLink = $filingInfoBase . '/' . urlencode($pureNo);
          } else {
            $pfLink = site_url('filing/info/' . urlencode($pf['icp_no']));
          }
        ?>
          <div style="line-height:2;display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
            <?php foreach ($icpImgs as $ii):
              $iiLink = $ii['link'] ?: 'javascript:void(0);';
              $iiTarget = $ii['link'] ? 'target="_blank" rel="noopener"' : '';
            ?>
              <a href="<?php echo e($iiLink); ?>" <?php echo $iiTarget; ?> title="<?php echo e($ii['name']); ?>">
                <span style="display:inline-flex;align-items:center;background:linear-gradient(135deg,#0c2461,#1e3799);border-radius:4px;padding:2px 6px;height:24px;vertical-align:middle;margin-right:2px;">
                  <img src="<?php echo asset($ii['image']); ?>" alt="<?php echo e($ii['name']); ?>" style="height:24px;vertical-align:middle;">
                </span>
              </a>
            <?php endforeach; ?>
            <a href="<?php echo e($pfLink); ?>" target="_blank" rel="noopener" style="color:var(--text-muted, #6b7280); text-decoration:none;">
              管ICP备<?php echo e($pureNo ?: $pf['icp_no']); ?>号
            </a>
          </div>
        <?php endforeach; endif; ?>

        <?php if (!empty($icpInfo)): ?>
          <div style="line-height:2;display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
            <?php foreach ($icpImgs as $ii):
              $iiLink = $ii['link'] ?: 'javascript:void(0);';
              $iiTarget = $ii['link'] ? 'target="_blank" rel="noopener"' : '';
            ?>
              <a href="<?php echo e($iiLink); ?>" <?php echo $iiTarget; ?> title="<?php echo e($ii['name']); ?>">
                <span style="display:inline-flex;align-items:center;background:linear-gradient(135deg,#0c2461,#1e3799);border-radius:4px;padding:2px 6px;height:24px;vertical-align:middle;margin-right:2px;">
                  <img src="<?php echo asset($ii['image']); ?>" alt="<?php echo e($ii['name']); ?>" style="height:24px;vertical-align:middle;">
                </span>
              </a>
            <?php endforeach; ?>
            <?php
            if (preg_match('/管ICP备([^号]+)号/u', $icpInfo, $m)) {
              $pureNo = preg_replace('/[^\d]/', '', $m[1]);
              if ($filingInfoBase && $pureNo) {
                $infoLink = $filingInfoBase . '/' . urlencode($pureNo);
              } else {
                $infoLink = site_url('filing/info/' . urlencode($icpInfo));
              }
              echo '<a href="'.e($infoLink).'" target="_blank" rel="noopener" style="color:var(--text-muted,#6b7280);text-decoration:none;">'.e($icpInfo).'</a>';
            } else {
              echo e($icpInfo);
            }
            ?>
          </div>
        <?php endif; ?>
      </div>
      <a class="tech-link" href="<?php echo e($techUrl); ?>" target="_blank"><?php echo e($techSupport); ?></a>
    </div>
  </div>
</footer>

<!-- 公告弹窗 -->
<?php if (!empty($site['announcement_enabled']) && !empty($site['announcement_content']) && empty($_COOKIE['gb_announce_closed'])): ?>
<div class="announce-modal open" id="announce-modal">
  <div class="announce-box">
    <div class="ah"><h3><?php echo e($site['announcement_title'] ?? '系统公告'); ?></h3><div class="close" onclick="closeAnnounce()"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></div></div>
    <div class="ab"><?php echo $site['announcement_content']; ?></div>
    <div class="af"><button class="btn btn-primary btn-sm" onclick="closeAnnounce()">我知道了</button></div>
  </div>
</div>
<?php endif; ?>

<script>
function toggleLang() {
  var cur = localStorage.getItem('gb-lang') || 'zh';
  var next = cur === 'zh' ? 'en' : 'zh';
  localStorage.setItem('gb-lang', next);
  gbToast.info(next === 'zh' ? '已切换至中文' : 'Switched to English');
}
</script>
<script src="<?php echo asset('assets/js/app.js'); ?>"></script>
<script src="<?php echo asset('assets/js/slider-captcha.js'); ?>"></script>
<?php if (!empty($extraJs)): foreach ($extraJs as $j): ?><script src="<?php echo asset($j); ?>"></script><?php endforeach; endif; ?>
<?php if (!empty($inlineJs)): ?><script><?php echo $inlineJs; ?></script><?php endif; ?>
</body>
</html>

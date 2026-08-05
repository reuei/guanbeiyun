<?php if (!defined('GB_ROOT')) define('GB_ROOT', dirname(__DIR__, 2));
$siteName = '管备云备案系统';
try { $siteName = site_config('site_name') ?: '管备云备案系统'; } catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>404 页面不存在 - <?php echo e($siteName); ?></title>
<link rel="stylesheet" href="<?php echo site_url('public/assets/css/theme.css'); ?>">
<link rel="stylesheet" href="<?php echo site_url('public/assets/css/site.css'); ?>">
<style>
  body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
  .error-page-404 {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    background: linear-gradient(135deg, var(--primary-bg) 0%, var(--bg) 60%);
    position: relative;
    overflow: hidden;
  }
  [data-theme="dark"] .error-page-404,
  @media (prefers-color-scheme: dark) {
    .error-page-404 { background: linear-gradient(135deg, #0d2543 0%, #0f1419 60%); }
  }
  .floating-shape {
    position: absolute;
    border-radius: 50%;
    opacity: 0.4;
    pointer-events: none;
    animation: float 6s ease-in-out infinite;
  }
  .shape-1 {
    width: 120px; height: 120px;
    top: 10%; left: 8%;
    background: radial-gradient(circle, var(--primary) 0%, transparent 70%);
    animation-delay: 0s;
  }
  .shape-2 {
    width: 180px; height: 180px;
    bottom: 15%; right: 10%;
    background: radial-gradient(circle, #a855f7 0%, transparent 70%);
    animation-delay: 1.5s;
  }
  .shape-3 {
    width: 90px; height: 90px;
    top: 55%; left: 5%;
    background: radial-gradient(circle, var(--success) 0%, transparent 70%);
    animation-delay: 3s;
  }
  .shape-4 {
    width: 140px; height: 140px;
    top: 20%; right: 15%;
    background: radial-gradient(circle, var(--warning) 0%, transparent 70%);
    animation-delay: 4.5s;
  }
  @keyframes float {
    0%, 100% { transform: translateY(0px) scale(1); }
    50% { transform: translateY(-25px) scale(1.05); }
  }
  .svg-404-wrap {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 520px;
    margin-bottom: 24px;
  }
  .svg-404-wrap svg {
    width: 100%;
    height: auto;
    display: block;
    filter: drop-shadow(0 10px 40px rgba(0,0,0,0.1));
  }
  .float-slow { animation: floatSlow 5s ease-in-out infinite; transform-origin: center; }
  .float-slower { animation: floatSlow 7s ease-in-out infinite; animation-delay: 0.8s; transform-origin: center; }
  @keyframes floatSlow {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-12px) rotate(1.5deg); }
  }
  .error-content-404 {
    position: relative;
    z-index: 2;
    text-align: center;
    max-width: 560px;
  }
  .error-content-404 h2 {
    font-size: 28px;
    font-weight: 700;
    color: var(--text);
    margin: 0 0 10px;
  }
  .error-content-404 p {
    font-size: 15px;
    color: var(--text-muted);
    margin: 0 0 30px;
    line-height: 1.7;
  }
  .quick-links {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
    margin-bottom: 20px;
  }
  .quick-links .btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 20px;
    border-radius: var(--radius-md, 10px);
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.25s ease;
    border: 1px solid var(--border);
    background: var(--bg-elevated, #fff);
    color: var(--text-2, #555);
    cursor: pointer;
  }
  .quick-links .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    border-color: var(--primary, #2563eb);
    color: var(--primary, #2563eb);
  }
  .quick-links .btn.btn-primary {
    background: var(--primary, #2563eb);
    color: #fff;
    border-color: var(--primary, #2563eb);
  }
  .quick-links .btn.btn-primary:hover {
    background: var(--primary-light, #3b82f6);
    border-color: var(--primary-light, #3b82f6);
    color: #fff;
  }
  .theme-links-404 {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--divider, #e5e7eb);
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
  }
  .theme-links-404 a {
    color: var(--text-muted, #6b7280);
    font-size: 13px;
    text-decoration: none;
    transition: color 0.2s;
  }
  .theme-links-404 a:hover { color: var(--primary, #2563eb); }
</style>
</head>
<body>
<div class="error-page-404">
  <div class="floating-shape shape-1"></div>
  <div class="floating-shape shape-2"></div>
  <div class="floating-shape shape-3"></div>
  <div class="floating-shape shape-4"></div>

  <div class="svg-404-wrap">
    <svg viewBox="0 0 500 320" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient id="grad404_1" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" style="stop-color:var(--primary,#2563eb);stop-opacity:1" />
          <stop offset="100%" style="stop-color:var(--primary-light,#7c3aed);stop-opacity:1" />
        </linearGradient>
        <linearGradient id="grad404_2" x1="0%" y1="0%" x2="100%" y2="0%">
          <stop offset="0%" style="stop-color:#60a5fa;stop-opacity:1" />
          <stop offset="100%" style="stop-color:#a78bfa;stop-opacity:1" />
        </linearGradient>
        <linearGradient id="gradBg" x1="0%" y1="0%" x2="0%" y2="100%">
          <stop offset="0%" style="stop-color:var(--primary-bg,#eff6ff);stop-opacity:0.6" />
          <stop offset="100%" style="stop-color:transparent;stop-opacity:0" />
        </linearGradient>
        <filter id="softShadow">
          <feDropShadow dx="0" dy="6" stdDeviation="8" flood-color="#000" flood-opacity="0.1"/>
        </filter>
      </defs>

      <ellipse cx="250" cy="280" rx="180" ry="16" fill="url(#gradBg)" />

      <g class="float-slow">
        <text x="60" y="200" font-family="Arial Black, Arial, sans-serif" font-size="180" font-weight="900" fill="url(#grad404_1)" filter="url(#softShadow)">4</text>
      </g>

      <g class="float-slower" style="transform-box: fill-box;">
        <g transform="translate(190, 70)">
          <circle cx="60" cy="90" r="70" fill="url(#grad404_2)" opacity="0.95" filter="url(#softShadow)"/>
          <circle cx="60" cy="90" r="48" fill="none" stroke="#fff" stroke-width="4" opacity="0.9"/>
          <circle cx="44" cy="78" r="7" fill="#fff" opacity="0.95"/>
          <circle cx="76" cy="78" r="7" fill="#fff" opacity="0.95"/>
          <circle cx="45" cy="78" r="3.5" fill="#1e293b"/>
          <circle cx="77" cy="78" r="3.5" fill="#1e293b"/>
          <path d="M 40 105 Q 60 120 80 105" stroke="#fff" stroke-width="4" fill="none" stroke-linecap="round"/>
          <path d="M 30 55 L 20 35" stroke="#ef4444" stroke-width="4" stroke-linecap="round"/>
          <path d="M 90 55 L 100 35" stroke="#ef4444" stroke-width="4" stroke-linecap="round"/>
        </g>
      </g>

      <g class="float-slow" style="animation-delay: 0.4s;">
        <text x="300" y="200" font-family="Arial Black, Arial, sans-serif" font-size="180" font-weight="900" fill="url(#grad404_1)" filter="url(#softShadow)">4</text>
      </g>

      <g opacity="0.7">
        <circle cx="40" cy="60" r="5" fill="var(--primary,#2563eb)" class="float-slower"/>
        <circle cx="460" cy="80" r="4" fill="#a78bfa" class="float-slow"/>
        <circle cx="430" cy="240" r="6" fill="#34d399" class="float-slower"/>
        <circle cx="70" cy="260" r="4" fill="#fbbf24" class="float-slow"/>
      </g>
    </svg>
  </div>

  <div class="error-content-404">
    <h2>哎呀，页面走丢了</h2>
    <p>您访问的页面不存在、已被移除或临时不可用。<br/>不如先去其他地方看看？</p>
    <div class="quick-links">
      <a class="btn btn-primary" href="<?php echo site_url(); ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        首页
      </a>
      <a class="btn" href="<?php echo site_url('query'); ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        备案查询
      </a>
      <a class="btn" href="<?php echo site_url('feedback'); ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        反馈
      </a>
      <a class="btn" href="<?php echo site_url('login'); ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        登录
      </a>
    </div>
    <div class="theme-links-404">
      <a href="<?php echo site_url(); ?>">返回首页</a>
      <a href="javascript:history.back()">返回上一页</a>
      <a href="<?php echo site_url('query'); ?>">备案查询</a>
    </div>
  </div>
</div>
<script src="<?php echo site_url('public/assets/js/app.js'); ?>"></script>
</body>
</html>

<?php if (!defined('GB_ROOT')) define('GB_ROOT', dirname(__DIR__, 2));
$siteName = '管备云备案系统';
try { $siteName = site_config('site_name') ?: '管备云备案系统'; } catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>服务器错误 - <?php echo e($siteName); ?></title>
<link rel="stylesheet" href="<?php echo site_url('public/assets/css/theme.css'); ?>">
<link rel="stylesheet" href="<?php echo site_url('public/assets/css/site.css'); ?>">
</head>
<body>
<div class="error-page">
  <div class="code">500</div>
  <h2>服务器开小差了</h2>
  <p>系统遇到一些问题，请稍后再试</p>
  <div style="display:flex;gap:12px;justify-content:center;">
    <a class="btn btn-primary" href="<?php echo site_url(); ?>">返回首页</a>
    <a class="btn" href="javascript:location.reload()">刷新重试</a>
  </div>
</div>
<script src="<?php echo site_url('public/assets/js/app.js'); ?>"></script>
</body>
</html>

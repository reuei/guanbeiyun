<?php /** 管理员登录页 (独立页，不使用后台布局) */
$siteName = '管备云备案系统';
try { $siteName = site_config('site_name') ?: '管备云备案系统'; } catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>管理员登录 - <?php echo e($siteName); ?></title>
<link rel="stylesheet" href="<?php echo site_url('public/assets/css/theme.css'); ?>">
<link rel="stylesheet" href="<?php echo site_url('public/assets/css/site.css'); ?>">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card fade-in">
    <div class="auth-logo">
      <div class="logo">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
      </div>
      <h2>管理后台</h2>
      <div class="auth-sub"><?php echo e($siteName); ?></div>
    </div>
    <form id="adminLoginForm" onsubmit="return doLogin(event)">
      <div class="form-group">
        <label class="form-label">管理员账号</label>
        <div class="input-group">
          <span class="input-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
          <input type="text" class="form-control" id="username" placeholder="请输入管理员账号" autocomplete="username">
        </div>
        <div class="form-error"></div>
      </div>
      <div class="form-group">
        <label class="form-label">密码</label>
        <div class="input-group">
          <span class="input-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
          <input type="password" class="form-control" id="password" placeholder="请输入密码" autocomplete="current-password">
          <span class="input-right" onclick="var i=document.getElementById('password');i.type=i.type==='password'?'text':'password';">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </span>
        </div>
        <div class="form-error"></div>
      </div>
      <div class="form-group">
        <label class="form-label">人机验证</label>
        <div class="slider-captcha" data-slider-captcha data-on-success="onCaptchaOk"></div>
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg" id="submitBtn">进入后台</button>
    </form>
    <div class="auth-footer">
      <a href="<?php echo site_url(); ?>">← 返回首页</a>
    </div>
  </div>
</div>
<script src="<?php echo site_url('public/assets/js/app.js'); ?>"></script>
<script src="<?php echo site_url('public/assets/js/slider-captcha.js'); ?>"></script>
<script>
var captchaOk = false;
function onCaptchaOk(){ captchaOk = true; }
(function(){
  var u=document.getElementById('username'),p=document.getElementById('password');
  u.addEventListener('input',function(){ if(u.value.trim()) gbValidate.setSuccess(u); else gbValidate.setError(u,'请输入账号'); });
  p.addEventListener('input',function(){ if(p.value) gbValidate.setSuccess(p); else gbValidate.setError(p,'请输入密码'); });
})();
function doLogin(e){
  e.preventDefault();
  var u=document.getElementById('username'),p=document.getElementById('password');
  if(!u.value.trim()){gbValidate.setError(u,'请输入账号');return false;}
  if(!p.value){gbValidate.setError(p,'请输入密码');return false;}
  if(!captchaOk){gbToast.warning('请先完成人机验证');return false;}
  var btn=document.getElementById('submitBtn');
  btn.disabled=true;btn.innerHTML='<span class="gb-loading gb-loading-sm"></span> 登录中...';
  gbAjax({
    method:'POST',url:'<?php echo site_url('admin/login'); ?>',
    data:{username:u.value.trim(),password:p.value,captcha_verified:1},
    success:function(res){
      if(res.code===0){ gbToast.success(res.msg); setTimeout(function(){location.href=res.data.redirect;},600); }
      else { btn.disabled=false; btn.innerHTML='进入后台'; }
    },
    fail:function(){ btn.disabled=false; btn.innerHTML='进入后台'; }
  });
  return false;
}
</script>
</body>
</html>

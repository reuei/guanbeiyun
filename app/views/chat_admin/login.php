<?php /** 聊天室管理登录页 (独立完整 HTML) */
$site = $site ?? [];
$siteName = $site['site_name'] ?? '管备云备案系统';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>聊天室管理登录 - <?php echo e($siteName); ?></title>
<link rel="icon" href="<?php echo !empty($site['site_favicon']) ? asset($site['site_favicon']) : asset('assets/img/logo.svg'); ?>">
<link rel="stylesheet" href="<?php echo asset('assets/css/theme.css'); ?>">
<link rel="stylesheet" href="<?php echo asset('assets/css/site.css'); ?>">
<style>
.ca-login-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #1e40af 100%); }
.ca-login-card { background: var(--card-bg, #fff); border-radius: 16px; padding: 40px 36px; max-width: 400px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
.ca-login-logo { width: 56px; height: 56px; border-radius: 14px; background: var(--primary, #3b82f6); margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; color: #fff; }
.ca-login-title { font-size: 22px; font-weight: 700; text-align: center; color: var(--text, #1f2937); margin-bottom: 6px; }
.ca-login-sub { font-size: 13px; color: var(--text-muted, #6b7280); text-align: center; margin-bottom: 24px; }
.ca-form-group { margin-bottom: 14px; }
.ca-form-label { display: block; font-size: 13px; color: var(--text-2, #4b5563); margin-bottom: 6px; }
.ca-form-input { width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border, #e5e7eb); background: var(--card-bg, #fff); color: var(--text, #1f2937); font-size: 14px; box-sizing: border-box; }
.ca-form-input:focus { outline: none; border-color: var(--primary, #3b82f6); box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
.ca-login-btn { width: 100%; padding: 11px; border-radius: 8px; border: none; background: var(--primary, #3b82f6); color: #fff; font-size: 14px; font-weight: 600; cursor: pointer; transition: opacity .2s; margin-top: 8px; }
.ca-login-btn:hover { opacity: .9; }
.ca-login-btn:disabled { opacity: .5; cursor: not-allowed; }
.ca-login-tip { margin-top: 16px; padding: 10px 12px; background: var(--bg-soft, #f5f7fa); border-radius: 6px; font-size: 12px; color: var(--text-muted, #6b7280); line-height: 1.6; }
.ca-login-back { display: block; text-align: center; margin-top: 16px; font-size: 13px; color: var(--primary, #3b82f6); text-decoration: none; }
</style>
<div class="ca-login-wrap">
  <div class="ca-login-card">
    <div class="ca-login-logo">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
    </div>
    <div class="ca-login-title">聊天室管理后台</div>
    <div class="ca-login-sub"><?php echo e($site['site_name'] ?? '管备云备案系统'); ?></div>
    <form id="caLoginForm" onsubmit="return caDoLogin(event)">
      <div class="ca-form-group">
        <label class="ca-form-label">用户名</label>
        <input class="ca-form-input" type="text" name="username" id="caUsername" required autofocus placeholder="请输入用户名">
      </div>
      <div class="ca-form-group">
        <label class="ca-form-label">密码</label>
        <input class="ca-form-input" type="password" name="password" id="caPassword" required placeholder="请输入密码">
      </div>
      <button type="submit" class="ca-login-btn" id="caLoginBtn">登录</button>
    </form>
    <div class="ca-login-tip">
      <b>提示：</b><br>
      1. 可使用<b>平台后台管理员账号</b>或<b>前台聊天室管理员账号</b>登录;<br>
      2. 平台后台管理员默认拥有<b>平台管理</b>权限(最高级);<br>
      3. 超管/平台管理可在「用户头衔」中任命管理员。
    </div>
    <a class="ca-login-back" href="<?php echo site_url('chat'); ?>">← 返回聊天室</a>
  </div>
</div>
<script>
function caDoLogin(e){
  e.preventDefault();
  var u = document.getElementById('caUsername').value.trim();
  var p = document.getElementById('caPassword').value;
  if(!u || !p){ gbToast.warning('请输入用户名和密码'); return false; }
  var btn = document.getElementById('caLoginBtn');
  btn.disabled = true; btn.innerHTML = '登录中...';
  gbAjax({method:'POST', url:'<?php echo site_url("admins/doLogin"); ?>', data:{username:u, password:p},
    success:function(res){
      if(res.code===0){
        gbToast.success('登录成功');
        setTimeout(function(){ location.href = res.data.redirect || '<?php echo site_url("admins"); ?>'; }, 500);
      } else {
        gbToast.error(res.msg || '登录失败');
      }
    },
    complete:function(){ btn.disabled=false; btn.innerHTML='登录'; }
  });
  return false;
}
</script>
<script src="<?php echo asset('assets/js/app.js'); ?>"></script>
<div class="toast-container" id="gb-toast-container"></div>
</body>
</html>

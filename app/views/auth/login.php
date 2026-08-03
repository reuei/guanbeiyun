<?php /** 登录页 */ ?>
<div class="auth-wrap">
  <div class="auth-card fade-in">
    <div class="auth-logo">
      <div class="logo">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
      </div>
      <h2>欢迎回来</h2>
      <div class="auth-sub">登录您的管备云账号</div>
    </div>

    <div class="auth-tabs">
      <a href="<?php echo site_url('login'); ?>" class="active">登录</a>
      <a href="<?php echo site_url('register'); ?>">注册</a>
    </div>

    <form id="loginForm" onsubmit="return doLogin(event)">
      <div class="form-group">
        <label class="form-label">账号 / 邮箱</label>
        <div class="input-group">
          <span class="input-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
          <input type="text" class="form-control" name="username" id="username" placeholder="请输入用户名或邮箱" autocomplete="username">
        </div>
        <div class="form-error"></div>
      </div>

      <div class="form-group">
        <label class="form-label">密码</label>
        <div class="input-group">
          <span class="input-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
          <input type="password" class="form-control" name="password" id="password" placeholder="请输入密码" autocomplete="current-password">
          <span class="input-right" onclick="togglePwd('password', this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </span>
        </div>
        <div class="form-error"></div>
      </div>

      <!-- 人机验证滑块 -->
      <div class="form-group">
        <label class="form-label">人机验证</label>
        <div class="slider-captcha" data-slider-captcha data-on-success="onCaptchaOk"></div>
      </div>

      <div class="form-group" style="display:flex;justify-content:space-between;align-items:center;">
        <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text-2);cursor:pointer;">
          <input type="checkbox" name="remember" style="width:14px;height:14px;"> 记住我
        </label>
        <a href="<?php echo site_url('register'); ?>" style="font-size:13px;">还没有账号？立即注册</a>
      </div>

      <button type="submit" class="btn btn-primary btn-block btn-lg" id="submitBtn">登 录</button>
    </form>

    <div class="auth-footer">
      登录即表示同意 <a href="<?php echo site_url('article/2'); ?>">隐私政策</a> 与 <a href="<?php echo site_url('article/3'); ?>">用户协议</a>
    </div>
  </div>
</div>

<script>
var captchaOk = false;
function onCaptchaOk() { captchaOk = true; }
function togglePwd(id, el) {
  var inp = document.getElementById(id);
  inp.type = inp.type === 'password' ? 'text' : 'password';
}

// 实时校验
(function(){
  var username = document.getElementById('username');
  var password = document.getElementById('password');

  function checkUsername() {
    var v = username.value.trim();
    if (!v) { gbValidate.setError(username, '请输入用户名或邮箱'); return false; }
    if (v.length < 2) { gbValidate.setError(username, '账号至少2位'); return false; }
    gbValidate.setSuccess(username);
    return true;
  }
  function checkPassword() {
    var v = password.value;
    if (!v) { gbValidate.setError(password, '请输入密码'); return false; }
    if (v.length < 6) { gbValidate.setError(password, '密码至少6位'); return false; }
    gbValidate.setSuccess(password);
    return true;
  }
  username.addEventListener('input', checkUsername);
  username.addEventListener('blur', checkUsername);
  password.addEventListener('input', checkPassword);
  password.addEventListener('blur', checkPassword);

  window.checkUsername = checkUsername;
  window.checkPassword = checkPassword;
})();

function doLogin(e) {
  e.preventDefault();
  if (!checkUsername() || !checkPassword()) return false;
  if (!captchaOk) { gbToast.warning('请先完成人机验证'); return false; }
  var btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="gb-loading gb-loading-sm"></span> 登录中...';
  gbAjax({
    method: 'POST',
    url: '<?php echo site_url('login'); ?>',
    data: {
      username: document.getElementById('username').value.trim(),
      password: document.getElementById('password').value,
      captcha_verified: captchaOk ? 1 : 0,
      _csrf: '<?php echo csrf_token(); ?>'
    },
    success: function(res) {
      if (res.code === 0) {
        gbToast.success(res.msg || '登录成功');
        setTimeout(function(){ location.href = res.data.redirect; }, 600);
      } else {
        gbToast.error(res.msg || '登录失败');
        btn.disabled = false;
        btn.innerHTML = '登 录';
      }
    },
    fail: function() {
      btn.disabled = false;
      btn.innerHTML = '登 录';
    }
  });
  return false;
}
</script>

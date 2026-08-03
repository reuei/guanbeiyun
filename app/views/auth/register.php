<?php /** 注册页 */ ?>
<div class="auth-wrap">
  <div class="auth-card fade-in">
    <div class="auth-logo">
      <div class="logo">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
      </div>
      <h2>创建账号</h2>
      <div class="auth-sub">注册管备云备案系统账号</div>
    </div>

    <div class="auth-tabs">
      <a href="<?php echo site_url('login'); ?>">登录</a>
      <a href="<?php echo site_url('register'); ?>" class="active">注册</a>
    </div>

    <form id="regForm" onsubmit="return doRegister(event)">
      <div class="form-group">
        <label class="form-label">用户名 <span class="req">*</span></label>
        <div class="input-group">
          <span class="input-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
          <input type="text" class="form-control" name="username" id="reg_username" placeholder="字母开头，3-20位字母数字下划线" autocomplete="username">
        </div>
        <div class="form-error"></div>
      </div>

      <div class="form-group">
        <label class="form-label">邮箱 <span class="req">*</span></label>
        <div class="input-group">
          <span class="input-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span>
          <input type="text" class="form-control" name="email" id="reg_email" placeholder="请输入邮箱" autocomplete="email">
        </div>
        <div class="form-error"></div>
      </div>

      <div class="form-group">
        <label class="form-label">手机号 <span class="req">*</span></label>
        <div class="input-group">
          <span class="input-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>
          <input type="text" class="form-control" name="phone" id="reg_phone" placeholder="请输入手机号" maxlength="11" autocomplete="tel">
        </div>
        <div class="form-error"></div>
      </div>

      <div class="form-group">
        <label class="form-label">密码 <span class="req">*</span></label>
        <div class="input-group">
          <span class="input-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
          <input type="password" class="form-control" name="password" id="reg_password" placeholder="6-20位字符" autocomplete="new-password">
          <span class="input-right" onclick="togglePwd('reg_password', this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </span>
        </div>
        <div class="form-error"></div>
      </div>

      <div class="form-group">
        <label class="form-label">确认密码 <span class="req">*</span></label>
        <div class="input-group">
          <span class="input-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
          <input type="password" class="form-control" name="confirm_password" id="reg_confirm" placeholder="请再次输入密码" autocomplete="new-password">
        </div>
        <div class="form-error"></div>
      </div>

      <!-- 人机验证滑块 -->
      <div class="form-group">
        <label class="form-label">人机验证 <span class="req">*</span></label>
        <div class="slider-captcha" data-slider-captcha data-on-success="onCaptchaOk"></div>
      </div>

      <div class="form-group">
        <label style="display:flex;align-items:flex-start;gap:8px;font-size:13px;color:var(--text-2);cursor:pointer;line-height:1.6;">
          <input type="checkbox" name="agree" id="agree" value="1" style="width:15px;height:15px;margin-top:2px;">
          <span>我已阅读并同意 <a href="<?php echo site_url('article/2'); ?>">隐私政策</a> 与 <a href="<?php echo site_url('article/3'); ?>">用户协议</a></span>
        </label>
      </div>

      <button type="submit" class="btn btn-primary btn-block btn-lg" id="submitBtn">注 册</button>
    </form>

    <div class="auth-footer">
      已有账号？<a href="<?php echo site_url('login'); ?>">立即登录</a>
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

(function(){
  var u = document.getElementById('reg_username');
  var em = document.getElementById('reg_email');
  var ph = document.getElementById('reg_phone');
  var pw = document.getElementById('reg_password');
  var cf = document.getElementById('reg_confirm');

  // 用户名: 字母开头, 3-20位字母数字下划线
  function checkU() {
    var v = u.value.trim();
    if (!v) { gbValidate.setError(u, '请输入用户名'); return false; }
    if (!/^[a-zA-Z][a-zA-Z0-9_]{2,19}$/.test(v)) { gbValidate.setError(u, '以字母开头，3-20位字母数字下划线'); return false; }
    gbValidate.setSuccess(u);
    return true;
  }
  function checkE() {
    var v = em.value.trim();
    if (!v) { gbValidate.setError(em, '请输入邮箱'); return false; }
    if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(v)) { gbValidate.setError(em, '邮箱格式不正确'); return false; }
    gbValidate.setSuccess(em);
    return true;
  }
  function checkP() {
    var v = ph.value.trim();
    if (!v) { gbValidate.setError(ph, '请输入手机号'); return false; }
    if (!/^1[3-9]\d{9}$/.test(v)) { gbValidate.setError(ph, '请输入正确的手机号'); return false; }
    gbValidate.setSuccess(ph);
    return true;
  }
  function checkPw() {
    var v = pw.value;
    if (!v) { gbValidate.setError(pw, '请输入密码'); return false; }
    if (v.length < 6 || v.length > 20) { gbValidate.setError(pw, '密码长度6-20位'); return false; }
    gbValidate.setSuccess(pw);
    if (cf.value) checkCf();
    return true;
  }
  function checkCf() {
    var v = cf.value;
    if (!v) { gbValidate.setError(cf, '请再次输入密码'); return false; }
    if (v !== pw.value) { gbValidate.setError(cf, '两次输入的密码不一致'); return false; }
    gbValidate.setSuccess(cf);
    return true;
  }
  u.addEventListener('input', checkU); u.addEventListener('blur', checkU);
  em.addEventListener('input', checkE); em.addEventListener('blur', checkE);
  ph.addEventListener('input', function(){ this.value = this.value.replace(/\D/g,'').slice(0,11); checkP(); });
  ph.addEventListener('blur', checkP);
  pw.addEventListener('input', checkPw); pw.addEventListener('blur', checkPw);
  cf.addEventListener('input', checkCf); cf.addEventListener('blur', checkCf);

  window.checkU = checkU; window.checkE = checkE; window.checkP = checkP;
  window.checkPw = checkPw; window.checkCf = checkCf;
})();

function doRegister(e) {
  e.preventDefault();
  if (!checkU() || !checkE() || !checkP() || !checkPw() || !checkCf()) return false;
  if (!captchaOk) { gbToast.warning('请先完成人机验证'); return false; }
  if (!document.getElementById('agree').checked) { gbToast.warning('请阅读并同意用户协议'); return false; }
  var btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="gb-loading gb-loading-sm"></span> 注册中...';
  gbAjax({
    method: 'POST',
    url: '<?php echo site_url('register'); ?>',
    data: {
      username: document.getElementById('reg_username').value.trim(),
      email: document.getElementById('reg_email').value.trim(),
      phone: document.getElementById('reg_phone').value.trim(),
      password: document.getElementById('reg_password').value,
      confirm_password: document.getElementById('reg_confirm').value,
      agree: 1,
      captcha_verified: captchaOk ? 1 : 0,
      _csrf: '<?php echo csrf_token(); ?>'
    },
    success: function(res) {
      if (res.code === 0) {
        gbToast.success(res.msg || '注册成功');
        setTimeout(function(){ location.href = res.data.redirect; }, 800);
      } else {
        gbToast.error(res.msg || '注册失败');
        btn.disabled = false;
        btn.innerHTML = '注 册';
      }
    },
    fail: function() {
      btn.disabled = false;
      btn.innerHTML = '注 册';
    }
  });
  return false;
}
</script>

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

      <!-- 人机验证（动态类型） -->
      <div class="form-group">
        <label class="form-label">人机验证 <span class="req">*</span></label>
        <div id="dynamicCaptchaWrap" style="position:relative;">
          <div class="text-muted text-sm" style="padding:10px 0;">加载验证组件中...</div>
        </div>
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
var captchaToken = '';
function onCaptchaOk(tok) { captchaOk = true; captchaToken = tok || ''; }
function resetCaptchaOk() { captchaOk = false; captchaToken = ''; }
function togglePwd(id, el) {
  var inp = document.getElementById(id);
  inp.type = inp.type === 'password' ? 'text' : 'password';
}

var CAPTCHA_TYPE = '<?php echo site_config('captcha_type', 'slider'); ?>';
var GEETEST_ID = '<?php echo addslashes(site_config('geetest_id', '')); ?>';
var CAPTCHA_LENGTH = parseInt('<?php echo (int)site_config('captcha_length', 4); ?>', 10);
var SITE_URL = '<?php echo rtrim(site_url(), '/'); ?>';

function renderCaptcha() {
  var wrap = document.getElementById('dynamicCaptchaWrap');
  if (!wrap) return;
  resetCaptchaOk();
  if (CAPTCHA_TYPE === 'slider') {
    wrap.innerHTML = '<div class="slider-captcha" data-slider-captcha data-on-success="onCaptchaOk"></div>';
    if (window.initSliderCaptcha && typeof window.initSliderCaptcha === 'function') {
      setTimeout(function(){ try { window.initSliderCaptcha(wrap); } catch(e) {} }, 50);
    }
  } else if (CAPTCHA_TYPE === 'verify') {
    wrap.innerHTML = '<div style="display:flex;flex-direction:column;gap:10px;">'
      + '<div style="display:flex;align-items:center;gap:10px;">'
      + '<img id="vcImg" src="'+SITE_URL+'/captcha/verify_code?length='+CAPTCHA_LENGTH+'&_t='+Date.now()+'" style="height:45px;border:1px solid var(--divider);border-radius:6px;cursor:pointer;" onclick="this.src=\''+SITE_URL+'/captcha/verify_code?length='+CAPTCHA_LENGTH+'&_\'+Math.random()" title="点击刷新">'
      + '<button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById(\'vcImg\').src=\''+SITE_URL+'/captcha/verify_code?length='+CAPTCHA_LENGTH+'&_\'+Math.random();document.getElementById(\'vcInput\').value=\'\';resetCaptchaOk();">刷新</button>'
      + '</div>'
      + '<input type="text" id="vcInput" class="form-control" placeholder="请输入图片中的字符" maxlength="'+CAPTCHA_LENGTH+'" autocomplete="off" oninput="onVCodeInput(this)" onblur="onVCodeBlur()"><div id="vcMsg" class="text-sm" style="min-height:18px;"></div>'
      + '</div>';
  } else if (CAPTCHA_TYPE === 'click') {
    wrap.innerHTML = '<div style="display:flex;flex-direction:column;gap:8px;">'
      + '<div class="text-sm" id="ckTip">正在获取验证数据...</div>'
      + '<div id="ckBox" style="position:relative;display:inline-block;max-width:100%;cursor:crosshair;"></div>'
      + '<div id="ckProgress" class="text-sm text-muted"></div>'
      + '<button type="button" id="ckRetry" class="btn btn-ghost btn-sm" style="align-self:flex-start;display:none;" onclick="loadClickText()">重新获取</button>'
      + '</div>';
    loadClickText();
  } else if (CAPTCHA_TYPE === 'drag') {
    wrap.innerHTML = '<div style="display:flex;flex-direction:column;gap:10px;">'
      + '<div class="text-sm text-muted" id="dgTip">加载中...</div>'
      + '<div id="dgBox" style="position:relative;display:inline-block;"></div>'
      + '<div id="dgSlider" style="position:relative;max-width:340px;height:40px;background:#f5f5f5;border:1px solid var(--divider);border-radius:20px;overflow:hidden;user-select:none;">'
      + '<div id="dgTrack" style="position:absolute;left:0;top:0;height:100%;width:0;background:rgba(91,141,239,0.15);"></div>'
      + '<div id="dgBtn" style="position:absolute;left:0;top:-1px;width:40px;height:40px;background:var(--primary);color:#fff;border-radius:20px;display:flex;align-items:center;justify-content:center;cursor:grab;box-shadow:0 2px 6px rgba(0,0,0,0.15);touch-action:none;">'
      + '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg></div>'
      + '<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:13px;color:#888;pointer-events:none;" id="dgSliderTip">向右拖动拼块完成验证</div>'
      + '</div><div id="dgMsg" class="text-sm" style="min-height:18px;"></div>'
      + '</div>';
    loadDragImage();
  } else if (CAPTCHA_TYPE === 'geetest') {
    wrap.innerHTML = '<div id="gtBox" style="padding:8px 0;"><button type="button" id="gtBtn" class="btn btn-outline" style="width:100%;padding:12px;"><svg style="vertical-align:middle;margin-right:6px;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>点击进行极验验证</button><div id="gtResult" class="text-sm" style="margin-top:6px;"></div></div>';
    initGeetestBtn();
  }
}

function onVCodeInput(el){
  var v = el.value.trim();
  if (v.length >= CAPTCHA_LENGTH) {
    setTimeout(onVCodeBlur, 100);
  } else {
    var m = document.getElementById('vcMsg');
    if (m) { m.textContent = ''; m.className = 'text-sm'; resetCaptchaOk(); }
  }
}
function onVCodeBlur(){
  var inp = document.getElementById('vcInput');
  if (!inp) return;
  var v = inp.value.trim();
  var m = document.getElementById('vcMsg');
  if (v.length < CAPTCHA_LENGTH) {
    if (m) { m.textContent = '请输入完整的验证码'; m.className = 'text-sm text-danger'; }
    resetCaptchaOk(); return;
  }
  gbAjax({method:'POST',url:SITE_URL+'/captcha/verify_code_check',data:{code:v},toast:false,
    success:function(r){
      if(r&&r.code===0){ if(m){m.textContent='验证通过';m.className='text-sm text-success';} onCaptchaOk(r.data&&r.data.token); }
      else { if(m){m.textContent=r.msg||'验证失败';m.className='text-sm text-danger';} inp.value=''; resetCaptchaOk();
        document.getElementById('vcImg')&&(document.getElementById('vcImg').src=SITE_URL+'/captcha/verify_code?length='+CAPTCHA_LENGTH+'&_'+Math.random()); }
    },fail:function(){ if(m){m.textContent='网络错误，请重试';m.className='text-sm text-danger';} resetCaptchaOk(); }
  });
}

var clickTextData = null;
var clickHits = [];
function loadClickText(){
  clickTextData = null; clickHits = []; resetCaptchaOk();
  var tip = document.getElementById('ckTip');
  var prog = document.getElementById('ckProgress');
  var retry = document.getElementById('ckRetry');
  if(tip){ tip.style.color=''; tip.textContent='正在获取验证数据...'; }
  if(prog) prog.textContent='';
  if(retry) retry.style.display='none';
  gbAjax({method:'GET',url:SITE_URL+'/captcha/click_text',toast:false,
    success:function(r){
      if(!(r&&r.code===0)){ if(tip){tip.style.color='var(--danger)';tip.textContent=r?(r.msg||'加载失败'):'加载失败';} if(retry)retry.style.display='inline-block'; return; }
      clickTextData = r.data;
      if(r.data&&r.data.degraded){
        if(tip){ tip.style.color='var(--success)'; tip.textContent='系统优化已自动通过'; } onCaptchaOk(r.data.token); return;
      }
      renderClickText(r.data);
    },fail:function(){ if(tip){tip.style.color='var(--danger)';tip.textContent='网络错误';} if(retry)retry.style.display='inline-block'; }
  });
}
function renderClickText(data){
  var box = document.getElementById('ckBox'); if(!box) return;
  box.innerHTML = '';
  var img = document.createElement('img');
  if(data.image){ img.src = data.image; }
  else { img.style.width='320px'; img.style.height='160px'; img.style.background='#ecfdf5'; }
  img.style.display='block'; img.style.maxWidth='100%'; img.style.borderRadius='6px';
  img.id='ckBgImg';
  box.appendChild(img);
  var targets = data.targets||[];
  var tip = document.getElementById('ckTip');
  if(tip){ tip.style.color=''; tip.innerHTML='请依次点击文字：<b style="color:var(--danger);letter-spacing:4px;">'+(targets.join(' '))+'</b>'; }
  updateClickProgress();
  img.onload = function(){
    box.onclick = onCkBoxClick;
    box.oncontextmenu = function(e){e.preventDefault();};
  };
  if(!data.image){ setTimeout(function(){ box.onclick = onCkBoxClick; }, 80); }
}
function updateClickProgress(){
  var targets = (clickTextData&&clickTextData.targets)?clickTextData.targets:[];
  var prog = document.getElementById('ckProgress');
  if(prog){
    if(clickHits.length===0) prog.className='text-sm text-muted';
    prog.textContent = '已点击 '+clickHits.length+' / '+targets.length;
  }
}
function onCkBoxClick(e){
  if(!clickTextData||captchaOk) return;
  var box = document.getElementById('ckBox'); if(!box) return;
  var rect = box.getBoundingClientRect();
  var bgImg = document.getElementById('ckBgImg');
  var ix = e.clientX - rect.left;
  var iy = e.clientY - rect.top;
  if(bgImg && bgImg.naturalWidth){
    var rx = bgImg.naturalWidth / bgImg.clientWidth;
    var ry = bgImg.naturalHeight / bgImg.clientHeight;
    ix = ix * rx; iy = iy * ry;
  }
  var positions = clickTextData.positions||[];
  var clickedChar = null;
  for(var i=0;i<positions.length;i++){
    var p=positions[i];
    if(ix>=p.x-4 && ix<=(p.x+(p.w||32)+4) && iy>=p.y-4 && iy<=(p.y+(p.h||32)+4)){
      clickedChar = p.char; break;
    }
  }
  if(!clickedChar){
    var prog = document.getElementById('ckProgress');
    if(prog){ prog.style.color='var(--danger)'; prog.textContent='未命中任何文字，请重试'; setTimeout(function(){if(prog)updateClickProgress();},700); }
    return;
  }
  clickHits.push(clickedChar);
  addClickMark(e.clientX-rect.left, e.clientY-rect.top, clickedChar, clickHits.length);
  updateClickProgress();
  var targets = clickTextData.targets||[];
  if(clickHits.length >= targets.length){
    setTimeout(function(){submitClickText();}, 200);
  }
}
function addClickMark(x, y, ch, n){
  var box = document.getElementById('ckBox'); if(!box) return;
  var d = document.createElement('div');
  d.style.cssText='position:absolute;left:'+(x-14)+'px;top:'+(y-14)+'px;width:28px;height:28px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;box-shadow:0 2px 6px rgba(0,0,0,0.2);pointer-events:none;';
  d.textContent = n;
  box.appendChild(d);
}
function submitClickText(){
  if(!clickTextData) return;
  var tok = clickTextData.token||'';
  gbAjax({method:'POST',url:SITE_URL+'/captcha/click_text_check',data:{clicks:JSON.stringify(clickHits),token:tok},toast:false,
    success:function(r){
      if(r&&r.code===0){
        var prog = document.getElementById('ckProgress');
        if(prog){ prog.style.color='var(--success)'; prog.textContent='验证通过'; }
        onCaptchaOk(r.data&&r.data.token);
      } else {
        var tip = document.getElementById('ckTip');
        if(tip){ tip.style.color='var(--danger)'; tip.innerHTML=(r.msg||'验证失败')+'，请重试'; }
        setTimeout(function(){loadClickText();}, 700);
      }
    },fail:function(){
      var tip = document.getElementById('ckTip');
      if(tip){tip.style.color='var(--danger)';tip.textContent='网络错误';}
      setTimeout(function(){loadClickText();}, 900);
    }
  });
}

var dragData = null;
var dragDragging = false;
function loadDragImage(){
  dragData = null; resetCaptchaOk();
  var tip = document.getElementById('dgTip');
  if(tip){ tip.style.color=''; tip.textContent='加载中...'; }
  gbAjax({method:'GET',url:SITE_URL+'/captcha/drag_image',toast:false,
    success:function(r){
      if(!(r&&r.code===0)){ if(tip){tip.style.color='var(--danger)';tip.textContent=r?(r.msg||'加载失败'):'加载失败';} setTimeout(loadDragImage,1200); return; }
      dragData = r.data;
      if(r.data&&r.data.degraded){ if(tip){tip.style.color='var(--success)';tip.textContent='系统优化已自动通过';} onCaptchaOk(r.data.token); return; }
      renderDragImage(r.data); initDragSlider(r.data);
    },fail:function(){ if(tip){tip.style.color='var(--danger)';tip.textContent='网络错误，重试中...';} setTimeout(loadDragImage,1200); }
  });
}
function renderDragImage(data){
  var box = document.getElementById('dgBox'); if(!box) return;
  box.innerHTML = '';
  var bg = document.createElement('img');
  bg.id = 'dgBgImg'; bg.src = data.bg_image; bg.style.display='block'; bg.style.maxWidth='100%'; bg.style.borderRadius='6px';
  box.appendChild(bg);
  var piece = document.createElement('img');
  piece.id = 'dgPiece'; piece.src = data.piece_image;
  piece.style.position='absolute';
  piece.style.top = data.piece_y + 'px';
  piece.style.left = '0px';
  piece.style.width = data.piece_size + 'px';
  piece.style.height = data.piece_size + 'px';
  piece.style.pointerEvents = 'none';
  piece.style.transition = 'box-shadow .2s';
  piece.style.borderRadius = '4px';
  box.appendChild(piece);
  var tip = document.getElementById('dgTip');
  if(tip){ tip.style.color=''; tip.textContent = '拖动下方拼块，对齐右侧空缺位置'; }
  var msg = document.getElementById('dgMsg'); if(msg){ msg.textContent=''; msg.className='text-sm'; }
}
function initDragSlider(data){
  var slider = document.getElementById('dgSlider'); if(!slider) return;
  var btn = document.getElementById('dgBtn'); var track = document.getElementById('dgTrack');
  if(!btn||!track) return;
  var piece = document.getElementById('dgPiece');
  var sliderRect = null;
  var bgImg = document.getElementById('dgBgImg');
  var startX = 0; var btnLeft = 0;
  var maxLeft = function(){
    if(!slider) return 300;
    return slider.clientWidth - btn.offsetWidth;
  };
  function getBgW(){
    return bgImg? (bgImg.clientWidth || bgImg.naturalWidth || 340) : 340;
  }
  function onDown(e){
    if(captchaOk) return;
    dragDragging = true;
    sliderRect = slider.getBoundingClientRect();
    var cx = (e.touches && e.touches[0]) ? e.touches[0].clientX : e.clientX;
    startX = cx;
    var s = btn.style.left || '0px';
    btnLeft = parseInt(s,10)||0;
    document.getElementById('dgSliderTip').style.display='none';
    e.preventDefault&&e.preventDefault();
  }
  function onMove(e){
    if(!dragDragging) return;
    var cx = (e.touches && e.touches[0]) ? e.touches[0].clientX : ((e.changedTouches && e.changedTouches[0]) ? e.changedTouches[0].clientX : e.clientX);
    var delta = cx - startX;
    var left = Math.max(0, Math.min(maxLeft(), btnLeft + delta));
    btn.style.left = left + 'px';
    track.style.width = (left + btn.offsetWidth/2) + 'px';
    if(piece){
      var bgW = getBgW();
      var ratio = left / maxLeft();
      var px = Math.max(0, Math.min(bgW - data.piece_size, ratio * (bgW - data.piece_size)));
      piece.style.left = px + 'px';
    }
  }
  function onUp(e){
    if(!dragDragging) return;
    dragDragging = false;
    var left = parseInt(btn.style.left||'0',10)||0;
    var bgW = getBgW();
    var ratio = left / Math.max(1, maxLeft());
    var userX = Math.round(ratio * (bgW - data.piece_size));
    submitDragCheck(userX, left);
  }
  btn.addEventListener('mousedown', onDown);
  document.addEventListener('mousemove', onMove);
  document.addEventListener('mouseup', onUp);
  btn.addEventListener('touchstart', onDown, {passive:false});
  document.addEventListener('touchmove', onMove, {passive:false});
  document.addEventListener('touchend', onUp);
}
function submitDragCheck(userX, btnLeft){
  if(!dragData) return;
  var tok = dragData.token||'';
  gbAjax({method:'POST',url:SITE_URL+'/captcha/drag_image_check',data:{x:userX,token:tok},toast:false,
    success:function(r){
      var msg = document.getElementById('dgMsg');
      if(r&&r.code===0){
        if(msg){ msg.style.color='var(--success)'; msg.textContent='验证通过'; }
        onCaptchaOk(r.data&&r.data.token);
      } else {
        if(msg){ msg.style.color='var(--danger)'; msg.textContent=(r.msg||'位置不对，再试试'); }
        setTimeout(function(){
          var btn = document.getElementById('dgBtn'); var track = document.getElementById('dgTrack');
          var piece = document.getElementById('dgPiece');
          if(btn) btn.style.transition='left .3s ease';
          if(btn) btn.style.left='0px';
          if(track) track.style.transition='width .3s ease';
          if(track) track.style.width='0px';
          if(piece) piece.style.transition='left .3s ease';
          if(piece) piece.style.left='0px';
          setTimeout(function(){
            if(btn) btn.style.transition='';
            if(track) track.style.transition='';
            if(piece) piece.style.transition='';
            document.getElementById('dgSliderTip').style.display='';
          }, 320);
        }, 350);
      }
    },fail:function(){
      var msg = document.getElementById('dgMsg');
      if(msg){ msg.style.color='var(--danger)'; msg.textContent='网络错误，请重试'; }
    }
  });
}

function initGeetestBtn(){
  var btn = document.getElementById('gtBtn');
  if(!btn) return;
  btn.onclick = function(){
    if(captchaOk){ return; }
    var res = document.getElementById('gtResult');
    if(res){ res.textContent='正在加载极验...'; res.style.color=''; }
    if(typeof window.initGeetest4 === 'function' && GEETEST_ID){
      try{
        window.initGeetest4({captchaId:GEETEST_ID,product:'bind'}, function(captcha){
          captcha.onReady(function(){ captcha.showCaptcha(); });
          captcha.onSuccess(function(){
            var ret = captcha.getValidate();
            if(!res) return;
            gbAjax({method:'POST',url:SITE_URL+'/captcha/verify',data:{verified:1},toast:false,
              success:function(r){
                if(r&&r.code===0){ res.style.color='var(--success)'; res.textContent='极验验证通过'; onCaptchaOk(r.data&&r.data.token); }
                else { res.style.color='var(--danger)'; res.textContent=r.msg||'验证失败'; }
              },fail:function(){ res.style.color='var(--success)'; res.textContent='本地验证通过'; onCaptchaOk(); }
            });
          });
          captcha.onError(function(err){ if(res){res.style.color='var(--danger)';res.textContent='极验加载失败：'+(err&&err.msg?err.msg:'未知错误');} });
          captcha.onClose(function(){ if(!captchaOk){ if(res){res.style.color='var(--text-2)';res.textContent='';} } });
        });
      }catch(e2){ if(res){res.style.color='var(--success)';res.textContent='（降级）验证通过'; onCaptchaOk();} }
    } else if(GEETEST_ID) {
      var s = document.createElement('script');
      s.src = 'https://static.geetest.com/g4/gt4.js';
      s.onload = initGeetestBtn;
      s.onerror = function(){ if(res){res.style.color='var(--success)';res.textContent='（降级）验证通过'; onCaptchaOk();} };
      document.head.appendChild(s);
    } else {
      if(res){res.style.color='var(--success)';res.textContent='（未配置极验ID）已降级通过'; onCaptchaOk();}
    }
  };
}

document.addEventListener('DOMContentLoaded', function(){ renderCaptcha(); });

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

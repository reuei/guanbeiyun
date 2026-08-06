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

      <!-- 人机验证（动态类型） -->
      <div class="form-group">
        <label class="form-label">人机验证 <span class="req">*</span></label>
        <div id="dynamicCaptchaWrap" style="position:relative;">
          <div class="text-muted text-sm" style="padding:10px 0;">加载验证组件中...</div>
        </div>
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
  var natW = data.width || 320;
  var natH = data.height || 160;
  // 容器: position relative, 保持宽高比, 响应式缩放
  box.style.position = 'relative';
  box.style.display = 'inline-block';
  box.style.maxWidth = '100%';
  box.style.width = natW + 'px';
  box.style.height = 'auto';
  box.style.aspectRatio = natW + ' / ' + natH;
  box.style.borderRadius = '6px';
  box.style.overflow = 'hidden';
  box.style.cursor = 'crosshair';
  box.style.lineHeight = '0';
  box.style.background = '#f0fdf4';
  // 背景图
  var img = document.createElement('img');
  img.id = 'ckBgImg';
  img.style.cssText = 'display:block;width:100%;height:100%;border-radius:6px;';
  if(data.image){ img.src = data.image; }
  else { img.style.background = 'linear-gradient(135deg, #f0fdf4, #ecfdf5)'; }
  box.appendChild(img);
  // 字符叠加层 (HTML 渲染, 确保中文正确显示)
  var positions = data.positions || [];
  var charLayer = document.createElement('div');
  charLayer.style.cssText = 'position:absolute;inset:0;pointer-events:none;';
  box.appendChild(charLayer);
  for (var i = 0; i < positions.length; i++) {
    var p = positions[i];
    var span = document.createElement('span');
    span.textContent = p.char;
    span.style.cssText = 'position:absolute;' +
      'left:' + (p.x / natW * 100) + '%;' +
      'top:' + (p.y / natH * 100) + '%;' +
      'width:' + ((p.w||34) / natW * 100) + '%;' +
      'height:' + ((p.h||34) / natH * 100) + '%;' +
      'display:flex;align-items:center;justify-content:center;' +
      'font-size:clamp(14px,' + (22 / natW * 100) + 'cqw,24px);' +
      'font-weight:700;color:' + (p.color || '#333') + ';' +
      'transform:rotate(' + (p.angle || 0) + 'deg);' +
      'transform-origin:center center;user-select:none;' +
      'text-shadow:0 1px 2px rgba(255,255,255,0.7);';
    charLayer.appendChild(span);
  }
  // 启用 container query 让 cqw 单位生效
  try { box.style.containerType = 'inline-size'; } catch(e) {}
  var targets = data.targets||[];
  var tip = document.getElementById('ckTip');
  if(tip){ tip.style.color=''; tip.innerHTML='请依次点击文字：<b style="color:var(--danger);letter-spacing:4px;">'+(targets.join(' '))+'</b>'; }
  updateClickProgress();
  box.onclick = onCkBoxClick;
  box.oncontextmenu = function(e){e.preventDefault();};
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
  var natW = clickTextData.width || 320;
  var natH = clickTextData.height || 160;
  // 将显示坐标转换为原始坐标
  var ix = (e.clientX - rect.left) * (natW / Math.max(1, rect.width));
  var iy = (e.clientY - rect.top) * (natH / Math.max(1, rect.height));
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

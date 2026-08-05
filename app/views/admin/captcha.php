<?php /** 人机验证配置 */
$cfg = $cfg ?? [];
function cfgv($k, $d='') { global $cfg; return $cfg[$k] ?? $d; }
$captchaType = cfgv('captcha_type', 'slider');
?>
<div class="panel">
  <div class="panel-head">
    <span class="title">
      <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        <path d="M9 12l2 2 4-4"/>
      </svg>
      人机验证配置
    </span>
  </div>
  <div class="panel-body">
    <form id="captchaForm" onsubmit="return saveCaptcha(event)">
      <h4 style="margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--divider);">验证方式选择</h4>
      <div class="form-group">
        <label class="form-label">验证类型</label>
        <div class="radio-group">
          <label class="radio-item">
            <input type="radio" name="captcha_type" value="slider" <?php echo $captchaType==='slider'?'checked':''; ?> onchange="updatePreview()">
            <span>本地滑块验证</span>
          </label>
          <label class="radio-item">
            <input type="radio" name="captcha_type" value="verify" <?php echo $captchaType==='verify'?'checked':''; ?> onchange="updatePreview()">
            <span>本地验证码图片</span>
          </label>
          <label class="radio-item">
            <input type="radio" name="captcha_type" value="click" <?php echo $captchaType==='click'?'checked':''; ?> onchange="updatePreview()">
            <span>本地点击文字验证</span>
          </label>
          <label class="radio-item">
            <input type="radio" name="captcha_type" value="drag" <?php echo $captchaType==='drag'?'checked':''; ?> onchange="updatePreview()">
            <span>本地拖动图片合并验证</span>
          </label>
          <label class="radio-item">
            <input type="radio" name="captcha_type" value="geetest" <?php echo $captchaType==='geetest'?'checked':''; ?> onchange="updatePreview()">
            <span>Geetest极验</span>
          </label>
        </div>
      </div>

      <div id="geetestFields" style="<?php echo $captchaType==='geetest'?'':'display:none;'; ?>">
        <h4 style="margin:20px 0 14px;padding-bottom:10px;border-bottom:1px solid var(--divider);">Geetest极验配置</h4>
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Geetest ID</label>
            <input class="form-control" name="geetest_id" value="<?php echo e(cfgv('geetest_id')); ?>" placeholder="请输入极验 ID">
          </div>
          <div class="form-group">
            <label class="form-label">Geetest KEY</label>
            <input type="password" class="form-control" name="geetest_key" value="<?php echo e(cfgv('geetest_key')); ?>" placeholder="请输入极验 KEY">
          </div>
        </div>
      </div>

      <h4 style="margin:20px 0 14px;padding-bottom:10px;border-bottom:1px solid var(--divider);">本地验证参数</h4>
      <div class="grid-2">
        <div class="form-group">
          <label class="form-label">验证码长度（文字验证码）</label>
          <input type="number" class="form-control" name="captcha_length" value="<?php echo e(cfgv('captcha_length', 4)); ?>" min="4" max="6" placeholder="4-6位">
          <div class="text-sm text-muted">默认4位，可选4-6位字母数字</div>
        </div>
        <div class="form-group">
          <label class="form-label">点击目标数量（点击验证）</label>
          <input type="number" class="form-control" name="captcha_click_count" value="<?php echo e(cfgv('captcha_click_count', 3)); ?>" min="2" max="5" placeholder="2-5个">
          <div class="text-sm text-muted">默认3个，用户需点击指定数量的文字</div>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">拼图难度</label>
        <div class="radio-group">
          <label class="radio-item">
            <input type="radio" name="captcha_difficulty" value="easy" <?php echo cfgv('captcha_difficulty','medium')==='easy'?'checked':''; ?>>
            <span>简单（拼块大，易识别）</span>
          </label>
          <label class="radio-item">
            <input type="radio" name="captcha_difficulty" value="medium" <?php echo cfgv('captcha_difficulty','medium')==='medium'?'checked':''; ?>>
            <span>中等</span>
          </label>
          <label class="radio-item">
            <input type="radio" name="captcha_difficulty" value="hard" <?php echo cfgv('captcha_difficulty','medium')==='hard'?'checked':''; ?>>
            <span>困难（拼块小，辨识度低）</span>
          </label>
        </div>
      </div>

      <h4 style="margin:20px 0 14px;padding-bottom:10px;border-bottom:1px solid var(--divider);">效果预览</h4>
      <div class="form-group">
        <div id="captchaPreview" style="padding:20px;background:var(--bg-elevated);border-radius:8px;min-height:220px;display:flex;align-items:center;justify-content:center;">
          <div class="text-muted">正在加载预览...</div>
        </div>
      </div>

      <div style="text-align:right;margin-top:20px;">
        <button type="submit" class="btn btn-primary btn-lg" id="saveBtn">保存配置</button>
      </div>
    </form>
  </div>
</div>

<style>
.radio-group{display:flex;flex-direction:column;gap:10px;}
.radio-item{display:flex;align-items:center;gap:8px;cursor:pointer;padding:8px 12px;border:1px solid var(--divider);border-radius:6px;transition:all .2s;}
.radio-item:hover{border-color:var(--primary);}
.radio-item input[type="radio"]{accent-color:var(--primary);}
</style>

<script>
var curType = '<?php echo $captchaType; ?>';
function updatePreview(){
  var r = document.querySelector('input[name="captcha_type"]:checked');
  curType = r?r.value:'slider';
  document.getElementById('geetestFields').style.display = (curType==='geetest')?'':'none';
  var pv = document.getElementById('captchaPreview');
  if(curType==='slider'){
    pv.innerHTML = '<div style="width:100%;max-width:340px;"><div style="position:relative;height:40px;background:#f0f0f0;border-radius:6px;overflow:hidden;"><div style="position:absolute;left:0;top:0;height:100%;width:30%;background:#d6eaff;transition:width .3s;"></div><div style="position:absolute;left:calc(30% - 40px);top:0;width:40px;height:100%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;border-radius:6px;cursor:pointer;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg></div><div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#888;font-size:13px;pointer-events:none;">向右拖动滑块完成验证</div></div></div>';
  } else if(curType==='verify'){
    var len = parseInt(document.querySelector('input[name="captcha_length"]').value||'4',10);
    pv.innerHTML = '<div style="display:flex;flex-direction:column;align-items:center;gap:12px;"><div style="width:'+(120+len*20)+'px;height:45px;background:linear-gradient(135deg,#e0f2fe,#bae6fd);border-radius:4px;display:flex;align-items:center;justify-content:center;letter-spacing:4px;font-size:22px;font-weight:700;color:#1e40af;user-select:none;filter:blur(0.3px);">A3F9</div><input class="form-control" style="max-width:240px;" placeholder="请输入图中字符" readonly><div class="text-sm text-muted">点击图片可刷新验证码</div></div>';
  } else if(curType==='click'){
    var cnt = parseInt(document.querySelector('input[name="captcha_click_count"]').value||'3',10);
    pv.innerHTML = '<div style="display:flex;flex-direction:column;align-items:center;gap:10px;"><div style="font-size:13px;color:var(--text-2);">请依次点击：<b style="color:var(--danger);letter-spacing:4px;">云 备 管</b></div><div style="width:320px;height:160px;background:linear-gradient(135deg,#f0fdf4,#ecfdf5);border-radius:4px;position:relative;overflow:hidden;"><span style="position:absolute;left:10%;top:20%;font-size:20px;color:#7c2d12;transform:rotate(-12deg);">管</span><span style="position:absolute;left:35%;top:55%;font-size:20px;color:#14532d;transform:rotate(8deg);">备</span><span style="position:absolute;left:60%;top:25%;font-size:20px;color:#1e3a8a;transform:rotate(-5deg);">云</span><span style="position:absolute;left:80%;top:60%;font-size:18px;color:#581c87;transform:rotate(15deg);">系</span><span style="position:absolute;left:20%;top:70%;font-size:18px;color:#831843;transform:rotate(-20deg);">统</span><span style="position:absolute;left:50%;top:10%;font-size:18px;color:#0f766e;transform:rotate(18deg);">案</span><span style="position:absolute;left:70%;top:75%;font-size:18px;color:#78350f;transform:rotate(0deg);">备</span><span style="position:absolute;left:5%;top:45%;font-size:18px;color:#4c1d95;transform:rotate(25deg);">我</span></div><div class="text-sm text-muted">在背景图中点击目标 '+cnt+' 个指定文字</div></div>';
  } else if(curType==='drag'){
    var diff = document.querySelector('input[name="captcha_difficulty"]:checked');
    var diffTxt = (diff?diff.value:'medium');
    var ps = {easy:60,medium:50,hard:40}[diffTxt]||50;
    pv.innerHTML = '<div style="display:flex;flex-direction:column;align-items:center;gap:10px;"><div style="position:relative;width:340px;height:180px;background:linear-gradient(135deg,#bfdbfe,#ddd6fe);border-radius:4px;overflow:hidden;"><div style="position:absolute;left:180px;top:50px;width:'+ps+'px;height:'+ps+'px;background:rgba(0,0,0,0.35);border:2px solid #fff;border-radius:4px;box-shadow:inset 0 0 10px rgba(0,0,0,0.3);"></div><div style="position:absolute;left:0;top:50px;width:'+ps+'px;height:'+ps+'px;background:linear-gradient(135deg,#93c5fd,#818cf8);border:2px solid #fff;border-radius:4px;box-shadow:0 2px 8px rgba(0,0,0,0.2);"></div></div><div style="width:340px;height:36px;background:#f3f4f6;border-radius:6px;position:relative;overflow:hidden;"><div style="position:absolute;left:0;top:0;height:100%;width:'+ps+'px;background:var(--primary);border-radius:6px;display:flex;align-items:center;justify-content:center;color:#fff;cursor:pointer;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg></div></div><div class="text-sm text-muted">拖动下方拼块到右侧空缺位置（允许±5px误差）</div></div>';
  } else if(curType==='geetest'){
    pv.innerHTML = '<div style="text-align:center;color:var(--text-muted);"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:.5;"><path d="M21 12a9 9 0 1 1-9-9"/><path d="M21 3v6h-6"/></svg><div style="margin-top:8px;font-size:13px;">极验 Geetest 第三方验证</div><div style="margin-top:4px;font-size:12px;">需在上方配置 ID 和 KEY 后启用</div><div style="margin-top:12px;padding:12px 20px;background:var(--bg);border:1px solid var(--divider);border-radius:6px;display:inline-block;cursor:pointer;"><div style="display:flex;align-items:center;gap:8px;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg><span style="font-size:14px;color:var(--text-2);">点击按钮进行验证</span></div></div></div>';
  }
}

function saveCaptcha(e){
  e.preventDefault();
  var fd = new FormData(e.target);
  var data = {};
  fd.forEach(function(v,k){ data[k] = v; });
  var btn = document.getElementById('saveBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="gb-loading gb-loading-sm"></span> 保存中...';
  gbAjax({
    method:'POST',
    url:'<?php echo site_url('admin/captcha/save'); ?>',
    data:data,
    success:function(res){
      if(res.code===0){
        gbToast.success(res.msg);
        setTimeout(function(){ location.reload(); }, 600);
      }
    },
    complete:function(){
      btn.disabled = false;
      btn.innerHTML = '保存配置';
    }
  });
  return false;
}

document.addEventListener('DOMContentLoaded', function(){
  updatePreview();
});
</script>

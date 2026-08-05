<?php
$cfg = $cfg ?? [];
function cfgo($k, $d='') { global $cfg; return $cfg[$k] ?? $d; }
$callbackUrl = site_url('oauth/callback');
$rainbowMethods = explode(',', cfgo('rainbow_methods', ''));
$rainbowMethods = array_filter($rainbowMethods);
$methodList = [
    'wechat' => '微信',
    'qq' => 'QQ',
    'alipay' => '支付宝',
    'baidu' => '百度',
    'sina' => '新浪',
    'taobao' => '淘宝',
    'douyin' => '抖音',
    'github' => 'GitHub',
    'google' => 'Google',
    'facebook' => 'Facebook',
    'twitter' => 'Twitter',
    'gitee' => 'Gitee',
    'dingtalk' => '钉钉',
    'weibo' => '微博',
    'renren' => '人人网',
    'kuaishou' => '快手',
];
?>
<style>
.cb-group { display:flex; flex-wrap:wrap; gap:10px 16px; }
.cb-group label { display:inline-flex; align-items:center; gap:6px; cursor:pointer; padding:6px 10px; border:1px solid var(--border, #e5e7eb); border-radius:6px; background:var(--bg-soft, #f9fafb); font-size:14px; transition:all .15s; }
.cb-group label:hover { border-color: var(--primary, #3b82f6); }
.cb-group label input { width:15px; height:15px; accent-color: var(--primary, #3b82f6); }
.cb-group label.checked { background: rgba(59,130,246,0.08); border-color: var(--primary, #3b82f6); }
.accordion { border:1px solid var(--border, #e5e7eb); border-radius:8px; overflow:hidden; margin-top:18px; }
.accordion-head { padding:12px 16px; background:var(--bg-soft, #f9fafb); cursor:pointer; display:flex; justify-content:space-between; align-items:center; font-weight:600; user-select:none; }
.accordion-head svg { transition: transform .2s; }
.accordion.open .accordion-head svg { transform: rotate(180deg); }
.accordion-body { display:none; padding:16px; }
.accordion.open .accordion-body { display:block; }
</style>
<div class="panel" style="max-width:960px;">
  <div class="panel-head"><span class="title">第三方登录平台（彩虹聚合登录平台）</span></div>
  <div class="panel-body">
    <div class="form-hint" style="margin-bottom:16px;background:var(--bg-soft);border:1px solid var(--border);border-radius:6px;padding:10px 14px;">
      <b>统一回调地址:</b> <code><?php echo e($callbackUrl); ?></code>
      <div class="text-muted text-sm" style="margin-top:4px;">请在彩虹聚合登录平台添加该回调域名，并在下方配置 AppID / APPKEY 等参数。</div>
    </div>

    <form id="oauthForm" onsubmit="return saveOauth(event)">
      <div class="form-group" style="margin-bottom:18px;">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" name="oauth_enabled" value="1" <?php echo cfgo('oauth_enabled')=='1'?'checked':''; ?> style="width:18px;height:18px;"> <span style="font-size:15px;font-weight:600;">启用聚合登录</span></label>
      </div>

      <div style="background:linear-gradient(135deg, rgba(59,130,246,0.06), rgba(147,51,234,0.06)); border:1px solid rgba(59,130,246,0.2); border-radius:10px; padding:20px; margin-bottom:18px;">
        <h4 style="margin:0 0 14px 0; display:flex; align-items:center; gap:8px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
          彩虹聚合登录配置
        </h4>
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">接口地址 <span class="req">*</span></label>
            <input class="form-control" name="rainbow_api_url" value="<?php echo e(cfgo('rainbow_api_url')); ?>" placeholder="例如：https://oauth.example.com">
          </div>
          <div class="form-group">
            <label class="form-label">AppID <span class="req">*</span></label>
            <input class="form-control" name="rainbow_app_id" value="<?php echo e(cfgo('rainbow_app_id')); ?>" placeholder="彩虹平台 AppID">
          </div>
          <div class="form-group">
            <label class="form-label">APPKEY <span class="req">*</span></label>
            <input class="form-control" type="password" name="rainbow_app_key" value="<?php echo e(cfgo('rainbow_app_key')); ?>" placeholder="彩虹平台 APPKEY" autocomplete="off">
          </div>
          <div class="form-group">
            <label class="form-label">回调域名 <span class="req">*</span></label>
            <input class="form-control" name="rainbow_callback" value="<?php echo e(cfgo('rainbow_callback', $callbackUrl)); ?>" placeholder="授权回调完整地址">
          </div>
        </div>

        <div class="form-group" style="margin-top:12px;">
          <label class="form-label">选择登录方式（从第三方平台获取列表）</label>
          <div class="cb-group" id="cbGroup">
            <?php foreach ($methodList as $k => $v):
              $chk = in_array($k, $rainbowMethods) ? 'checked' : '';
              $cls = in_array($k, $rainbowMethods) ? 'checked' : '';
            ?>
              <label class="<?php echo $cls; ?>">
                <input type="checkbox" name="rainbow_methods[]" value="<?php echo e($k); ?>" <?php echo $chk; ?> onchange="toggleCb(this)">
                <?php echo e($v); ?>
              </label>
            <?php endforeach; ?>
          </div>
          <div class="text-muted text-sm" style="margin-top:8px;">勾选后将在登录页展示对应登录入口，保存为逗号分隔字符串。</div>
        </div>

        <div style="display:flex; gap:10px; margin-top:16px; align-items:center;">
          <button type="button" class="btn btn-ghost" onclick="testRainbow()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            测试连接
          </button>
          <span id="testResult" class="text-sm text-muted"></span>
        </div>
      </div>

      <div class="accordion" id="legacyAccordion">
        <div class="accordion-head" onclick="document.getElementById('legacyAccordion').classList.toggle('open')">
          <span>传统独立配置(兼容)</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
        <div class="accordion-body">
          <div style="background:var(--bg-soft);border:1px solid var(--border);border-radius:6px;padding:16px;margin-bottom:14px;">
            <h4 style="margin-bottom:12px;display:flex;align-items:center;gap:8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/></svg> QQ 登录</h4>
            <div class="grid-2">
              <div class="form-group"><label class="form-label">App ID (APPID)</label><input class="form-control" name="oauth_qq_id" value="<?php echo e(cfgo('oauth_qq_id')); ?>" placeholder="QQ互联 App ID"></div>
              <div class="form-group"><label class="form-label">App Key (Secret)</label><input class="form-control" type="password" name="oauth_qq_secret" value="<?php echo e(cfgo('oauth_qq_secret')); ?>"></div>
            </div>
            <div class="form-group"><label class="form-label">授权回调网址</label><input class="form-control" name="oauth_qq_callback" value="<?php echo e(cfgo('oauth_qq_callback', $callbackUrl.'?type=qq')); ?>" placeholder="填写到QQ互联后台的回调地址"></div>
          </div>

          <div style="background:var(--bg-soft);border:1px solid var(--border);border-radius:6px;padding:16px;margin-bottom:14px;">
            <h4 style="margin-bottom:12px;display:flex;align-items:center;gap:8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M8.69 2C4.44 2 1 4.94 1 8.56c0 2.1 1.16 3.96 2.96 5.16"/></svg> 微信登录</h4>
            <div class="grid-2">
              <div class="form-group"><label class="form-label">App ID (APPID)</label><input class="form-control" name="oauth_wechat_id" value="<?php echo e(cfgo('oauth_wechat_id')); ?>" placeholder="微信开放平台 App ID"></div>
              <div class="form-group"><label class="form-label">App Secret</label><input class="form-control" type="password" name="oauth_wechat_secret" value="<?php echo e(cfgo('oauth_wechat_secret')); ?>"></div>
            </div>
            <div class="form-group"><label class="form-label">授权回调网址</label><input class="form-control" name="oauth_wechat_callback" value="<?php echo e(cfgo('oauth_wechat_callback', $callbackUrl.'?type=wechat')); ?>" placeholder="微信开放平台回调地址"></div>
          </div>

          <div style="background:var(--bg-soft);border:1px solid var(--border);border-radius:6px;padding:16px;">
            <h4 style="margin-bottom:12px;display:flex;align-items:center;gap:8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M2 12c0 5.52 4.48 10 10 10s10-4.48 10-10S17.52 2 12 2 2 6.48 2 12z"/></svg> 支付宝登录</h4>
            <div class="grid-2">
              <div class="form-group"><label class="form-label">App ID (APPID)</label><input class="form-control" name="oauth_alipay_id" value="<?php echo e(cfgo('oauth_alipay_id')); ?>" placeholder="支付宝开放平台 App ID"></div>
              <div class="form-group"><label class="form-label">App Secret</label><input class="form-control" type="password" name="oauth_alipay_secret" value="<?php echo e(cfgo('oauth_alipay_secret')); ?>"></div>
            </div>
            <div class="form-group"><label class="form-label">授权回调网址</label><input class="form-control" name="oauth_alipay_callback" value="<?php echo e(cfgo('oauth_alipay_callback', $callbackUrl.'?type=alipay')); ?>" placeholder="支付宝回调地址"></div>
          </div>
        </div>
      </div>

      <div style="text-align:right; margin-top:20px;"><button type="submit" class="btn btn-primary" id="saveBtn">保存配置</button></div>
    </form>
  </div>
</div>
<script>
function toggleCb(inp){
  var lb = inp.closest('label');
  if (inp.checked) lb.classList.add('checked');
  else lb.classList.remove('checked');
}
function saveOauth(e){
  e.preventDefault();
  var d = {};
  new FormData(e.target).forEach(function(v,k){
    if (k === 'rainbow_methods[]') {
      if (!d['rainbow_methods[]']) d['rainbow_methods[]'] = [];
      d['rainbow_methods[]'].push(v);
    } else {
      d[k] = v;
    }
  });
  var b = document.getElementById('saveBtn');
  b.disabled = true;
  b.innerHTML = '<span class="gb-loading gb-loading-sm"></span> 保存中';
  gbAjax({
    method: 'POST',
    url: '<?php echo site_url('admin/oauth/save'); ?>',
    data: d,
    success: function(r) {
      if (r.code === 0) {
        gbToast.success(r.msg);
        setTimeout(function() { location.reload(); }, 600);
      }
    },
    complete: function() {
      b.disabled = false;
      b.innerHTML = '保存配置';
    }
  });
  return false;
}
function testRainbow() {
  var url = document.querySelector('[name="rainbow_api_url"]').value.trim();
  var appid = document.querySelector('[name="rainbow_app_id"]').value.trim();
  var tip = document.getElementById('testResult');
  if (!url || !appid) {
    tip.textContent = '请先填写接口地址和 AppID';
    tip.className = 'text-sm';
    tip.style.color = '#dc2626';
    return;
  }
  tip.textContent = '正在测试连接...';
  tip.className = 'text-sm';
  tip.style.color = '';
  gbAjax({
    method: 'POST',
    url: '<?php echo site_url('admin/oauth/test'); ?>',
    data: { rainbow_api_url: url, rainbow_app_id: appid },
    success: function(r) {
      if (r.code === 0) {
        tip.textContent = '✓ ' + (r.msg || '连接成功');
        tip.style.color = '#059669';
      } else {
        tip.textContent = '✗ ' + (r.msg || '连接失败');
        tip.style.color = '#dc2626';
      }
    },
    error: function() {
      tip.textContent = '✗ 请求失败，请检查网络';
      tip.style.color = '#dc2626';
    }
  });
}
</script>

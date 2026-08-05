<?php /** 聚合登录配置 */
$cfg = $cfg ?? [];
function cfgo($k, $d='') { global $cfg; return $cfg[$k] ?? $d; }
$callbackUrl = site_url('oauth/callback');
?>
<div class="panel" style="max-width:760px;">
  <div class="panel-head"><span class="title">聚合登录配置</span></div>
  <div class="panel-body">
    <div class="form-hint" style="margin-bottom:16px;background:var(--bg-soft);border:1px solid var(--border);border-radius:6px;padding:10px 14px;">
      <b>统一回调地址:</b> <code><?php echo e($callbackUrl); ?></code>
      <div class="text-muted text-sm" style="margin-top:4px;">请在第三方平台将该地址填写到「授权回调地址 / Redirect URI」中。各平台支持参数 <code>?type=qq|wechat|alipay</code></div>
    </div>

    <form id="oauthForm" onsubmit="return saveOauth(event)">
      <div class="form-group">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" name="oauth_enabled" value="1" <?php echo cfgo('oauth_enabled')=='1'?'checked':''; ?> style="width:16px;height:16px;"> <span>开启聚合登录功能</span></label>
      </div>

      <div style="background:var(--bg-soft);border:1px solid var(--border);border-radius:6px;padding:16px;margin-bottom:18px;">
        <h4 style="margin-bottom:12px;display:flex;align-items:center;gap:8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/></svg> QQ 登录</h4>
        <div class="grid-2">
          <div class="form-group"><label class="form-label">App ID (APPID) <span class="req">*</span></label><input class="form-control" name="oauth_qq_id" value="<?php echo e(cfgo('oauth_qq_id')); ?>" placeholder="QQ互联 App ID"></div>
          <div class="form-group"><label class="form-label">App Key (Secret) <span class="req">*</span></label><input class="form-control" type="password" name="oauth_qq_secret" value="<?php echo e(cfgo('oauth_qq_secret')); ?>"></div>
        </div>
        <div class="form-group"><label class="form-label">授权回调网址</label><input class="form-control" name="oauth_qq_callback" value="<?php echo e(cfgo('oauth_qq_callback', $callbackUrl.'?type=qq')); ?>" placeholder="填写到QQ互联后台的回调地址"></div>
      </div>

      <div style="background:var(--bg-soft);border:1px solid var(--border);border-radius:6px;padding:16px;margin-bottom:18px;">
        <h4 style="margin-bottom:12px;display:flex;align-items:center;gap:8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M8.69 2C4.44 2 1 4.94 1 8.56c0 2.1 1.16 3.96 2.96 5.16l-.44 1.5c-.06.16 0 .3.16.3l2.04-1.2c.86.26 1.8.4 2.78.4"/></svg> 微信登录</h4>
        <div class="grid-2">
          <div class="form-group"><label class="form-label">App ID (APPID) <span class="req">*</span></label><input class="form-control" name="oauth_wechat_id" value="<?php echo e(cfgo('oauth_wechat_id')); ?>" placeholder="微信开放平台 App ID"></div>
          <div class="form-group"><label class="form-label">App Secret <span class="req">*</span></label><input class="form-control" type="password" name="oauth_wechat_secret" value="<?php echo e(cfgo('oauth_wechat_secret')); ?>"></div>
        </div>
        <div class="form-group"><label class="form-label">授权回调网址</label><input class="form-control" name="oauth_wechat_callback" value="<?php echo e(cfgo('oauth_wechat_callback', $callbackUrl.'?type=wechat')); ?>" placeholder="微信开放平台回调地址"></div>
      </div>

      <div style="background:var(--bg-soft);border:1px solid var(--border);border-radius:6px;padding:16px;margin-bottom:18px;">
        <h4 style="margin-bottom:12px;display:flex;align-items:center;gap:8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M2 12c0 5.52 4.48 10 10 10s10-4.48 10-10S17.52 2 12 2 2 6.48 2 12z"/></svg> 支付宝登录</h4>
        <div class="grid-2">
          <div class="form-group"><label class="form-label">App ID (APPID) <span class="req">*</span></label><input class="form-control" name="oauth_alipay_id" value="<?php echo e(cfgo('oauth_alipay_id')); ?>" placeholder="支付宝开放平台 App ID"></div>
          <div class="form-group"><label class="form-label">App Secret <span class="req">*</span></label><input class="form-control" type="password" name="oauth_alipay_secret" value="<?php echo e(cfgo('oauth_alipay_secret')); ?>"></div>
        </div>
        <div class="form-group"><label class="form-label">授权回调网址</label><input class="form-control" name="oauth_alipay_callback" value="<?php echo e(cfgo('oauth_alipay_callback', $callbackUrl.'?type=alipay')); ?>" placeholder="支付宝回调地址"></div>
      </div>

      <div style="text-align:right;"><button type="submit" class="btn btn-primary" id="saveBtn">保存配置</button></div>
    </form>
  </div>
</div>
<script>
function saveOauth(e){e.preventDefault();var d={};new FormData(e.target).forEach(function(v,k){d[k]=v;});
  var b=document.getElementById('saveBtn');b.disabled=true;b.innerHTML='<span class="gb-loading gb-loading-sm"></span> 保存中';
  gbAjax({method:'POST',url:'<?php echo site_url('admin/oauth/save'); ?>',data:d,success:function(r){if(r.code===0){gbToast.success(r.msg);setTimeout(function(){location.reload();},600);}},complete:function(){b.disabled=false;b.innerHTML='保存配置';}});return false;}
</script>

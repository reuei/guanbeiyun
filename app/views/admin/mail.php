<?php /** 邮箱配置 */
$cfg = $cfg ?? [];
function cfgm($k, $d='') { global $cfg; return $cfg[$k] ?? $d; }
?>
<div class="panel" style="max-width:680px;">
  <div class="panel-head"><span class="title">邮箱配置</span></div>
  <div class="panel-body">
    <form id="mailForm" onsubmit="return saveMail(event)">
      <div class="form-group">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" name="mail_enabled" value="1" <?php echo cfgm('mail_enabled')=='1'?'checked':''; ?> style="width:16px;height:16px;"> <span>开启邮件发送功能</span></label>
      </div>
      <div class="form-group">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" name="mail_reg_login" value="1" <?php echo cfgm('mail_reg_login')=='1'?'checked':''; ?> style="width:16px;height:16px;"> <span>登录/注册页面开启邮箱验证码发送</span></label>
      </div>
      <h4 style="margin:18px 0 12px;padding-bottom:8px;border-bottom:1px solid var(--divider);">SMTP 配置</h4>
      <div class="grid-2">
        <div class="form-group"><label class="form-label">SMTP 主机</label><input class="form-control" name="mail_host" value="<?php echo e(cfgm('mail_host')); ?>" placeholder="如 smtp.qq.com"></div>
        <div class="form-group"><label class="form-label">SMTP 端口</label><input class="form-control" name="mail_port" value="<?php echo e(cfgm('mail_port','465')); ?>" placeholder="465"></div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label class="form-label">SMTP 用户名</label><input class="form-control" name="mail_user" value="<?php echo e(cfgm('mail_user')); ?>"></div>
        <div class="form-group"><label class="form-label">SMTP 密码/授权码</label><input class="form-control" type="password" name="mail_pass" value="<?php echo e(cfgm('mail_pass')); ?>"></div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label class="form-label">发件邮箱</label><input class="form-control" name="mail_from" value="<?php echo e(cfgm('mail_from')); ?>"></div>
        <div class="form-group"><label class="form-label">发件人名称</label><input class="form-control" name="mail_from_name" value="<?php echo e(cfgm('mail_from_name')); ?>"></div>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:18px;">
        <div class="form-group" style="margin:0;display:flex;gap:8px;align-items:center;">
          <input class="form-control" id="testMail" placeholder="测试收件邮箱" style="width:200px;">
          <button type="button" class="btn" onclick="testMail()">发送测试</button>
        </div>
        <button type="submit" class="btn btn-primary" id="saveBtn">保存配置</button>
      </div>
    </form>
  </div>
</div>
<script>
function saveMail(e){e.preventDefault();var d={};new FormData(e.target).forEach(function(v,k){d[k]=v;});
  var b=document.getElementById('saveBtn');b.disabled=true;b.innerHTML='<span class="gb-loading gb-loading-sm"></span> 保存中';
  gbAjax({method:'POST',url:'<?php echo site_url('admin/mail/save'); ?>',data:d,success:function(r){if(r.code===0){gbToast.success(r.msg);setTimeout(function(){location.reload();},600);}},complete:function(){b.disabled=false;b.innerHTML='保存配置';}});return false;}
function testMail(){var to=document.getElementById('testMail').value.trim();if(!to){gbToast.warning('请输入收件邮箱');return;}
  gbAjax({method:'POST',url:'<?php echo site_url('admin/mail/test'); ?>',data:{to:to},success:function(r){}});}
</script>

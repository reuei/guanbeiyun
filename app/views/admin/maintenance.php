<?php /** 网站维护配置 */
$cfg = $cfg ?? [];
function cfgm($k, $d='') { global $cfg; return $cfg[$k] ?? $d; }
?>
<div class="panel" style="max-width:760px;">
  <div class="panel-head"><span class="title">网站维护配置</span></div>
  <div class="panel-body">
    <div class="form-hint" style="margin-bottom:16px;background:var(--bg-soft);border:1px solid var(--border);border-radius:6px;padding:10px 14px;">
      <b>说明:</b> 开启维护模式后，前台将显示维护提示页面，已登录管理员仍可正常访问后台。
      <div class="text-muted text-sm" style="margin-top:4px;">建议填写预计恢复时间，以便用户了解维护进度。</div>
    </div>

    <form id="maintenanceForm" onsubmit="return saveMaintenance(event)">
      <div class="form-group">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" name="maintenance_enabled" value="1" <?php echo cfgm('maintenance_enabled')=='1'?'checked':''; ?> style="width:16px;height:16px;"> <span>开启网站维护模式</span></label>
      </div>

      <div class="form-group">
        <label class="form-label">维护标题</label>
        <input class="form-control" name="maintenance_title" value="<?php echo e(cfgm('maintenance_title', '网站维护中')); ?>" placeholder="如：网站维护中">
      </div>

      <div class="form-group">
        <label class="form-label">维护内容</label>
        <textarea class="form-control" name="maintenance_content" rows="5" placeholder="向用户说明维护原因及预计恢复时间"><?php echo e(cfgm('maintenance_content')); ?></textarea>
      </div>

      <div class="form-group">
        <label class="form-label">预计恢复时间</label>
        <input class="form-control" type="datetime-local" name="maintenance_recover_time" value="<?php echo e(cfgm('maintenance_recover_time')); ?>" placeholder="如：2026-08-05 12:00">
      </div>

      <div style="text-align:right;"><button type="submit" class="btn btn-primary" id="saveBtn">保存配置</button></div>
    </form>
  </div>
</div>
<script>
function saveMaintenance(e){e.preventDefault();var d={};new FormData(e.target).forEach(function(v,k){d[k]=v;});
  if(!d.maintenance_enabled)d.maintenance_enabled=0;
  var b=document.getElementById('saveBtn');b.disabled=true;b.innerHTML='<span class="gb-loading gb-loading-sm"></span> 保存中';
  gbAjax({method:'POST',url:'<?php echo site_url('admin/maintenance/save'); ?>',data:d,success:function(r){if(r.code===0){gbToast.success(r.msg);setTimeout(function(){location.reload();},600);}},complete:function(){b.disabled=false;b.innerHTML='保存配置';}});return false;}
</script>

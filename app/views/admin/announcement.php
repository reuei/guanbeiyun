<?php /** 公告配置 */
$cfg = $cfg ?? [];
function cfgv2($k, $d='') { global $cfg; return $cfg[$k] ?? $d; }
?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;" class="cfg-grid">
  <div class="panel">
    <div class="panel-head"><span class="title">首页公告弹窗</span></div>
    <div class="panel-body">
      <form id="annForm" onsubmit="return saveAnn(event)">
        <div class="form-group">
          <label class="form-label">是否开启</label>
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" name="announcement_enabled" value="1" <?php echo cfgv2('announcement_enabled')=='1'?'checked':''; ?> style="width:16px;height:16px;"> <span>开启首页公告弹窗</span></label>
        </div>
        <div class="form-group"><label class="form-label">公告标题</label><input class="form-control" name="announcement_title" value="<?php echo e(cfgv2('announcement_title')); ?>" placeholder="如：系统公告"></div>
        <div class="form-group"><label class="form-label">公告内容 (支持HTML)</label><textarea class="form-control" name="announcement_content" rows="8" placeholder="公告内容..."><?php echo e(cfgv2('announcement_content')); ?></textarea></div>
        <div style="text-align:right;"><button type="submit" class="btn btn-primary" id="annBtn">保存</button></div>
      </form>
    </div>
  </div>
  <div class="panel">
    <div class="panel-head"><span class="title">消息通知发送</span><a href="<?php echo site_url('admin/notify'); ?>" class="text-sm">查看全部</a></div>
    <div class="panel-body">
      <form id="notifyForm" onsubmit="return sendNotify(event)">
        <div class="form-group"><label class="form-label">接收用户</label><select class="form-control" name="user_id"><option value="0">全体用户</option>
          <?php
          try { $users = db()->query("SELECT id,username FROM " . db()->table('users') . " ORDER BY id DESC LIMIT 200"); foreach ($users as $u): ?>
          <option value="<?php echo $u['id']; ?>"><?php echo e($u['username']); ?></option>
          <?php endforeach; } catch (Throwable $e) {}
          ?>
        </select></div>
        <div class="form-group"><label class="form-label">通知标题</label><input class="form-control" name="title" placeholder="通知标题"></div>
        <div class="form-group"><label class="form-label">通知内容</label><textarea class="form-control" name="content" rows="4" placeholder="通知内容"></textarea></div>
        <div class="form-group"><label class="form-label">类型</label><select class="form-control" name="type"><option value="system">系统通知</option><option value="filing">备案通知</option><option value="activity">活动通知</option></select></div>
        <div style="text-align:right;"><button type="submit" class="btn btn-primary" id="notifyBtn">发送</button></div>
      </form>
    </div>
  </div>
</div>
<style>@media(max-width:900px){.cfg-grid{grid-template-columns:1fr;}}</style>
<script>
function saveAnn(e){e.preventDefault();var d={};new FormData(e.target).forEach(function(v,k){d[k]=v;});
  var b=document.getElementById('annBtn');b.disabled=true;b.innerHTML='<span class="gb-loading gb-loading-sm"></span> 保存中';
  gbAjax({method:'POST',url:'<?php echo site_url('admin/announcement/save'); ?>',data:d,success:function(r){if(r.code===0)gbToast.success(r.msg);},complete:function(){b.disabled=false;b.innerHTML='保存';}});return false;}
function sendNotify(e){e.preventDefault();var d={};new FormData(e.target).forEach(function(v,k){d[k]=v;});
  var b=document.getElementById('notifyBtn');b.disabled=true;b.innerHTML='<span class="gb-loading gb-loading-sm"></span> 发送中';
  gbAjax({method:'POST',url:'<?php echo site_url('admin/notify/send'); ?>',data:d,success:function(r){if(r.code===0){gbToast.success(r.msg);e.target.reset();}},complete:function(){b.disabled=false;b.innerHTML='发送';}});return false;}
</script>

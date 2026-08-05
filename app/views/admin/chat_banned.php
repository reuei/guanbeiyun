<?php /** 聊天室禁言用户管理 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15;
$sourceMap = ['auto' => ['自动','tag'], 'manual' => ['手动','tag-primary']];
?>
<div class="panel">
  <div class="panel-head">
    <span class="title">禁言用户管理 <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <button class="btn btn-primary btn-sm" onclick="openBan()">手动禁言</button>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>用户</th><th>原因</th><th>来源</th><th>截止时间</th><th>创建时间</th><th>操作</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): $sm = $sourceMap[$r['source']] ?? ['未知','tag']; ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td class="text-sm font-bold"><?php echo e($r['username'] ?: ('用户#'.$r['user_id'])); ?></td>
          <td class="text-sm truncate" style="max-width:240px;"><?php echo e($r['reason'] ?: '-'); ?></td>
          <td><span class="tag <?php echo $sm[1]; ?>"><?php echo $sm[0]; ?></span></td>
          <td class="text-sm"><?php echo e(date('Y-m-d H:i', strtotime($r['banned_until']))); ?></td>
          <td class="text-muted text-sm"><?php echo e(date('Y-m-d H:i', strtotime($r['created_at']))); ?></td>
          <td><button class="btn btn-ghost btn-sm" onclick="unbanUser(<?php echo (int)$r['user_id']; ?>)">解禁</button></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="7" class="empty">暂无数据</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('admin/chat/banned'); require __DIR__ . '/../shared/pagination.php'; ?>

<div class="modal-overlay" id="banModal">
  <div class="modal-box">
    <div class="modal-head"><h3>手动禁言</h3><span class="icon-btn" onclick="gbModal.close('banModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body">
      <form id="banForm" onsubmit="return submitBan(event)">
        <div class="form-group"><label class="form-label">用户 ID <span class="req">*</span></label><input class="form-control" type="number" name="user_id" id="banUserId" placeholder="请输入用户ID" required></div>
        <div class="form-group"><label class="form-label">禁言时长（分钟）</label><input class="form-control" type="number" name="minutes" id="banMinutes" value="60"></div>
        <div class="form-group"><label class="form-label">禁言原因</label><input class="form-control" type="text" name="reason" id="banReason" placeholder="禁言原因"></div>
      </form>
    </div>
    <div class="modal-foot"><button class="btn" onclick="gbModal.close('banModal')">取消</button><button class="btn btn-primary" onclick="submitBan()">确认禁言</button></div>
  </div>
</div>
<script>
function openBan(){
  document.getElementById('banForm').reset();
  document.getElementById('banMinutes').value=60;
  gbModal.open('banModal');
}
function submitBan(e){
  if(e)e.preventDefault();
  var d={};new FormData(document.getElementById('banForm')).forEach(function(v,k){d[k]=v;});
  if(!d.user_id){gbToast.error('请输入用户ID');return false;}
  gbAjax({method:'POST',url:'<?php echo site_url('admin/chat/ban'); ?>',data:d,
    success:function(r){if(r.code===0){gbToast.success(r.msg);gbModal.close('banModal');setTimeout(function(){location.reload();},600);}}});
  return false;
}
function unbanUser(uid){
  if(!confirm('确认解禁该用户？'))return;
  gbAjax({method:'POST',url:'<?php echo site_url('admin/chat/unban'); ?>',data:{user_id:uid},
    success:function(r){if(r.code===0){gbToast.success(r.msg);setTimeout(function(){location.reload();},600);}}});
}
</script>

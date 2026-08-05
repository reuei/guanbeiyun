<?php /** 备案公示 / 失效网站公示管理 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15; $kw = $kw ?? '';
$pubType = $pubType ?? 'filing';
$isFiling = ($pubType === 'filing');
$title = $isFiling ? '备案公示管理' : '失效网站公示管理';
$statusMap = [0 => ['禁用','badge-danger'], 1 => ['启用','badge-success']];
?>
<div class="panel">
  <div class="panel-head">
    <span class="title"><?php echo $title; ?> <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <form method="get" class="toolbar" style="margin:0;">
      <div class="search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><input type="text" name="kw" value="<?php echo e($kw); ?>" placeholder="搜索标题/内容/备案号" class="form-control"></div>
      <button class="btn btn-primary btn-sm">搜索</button>
      <button type="button" class="btn btn-primary btn-sm" onclick="openPub()">新增</button>
    </form>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>标题</th><th>内容</th><?php if ($isFiling): ?><th>备案号</th><?php else: ?><th>失效原因</th><?php endif; ?><th>备案用户</th><th>链接</th><th>排序</th><th>状态</th><th>时间</th><th>操作</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): $st = $statusMap[$r['status']] ?? ['未知','badge-info']; ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td class="text-sm font-bold"><?php echo e($r['title'] ?: '(无标题)'); ?></td>
          <td class="text-sm truncate" style="max-width:240px;"><?php echo e(mb_substr($r['content'],0,40)); ?><?php echo mb_strlen($r['content'])>40?'...':''; ?></td>
          <?php if ($isFiling): ?><td class="text-sm"><?php echo e($r['icp_no'] ?: '-'); ?></td><?php else: ?><td class="text-sm truncate" style="max-width:180px;"><?php echo e($r['reason'] ?: '-'); ?></td><?php endif; ?>
          <td class="text-sm"><?php echo e($r['username'] ?: '-'); ?></td>
          <td class="text-sm truncate" style="max-width:160px;"><?php if (!empty($r['link'])): ?><a href="<?php echo e($r['link']); ?>" target="_blank"><?php echo e($r['link']); ?></a><?php else: ?><span class="text-muted">-</span><?php endif; ?></td>
          <td class="text-sm"><?php echo (int)$r['sort']; ?></td>
          <td><span class="badge <?php echo $st[1]; ?>"><?php echo $st[0]; ?></span></td>
          <td class="text-muted text-sm"><?php echo e(date('Y-m-d H:i', strtotime($r['created_at']))); ?></td>
          <td>
            <button class="btn btn-ghost btn-sm" onclick='editPub(<?php echo json_encode($r, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>)'>编辑</button>
            <button class="btn btn-danger btn-sm" onclick="delPub(<?php echo (int)$r['id']; ?>)">删除</button>
          </td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="10" class="empty">暂无数据</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('admin/publicity/' . ($isFiling?'filing':'invalid')) . ($kw?'?kw='.urlencode($kw):''); require __DIR__ . '/../shared/pagination.php'; ?>

<div class="modal-overlay" id="pubModal">
  <div class="modal-box">
    <div class="modal-head"><h3 id="pubModalTitle"><?php echo $title; ?></h3><span class="icon-btn" onclick="gbModal.close('pubModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body">
      <form id="pubForm" onsubmit="return submitPub(event)">
        <input type="hidden" name="id" id="pubId" value="0">
        <input type="hidden" name="type" value="<?php echo e($pubType); ?>">
        <div class="form-group"><label class="form-label">标题 <span class="req">*</span></label><input class="form-control" name="title" id="pubTitle" placeholder="请输入标题"></div>
        <div class="form-group"><label class="form-label">内容</label><textarea class="form-control" name="content" id="pubContent" rows="4" placeholder="公示内容"></textarea></div>
        <?php if ($isFiling): ?>
        <div class="form-group"><label class="form-label">备案号</label><input class="form-control" name="icp_no" id="pubIcpNo" placeholder="如：京ICP备XXXXXXXX号"></div>
        <?php else: ?>
        <div class="form-group"><label class="form-label">失效原因</label><input class="form-control" name="reason" id="pubReason" placeholder="网站失效原因"></div>
        <?php endif; ?>
        <div class="form-group"><label class="form-label">链接</label><input class="form-control" name="link" id="pubLink" placeholder="https://"></div>
        <div class="grid-2">
          <div class="form-group"><label class="form-label">备案用户 ID</label><input class="form-control" type="number" name="user_id" id="pubUserId" placeholder="可选，留空为当前管理员"></div>
          <div class="form-group"><label class="form-label">排序</label><input class="form-control" type="number" name="sort" id="pubSort" value="0"></div>
        </div>
        <div class="form-group"><label class="form-label">状态</label><select class="form-control" name="status" id="pubStatus"><option value="1">启用</option><option value="0">禁用</option></select></div>
      </form>
    </div>
    <div class="modal-foot"><button class="btn" onclick="gbModal.close('pubModal')">取消</button><button class="btn btn-primary" onclick="submitPub()">保存</button></div>
  </div>
</div>
<script>
function openPub(){
  document.getElementById('pubModalTitle').textContent='<?php echo $title; ?> - 新增';
  document.getElementById('pubId').value=0;
  document.getElementById('pubForm').reset();
  document.getElementById('pubSort').value=0;
  document.getElementById('pubStatus').value='1';
  gbModal.open('pubModal');
}
function editPub(r){
  document.getElementById('pubModalTitle').textContent='<?php echo $title; ?> - 编辑';
  document.getElementById('pubId').value=r.id;
  document.getElementById('pubTitle').value=r.title||'';
  document.getElementById('pubContent').value=r.content||'';
  <?php if ($isFiling): ?>document.getElementById('pubIcpNo').value=r.icp_no||'';<?php else: ?>document.getElementById('pubReason').value=r.reason||'';<?php endif; ?>
  document.getElementById('pubLink').value=r.link||'';
  document.getElementById('pubUserId').value=r.user_id||'';
  document.getElementById('pubSort').value=r.sort||0;
  document.getElementById('pubStatus').value=r.status==1?'1':'0';
  gbModal.open('pubModal');
}
function submitPub(e){
  if(e)e.preventDefault();
  var d={};new FormData(document.getElementById('pubForm')).forEach(function(v,k){d[k]=v;});
  if(!d.title){gbToast.error('请输入标题');return false;}
  gbAjax({method:'POST',url:'<?php echo site_url('admin/publicity/save'); ?>',data:d,
    success:function(r){if(r.code===0){gbToast.success(r.msg);gbModal.close('pubModal');setTimeout(function(){location.reload();},600);}}});
  return false;
}
function delPub(id){
  if(!confirm('确认删除该公示？'))return;
  gbAjax({method:'POST',url:'<?php echo site_url('admin/publicity/delete'); ?>',data:{id:id},
    success:function(r){if(r.code===0){gbToast.success(r.msg);setTimeout(function(){location.reload();},600);}}});
}
</script>

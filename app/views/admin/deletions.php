<?php /** 账号注销申请管理 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15; $status = $status ?? '';
$statusMap = [0 => ['待审核','badge-pending'], 1 => ['已通过','badge-success'], 2 => ['已驳回','badge-danger']];
?>
<div class="panel">
  <div class="panel-head">
    <span class="title">注销申请管理 <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <form method="get" class="toolbar" style="margin:0;">
      <select name="status" class="form-control" onchange="this.form.submit()" style="width:auto;">
        <option value="">全部状态</option>
        <option value="0" <?php echo $status==='0'?'selected':''; ?>>待审核</option>
        <option value="1" <?php echo $status==='1'?'selected':''; ?>>已通过</option>
        <option value="2" <?php echo $status==='2'?'selected':''; ?>>已驳回</option>
      </select>
    </form>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>用户</th><th>注销理由</th><th>状态</th><th>申请时间</th><th>审核意见</th><th>操作</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): $st = $statusMap[$r['status']] ?? ['未知','badge-info']; ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td class="text-sm"><?php echo e($r['username'] ?: '-'); ?><br><span class="text-muted"><?php echo e($r['email'] ?: ''); ?></span></td>
          <td class="text-sm truncate" style="max-width:240px;"><?php echo e(mb_substr($r['reason'],0,40)); ?><?php echo mb_strlen($r['reason'])>40?'...':''; ?></td>
          <td><span class="badge <?php echo $st[1]; ?>"><?php echo $st[0]; ?></span></td>
          <td class="text-muted text-sm"><?php echo e(date('Y-m-d H:i', strtotime($r['created_at']))); ?></td>
          <td class="text-sm truncate" style="max-width:180px;"><?php echo e($r['audit_remark'] ?: '-'); ?></td>
          <td><?php if ($r['status']==0): ?><button class="btn btn-ghost btn-sm" onclick='showDel(<?php echo json_encode($r, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>)'>审核</button><?php else: ?><span class="text-muted">-</span><?php endif; ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="7" class="empty">暂无数据</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('admin/deletions') . ($status!==''?'?status='.urlencode($status):''); require __DIR__ . '/../shared/pagination.php'; ?>

<div class="modal-overlay" id="delModal">
  <div class="modal-box">
    <div class="modal-head"><h3>注销申请审核</h3><span class="icon-btn" onclick="gbModal.close('delModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body">
      <div class="detail-list" id="delDetail" style="margin-bottom:18px;"></div>
      <div class="form-group"><label class="form-label">审核结果</label><select class="form-control" id="delStatus"><option value="1">通过</option><option value="2">驳回</option></select></div>
      <div class="form-group"><label class="form-label">审核意见</label><textarea class="form-control" id="delRemark" rows="3" placeholder="审核说明"></textarea></div>
    </div>
    <div class="modal-foot"><button class="btn" onclick="gbModal.close('delModal')">取消</button><button class="btn btn-primary" onclick="submitDel()">提交审核</button></div>
  </div>
</div>
<script>
var curDelId=0;
function showDel(r){
  curDelId=r.id;
  document.getElementById('delDetail').innerHTML=
    '<div class="dl-item"><div class="dl-label">用户</div><div class="dl-value">'+(r.username||'-')+' '+(r.email||'')+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">注销理由</div><div class="dl-value">'+(r.reason||'-')+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">申请时间</div><div class="dl-value">'+(r.created_at||'-')+'</div></div>';
  document.getElementById('delStatus').value='1';
  document.getElementById('delRemark').value='';
  gbModal.open('delModal');
}
function submitDel(){
  gbAjax({method:'POST',url:'<?php echo site_url('admin/deletion/audit'); ?>',data:{id:curDelId,status:document.getElementById('delStatus').value,audit_remark:document.getElementById('delRemark').value},
  success:function(res){if(res.code===0){gbToast.success('审核完成');gbModal.close('delModal');setTimeout(function(){location.reload();},800);}}});
}
</script>

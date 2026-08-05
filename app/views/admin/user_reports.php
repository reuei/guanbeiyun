<?php /** 用户举报管理 (用户间举报) */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15;
$status = $status ?? '';
$statusMap = [0 => '待处理', 1 => '已处理', 2 => '已驳回'];
?>
<div class="panel">
  <div class="panel-head">
    <span class="title">用户举报管理 <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <div class="filter-bar" style="display:flex;gap:6px;">
      <a class="btn btn-sm <?php echo $status===''?'btn-primary':'btn-ghost'; ?>" href="<?php echo site_url('admin/user-reports'); ?>">全部</a>
      <a class="btn btn-sm <?php echo $status==='0'?'btn-primary':'btn-ghost'; ?>" href="<?php echo site_url('admin/user-reports?status=0'); ?>">待处理</a>
      <a class="btn btn-sm <?php echo $status==='1'?'btn-primary':'btn-ghost'; ?>" href="<?php echo site_url('admin/user-reports?status=1'); ?>">已处理</a>
      <a class="btn btn-sm <?php echo $status==='2'?'btn-primary':'btn-ghost'; ?>" href="<?php echo site_url('admin/user-reports?status=2'); ?>">已驳回</a>
    </div>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>举报者</th><th>被举报用户</th><th>举报原因</th><th>状态</th><th>处理意见</th><th>举报时间</th><th>处理时间</th><th>操作</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): $st = (int)$r['status']; ?>
        <tr>
          <td><?php echo (int)$r['id']; ?></td>
          <td class="text-sm"><?php echo e($r['reporter_name'] ?? ('用户#' . $r['user_id'])); ?></td>
          <td class="text-sm"><?php echo e($r['target_name'] ?? ('用户#' . $r['target_id'])); ?></td>
          <td class="text-sm" style="max-width:240px;"><?php echo e($r['reason']); ?></td>
          <td><span class="tag <?php echo $st===0?'tag-warning':($st===1?'tag-success':'tag-danger'); ?>"><?php echo $statusMap[$st] ?? '未知'; ?></span></td>
          <td class="text-sm"><?php echo !empty($r['remark']) ? e($r['remark']) : '<span class="text-muted">-</span>'; ?></td>
          <td class="text-muted text-sm"><?php echo e($r['created_at']); ?></td>
          <td class="text-muted text-sm"><?php echo !empty($r['handled_at']) ? e($r['handled_at']) : '-'; ?></td>
          <td>
            <?php if ($st === 0): ?>
            <button class="btn btn-primary btn-sm" onclick='auditUserReport(<?php echo json_encode(["id"=>(int)$r["id"],"target_name"=>$r["target_name"]??("用户#".$r["target_id"]),"reason"=>$r["reason"]??"","reporter_name"=>$r["reporter_name"]??("用户#".$r["user_id"])], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>)'>处理</button>
            <?php else: ?>
            <button class="btn btn-ghost btn-sm" onclick='viewReportDetail(<?php echo json_encode($r, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>)'>查看</button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="9" class="empty">暂无举报记录</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('admin/user-reports'); require __DIR__ . '/../shared/pagination.php'; ?>

<!-- 处理举报弹窗 -->
<div class="modal-overlay" id="auditModal" onclick="if(event.target===this)gbModal.close('auditModal')">
  <div class="modal-box" style="max-width:500px;">
    <div class="modal-head"><h3>处理用户举报</h3><span class="icon-btn" onclick="gbModal.close('auditModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body" id="auditBody"></div>
    <form id="auditForm" onsubmit="return submitAudit(event)">
      <input type="hidden" name="id" id="auditId" value="0">
      <div class="form-group"><label class="form-label">处理结果 *</label>
        <select class="form-control" name="status" id="auditStatus">
          <option value="1">已处理 (属实/已处置)</option>
          <option value="2">已驳回 (不属实)</option>
        </select>
      </div>
      <div class="form-group"><label class="form-label">处理意见</label><textarea class="form-control" name="remark" id="auditRemark" rows="3" placeholder="处理说明，会通知举报者"></textarea></div>
      <div class="modal-foot">
        <button type="button" class="btn btn-ghost" onclick="gbModal.close('auditModal')">取消</button>
        <button type="submit" class="btn btn-primary" id="auditSubmitBtn">提交</button>
      </div>
    </form>
  </div>
</div>

<!-- 详情查看弹窗 -->
<div class="modal-overlay" id="detailModal" onclick="if(event.target===this)gbModal.close('detailModal')">
  <div class="modal-box" style="max-width:500px;">
    <div class="modal-head"><h3>举报详情</h3><span class="icon-btn" onclick="gbModal.close('detailModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body" id="detailBody"></div>
    <div class="modal-foot"><button class="btn btn-primary" onclick="gbModal.close('detailModal')">关闭</button></div>
  </div>
</div>

<script>
var statusMap={0:'待处理',1:'已处理',2:'已驳回'};
function auditUserReport(d){
  document.getElementById('auditId').value=d.id||0;
  document.getElementById('auditStatus').value=1;
  document.getElementById('auditRemark').value='';
  document.getElementById('auditBody').innerHTML='<div class="detail-list">'+
    '<div class="dl-item"><div class="dl-label">举报者</div><div class="dl-value">'+(d.reporter_name||'-')+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">被举报用户</div><div class="dl-value">'+(d.target_name||'-')+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">举报原因</div><div class="dl-value" style="white-space:pre-wrap;">'+(d.reason||'-')+'</div></div>'+
    '</div>';
  gbModal.open('auditModal');
}
function submitAudit(e){
  e.preventDefault();
  var fd=new FormData(e.target);
  var data={};fd.forEach(function(v,k){data[k]=v;});
  var btn=document.getElementById('auditSubmitBtn');btn.disabled=true;btn.textContent='提交中...';
  gbAjax({method:'POST',url:'<?php echo site_url('admin/user-report/audit'); ?>',data:data,
  success:function(r){if(r&&r.code===0){gbToast.success(r.msg);setTimeout(function(){location.reload();},500);}},
  complete:function(){btn.disabled=false;btn.textContent='提交';}});
  return false;
}
function viewReportDetail(d){
  var html='<div class="detail-list">'+
    '<div class="dl-item"><div class="dl-label">举报者</div><div class="dl-value">'+(d.reporter_name||('用户#'+d.user_id))+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">被举报用户</div><div class="dl-value">'+(d.target_name||('用户#'+d.target_id))+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">举报原因</div><div class="dl-value" style="white-space:pre-wrap;">'+(d.reason||'-')+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">处理状态</div><div class="dl-value">'+(statusMap[d.status]||'-')+'</div></div>'+
    (d.remark?'<div class="dl-item"><div class="dl-label">处理意见</div><div class="dl-value">'+d.remark+'</div></div>':'')+
    '<div class="dl-item"><div class="dl-label">举报时间</div><div class="dl-value">'+(d.created_at||'-')+'</div></div>'+
    (d.handled_at?'<div class="dl-item"><div class="dl-label">处理时间</div><div class="dl-value">'+d.handled_at+'</div></div>':'')+
    '</div>';
  document.getElementById('detailBody').innerHTML=html;
  gbModal.open('detailModal');
}
</script>

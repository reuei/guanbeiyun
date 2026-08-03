<?php /** 认证/合作方申请列表 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15;
$typeMap = ['enterprise' => '企业认证', 'personal' => '个人认证', 'partner' => '合作伙伴'];
$statusMap = [0 => ['申请中','badge-pending'], 1 => ['已通过','badge-success'], 2 => ['未通过','badge-danger']];
?>
<div class="panel">
  <div class="panel-head"><span class="title"><?php echo $pageTitle; ?> <span class="tag tag-primary"><?php echo $total; ?></span></span></div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>用户</th><th>类型</th><th>名称</th><th>公司/证件</th><th>联系方式</th><th>状态</th><th>申请时间</th><th>操作</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): $st = $statusMap[$r['status']] ?? ['未知','badge-info']; ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td><?php echo e($r['username'] ?? '-'); ?></td>
          <td><span class="tag tag-primary"><?php echo $typeMap[$r['type']] ?? $r['type']; ?></span></td>
          <td><?php echo e($r['name']); ?></td>
          <td class="text-sm"><?php echo e($r['company'] ?: $r['id_card'] ?: '-'); ?></td>
          <td class="text-sm"><?php echo e($r['phone'] ?: $r['email'] ?: '-'); ?></td>
          <td><span class="badge <?php echo $st[1]; ?>"><?php echo $st[0]; ?></span></td>
          <td class="text-muted text-sm"><?php echo e(date('Y-m-d H:i', strtotime($r['created_at']))); ?></td>
          <td><button class="btn btn-ghost btn-sm" onclick='showAudit(<?php echo json_encode($r, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>)'>审核</button></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="9" class="empty">暂无申请</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('admin/' . ($activeSub==='partner-apply'?'partner-apply':'cert-apply')); require __DIR__ . '/../shared/pagination.php'; ?>

<div class="modal-overlay" id="auditModal">
  <div class="modal-box">
    <div class="modal-head"><h3>申请审核</h3><span class="icon-btn" onclick="gbModal.close('auditModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body">
      <div class="detail-list" id="auditDetail" style="margin-bottom:18px;"></div>
      <div class="form-group"><label class="form-label">审核结果 <span class="req">*</span></label><select class="form-control" id="auditStatus"><option value="1">通过</option><option value="2">未通过</option></select></div>
      <div class="form-group"><label class="form-label">审核意见</label><textarea class="form-control" id="auditRemark" rows="3" placeholder="审核说明"></textarea></div>
    </div>
    <div class="modal-foot"><button class="btn" onclick="gbModal.close('auditModal')">取消</button><button class="btn btn-primary" onclick="submitAudit()">确认</button></div>
  </div>
</div>
<script>
var curId=0;
function showAudit(r){curId=r.id;
  document.getElementById('auditDetail').innerHTML=
    '<div class="dl-item"><div class="dl-label">名称</div><div class="dl-value">'+r.name+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">电话</div><div class="dl-value">'+(r.phone||'-')+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">邮箱</div><div class="dl-value">'+(r.email||'-')+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">公司</div><div class="dl-value">'+(r.company||'-')+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">证号</div><div class="dl-value">'+(r.license_no||r.id_card||'-')+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">简介</div><div class="dl-value">'+(r.intro||'-')+'</div></div>';
  document.getElementById('auditStatus').value='1';document.getElementById('auditRemark').value=r.audit_remark||'';
  gbModal.open('auditModal');}
function submitAudit(){gbAjax({method:'POST',url:'<?php echo site_url('admin/apply/audit'); ?>',data:{id:curId,status:document.getElementById('auditStatus').value,audit_remark:document.getElementById('auditRemark').value},
  success:function(r){if(r.code===0){gbToast.success(r.msg);gbModal.close('auditModal');setTimeout(function(){location.reload();},800);}}});}
</script>

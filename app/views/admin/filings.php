<?php /** 备案管理 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15;
$status = $status ?? ''; $kw = $kw ?? '';
$statusMap = [0 => ['审核中','badge-pending'], 1 => ['已通过','badge-success'], 2 => ['未通过','badge-danger'], 3 => ['已撤销','badge-info']];
?>
<div class="panel">
  <div class="panel-head">
    <span class="title"><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg> 备案管理 <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <form method="get" class="toolbar" style="margin:0;">
      <select name="status" class="form-control" style="width:auto;" onchange="this.form.submit()">
        <option value="">全部状态</option>
        <option value="0" <?php echo $status==='0'?'selected':''; ?>>审核中</option>
        <option value="1" <?php echo $status==='1'?'selected':''; ?>>已通过</option>
        <option value="2" <?php echo $status==='2'?'selected':''; ?>>未通过</option>
        <option value="3" <?php echo $status==='3'?'selected':''; ?>>已撤销</option>
      </select>
      <div class="search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><input type="text" name="kw" value="<?php echo e($kw); ?>" placeholder="搜索网站/域名/主办单位" class="form-control"></div>
      <button class="btn btn-primary btn-sm">搜索</button>
    </form>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>网站名称</th><th>域名</th><th>主办单位</th><th>性质</th><th>备案号</th><th>状态</th><th>申请时间</th><th>操作</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): $st = $statusMap[$r['status']] ?? ['未知','badge-info']; ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td><?php echo e($r['site_name']); ?></td>
          <td><?php echo e($r['site_domain']); ?></td>
          <td><?php echo e($r['owner_name']); ?></td>
          <td><?php echo $r['owner_type']==1?'企业':'个人'; ?></td>
          <td><?php echo $r['icp_no']?e($r['icp_no']):'<span class="text-muted">-</span>'; ?></td>
          <td><span class="badge <?php echo $st[1]; ?>"><?php echo $st[0]; ?></span></td>
          <td class="text-muted text-sm"><?php echo e(date('Y-m-d H:i', strtotime($r['created_at']))); ?></td>
          <td>
            <button class="btn btn-ghost btn-sm" onclick='showAudit(<?php echo json_encode($r, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>)'>审核</button>
          </td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="9" class="empty">暂无备案数据</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('admin/filings') . '?' . http_build_query(array_filter(['status'=>$status,'kw'=>$kw])); require __DIR__ . '/../shared/pagination.php'; ?>

<!-- 审核弹窗 -->
<div class="modal-overlay" id="auditModal">
  <div class="modal-box">
    <div class="modal-head"><h3>备案审核</h3><span class="icon-btn" onclick="gbModal.close('auditModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body">
      <div class="detail-list" id="auditDetail" style="margin-bottom:18px;"></div>
      <div class="form-group">
        <label class="form-label">备案号 (通过时填写，留空自动生成)</label>
        <input type="text" class="form-control" id="auditIcpNo" placeholder="如 京ICP备2024000000号">
      </div>
      <div class="form-group">
        <label class="form-label">审核结果 <span class="req">*</span></label>
        <select class="form-control" id="auditStatus">
          <option value="1">通过</option>
          <option value="2">未通过</option>
          <option value="3">撤销</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">审核意见</label>
        <textarea class="form-control" id="auditRemark" rows="3" placeholder="审核说明(选填)"></textarea>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn" onclick="gbModal.close('auditModal')">取消</button>
      <button class="btn btn-primary" onclick="submitAudit()">确认审核</button>
    </div>
  </div>
</div>
<script>
var curFilingId = 0;
function showAudit(r) {
  curFilingId = r.id;
  document.getElementById('auditDetail').innerHTML =
    '<div class="dl-item"><div class="dl-label">网站名称</div><div class="dl-value">'+r.site_name+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">域名</div><div class="dl-value">'+r.site_domain+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">主办单位</div><div class="dl-value">'+r.owner_name+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">性质</div><div class="dl-value">'+(r.owner_type==1?'企业':'个人')+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">证件号</div><div class="dl-value">'+(r.owner_id||'-')+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">联系电话</div><div class="dl-value">'+(r.owner_phone||'-')+'</div></div>';
  document.getElementById('auditIcpNo').value = r.icp_no || '';
  document.getElementById('auditStatus').value = r.status==1?'1':'1';
  document.getElementById('auditRemark').value = r.audit_remark || '';
  gbModal.open('auditModal');
}
function submitAudit() {
  gbAjax({
    method:'POST', url:'<?php echo site_url('admin/filing/audit'); ?>',
    data:{id:curFilingId, status:document.getElementById('auditStatus').value, icp_no:document.getElementById('auditIcpNo').value, audit_remark:document.getElementById('auditRemark').value},
    success:function(res){ if(res.code===0){ gbToast.success(res.msg); gbModal.close('auditModal'); setTimeout(function(){location.reload();},800); } }
  });
}
</script>

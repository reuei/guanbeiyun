<?php /** 备案申请管理 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15; $status = $status ?? '';
$statusMap = [0 => ['审核中','badge-pending'], 1 => ['已通过','badge-success'], 2 => ['未通过','badge-danger'], 3 => ['已撤销','badge-info']];
?>
<div class="panel">
  <div class="panel-head">
    <span class="title">备案申请管理 <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <div class="toolbar" style="margin:0;gap:10px;">
      <form method="get"><select name="status" class="form-control" style="width:auto;" onchange="this.form.submit()">
        <option value="">全部状态</option>
        <option value="0" <?php echo $status==='0'?'selected':''; ?>>审核中</option>
        <option value="1" <?php echo $status==='1'?'selected':''; ?>>已通过</option>
        <option value="2" <?php echo $status==='2'?'selected':''; ?>>未通过</option>
      </select></form>
      <button class="btn btn-primary btn-sm" onclick="gbModal.open('applyModal')">+ 新建备案申请</button>
    </div>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>网站名称</th><th>域名</th><th>主办单位</th><th>备案号</th><th>状态</th><th>申请时间</th><th>审核意见</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): $st = $statusMap[$r['status']] ?? ['未知','badge-info']; ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td><?php echo e($r['site_name']); ?></td>
          <td><?php echo e($r['site_domain']); ?></td>
          <td><?php echo e($r['owner_name']); ?></td>
          <td><?php echo $r['icp_no']?e($r['icp_no']):'<span class="text-muted">-</span>'; ?></td>
          <td><span class="badge <?php echo $st[1]; ?>"><?php echo $st[0]; ?></span></td>
          <td class="text-muted text-sm"><?php echo e(date('Y-m-d H:i', strtotime($r['created_at']))); ?></td>
          <td class="text-sm"><?php echo e($r['audit_remark'] ?: '-'); ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="8" class="empty">暂无备案申请，点击右上角"新建备案申请"</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('user/filings') . ($status!==''?'?status='.urlencode($status):''); require __DIR__ . '/../shared/pagination.php'; ?>

<div class="modal-overlay" id="applyModal">
  <div class="modal-box lg">
    <div class="modal-head"><h3>新建备案申请</h3><span class="icon-btn" onclick="gbModal.close('applyModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body">
      <form id="applyForm" onsubmit="return submitApply(event)">
        <div class="grid-2">
          <div class="form-group"><label class="form-label">网站名称 <span class="req">*</span></label><input class="form-control" name="site_name" required placeholder="网站名称"></div>
          <div class="form-group"><label class="form-label">网站域名 <span class="req">*</span></label><input class="form-control" name="site_domain" required placeholder="如 example.com"></div>
        </div>
        <div class="grid-2">
          <div class="form-group"><label class="form-label">网站URL</label><input class="form-control" name="site_url" placeholder="https://example.com"></div>
          <div class="form-group"><label class="form-label">主办单位/姓名 <span class="req">*</span></label><input class="form-control" name="owner_name" required></div>
        </div>
        <div class="grid-2">
          <div class="form-group"><label class="form-label">主办单位性质</label><select class="form-control" name="owner_type"><option value="1">企业</option><option value="2">个人</option></select></div>
          <div class="form-group"><label class="form-label">证件号码</label><input class="form-control" name="owner_id" placeholder="营业执照号/身份证号"></div>
        </div>
        <div class="grid-2">
          <div class="form-group"><label class="form-label">联系电话</label><input class="form-control" name="owner_phone"></div>
          <div class="form-group"><label class="form-label">联系邮箱</label><input class="form-control" name="owner_email"></div>
        </div>
        <div class="grid-2">
          <div class="form-group"><label class="form-label">服务器IP</label><input class="form-control" name="server_ip"></div>
          <div class="form-group"><label class="form-label">网站语言</label><input class="form-control" name="language" value="中文"></div>
        </div>
        <div class="form-group"><label class="form-label">网站内容类型</label><input class="form-control" name="content_type" placeholder="如 综合门户/电子商务"></div>
        <div class="form-group"><label class="form-label">备注</label><textarea class="form-control" name="remark" rows="2" placeholder="补充说明"></textarea></div>
      </form>
    </div>
    <div class="modal-foot"><button class="btn" onclick="gbModal.close('applyModal')">取消</button><button class="btn btn-primary" onclick="document.getElementById('applyForm').requestSubmit()">提交申请</button></div>
  </div>
</div>
<script>
function submitApply(e){
  e.preventDefault();
  var d={};new FormData(e.target).forEach(function(v,k){d[k]=v;});
  gbAjax({method:'POST',url:'<?php echo site_url('user/filing/apply'); ?>',data:d,success:function(r){
    if(r.code===0){gbToast.success(r.msg);gbModal.close('applyModal');setTimeout(function(){location.reload();},800);}
  }});
  return false;
}
</script>

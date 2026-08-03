<?php /** 认证管理 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15;
$typeMap = ['enterprise' => '企业认证', 'personal' => '个人认证'];
$statusMap = [0 => ['申请中','badge-pending'], 1 => ['已通过','badge-success'], 2 => ['未通过','badge-danger']];
?>
<div class="panel">
  <div class="panel-head"><span class="title">认证管理 <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <button class="btn btn-primary btn-sm" onclick="gbModal.open('certModal')">+ 申请认证</button>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>类型</th><th>名称</th><th>公司/证件</th><th>状态</th><th>审核意见</th><th>申请时间</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): $st = $statusMap[$r['status']] ?? ['未知','badge-info']; ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td><span class="tag tag-primary"><?php echo $typeMap[$r['type']] ?? $r['type']; ?></span></td>
          <td><?php echo e($r['name']); ?></td>
          <td class="text-sm"><?php echo e($r['company'] ?: $r['id_card'] ?: '-'); ?></td>
          <td><span class="badge <?php echo $st[1]; ?>"><?php echo $st[0]; ?></span></td>
          <td class="text-sm"><?php echo e($r['audit_remark'] ?: '-'); ?></td>
          <td class="text-muted text-sm"><?php echo e(date('Y-m-d', strtotime($r['created_at']))); ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="7" class="empty">暂无认证申请</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('user/certification'); require __DIR__ . '/../shared/pagination.php'; ?>

<div class="modal-overlay" id="certModal">
  <div class="modal-box">
    <div class="modal-head"><h3>申请认证</h3><span class="icon-btn" onclick="gbModal.close('certModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body">
      <form id="certForm" onsubmit="return submitCert(event)">
        <div class="form-group"><label class="form-label">认证类型 <span class="req">*</span></label><select class="form-control" name="type" id="certType" onchange="toggleCertFields()"><option value="personal">个人认证</option><option value="enterprise">企业认证</option></select></div>
        <div class="form-group"><label class="form-label">名称 <span class="req">*</span></label><input class="form-control" name="name" required placeholder="个人姓名或企业名称"></div>
        <div class="form-group" id="idCardField"><label class="form-label">身份证号</label><input class="form-control" name="id_card"></div>
        <div class="form-group" id="companyField" style="display:none;"><label class="form-label">公司名称</label><input class="form-control" name="company"></div>
        <div class="form-group" id="licenseField" style="display:none;"><label class="form-label">营业执照号</label><input class="form-control" name="license_no"></div>
        <div class="grid-2">
          <div class="form-group"><label class="form-label">手机号</label><input class="form-control" name="phone"></div>
          <div class="form-group"><label class="form-label">邮箱</label><input class="form-control" name="email"></div>
        </div>
        <div class="form-group"><label class="form-label">简介</label><textarea class="form-control" name="intro" rows="2"></textarea></div>
      </form>
    </div>
    <div class="modal-foot"><button class="btn" onclick="gbModal.close('certModal')">取消</button><button class="btn btn-primary" onclick="document.getElementById('certForm').requestSubmit()">提交申请</button></div>
  </div>
</div>
<script>
function toggleCertFields(){
  var t=document.getElementById('certType').value;
  document.getElementById('idCardField').style.display=t==='personal'?'block':'none';
  document.getElementById('companyField').style.display=t==='enterprise'?'block':'none';
  document.getElementById('licenseField').style.display=t==='enterprise'?'block':'none';
}
function submitCert(e){e.preventDefault();var d={};new FormData(e.target).forEach(function(v,k){d[k]=v;});
  gbAjax({method:'POST',url:'<?php echo site_url('user/certification/apply'); ?>',data:d,success:function(r){if(r.code===0){gbToast.success(r.msg);gbModal.close('certModal');setTimeout(function(){location.reload();},800);}}});return false;}
</script>

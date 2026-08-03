<?php /** 合作伙伴申请 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15;
$statusMap = [0 => ['申请中','badge-pending'], 1 => ['已通过','badge-success'], 2 => ['未通过','badge-danger']];
?>
<div class="panel">
  <div class="panel-head"><span class="title">合作伙伴申请 <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <button class="btn btn-primary btn-sm" onclick="gbModal.open('partnerModal')">+ 申请合作</button>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>名称</th><th>公司</th><th>联系方式</th><th>状态</th><th>审核意见</th><th>申请时间</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): $st = $statusMap[$r['status']] ?? ['未知','badge-info']; ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td><?php echo e($r['name']); ?></td>
          <td class="text-sm"><?php echo e($r['company'] ?: '-'); ?></td>
          <td class="text-sm"><?php echo e($r['phone'] ?: $r['email'] ?: '-'); ?></td>
          <td><span class="badge <?php echo $st[1]; ?>"><?php echo $st[0]; ?></span></td>
          <td class="text-sm"><?php echo e($r['audit_remark'] ?: '-'); ?></td>
          <td class="text-muted text-sm"><?php echo e(date('Y-m-d', strtotime($r['created_at']))); ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="7" class="empty">暂无申请</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('user/partner'); require __DIR__ . '/../shared/pagination.php'; ?>

<div class="modal-overlay" id="partnerModal">
  <div class="modal-box">
    <div class="modal-head"><h3>申请合作伙伴</h3><span class="icon-btn" onclick="gbModal.close('partnerModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body">
      <form id="partnerForm" onsubmit="return submitPartner(event)">
        <div class="form-group"><label class="form-label">名称 <span class="req">*</span></label><input class="form-control" name="name" required></div>
        <div class="form-group"><label class="form-label">公司名称</label><input class="form-control" name="company"></div>
        <div class="grid-2">
          <div class="form-group"><label class="form-label">手机号</label><input class="form-control" name="phone"></div>
          <div class="form-group"><label class="form-label">邮箱</label><input class="form-control" name="email"></div>
        </div>
        <div class="form-group"><label class="form-label">联系人</label><input class="form-control" name="contact"></div>
        <div class="form-group"><label class="form-label">合作简介</label><textarea class="form-control" name="intro" rows="3" placeholder="请描述您的合作意向"></textarea></div>
      </form>
    </div>
    <div class="modal-foot"><button class="btn" onclick="gbModal.close('partnerModal')">取消</button><button class="btn btn-primary" onclick="document.getElementById('partnerForm').requestSubmit()">提交申请</button></div>
  </div>
</div>
<script>
function submitPartner(e){e.preventDefault();var d={};new FormData(e.target).forEach(function(v,k){d[k]=v;});
  gbAjax({method:'POST',url:'<?php echo site_url('user/partner/apply'); ?>',data:d,success:function(r){if(r.code===0){gbToast.success(r.msg);gbModal.close('partnerModal');setTimeout(function(){location.reload();},800);}}});return false;}
</script>

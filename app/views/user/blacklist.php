<?php /** 黑名单管理 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15;
?>
<div class="panel">
  <div class="panel-head">
    <span class="title">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--danger);"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
      黑名单管理 <span class="tag tag-danger"><?php echo $total; ?></span>
    </span>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead>
        <tr>
          <th width="60">ID</th>
          <th>用户</th>
          <th>邮箱</th>
          <th>拉黑时间</th>
          <th width="120">操作</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): ?>
        <tr data-blocked-id="<?php echo (int)($r['blocked_id'] ?? 0); ?>">
          <td><?php echo (int)($r['blocked_id'] ?? 0); ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:10px;">
              <?php if (!empty($r['avatar'])): ?>
                <img src="<?php echo asset($r['avatar']); ?>" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
              <?php else: ?>
                <div style="width:32px;height:32px;border-radius:50%;background:var(--primary-bg);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;">
                  <?php echo e(strtoupper(mb_substr($r['username'] ?? 'U', 0, 1))); ?>
                </div>
              <?php endif; ?>
              <div>
                <div style="font-weight:500;color:var(--text);"><?php echo e($r['username'] ?? '已注销用户'); ?></div>
              </div>
            </div>
          </td>
          <td class="text-muted text-sm"><?php echo e($r['email'] ?? '-'); ?></td>
          <td class="text-muted text-sm"><?php echo e($r['created_at'] ?? '-'); ?></td>
          <td>
            <button class="btn btn-ghost btn-sm unblock-btn" data-target-id="<?php echo (int)($r['blocked_id'] ?? 0); ?>" data-username="<?php echo e($r['username'] ?? '该用户'); ?>">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              取消拉黑
            </button>
          </td>
        </tr>
        <?php endforeach; else: ?>
        <tr>
          <td colspan="5" class="empty">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--text-muted);opacity:0.5;margin-bottom:10px;"><circle cx="12" cy="12" r="10"/><path d="M8 15s1.5-2 4-2 4 2 4 2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
            <div style="color:var(--text-muted);font-size:14px;">黑名单为空，您没有拉黑任何用户</div>
            <div style="color:var(--text-muted);font-size:12px;margin-top:4px;">当您拉黑用户后，可以在这里管理</div>
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('user/blacklist'); require __DIR__ . '/../shared/pagination.php'; ?>

<script>
(function() {
  var btns = document.querySelectorAll('.unblock-btn');
  btns.forEach(function(btn) {
    btn.addEventListener('click', function() {
      var targetId = parseInt(btn.getAttribute('data-target-id'), 10) || 0;
      var username = btn.getAttribute('data-username') || '该用户';
      if (!confirm('确定要取消拉黑 ' + username + ' 吗？')) return;
      gbAjax({
        method: 'POST',
        url: '<?php echo site_url('user/unblock'); ?>',
        data: { target_id: targetId },
        success: function(r) {
          if (r && r.code === 0) {
            gbToast('已取消拉黑', 'success');
            var tr = btn.closest('tr');
            if (tr) {
              tr.style.transition = 'all 0.3s';
              tr.style.opacity = '0';
              tr.style.transform = 'translateX(20px)';
              setTimeout(function() {
                tr.remove();
                var tbody = document.querySelector('.table tbody');
                if (tbody && tbody.querySelectorAll('tr').length === 0) {
                  tbody.innerHTML = '<tr><td colspan="5" class="empty"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--text-muted);opacity:0.5;margin-bottom:10px;"><circle cx="12" cy="12" r="10"/><path d="M8 15s1.5-2 4-2 4 2 4 2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg><div style="color:var(--text-muted);font-size:14px;">黑名单为空，您没有拉黑任何用户</div><div style="color:var(--text-muted);font-size:12px;margin-top:4px;">当您拉黑用户后，可以在这里管理</div></td></tr>';
                }
                var tag = document.querySelector('.tag-danger');
                if (tag) {
                  var cur = parseInt(tag.textContent, 10) || 0;
                  tag.textContent = Math.max(0, cur - 1);
                }
              }, 280);
            }
          }
        }
      });
    });
  });
})();
</script>

<?php /** 我的日志 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15;
?>
<div class="panel">
  <div class="panel-head"><span class="title">我的日志 <span class="tag tag-primary"><?php echo $total; ?></span></span></div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th width="60">ID</th><th>内容</th><th>IP</th><th>时间</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td class="text-sm"><?php echo e($r['content']); ?></td>
          <td class="text-muted text-sm"><?php echo e($r['ip']); ?></td>
          <td class="text-muted text-sm"><?php echo e($r['created_at']); ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="4" class="empty">暂无日志</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('user/logs'); require __DIR__ . '/../shared/pagination.php'; ?>

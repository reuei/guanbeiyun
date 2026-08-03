<?php /** 消息通知列表 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15;
?>
<div class="panel">
  <div class="panel-head"><span class="title">消息通知记录 <span class="tag tag-primary"><?php echo $total; ?></span></span></div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>接收对象</th><th>标题</th><th>内容</th><th>类型</th><th>发送时间</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td><?php echo $r['user_id']==0?'<span class="tag tag-primary">全体</span>':'用户#'.$r['user_id']; ?></td>
          <td><?php echo e($r['title']); ?></td>
          <td class="text-sm truncate" style="max-width:240px;"><?php echo e(mb_substr($r['content'],0,40)); ?></td>
          <td><span class="tag"><?php echo e($r['type']); ?></span></td>
          <td class="text-muted text-sm"><?php echo e($r['created_at']); ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="6" class="empty">暂无通知</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('admin/notify'); require __DIR__ . '/../shared/pagination.php'; ?>

<?php /** 反馈与举报管理 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15;
$statusMap = [0 => ['待处理','badge-pending'], 1 => ['已处理','badge-success'], 2 => ['已关闭','badge-info']];
$typeMap = ['feedback' => '反馈', 'report' => '举报'];
?>
<div class="panel">
  <div class="panel-head"><span class="title">我的反馈与举报 <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <div class="toolbar" style="margin:0;gap:8px;">
      <a class="btn btn-primary btn-sm" href="<?php echo site_url('feedback'); ?>">+ 提交反馈</a>
      <a class="btn btn-danger btn-sm" href="<?php echo site_url('report'); ?>">+ 提交举报</a>
    </div>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>类型</th><th>标题</th><th>内容</th><th>状态</th><th>回复</th><th>时间</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): $st = $statusMap[$r['status']] ?? ['未知','badge-info']; ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td><span class="tag <?php echo $r['type']==='report'?'tag-danger':'tag-primary'; ?>"><?php echo $typeMap[$r['type']] ?? $r['type']; ?></span></td>
          <td class="text-sm"><?php echo e($r['title'] ?: '(无标题)'); ?></td>
          <td class="text-sm truncate" style="max-width:200px;"><?php echo e(mb_substr($r['content'],0,30)); ?></td>
          <td><span class="badge <?php echo $st[1]; ?>"><?php echo $st[0]; ?></span></td>
          <td class="text-sm truncate" style="max-width:200px;"><?php echo e($r['reply'] ?: '-'); ?></td>
          <td class="text-muted text-sm"><?php echo e(date('Y-m-d', strtotime($r['created_at']))); ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="7" class="empty">暂无记录</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('user/feedback'); require __DIR__ . '/../shared/pagination.php'; ?>

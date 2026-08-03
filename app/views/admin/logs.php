<?php /** 日志列表 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15; $logType = $logType ?? 'system';
?>
<div class="panel">
  <div class="panel-head">
    <span class="title"><?php echo $pageTitle; ?> <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <button class="btn btn-danger btn-sm" onclick="clearLogs('<?php echo e($logType); ?>')">清空日志</button>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th width="60">ID</th><th>内容</th><th>角色</th><th>用户ID</th><th>IP</th><th>时间</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td class="text-sm"><?php echo e($r['content']); ?></td>
          <td><span class="tag"><?php echo e($r['role']); ?></span></td>
          <td class="text-sm"><?php echo $r['user_id']; ?></td>
          <td class="text-muted text-sm"><?php echo e($r['ip']); ?></td>
          <td class="text-muted text-sm"><?php echo e($r['created_at']); ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="6" class="empty">暂无日志</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php
$map = ['system'=>'system','login'=>'login','operation'=>'operation'];
$baseUrl = site_url('admin/logs/' . $map[$logType]);
require __DIR__ . '/../shared/pagination.php';
?>
<script>
function clearLogs(t){if(!confirm('确定清空'+t+'日志?此操作不可恢复!'))return;
  gbAjax({method:'GET',url:'<?php echo site_url('admin/logs/clear'); ?>?type='+t,success:function(r){if(r.code===0){gbToast.success(r.msg);setTimeout(function(){location.reload();},600);}}});}
</script>

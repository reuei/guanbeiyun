<?php /** 后台私信查看 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15;
$kw = $kw ?? ''; $fromId = $fromId ?? 0; $toId = $toId ?? 0;
?>
<div class="panel">
  <div class="panel-head">
    <span class="title">私信查看 <span class="tag tag-primary"><?php echo $total; ?></span></span>
  </div>
  <div class="panel-body" style="padding:12px 16px 0;">
    <form method="get" action="" class="grid-3" style="gap:10px;align-items:end;">
      <div class="form-group"><label class="form-label">发送方 ID</label><input type="number" name="from_id" class="form-control" value="<?php echo (int)$fromId; ?>" placeholder="0=不限"></div>
      <div class="form-group"><label class="form-label">接收方 ID</label><input type="number" name="to_id" class="form-control" value="<?php echo (int)$toId; ?>" placeholder="0=不限"></div>
      <div class="form-group"><label class="form-label">内容关键字</label><input type="text" name="kw" class="form-control" value="<?php echo e($kw); ?>" placeholder="搜索消息内容"></div>
      <div class="form-group"><button type="submit" class="btn btn-primary">搜索</button> <a class="btn btn-ghost" href="<?php echo site_url('admin/private-messages'); ?>">重置</a></div>
    </form>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>发送方</th><th>接收方</th><th>内容</th><th>类型</th><th>状态</th><th>时间</th><th>操作</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r):
          $content = $r['content'] ?? '';
          $isImage = ($r['msg_type'] ?? '') === 'image';
          $isEmoji = ($r['msg_type'] ?? '') === 'emoji';
        ?>
        <tr>
          <td><?php echo (int)$r['id']; ?></td>
          <td class="text-sm">
            <?php if (!empty($r['from_avatar'])): ?><img src="<?php echo asset($r['from_avatar']); ?>" alt="" style="width:20px;height:20px;border-radius:50%;vertical-align:middle;"><?php endif; ?>
            <?php echo e($r['from_name'] ?? ('用户#' . $r['from_id'])); ?>
          </td>
          <td class="text-sm">
            <?php if (!empty($r['to_avatar'])): ?><img src="<?php echo asset($r['to_avatar']); ?>" alt="" style="width:20px;height:20px;border-radius:50%;vertical-align:middle;"><?php endif; ?>
            <?php echo e($r['to_name'] ?? ('用户#' . $r['to_id'])); ?>
          </td>
          <td class="text-sm" style="max-width:260px;">
            <?php if ($isImage): ?>
              <a href="<?php echo e(strpos($content,'data:')===0?$content:asset($content)); ?>" target="_blank">[图片消息]</a>
            <?php elseif ($isEmoji): ?>
              <span style="font-size:20px;"><?php echo e($content); ?></span>
            <?php else: ?>
              <?php echo e(mb_substr($content,0,60)); ?>
            <?php endif; ?>
          </td>
          <td><span class="tag"><?php echo e($r['msg_type'] ?? 'text'); ?></span></td>
          <td><?php echo (int)$r['is_read']===1?'<span class="tag tag-success">已读</span>':'<span class="tag tag-warning">未读</span>'; ?></td>
          <td class="text-muted text-sm"><?php echo e($r['created_at']); ?></td>
          <td><button class="btn btn-danger btn-sm" onclick="delPM(<?php echo (int)$r['id']; ?>)">删除</button></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="8" class="empty">暂无私信记录</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('admin/private-messages'); require __DIR__ . '/../shared/pagination.php'; ?>

<script>
function delPM(id){
  if(!confirm('确认删除此私信?')) return;
  gbAjax({method:'POST',url:'<?php echo site_url('admin/private-message/delete'); ?>',data:{id:id},success:function(r){
    if(r&&r.code===0){gbToast.success('已删除');setTimeout(function(){location.reload();},500);}
  }});
}
</script>

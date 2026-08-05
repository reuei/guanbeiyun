<?php /** 聊天室消息管理 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15; $kw = $kw ?? '';
$typeMap = ['text' => ['文本','tag-primary'], 'image' => ['图片','tag'], 'emoji' => ['表情','tag'], 'url' => ['链接','tag']];
?>
<div class="panel">
  <div class="panel-head">
    <span class="title">聊天室消息管理 <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <form method="get" class="toolbar" style="margin:0;">
      <div class="search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><input type="text" name="kw" value="<?php echo e($kw); ?>" placeholder="搜索内容/用户名" class="form-control"></div>
      <button class="btn btn-primary btn-sm">搜索</button>
    </form>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>用户</th><th>类型</th><th>内容</th><th>撤回状态</th><th>时间</th><th>操作</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): $tm = $typeMap[$r['msg_type']] ?? ['未知','tag']; ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td class="text-sm" style="white-space:nowrap;">
            <?php if (!empty($r['avatar'])): ?><img src="<?php echo asset($r['avatar']); ?>" style="width:24px;height:24px;border-radius:50%;object-fit:cover;vertical-align:middle;"><?php else: ?><span style="display:inline-block;width:24px;height:24px;border-radius:50%;background:var(--primary);color:#fff;text-align:center;line-height:24px;font-size:12px;vertical-align:middle;"><?php echo e(strtoupper(mb_substr($r['username'] ?: 'U',0,1))); ?></span><?php endif; ?>
            <span style="vertical-align:middle;"><?php echo e($r['username'] ?: '-'); ?></span>
          </td>
          <td><span class="tag <?php echo $tm[1]; ?>"><?php echo $tm[0]; ?></span></td>
          <td class="text-sm">
            <?php if ($r['msg_type']==='image' && !empty($r['content'])): ?>
              <img src="<?php echo asset($r['content']); ?>" style="width:40px;height:40px;border-radius:4px;object-fit:cover;">
            <?php else: ?>
              <span class="truncate" style="display:inline-block;max-width:280px;vertical-align:middle;"><?php echo e(mb_substr($r['content'],0,40)); ?><?php echo mb_strlen($r['content'])>40?'...':''; ?></span>
            <?php endif; ?>
          </td>
          <td><?php if ($r['is_recalled']==1): ?><span class="badge badge-danger">已撤回</span><?php else: ?><span class="badge badge-success">正常</span><?php endif; ?></td>
          <td class="text-muted text-sm"><?php echo e(date('Y-m-d H:i', strtotime($r['created_at']))); ?></td>
          <td><?php if ($r['is_recalled']==0): ?><button class="btn btn-danger btn-sm" onclick="recallMsg(<?php echo (int)$r['id']; ?>)">撤回</button><?php else: ?><span class="text-muted">-</span><?php endif; ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="7" class="empty">暂无数据</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('admin/chat') . ($kw?'?kw='.urlencode($kw):''); require __DIR__ . '/../shared/pagination.php'; ?>
<script>
function recallMsg(id){
  if(!confirm('确认撤回该消息？'))return;
  gbAjax({method:'POST',url:'<?php echo site_url('admin/chat/delete'); ?>',data:{id:id},
    success:function(r){if(r.code===0){gbToast.success(r.msg);setTimeout(function(){location.reload();},600);}}});
}
</script>

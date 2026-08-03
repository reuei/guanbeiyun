<?php /** 用户管理 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15; $kw = $kw ?? '';
?>
<div class="panel">
  <div class="panel-head">
    <span class="title"><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg> 用户列表 <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <form method="get" class="toolbar" style="margin:0;">
      <div class="search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><input type="text" name="kw" value="<?php echo e($kw); ?>" placeholder="搜索用户名/邮箱/手机号" class="form-control"></div>
      <button class="btn btn-primary btn-sm">搜索</button>
    </form>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>用户名</th><th>邮箱</th><th>手机号</th><th>状态</th><th>最后登录</th><th>注册时间</th><th>操作</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td><div style="display:flex;align-items:center;gap:8px;"><div style="width:28px;height:28px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;"><?php echo e(strtoupper(mb_substr($r['username'],0,1))); ?></div><?php echo e($r['username']); ?></div></td>
          <td><?php echo e($r['email'] ?: '-'); ?></td>
          <td><?php echo e($r['phone'] ?: '-'); ?></td>
          <td><?php if ($r['status']==1): ?><span class="badge badge-success">正常</span><?php else: ?><span class="badge badge-danger">禁用</span><?php endif; ?></td>
          <td class="text-muted text-sm"><?php echo $r['last_login'] ? e(time_ago($r['last_login'])) : '未登录'; ?></td>
          <td class="text-muted text-sm"><?php echo e($r['created_at']); ?></td>
          <td>
            <button class="btn btn-ghost btn-sm" onclick="toggleUser(<?php echo $r['id']; ?>,<?php echo $r['status']; ?>)"><?php echo $r['status']==1?'禁用':'启用'; ?></button>
          </td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="8" class="empty">暂无用户数据</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('admin/users') . ($kw ? '?kw=' . urlencode($kw) : ''); require __DIR__ . '/../shared/pagination.php'; ?>

<script>
function toggleUser(id, cur) {
  gbAjax({
    method:'POST', url:'<?php echo site_url('admin/user/toggle'); ?>',
    data:{id:id, status: cur==1?0:1},
    success:function(res){ if(res.code===0){ gbToast.success('已更新'); setTimeout(function(){location.reload();},800); } }
  });
}
</script>

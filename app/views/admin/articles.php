<?php /** 文章管理 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15; $cat = $cat ?? '';
$catMap = ['article'=>'公告','privacy'=>'隐私政策','policy'=>'用户协议'];
?>
<div class="panel">
  <div class="panel-head">
    <span class="title">文章管理 <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <div class="toolbar" style="margin:0;">
      <form method="get" style="display:flex;gap:10px;">
        <select name="cat" class="form-control" style="width:auto;" onchange="this.form.submit()">
          <option value="">全部分类</option>
          <?php foreach ($catMap as $k=>$v): ?><option value="<?php echo $k; ?>" <?php echo $cat===$k?'selected':''; ?>><?php echo $v; ?></option><?php endforeach; ?>
        </select>
      </form>
      <a class="btn btn-primary btn-sm" href="<?php echo site_url('admin/article/edit'); ?>">+ 新建文章</a>
    </div>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>标题</th><th>分类</th><th>状态</th><th>浏览</th><th>更新时间</th><th>操作</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td><?php echo e($r['title']); ?></td>
          <td><span class="tag tag-primary"><?php echo $catMap[$r['category']] ?? $r['category']; ?></span></td>
          <td><?php echo $r['status']==1?'<span class="badge badge-success">已发布</span>':'<span class="badge badge-info">草稿</span>'; ?></td>
          <td><?php echo $r['views']; ?></td>
          <td class="text-muted text-sm"><?php echo e($r['updated_at']); ?></td>
          <td>
            <a class="btn btn-ghost btn-sm" href="<?php echo site_url('admin/article/edit?id='.$r['id']); ?>">编辑</a>
            <button class="btn btn-ghost btn-sm text-danger" onclick="delArt(<?php echo $r['id']; ?>)">删除</button>
          </td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="7" class="empty">暂无文章</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('admin/articles') . ($cat?'?cat='.urlencode($cat):''); require __DIR__ . '/../shared/pagination.php'; ?>
<script>
function delArt(id){
  if(!confirm('确定删除该文章?'))return;
  gbAjax({method:'POST',url:'<?php echo site_url('admin/article/delete'); ?>',data:{id:id},success:function(r){if(r.code===0){gbToast.success(r.msg);setTimeout(function(){location.reload();},600);}}});
}
</script>

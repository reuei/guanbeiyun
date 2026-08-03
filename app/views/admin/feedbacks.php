<?php /** 反馈/举报管理 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15; $kw = $kw ?? '';
$fbType = $fbType ?? 'feedback';
$statusMap = [0 => ['待处理','badge-pending'], 1 => ['已处理','badge-success'], 2 => ['已关闭','badge-info']];
$title = $fbType === 'report' ? '举报管理' : '反馈管理';
?>
<div class="panel">
  <div class="panel-head">
    <span class="title"><?php echo $title; ?> <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <form method="get" class="toolbar" style="margin:0;">
      <div class="search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><input type="text" name="kw" value="<?php echo e($kw); ?>" placeholder="搜索标题/内容" class="form-control"></div>
      <button class="btn btn-primary btn-sm">搜索</button>
    </form>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>标题</th><th>内容</th><?php if ($fbType==='report'): ?><th>举报目标</th><?php endif; ?><th>联系人</th><th>状态</th><th>时间</th><th>操作</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): $st = $statusMap[$r['status']] ?? ['未知','badge-info']; ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td class="text-sm"><?php echo e($r['title'] ?: '(无标题)'); ?></td>
          <td class="text-sm truncate" style="max-width:240px;"><?php echo e(mb_substr($r['content'],0,40)); ?>...</td>
          <?php if ($fbType==='report'): ?><td class="text-sm truncate" style="max-width:180px;"><a href="<?php echo e($r['target_url']); ?>" target="_blank"><?php echo e($r['target_url']); ?></a></td><?php endif; ?>
          <td class="text-sm"><?php echo e($r['name'] ?: '-'); ?><br><span class="text-muted"><?php echo e($r['contact'] ?: ''); ?></span></td>
          <td><span class="badge <?php echo $st[1]; ?>"><?php echo $st[0]; ?></span></td>
          <td class="text-muted text-sm"><?php echo e(date('Y-m-d H:i', strtotime($r['created_at']))); ?></td>
          <td><button class="btn btn-ghost btn-sm" onclick='showFbDetail(<?php echo json_encode($r, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>)'>处理</button></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="<?php echo $fbType==='report'?8:7; ?>" class="empty">暂无数据</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('admin/' . ($fbType==='report'?'reports':'feedbacks')) . ($kw?'?kw='.urlencode($kw):''); require __DIR__ . '/../shared/pagination.php'; ?>

<div class="modal-overlay" id="fbModal">
  <div class="modal-box lg">
    <div class="modal-head"><h3>详情处理</h3><span class="icon-btn" onclick="gbModal.close('fbModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body">
      <div class="detail-list" id="fbDetail" style="margin-bottom:18px;"></div>
      <div class="form-group"><label class="form-label">处理状态</label><select class="form-control" id="fbStatus"><option value="1">已处理</option><option value="2">已关闭</option></select></div>
      <div class="form-group"><label class="form-label">处理回复</label><textarea class="form-control" id="fbReply" rows="3" placeholder="处理说明"></textarea></div>
    </div>
    <div class="modal-foot"><button class="btn" onclick="gbModal.close('fbModal')">取消</button><button class="btn btn-primary" onclick="submitFb()">提交处理</button></div>
  </div>
</div>
<script>
var curFbId=0;
function showFbDetail(r){
  curFbId=r.id;
  document.getElementById('fbDetail').innerHTML=
    '<div class="dl-item"><div class="dl-label">标题</div><div class="dl-value">'+(r.title||'-')+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">内容</div><div class="dl-value">'+r.content+'</div></div>'+
    (r.target_url?'<div class="dl-item"><div class="dl-label">目标</div><div class="dl-value">'+r.target_url+'</div></div>':'')+
    '<div class="dl-item"><div class="dl-label">联系人</div><div class="dl-value">'+(r.name||'-')+' '+(r.contact||'')+'</div></div>'+
    (r.reply?'<div class="dl-item"><div class="dl-label">已有回复</div><div class="dl-value">'+r.reply+'</div></div>':'');
  document.getElementById('fbStatus').value=r.status==2?'2':'1';
  document.getElementById('fbReply').value=r.reply||'';
  gbModal.open('fbModal');
}
function submitFb(){
  gbAjax({method:'POST',url:'<?php echo site_url('admin/feedback/reply'); ?>',data:{id:curFbId,status:document.getElementById('fbStatus').value,reply:document.getElementById('fbReply').value},
  success:function(res){if(res.code===0){gbToast.success('已处理');gbModal.close('fbModal');setTimeout(function(){location.reload();},800);}}});
}
</script>

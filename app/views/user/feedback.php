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
      <thead><tr><th>ID</th><th>类型</th><th>标题</th><th>内容</th><th>状态</th><th>回复</th><th>时间</th><th>操作</th></tr></thead>
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
          <td><button class="btn btn-ghost btn-sm" onclick='showFbDetail(<?php echo (int)$r['id']; ?>)'>详情</button></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="8" class="empty">暂无记录</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('user/feedback'); require __DIR__ . '/../shared/pagination.php'; ?>

<!-- 详情弹窗 -->
<div class="modal-overlay" id="fbDetailModal">
  <div class="modal-box">
    <div class="modal-head"><h3>反馈详情</h3><span class="icon-btn" onclick="gbModal.close('fbDetailModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body">
      <div class="detail-list" id="fbDetailContent"></div>
    </div>
    <div class="modal-foot"><button class="btn" onclick="gbModal.close('fbDetailModal')">关闭</button></div>
  </div>
</div>
<script>
function esc(s){ return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function dl(label, value){ return '<div class="dl-item"><div class="dl-label">'+label+'</div><div class="dl-value">'+(value==null||value===''?'-':value)+'</div></div>'; }
function showFbDetail(id){
  gbAjax({method:'GET', url:'<?php echo site_url('user/feedback/detail'); ?>?id='+id, success:function(res){
    if(res.code!==0){ gbToast.error(res.msg); return; }
    var f = res.data.feedback;
    var typeMap = {feedback:'反馈', report:'举报'};
    var stMap = {0:'待处理', 1:'已处理', 2:'已关闭'};
    var html = '';
    html += dl('类型', '<span class="tag tag-primary">'+(typeMap[f.type]||f.type)+'</span>');
    html += dl('标题', esc(f.title));
    html += dl('内容', esc(f.content));
    if(f.target_url){ html += dl('举报目标', '<a href="'+esc(f.target_url)+'" target="_blank">'+esc(f.target_url)+'</a>'); }
    html += dl('状态', '<span class="badge badge-info">'+(stMap[f.status]||'未知')+'</span>');
    html += dl('处理回复', esc(f.reply));
    html += dl('处理时间', f.replied_at || '-');
    html += dl('提交时间', f.created_at || '-');
    document.getElementById('fbDetailContent').innerHTML = html;
    gbModal.open('fbDetailModal');
  }});
}
</script>

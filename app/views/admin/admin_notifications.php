<?php /** 后台消息通知 (管理员自身收到的通知) */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15;
?>
<div class="panel">
  <div class="panel-head">
    <span class="title">消息通知 <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <div>
      <button class="btn btn-ghost btn-sm" onclick="readAllAdminNotify()"><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> 全部已读</button>
    </div>
  </div>
  <div class="panel-body" id="notifyList" style="padding:0;">
    <?php if ($rows): foreach ($rows as $r):
      $unread = (int)$r['is_read'] === 0;
      $rData = json_encode([
        'id' => (int)$r['id'],
        'title' => $r['title'] ?? '',
        'content' => $r['content'] ?? '',
        'type' => $r['type'] ?? 'system',
        'created_at' => $r['created_at'] ?? '',
        'is_read' => (int)$r['is_read'],
      ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    ?>
    <div class="notify-row<?php echo $unread ? ' unread' : ''; ?>" data-id="<?php echo (int)$r['id']; ?>" onclick='showAdminNotifyDetail(<?php echo $rData; ?>, this)'>
      <div class="notify-main">
        <div class="notify-title<?php echo $unread ? ' font-bold' : ''; ?>">
          <?php if ($unread): ?><span class="unread-dot"></span><?php endif; ?>
          <?php echo e($r['title']); ?>
        </div>
        <div class="notify-content text-sm"><?php echo e(mb_substr(strip_tags($r['content']),0,60)); ?></div>
        <div class="notify-meta">
          <span class="tag"><?php echo e($r['type']); ?></span>
          <span class="text-muted time-ago"><?php echo e($r['created_at']); ?></span>
        </div>
      </div>
      <button class="btn btn-danger btn-sm" onclick="event.stopPropagation();deleteAdminNotify(<?php echo (int)$r['id']; ?>,this)">删除</button>
    </div>
    <?php endforeach; else: ?>
    <div class="empty" style="padding:40px 0;">暂无通知</div>
    <?php endif; ?>
  </div>
</div>
<?php $baseUrl = site_url('admin/notifications'); require __DIR__ . '/../shared/pagination.php'; ?>

<!-- 通知详情弹窗 -->
<div class="modal-overlay" id="adminNotifyDetailModal" onclick="if(event.target===this)gbModal.close('adminNotifyDetailModal')">
  <div class="modal-box" style="max-width:520px;">
    <div class="modal-head"><h3 id="anTitle">通知详情</h3><span class="icon-btn" onclick="gbModal.close('adminNotifyDetailModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body" id="anBody"></div>
    <div class="modal-foot"><button class="btn btn-primary" onclick="gbModal.close('adminNotifyDetailModal')">关闭</button></div>
  </div>
</div>

<style>
.notify-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 18px;border-bottom:1px solid var(--divider);cursor:pointer;transition:background .2s;}
.notify-row:hover{background:var(--bg-hover);}
.notify-row.unread{background:var(--primary-bg);}
.notify-row.unread:hover{background:var(--primary-bg-2);}
.notify-main{flex:1;min-width:0;}
.notify-title{font-size:14px;color:var(--text);display:flex;align-items:center;gap:6px;}
.notify-content{color:var(--text-muted);margin-top:3px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.notify-meta{display:flex;align-items:center;gap:8px;margin-top:6px;font-size:12px;}
.unread-dot{width:8px;height:8px;border-radius:50%;background:var(--danger,#ef4444);display:inline-block;flex-shrink:0;}
</style>

<script>
function showAdminNotifyDetail(d, el){
  if(el && el.classList.contains('unread')){
    gbAjax({method:'POST',url:'<?php echo site_url('admin/notification/read'); ?>',data:{id:d.id},toast:false,success:function(){
      el.classList.remove('unread');
      el.querySelector('.unread-dot')&&el.querySelector('.unread-dot').remove();
      el.querySelector('.notify-title').classList.remove('font-bold');
    }});
  }
  var html='<div class="detail-list">'+
    '<div class="dl-item"><div class="dl-label">标题</div><div class="dl-value">'+(d.title||'-')+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">类型</div><div class="dl-value">'+(d.type||'-')+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">内容</div><div class="dl-value" style="white-space:pre-wrap;">'+(d.content||'-')+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">时间</div><div class="dl-value">'+(d.created_at||'-')+'</div></div>'+
    '</div>';
  document.getElementById('anBody').innerHTML=html;
  gbModal.open('adminNotifyDetailModal');
}
function readAllAdminNotify(){
  gbAjax({method:'POST',url:'<?php echo site_url('admin/notification/read_all'); ?>',success:function(r){
    if(r&&r.code===0){gbToast.success('已全部标记为已读');setTimeout(function(){location.reload();},500);}
  }});
}
function deleteAdminNotify(id, btn){
  if(!confirm('确认删除该通知?')) return;
  gbAjax({method:'POST',url:'<?php echo site_url('admin/notification/delete'); ?>',data:{id:id},success:function(r){
    if(r&&r.code===0){
      var row=btn.closest('.notify-row');
      if(row)row.remove();
      gbToast.success('已删除');
    }
  }});
}
</script>

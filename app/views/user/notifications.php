<?php /** 消息通知 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15;
$typeMap = [
    'system'  => ['系统', 'tag-primary'],
    'filing'  => ['备案', 'badge-info'],
    'ticket'  => ['工单', 'badge-pending'],
    'cert'    => ['认证', 'badge-success'],
    'account' => ['账号', 'badge-danger'],
];
?>
<div class="panel">
  <div class="panel-head"><span class="title">消息通知 <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <div class="toolbar" style="margin:0;gap:8px;">
      <button class="btn btn-ghost btn-sm" onclick="markAllRead()" id="readAllBtn">全部已读</button>
    </div>
  </div>
  <div class="panel-body" style="padding:0;">
    <?php if ($rows): foreach ($rows as $n):
      $tp = $typeMap[$n['type']] ?? [($n['type'] ?: '通知'), 'tag-primary'];
      $unread = empty($n['is_read']);
      $nData = json_encode([
        'title' => $n['title'],
        'content' => $n['content'],
        'type' => $tp[0],
        'created_at' => $n['created_at'],
        'link' => $n['link'] ?? '',
        'is_all' => (int)$n['user_id'] === 0,
      ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    ?>
    <div class="notify-row<?php echo $unread ? ' unread' : ''; ?>" data-id="<?php echo (int)$n['id']; ?>" onclick='showNotifyDetail(<?php echo $nData; ?>, this)'>
      <div class="notify-main">
        <div class="notify-title<?php echo $unread ? ' font-bold' : ''; ?>">
          <?php if ($unread): ?><span class="unread-dot"></span><?php endif; ?>
          <?php echo e($n['title']); ?>
        </div>
        <div class="notify-content text-sm"><?php echo e(mb_substr(strip_tags($n['content']),0,60)); ?></div>
        <div class="notify-meta">
          <span class="tag <?php echo $tp[1]; ?>"><?php echo e($tp[0]); ?></span>
          <?php if ((int)$n['user_id'] === 0): ?><span class="badge badge-info">全体</span><?php endif; ?>
          <span class="text-muted time-ago"><?php echo e(time_ago($n['created_at'])); ?></span>
        </div>
      </div>
    </div>
    <?php endforeach; else: ?>
    <div class="empty" style="padding:40px 0;">暂无通知</div>
    <?php endif; ?>
  </div>
</div>

<!-- 通知详情弹窗 -->
<div class="modal-overlay" id="notifyDetailModal" onclick="if(event.target===this)gbModal.close('notifyDetailModal')">
  <div class="modal-box" style="max-width:560px;">
    <div class="modal-head"><h3 id="ndTitle">通知详情</h3><span class="icon-btn" onclick="gbModal.close('notifyDetailModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body" id="ndBody"></div>
    <div class="modal-foot">
      <a class="btn btn-ghost" id="ndLink" href="#" style="display:none;" target="_blank" rel="noopener">查看相关</a>
      <button class="btn btn-primary" onclick="gbModal.close('notifyDetailModal')">关闭</button>
    </div>
  </div>
</div>
<?php $baseUrl = site_url('user/notifications'); require __DIR__ . '/../shared/pagination.php'; ?>
<style>
.notify-row{display:flex;align-items:flex-start;gap:14px;padding:14px 20px;border-bottom:1px solid var(--divider);cursor:pointer;transition:background var(--transition);}
.notify-row:hover{background:var(--primary-bg);}
.notify-row.unread{background:var(--primary-bg);}
.notify-main{flex:1;min-width:0;}
.notify-title{font-size:14px;color:var(--text);display:flex;align-items:center;gap:8px;}
.notify-content{color:var(--text-muted);margin-top:4px;word-break:break-word;}
.notify-meta{display:flex;align-items:center;gap:8px;margin-top:8px;flex-wrap:wrap;}
.notify-meta .time-ago{font-size:11px;margin-left:auto;}
.unread-dot{width:8px;height:8px;border-radius:50%;background:var(--danger);flex-shrink:0;display:inline-block;}
</style>
<script>
function markRead(el){
  var id=el.getAttribute('data-id');if(!id)return;
  if(!el.classList.contains('unread'))return;
  gbAjax({method:'POST',url:'<?php echo site_url('user/notification/read'); ?>',data:{id:id},toast:false,success:function(r){
    if(r&&r.code===0){
      el.classList.remove('unread');
      var t=el.querySelector('.notify-title');
      if(t){t.classList.remove('font-bold');var d=t.querySelector('.unread-dot');if(d)d.remove();}
    }
  }});
}
function showNotifyDetail(d, el){
  if(el && el.classList.contains('unread')) markRead(el);
  var html='<div class="detail-list">'+
    '<div class="dl-item"><div class="dl-label">标题</div><div class="dl-value">'+(d.title||'-')+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">类型</div><div class="dl-value">'+(d.type||'-')+'</div></div>'+
    (d.is_all?'<div class="dl-item"><div class="dl-label">接收对象</div><div class="dl-value">全体用户</div></div>':'')+
    '<div class="dl-item"><div class="dl-label">内容</div><div class="dl-value" style="white-space:pre-wrap;">'+(d.content||'-')+'</div></div>'+
    '<div class="dl-item"><div class="dl-label">时间</div><div class="dl-value">'+(d.created_at||'-')+'</div></div>'+
    '</div>';
  document.getElementById('ndBody').innerHTML=html;
  var linkEl=document.getElementById('ndLink');
  if(d.link){linkEl.href=d.link;linkEl.style.display='inline-flex';}
  else{linkEl.style.display='none';}
  gbModal.open('notifyDetailModal');
}
function markAllRead(){
  var b=document.getElementById('readAllBtn');if(!b)return;
  b.disabled=true;var oh=b.innerHTML;b.innerHTML='<span class="gb-loading gb-loading-sm"></span> 处理中';
  gbAjax({method:'POST',url:'<?php echo site_url('user/notification/read_all'); ?>',success:function(r){
    if(r&&r.code===0){gbToast.success(r.msg||'已全部标记已读');setTimeout(function(){location.reload();},600);}
  },complete:function(){b.disabled=false;b.innerHTML=oh;}});
}
</script>

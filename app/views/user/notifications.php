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
    ?>
    <div class="notify-row<?php echo $unread ? ' unread' : ''; ?>" data-id="<?php echo (int)$n['id']; ?>" onclick="markRead(this)">
      <div class="notify-main">
        <div class="notify-title<?php echo $unread ? ' font-bold' : ''; ?>">
          <?php if ($unread): ?><span class="unread-dot"></span><?php endif; ?>
          <?php echo e($n['title']); ?>
        </div>
        <div class="notify-content text-sm"><?php echo e($n['content']); ?></div>
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
function markAllRead(){
  var b=document.getElementById('readAllBtn');if(!b)return;
  b.disabled=true;var oh=b.innerHTML;b.innerHTML='<span class="gb-loading gb-loading-sm"></span> 处理中';
  gbAjax({method:'POST',url:'<?php echo site_url('user/notification/read_all'); ?>',success:function(r){
    if(r&&r.code===0){gbToast.success(r.msg||'已全部标记已读');setTimeout(function(){location.reload();},600);}
  },complete:function(){b.disabled=false;b.innerHTML=oh;}});
}
</script>

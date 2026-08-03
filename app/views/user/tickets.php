<?php /** 工单管理 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15;
$statusMap = [0 => ['待回复','badge-pending'], 1 => ['已回复','badge-success'], 2 => ['已关闭','badge-info']];
$catMap = ['general'=>'综合','filing'=>'备案','account'=>'账号','other'=>'其他'];
?>
<div class="panel">
  <div class="panel-head"><span class="title">我的工单 <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <button class="btn btn-primary btn-sm" onclick="gbModal.open('ticketModal')">+ 发送工单</button>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>标题</th><th>分类</th><th>状态</th><th>创建时间</th><th>更新时间</th><th>操作</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): $st = $statusMap[$r['status']] ?? ['未知','badge-info']; ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td><?php echo e($r['title']); ?></td>
          <td><span class="tag"><?php echo $catMap[$r['category']] ?? $r['category']; ?></span></td>
          <td><span class="badge <?php echo $st[1]; ?>"><?php echo $st[0]; ?></span></td>
          <td class="text-muted text-sm"><?php echo e(date('Y-m-d H:i', strtotime($r['created_at']))); ?></td>
          <td class="text-muted text-sm"><?php echo e($r['updated_at']?date('Y-m-d H:i', strtotime($r['updated_at'])):'-'); ?></td>
          <td><button class="btn btn-ghost btn-sm" onclick="showChat(<?php echo $r['id']; ?>)">查看/回复</button></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="7" class="empty">暂无工单</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('user/tickets'); require __DIR__ . '/../shared/pagination.php'; ?>

<!-- 新建工单 -->
<div class="modal-overlay" id="ticketModal">
  <div class="modal-box">
    <div class="modal-head"><h3>发送工单</h3><span class="icon-btn" onclick="gbModal.close('ticketModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body">
      <form id="createForm" onsubmit="return createTicket(event)">
        <div class="form-group"><label class="form-label">标题 <span class="req">*</span></label><input class="form-control" name="title" required></div>
        <div class="grid-2">
          <div class="form-group"><label class="form-label">分类</label><select class="form-control" name="category"><option value="general">综合</option><option value="filing">备案</option><option value="account">账号</option><option value="other">其他</option></select></div>
          <div class="form-group"><label class="form-label">优先级</label><select class="form-control" name="priority"><option value="1">低</option><option value="2" selected>中</option><option value="3">高</option></select></div>
        </div>
        <div class="form-group"><label class="form-label">内容 <span class="req">*</span></label><textarea class="form-control" name="content" rows="4" required></textarea></div>
      </form>
    </div>
    <div class="modal-foot"><button class="btn" onclick="gbModal.close('ticketModal')">取消</button><button class="btn btn-primary" onclick="document.getElementById('createForm').requestSubmit()">发送</button></div>
  </div>
</div>

<!-- 工单对话 -->
<div class="modal-overlay" id="chatModal">
  <div class="modal-box lg">
    <div class="modal-head"><h3>工单对话</h3><span class="icon-btn" onclick="gbModal.close('chatModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body">
      <div id="chatArea" style="max-height:340px;overflow-y:auto;margin-bottom:16px;"></div>
      <div class="form-group"><label class="form-label">回复内容</label><textarea class="form-control" id="replyContent" rows="3" placeholder="输入回复"></textarea></div>
    </div>
    <div class="modal-foot"><button class="btn" onclick="gbModal.close('chatModal')">关闭</button><button class="btn btn-primary" onclick="sendReply()">发送回复</button></div>
  </div>
</div>
<script>
var curTicket=0;
function createTicket(e){e.preventDefault();var d={};new FormData(e.target).forEach(function(v,k){d[k]=v;});
  gbAjax({method:'POST',url:'<?php echo site_url('user/ticket/create'); ?>',data:d,success:function(r){if(r.code===0){gbToast.success(r.msg);gbModal.close('ticketModal');setTimeout(function(){location.reload();},800);}}});return false;}
function showChat(id){curTicket=id;
  gbAjax({method:'GET',url:'<?php echo site_url('admin/ticket/detail'); ?>?id='+id,success:function(res){
    if(res.code===0){var html='';(res.data.replies||[]).forEach(function(m){
      var me=m.role==='user';
      html+='<div style="margin-bottom:12px;display:flex;flex-direction:column;align-items:'+(me?'flex-end':'flex-start')+';">'+
        '<span class="text-muted text-sm">'+(me?'我':'客服')+' · '+m.created_at+'</span>'+
        '<div style="margin-top:4px;padding:10px 14px;border-radius:6px;max-width:80%;background:'+(me?'var(--primary)':'var(--bg-soft)')+';color:'+(me?'#fff':'var(--text)')+';white-space:pre-wrap;">'+m.content+'</div></div>';
    });
    document.getElementById('chatArea').innerHTML=html||'<div class="empty">暂无消息</div>';
    gbModal.open('chatModal');
    var a=document.getElementById('chatArea');a.scrollTop=a.scrollHeight;
  }});}
function sendReply(){var c=document.getElementById('replyContent').value.trim();if(!c){gbToast.warning('请输入回复');return;}
  gbAjax({method:'POST',url:'<?php echo site_url('user/ticket/reply'); ?>',data:{id:curTicket,content:c},success:function(r){
    if(r.code===0){gbToast.success(r.msg);document.getElementById('replyContent').value='';showChat(curTicket);}}});}
</script>

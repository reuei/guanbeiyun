<?php /** 工单管理 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15; $status = $status ?? '';
$statusMap = [0 => ['待回复','badge-pending'], 1 => ['已回复','badge-success'], 2 => ['已关闭','badge-info']];
?>
<div class="panel">
  <div class="panel-head">
    <span class="title"><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> 工单管理 <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <form method="get" class="toolbar" style="margin:0;">
      <select name="status" class="form-control" style="width:auto;" onchange="this.form.submit()">
        <option value="">全部状态</option>
        <option value="0" <?php echo $status==='0'?'selected':''; ?>>待回复</option>
        <option value="1" <?php echo $status==='1'?'selected':''; ?>>已回复</option>
        <option value="2" <?php echo $status==='2'?'selected':''; ?>>已关闭</option>
      </select>
    </form>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>用户</th><th>标题</th><th>分类</th><th>优先级</th><th>状态</th><th>创建时间</th><th>操作</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): $st = $statusMap[$r['status']] ?? ['未知','badge-info'];
          $catMap = ['general'=>'综合','filing'=>'备案','account'=>'账号','other'=>'其他'];
          $priMap = [1=>['低','badge-info'],2=>['中','badge-pending'],3=>['高','badge-danger']];
        ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td><?php echo e($r['username'] ?? '-'); ?></td>
          <td><?php echo e($r['title']); ?></td>
          <td><?php echo $catMap[$r['category']] ?? $r['category']; ?></td>
          <td><span class="badge <?php echo ($priMap[$r['priority']]??['','badge-info'])[1]; ?>"><?php echo ($priMap[$r['priority']]??['未知',''])[0]; ?></span></td>
          <td><span class="badge <?php echo $st[1]; ?>"><?php echo $st[0]; ?></span></td>
          <td class="text-muted text-sm"><?php echo e(date('Y-m-d H:i', strtotime($r['created_at']))); ?></td>
          <td><button class="btn btn-ghost btn-sm" onclick="showTicket(<?php echo $r['id']; ?>)">查看/回复</button></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="8" class="empty">暂无工单</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('admin/tickets') . ($status!==''?'?status='.urlencode($status):''); require __DIR__ . '/../shared/pagination.php'; ?>

<div class="modal-overlay" id="ticketModal">
  <div class="modal-box lg">
    <div class="modal-head"><h3>工单详情</h3><span class="icon-btn" onclick="gbModal.close('ticketModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body">
      <div id="ticketChat" style="max-height:320px;overflow-y:auto;margin-bottom:16px;"></div>
      <div class="form-group"><label class="form-label">回复内容</label><textarea class="form-control" id="ticketReply" rows="3" placeholder="输入回复内容"></textarea></div>
    </div>
    <div class="modal-foot"><button class="btn" onclick="gbModal.close('ticketModal')">关闭</button><button class="btn btn-primary" onclick="submitTicketReply()">发送回复</button></div>
  </div>
</div>
<script>
var curTicketId=0;
function showTicket(id){
  curTicketId=id;
  gbAjax({method:'GET',url:'<?php echo site_url('admin/ticket/detail'); ?>?id='+id,success:function(res){
    if(res.code===0){
      var html='';
      (res.data.replies||[]).forEach(function(m){
        var isAdmin=m.role==='admin';
        html+='<div style="margin-bottom:12px;display:flex;flex-direction:column;align-items:'+(isAdmin?'flex-end':'flex-start')+';">'+
          '<span class="text-muted text-sm">'+(isAdmin?'管理员':'用户')+' · '+m.created_at+'</span>'+
          '<div style="margin-top:4px;padding:10px 14px;border-radius:6px;max-width:80%;background:'+(isAdmin?'var(--primary)':'var(--bg-soft)')+';color:'+(isAdmin?'#fff':'var(--text)')+';">'+m.content+'</div></div>';
      });
      document.getElementById('ticketChat').innerHTML=html||'<div class="empty">暂无回复</div>';
      gbModal.open('ticketModal');
    }
  }});
}
function submitTicketReply(){
  var c=document.getElementById('ticketReply').value.trim();
  if(!c){gbToast.warning('请输入回复内容');return;}
  gbAjax({method:'POST',url:'<?php echo site_url('admin/ticket/reply'); ?>',data:{id:curTicketId,content:c},
  success:function(res){if(res.code===0){gbToast.success(res.msg);document.getElementById('ticketReply').value='';showTicket(curTicketId);}}});
}
</script>

<?php /** 聊天室违禁词管理 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15;
?>
<div class="panel">
  <div class="panel-head">
    <span class="title">违禁词管理 <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <button class="btn btn-primary btn-sm" onclick="openWord()">添加违禁词</button>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>违禁词</th><th>添加时间</th><th>操作</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td class="text-sm font-bold"><?php echo e($r['word']); ?></td>
          <td class="text-muted text-sm"><?php echo e(date('Y-m-d H:i', strtotime($r['created_at']))); ?></td>
          <td><button class="btn btn-danger btn-sm" onclick="delWord(<?php echo (int)$r['id']; ?>)">删除</button></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="4" class="empty">暂无数据</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('admin/chat/words'); require __DIR__ . '/../shared/pagination.php'; ?>

<div class="modal-overlay" id="wordModal">
  <div class="modal-box">
    <div class="modal-head"><h3>添加违禁词</h3><span class="icon-btn" onclick="gbModal.close('wordModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body">
      <form id="wordForm" onsubmit="return submitWord(event)">
        <div class="form-group"><label class="form-label">违禁词 <span class="req">*</span></label><input class="form-control" type="text" name="word" id="wordInput" placeholder="请输入违禁词" required></div>
      </form>
    </div>
    <div class="modal-foot"><button class="btn" onclick="gbModal.close('wordModal')">取消</button><button class="btn btn-primary" onclick="submitWord()">添加</button></div>
  </div>
</div>
<script>
function openWord(){
  document.getElementById('wordForm').reset();
  gbModal.open('wordModal');
}
function submitWord(e){
  if(e)e.preventDefault();
  var d={};new FormData(document.getElementById('wordForm')).forEach(function(v,k){d[k]=v;});
  if(!d.word){gbToast.error('请输入违禁词');return false;}
  gbAjax({method:'POST',url:'<?php echo site_url('admin/chat/word/save'); ?>',data:d,
    success:function(r){if(r.code===0){gbToast.success(r.msg);gbModal.close('wordModal');setTimeout(function(){location.reload();},600);}}});
  return false;
}
function delWord(id){
  if(!confirm('确认删除该违禁词？'))return;
  gbAjax({method:'POST',url:'<?php echo site_url('admin/chat/word/delete'); ?>',data:{id:id},
    success:function(r){if(r.code===0){gbToast.success(r.msg);setTimeout(function(){location.reload();},600);}}});
}
</script>

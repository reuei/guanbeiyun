<?php /** 首页公示管理 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15; $type = $type ?? '';
$typeMap = ['partner' => '合作方', 'invalid' => '失效/违规'];
?>
<div class="panel">
  <div class="panel-head">
    <span class="title">首页公示管理 <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <div class="toolbar" style="margin:0;gap:10px;">
      <form method="get"><select name="type" class="form-control" style="width:auto;" onchange="this.form.submit()">
        <option value="">全部类型</option>
        <?php foreach ($typeMap as $k=>$v): ?><option value="<?php echo $k; ?>" <?php echo $type===$k?'selected':''; ?>><?php echo $v; ?></option><?php endforeach; ?>
      </select></form>
      <button class="btn btn-primary btn-sm" onclick="addPub()">+ 新增公示</button>
    </div>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>类型</th><th>标题</th><th>内容</th><th>链接</th><th>状态</th><th>时间</th><th>操作</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td><span class="tag <?php echo $r['type']==='partner'?'tag-success':'tag-danger'; ?>"><?php echo $typeMap[$r['type']] ?? $r['type']; ?></span></td>
          <td><?php echo e($r['title']); ?></td>
          <td class="text-sm truncate" style="max-width:200px;"><?php echo e(mb_substr($r['content'],0,30)); ?></td>
          <td class="text-sm truncate" style="max-width:160px;"><?php echo $r['link']?e($r['link']):'-'; ?></td>
          <td><?php echo $r['status']==1?'<span class="badge badge-success">显示</span>':'<span class="badge badge-info">隐藏</span>'; ?></td>
          <td class="text-muted text-sm"><?php echo e(date('Y-m-d', strtotime($r['created_at']))); ?></td>
          <td>
            <button class="btn btn-ghost btn-sm" onclick='editPub(<?php echo json_encode($r, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>)'>编辑</button>
            <button class="btn btn-ghost btn-sm text-danger" onclick="delPub(<?php echo $r['id']; ?>)">删除</button>
          </td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="8" class="empty">暂无公示</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('admin/publicity') . ($type?'?type='.urlencode($type):''); require __DIR__ . '/../shared/pagination.php'; ?>

<div class="modal-overlay" id="pubModal">
  <div class="modal-box">
    <div class="modal-head"><h3 id="pubTitle">新增公示</h3><span class="icon-btn" onclick="gbModal.close('pubModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body">
      <input type="hidden" id="pubId" value="0">
      <div class="form-group"><label class="form-label">类型 <span class="req">*</span></label><select class="form-control" id="pubType"><option value="partner">合作方公示</option><option value="invalid">失效/违规公示</option></select></div>
      <div class="form-group"><label class="form-label">标题 <span class="req">*</span></label><input class="form-control" id="pubTitleInput"></div>
      <div class="form-group"><label class="form-label">内容</label><textarea class="form-control" id="pubContent" rows="3"></textarea></div>
      <div class="form-group"><label class="form-label">链接</label><input class="form-control" id="pubLink" placeholder="选填"></div>
      <div class="grid-2">
        <div class="form-group"><label class="form-label">状态</label><select class="form-control" id="pubStatus"><option value="1">显示</option><option value="0">隐藏</option></select></div>
        <div class="form-group"><label class="form-label">排序</label><input class="form-control" type="number" id="pubSort" value="0"></div>
      </div>
    </div>
    <div class="modal-foot"><button class="btn" onclick="gbModal.close('pubModal')">取消</button><button class="btn btn-primary" onclick="savePub()">保存</button></div>
  </div>
</div>
<script>
function addPub(){document.getElementById('pubTitle').textContent='新增公示';document.getElementById('pubId').value=0;document.getElementById('pubType').value='partner';document.getElementById('pubTitleInput').value='';document.getElementById('pubContent').value='';document.getElementById('pubLink').value='';document.getElementById('pubStatus').value=1;document.getElementById('pubSort').value=0;gbModal.open('pubModal');}
function editPub(r){document.getElementById('pubTitle').textContent='编辑公示';document.getElementById('pubId').value=r.id;document.getElementById('pubType').value=r.type;document.getElementById('pubTitleInput').value=r.title;document.getElementById('pubContent').value=r.content||'';document.getElementById('pubLink').value=r.link||'';document.getElementById('pubStatus').value=r.status;document.getElementById('pubSort').value=r.sort||0;gbModal.open('pubModal');}
function savePub(){var d={id:document.getElementById('pubId').value,type:document.getElementById('pubType').value,title:document.getElementById('pubTitleInput').value,content:document.getElementById('pubContent').value,link:document.getElementById('pubLink').value,status:document.getElementById('pubStatus').value,sort:document.getElementById('pubSort').value};
  if(!d.title){gbToast.warning('请输入标题');return;}
  gbAjax({method:'POST',url:'<?php echo site_url('admin/publicity/save'); ?>',data:d,success:function(r){if(r.code===0){gbToast.success(r.msg);gbModal.close('pubModal');setTimeout(function(){location.reload();},600);}}});}
function delPub(id){if(!confirm('确定删除?'))return;gbAjax({method:'POST',url:'<?php echo site_url('admin/publicity/delete'); ?>',data:{id:id},success:function(r){if(r.code===0){gbToast.success(r.msg);setTimeout(function(){location.reload();},600);}}});}
</script>

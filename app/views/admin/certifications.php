<?php /** 认证图片配置 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15;
?>
<div class="panel">
  <div class="panel-head">
    <span class="title">认证图片配置 <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <button class="btn btn-primary btn-sm" onclick="openCert()">新增认证</button>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>认证名称</th><th>图片</th><th>说明</th><th>图标样式</th><th>排序</th><th>状态</th><th>添加时间</th><th>操作</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): $rj = $r; $rj['image_url'] = !empty($r['image']) ? asset($r['image']) : ''; ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td class="text-sm font-bold"><?php echo e($r['name']); ?></td>
          <td><?php if (!empty($r['image'])): ?><img src="<?php echo asset($r['image']); ?>" style="width:32px;height:32px;border-radius:4px;object-fit:cover;"><?php else: ?><span class="text-muted">-</span><?php endif; ?></td>
          <td class="text-sm truncate" style="max-width:240px;"><?php echo e($r['info'] ?: '-'); ?></td>
          <td class="text-sm"><span class="tag"><?php echo e($r['icon_style']); ?></span></td>
          <td class="text-sm"><?php echo (int)$r['sort']; ?></td>
          <td><?php if ($r['status']==1): ?><span class="badge badge-success">正常</span><?php else: ?><span class="badge badge-danger">禁用</span><?php endif; ?></td>
          <td class="text-muted text-sm"><?php echo e(date('Y-m-d H:i', strtotime($r['created_at']))); ?></td>
          <td>
            <button class="btn btn-ghost btn-sm" onclick='editCert(<?php echo json_encode($rj, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>)'>编辑</button>
            <button class="btn btn-danger btn-sm" onclick="delCert(<?php echo (int)$r['id']; ?>)">删除</button>
          </td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="9" class="empty">暂无数据</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('admin/certifications'); require __DIR__ . '/../shared/pagination.php'; ?>

<div class="modal-overlay" id="certModal">
  <div class="modal-box">
    <div class="modal-head"><h3 id="certModalTitle">新增认证</h3><span class="icon-btn" onclick="gbModal.close('certModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body">
      <form id="certForm" onsubmit="return submitCert(event)">
        <input type="hidden" name="id" id="certId" value="0">
        <div class="form-group"><label class="form-label">认证名称 <span class="req">*</span></label><input class="form-control" name="name" id="certName" placeholder="如：ICP认证"></div>
        <div class="form-group">
          <label class="form-label">认证图片</label>
          <div style="display:flex;align-items:center;gap:12px;">
            <img id="certImgPrev" src="" style="width:48px;height:48px;border-radius:6px;object-fit:cover;border:1px solid var(--border);display:none;">
            <button type="button" class="btn btn-sm" onclick="uploadCertImg()">上传图片</button>
            <span class="text-muted text-sm" id="certImgPath"></span>
          </div>
          <input type="hidden" name="image" id="certImage" value="">
        </div>
        <div class="form-group"><label class="form-label">说明</label><textarea class="form-control" name="info" id="certInfo" rows="3" placeholder="认证说明信息"></textarea></div>
        <div class="grid-2">
          <div class="form-group"><label class="form-label">图标样式</label><input class="form-control" name="icon_style" id="certIconStyle" value="default" placeholder="default"></div>
          <div class="form-group"><label class="form-label">排序</label><input class="form-control" type="number" name="sort" id="certSort" value="0"></div>
        </div>
        <div class="form-group"><label class="form-label">状态</label><select class="form-control" name="status" id="certStatus"><option value="1">正常</option><option value="0">禁用</option></select></div>
      </form>
    </div>
    <div class="modal-foot"><button class="btn" onclick="gbModal.close('certModal')">取消</button><button class="btn btn-primary" onclick="submitCert()">保存</button></div>
  </div>
</div>
<script>
function openCert(){
  document.getElementById('certModalTitle').textContent='新增认证';
  document.getElementById('certId').value=0;
  document.getElementById('certForm').reset();
  document.getElementById('certIconStyle').value='default';
  document.getElementById('certSort').value=0;
  document.getElementById('certStatus').value='1';
  document.getElementById('certImage').value='';
  document.getElementById('certImgPrev').style.display='none';
  document.getElementById('certImgPrev').src='';
  document.getElementById('certImgPath').textContent='';
  gbModal.open('certModal');
}
function editCert(r){
  document.getElementById('certModalTitle').textContent='编辑认证';
  document.getElementById('certId').value=r.id;
  document.getElementById('certName').value=r.name||'';
  document.getElementById('certInfo').value=r.info||'';
  document.getElementById('certIconStyle').value=r.icon_style||'default';
  document.getElementById('certSort').value=r.sort||0;
  document.getElementById('certStatus').value=r.status==1?'1':'0';
  document.getElementById('certImage').value=r.image||'';
  if(r.image){
    document.getElementById('certImgPrev').src=r.image_url||'';
    document.getElementById('certImgPrev').style.display='block';
    document.getElementById('certImgPath').textContent=r.image;
  }else{
    document.getElementById('certImgPrev').style.display='none';
    document.getElementById('certImgPath').textContent='';
  }
  gbModal.open('certModal');
}
function uploadCertImg(){
  var inp=document.createElement('input'); inp.type='file'; inp.accept='image/*';
  inp.onchange=function(){
    if(!inp.files[0])return;
    var fd=new FormData(); fd.append('file',inp.files[0]);
    gbAjax({method:'POST',url:'<?php echo site_url('admin/upload'); ?>',data:fd,
      success:function(r){
        if(r.code===0){
          document.getElementById('certImage').value=r.data.url;
          document.getElementById('certImgPrev').src=r.data.full;
          document.getElementById('certImgPrev').style.display='block';
          document.getElementById('certImgPath').textContent=r.data.url;
          gbToast.success('上传成功');
        }else{gbToast.error(r.msg||'上传失败');}
      }});
  };
  inp.click();
}
function submitCert(e){
  if(e)e.preventDefault();
  var d={};new FormData(document.getElementById('certForm')).forEach(function(v,k){d[k]=v;});
  if(!d.name){gbToast.error('请输入认证名称');return false;}
  gbAjax({method:'POST',url:'<?php echo site_url('admin/certification/save'); ?>',data:d,
    success:function(r){if(r.code===0){gbToast.success(r.msg);gbModal.close('certModal');setTimeout(function(){location.reload();},600);}}});
  return false;
}
function delCert(id){
  if(!confirm('确认删除该认证？'))return;
  gbAjax({method:'POST',url:'<?php echo site_url('admin/certification/delete'); ?>',data:{id:id},
    success:function(r){if(r.code===0){gbToast.success(r.msg);setTimeout(function(){location.reload();},600);}}});
}
</script>

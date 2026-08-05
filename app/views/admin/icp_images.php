<?php
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15;
?>
<style>
.mock-preview-box {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: linear-gradient(135deg, #0c2461, #1e3799);
  border-radius: 6px;
  padding: 3px 10px 3px 3px;
  height: 30px;
  vertical-align: middle;
}
.mock-preview-box img {
  height: 24px;
  border-radius: 4px;
}
.mock-preview-text {
  color: #fff;
  font-size: 13px;
  font-weight: 600;
  white-space: nowrap;
}
.mock-wrap-label {
  display: block;
  font-size: 12px;
  color: var(--text-muted, #6b7280);
  margin-bottom: 4px;
  font-weight: 600;
}
</style>
<div class="panel">
  <div class="panel-head">
    <span class="title">ICP 备案号前图片 <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <button class="btn btn-primary btn-sm" onclick="editIcpImage(0)"><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> 新增图片</button>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>模拟展示效果</th><th>名称</th><th>链接</th><th>排序</th><th>状态</th><th>创建时间</th><th>操作</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): ?>
        <tr>
          <td><?php echo (int)$r['id']; ?></td>
          <td>
            <?php if (!empty($r['image'])): ?>
              <span class="mock-wrap-label">模拟展示：</span>
              <span class="mock-preview-box">
                <img src="<?php echo asset($r['image']); ?>" alt="">
                <span class="mock-preview-text">管ICP备20260982号</span>
              </span>
            <?php else: ?><span class="text-muted">-</span><?php endif; ?>
          </td>
          <td><?php echo e($r['name']); ?></td>
          <td class="text-sm"><?php echo !empty($r['link']) ? e($r['link']) : '<span class="text-muted">-</span>'; ?></td>
          <td><?php echo (int)$r['sort']; ?></td>
          <td><span class="tag <?php echo (int)$r['status']===1?'tag-success':'tag-muted'; ?>"><?php echo (int)$r['status']===1?'启用':'禁用'; ?></span></td>
          <td class="text-muted text-sm"><?php echo e($r['created_at']); ?></td>
          <td>
            <button class="btn btn-ghost btn-sm" onclick='editIcpImage(<?php echo json_encode($r, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>)'>编辑</button>
            <button class="btn btn-danger btn-sm" onclick="delIcpImage(<?php echo (int)$r['id']; ?>)">删除</button>
          </td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="8" class="empty">暂无图片，请点击右上角新增</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('admin/icp-images'); require __DIR__ . '/../shared/pagination.php'; ?>

<!-- 编辑弹窗 -->
<div class="modal-overlay" id="icpEditModal" onclick="if(event.target===this)gbModal.close('icpEditModal')">
  <div class="modal-box" style="max-width:520px;">
    <div class="modal-head"><h3 id="icpEditTitle">新增 ICP 图片</h3><span class="icon-btn" onclick="gbModal.close('icpEditModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <form id="icpForm" onsubmit="return saveIcpImage(event)">
      <input type="hidden" name="id" id="icpId" value="0">
      <div class="form-group">
        <label class="form-label">图片名称 *</label>
        <input type="text" class="form-control" name="name" id="icpName" required placeholder="如：萌ICP备、公安备案">
      </div>
      <div class="form-group">
        <label class="form-label">图片 *</label>
        <div class="form-hint" style="margin-bottom:10px;background:linear-gradient(135deg,rgba(59,130,246,0.06),rgba(147,51,234,0.05));border:1px solid rgba(59,130,246,0.2);border-radius:6px;padding:10px 12px;">
          <div style="font-weight:600;margin-bottom:4px;">📐 图片规范提示</div>
          <div class="text-sm" style="line-height:1.7;">
            • 展示高度：<b>24px</b>（行内展示），建议设计时考虑比例<br>
            • 比例建议：<b>1:1 正方形</b>（徽章效果最佳），至少 <b>48x48 像素</b><br>
            • 文件类型：推荐 <b>SVG / 透明 PNG</b>，支持 JPG/WEBP<br>
            • 文件大小：<b>最大 512KB</b>，越小加载越快
          </div>
        </div>
        <div class="upload-box" onclick="uploadIcpImage()">
          <svg class="up-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          <div class="text-sm">点击上传</div>
        </div>
        <div class="upload-preview" id="prev_icp" style="display:none;">
          <img id="img_icp" src="">
          <div style="flex:1">
            <div class="text-sm" id="path_icp"></div>
            <div style="margin-top:8px;">
              <span class="mock-wrap-label">预览效果：</span>
              <span class="mock-preview-box">
                <img id="mock_icp" src="" style="height:24px;border-radius:4px;">
                <span class="mock-preview-text">管ICP备20260982号</span>
              </span>
            </div>
            <button type="button" class="btn btn-danger btn-sm mt-2" onclick="clearIcpImage()">删除</button>
          </div>
        </div>
        <input type="hidden" name="image" id="input_icp" value="">
      </div>
      <div class="form-group">
        <label class="form-label">点击跳转链接</label>
        <input type="text" class="form-control" name="link" id="icpLink" placeholder="如：https://icp.gov.moe/">
      </div>
      <div class="grid-2">
        <div class="form-group"><label class="form-label">排序 (越大越靠前)</label><input type="number" class="form-control" name="sort" id="icpSort" value="0"></div>
        <div class="form-group"><label class="form-label">状态</label>
          <select class="form-control" name="status" id="icpStatus">
            <option value="1">启用</option>
            <option value="0">禁用</option>
          </select>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-ghost" onclick="gbModal.close('icpEditModal')">取消</button>
        <button type="submit" class="btn btn-primary" id="icpSaveBtn">保存</button>
      </div>
    </form>
  </div>
</div>

<script>
function editIcpImage(d){
  if(typeof d === 'object' && d){
    document.getElementById('icpEditTitle').textContent='编辑 ICP 图片';
    document.getElementById('icpId').value=d.id||0;
    document.getElementById('icpName').value=d.name||'';
    document.getElementById('icpLink').value=d.link||'';
    document.getElementById('icpSort').value=d.sort||0;
    document.getElementById('icpStatus').value=d.status!=null?d.status:1;
    document.getElementById('input_icp').value=d.image||'';
    if(d.image){
      var base = '<?php echo site_url(); ?>/';
      var rel = d.image.replace(/^\//,'');
      var full = base + rel;
      document.getElementById('img_icp').src=full;
      document.getElementById('mock_icp').src=full;
      document.getElementById('path_icp').textContent=d.image;
      document.getElementById('prev_icp').style.display='flex';
    }else{
      document.getElementById('prev_icp').style.display='none';
    }
  }else{
    document.getElementById('icpEditTitle').textContent='新增 ICP 图片';
    document.getElementById('icpId').value=0;
    document.getElementById('icpName').value='';
    document.getElementById('icpLink').value='';
    document.getElementById('icpSort').value=0;
    document.getElementById('icpStatus').value=1;
    document.getElementById('input_icp').value='';
    document.getElementById('prev_icp').style.display='none';
  }
  gbModal.open('icpEditModal');
}
function uploadIcpImage(){
  var inp=document.createElement('input'); inp.type='file'; inp.accept='image/png,image/svg+xml,image/jpeg,image/webp,image/*';
  inp.onchange=function(){
    var fd=new FormData(); fd.append('file',inp.files[0]);
    var xhr=new XMLHttpRequest();
    xhr.open('POST','<?php echo site_url('admin/upload'); ?>');
    xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
    xhr.onreadystatechange=function(){
      if(xhr.readyState!==4)return;
      try{var r=JSON.parse(xhr.responseText); if(r.code===0){
        document.getElementById('input_icp').value=r.data.url;
        document.getElementById('img_icp').src=r.data.full;
        document.getElementById('mock_icp').src=r.data.full;
        document.getElementById('path_icp').textContent=r.data.url;
        document.getElementById('prev_icp').style.display='flex';
        gbToast.success('上传成功');
      }else{gbToast.error(r.msg||'上传失败');}}catch(e){gbToast.error('上传失败');}
    };
    xhr.send(fd);
  };
  inp.click();
}
function clearIcpImage(){
  document.getElementById('input_icp').value='';
  document.getElementById('prev_icp').style.display='none';
  gbToast.success('已清除');
}
function saveIcpImage(e){
  e.preventDefault();
  var fd=new FormData(e.target);
  var data={};fd.forEach(function(v,k){data[k]=v;});
  if(!data.image){gbToast.error('请上传图片');return false;}
  var btn=document.getElementById('icpSaveBtn');btn.disabled=true;btn.textContent='保存中...';
  gbAjax({method:'POST',url:'<?php echo site_url('admin/icp-image/save'); ?>',data:data,
  success:function(res){if(res.code===0){gbToast.success(res.msg);setTimeout(function(){location.reload();},500);}},
  complete:function(){btn.disabled=false;btn.textContent='保存';}});
  return false;
}
function delIcpImage(id){
  if(!confirm('确认删除此 ICP 图片?')) return;
  gbAjax({method:'POST',url:'<?php echo site_url('admin/icp-image/delete'); ?>',data:{id:id},success:function(r){
    if(r&&r.code===0){gbToast.success('已删除');setTimeout(function(){location.reload();},500);}
  }});
}
</script>

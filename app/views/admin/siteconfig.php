<?php /** 网站配置 */
$cfg = $cfg ?? [];
function cfgv($k, $d='') { global $cfg; return $cfg[$k] ?? $d; }
?>
<div class="panel">
  <div class="panel-head"><span class="title"><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg> 系统配置</span></div>
  <div class="panel-body">
    <form id="cfgForm" onsubmit="return saveCfg(event)">
      <h4 style="margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--divider);">基础信息</h4>
      <div class="grid-2">
        <div class="form-group"><label class="form-label">网站名称</label><input class="form-control" name="site_name" value="<?php echo e(cfgv('site_name')); ?>"></div>
        <div class="form-group"><label class="form-label">网站标题</label><input class="form-control" name="site_title" value="<?php echo e(cfgv('site_title')); ?>"></div>
      </div>
      <div class="form-group"><label class="form-label">网站关键词</label><input class="form-control" name="site_keywords" value="<?php echo e(cfgv('site_keywords')); ?>"></div>
      <div class="form-group"><label class="form-label">网站描述</label><textarea class="form-control" name="site_description" rows="2"><?php echo e(cfgv('site_description')); ?></textarea></div>

      <h4 style="margin:20px 0 14px;padding-bottom:10px;border-bottom:1px solid var(--divider);">图片资源</h4>
      <div class="grid-2">
        <?php foreach ([['site_logo','网站Logo'],['site_favicon','网站图标'],['site_thumbnail','网站缩略图'],['captcha_image','验证滑块图片']] as $img): $k=$img[0]; ?>
        <div class="form-group">
          <label class="form-label"><?php echo $img[1]; ?></label>
          <div class="upload-box" onclick="uploadFile('<?php echo $k; ?>')">
            <svg class="up-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <div class="text-sm">点击上传</div>
          </div>
          <div class="upload-preview" id="prev_<?php echo $k; ?>" <?php if(!cfgv($k)): ?>style="display:none;"<?php endif; ?>>
            <img id="img_<?php echo $k; ?>" src="<?php echo cfgv($k)?asset(cfgv($k)):''; ?>">
            <div style="flex:1">
              <div class="text-sm" id="path_<?php echo $k; ?>"><?php echo e(cfgv($k)); ?></div>
              <button type="button" class="btn btn-danger btn-sm mt-2" onclick="delImg('<?php echo $k; ?>')">删除</button>
            </div>
          </div>
          <input type="hidden" name="<?php echo $k; ?>" id="input_<?php echo $k; ?>" value="<?php echo e(cfgv($k)); ?>">
        </div>
        <?php endforeach; ?>
      </div>

      <h4 style="margin:20px 0 14px;padding-bottom:10px;border-bottom:1px solid var(--divider);">页脚与备案</h4>
      <div class="form-group"><label class="form-label">页脚介绍</label><textarea class="form-control" name="footer_intro" rows="2"><?php echo e(cfgv('footer_intro')); ?></textarea></div>
      <div class="grid-2">
        <div class="form-group"><label class="form-label">ICP备案信息</label><input class="form-control" name="icp_info" value="<?php echo e(cfgv('icp_info')); ?>"></div>
        <div class="form-group"><label class="form-label">版权信息</label><input class="form-control" name="copyright" value="<?php echo e(cfgv('copyright')); ?>"></div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label class="form-label">技术支持文字</label><input class="form-control" name="tech_support" value="<?php echo e(cfgv('tech_support')); ?>"></div>
        <div class="form-group"><label class="form-label">技术支持链接</label><input class="form-control" name="tech_support_url" value="<?php echo e(cfgv('tech_support_url')); ?>"></div>
      </div>

      <h4 style="margin:20px 0 14px;padding-bottom:10px;border-bottom:1px solid var(--divider);">社交媒体二维码</h4>
      <div class="grid-3">
        <?php foreach ([['qq_image','QQ'],['wechat_image','微信'],['kuaishou_image','快手']] as $img): $k=$img[0]; ?>
        <div class="form-group">
          <label class="form-label"><?php echo $img[1]; ?>二维码</label>
          <div class="upload-box" onclick="uploadFile('<?php echo $k; ?>')">
            <svg class="up-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <div class="text-sm">上传</div>
          </div>
          <div class="upload-preview" id="prev_<?php echo $k; ?>" <?php if(!cfgv($k)): ?>style="display:none;"<?php endif; ?>>
            <img id="img_<?php echo $k; ?>" src="<?php echo cfgv($k)?asset(cfgv($k)):''; ?>">
            <div style="flex:1"><button type="button" class="btn btn-danger btn-sm" onclick="delImg('<?php echo $k; ?>')">删除</button></div>
          </div>
          <input type="hidden" name="<?php echo $k; ?>" id="input_<?php echo $k; ?>" value="<?php echo e(cfgv($k)); ?>">
        </div>
        <?php endforeach; ?>
      </div>

      <div style="text-align:right;margin-top:20px;">
        <button type="submit" class="btn btn-primary btn-lg" id="saveBtn">保存配置</button>
      </div>
    </form>
  </div>
</div>
<script>
function uploadFile(field){
  var inp=document.createElement('input'); inp.type='file'; inp.accept='image/*';
  inp.onchange=function(){
    var fd=new FormData(); fd.append('file',inp.files[0]);
    var xhr=new XMLHttpRequest();
    xhr.open('POST','<?php echo site_url('admin/upload'); ?>');
    xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
    xhr.onreadystatechange=function(){
      if(xhr.readyState!==4)return;
      try{var r=JSON.parse(xhr.responseText); if(r.code===0){
        document.getElementById('input_'+field).value=r.data.url;
        document.getElementById('img_'+field).src=r.data.full;
        document.getElementById('prev_'+field).style.display='flex';
        document.getElementById('path_'+field)&&(document.getElementById('path_'+field).textContent=r.data.url);
        gbToast.success('上传成功');
      }else{gbToast.error(r.msg||'上传失败');}}catch(e){gbToast.error('上传失败');}
    };
    xhr.send(fd);
  };
  inp.click();
}
function delImg(field){
  var v=document.getElementById('input_'+field).value;
  if(v){gbAjax({method:'POST',url:'<?php echo site_url('admin/upload/delete'); ?>',data:{path:v}});}
  document.getElementById('input_'+field).value='';
  document.getElementById('prev_'+field).style.display='none';
  gbToast.success('已删除');
}
function saveCfg(e){
  e.preventDefault();
  var fd=new FormData(e.target);
  var data={};fd.forEach(function(v,k){data[k]=v;});
  var btn=document.getElementById('saveBtn');btn.disabled=true;btn.innerHTML='<span class="gb-loading gb-loading-sm"></span> 保存中...';
  gbAjax({method:'POST',url:'<?php echo site_url('admin/siteconfig/save'); ?>',data:data,
  success:function(res){if(res.code===0){gbToast.success(res.msg);setTimeout(function(){location.reload();},600);}},
  complete:function(){btn.disabled=false;btn.innerHTML='保存配置';}});
  return false;
}
</script>

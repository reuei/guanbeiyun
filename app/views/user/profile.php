<?php /** 信息配置 */
$user = $user ?? [];
$avatar = $user['avatar'] ?? '';
?>
<div class="panel" style="max-width:720px;">
  <div class="panel-head"><span class="title">信息配置</span></div>
  <div class="panel-body">
    <div style="display:flex;align-items:center;gap:20px;margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid var(--divider);">
      <div style="position:relative;">
        <?php if ($avatar): ?><img src="<?php echo asset($avatar); ?>" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid var(--border);"><?php else: ?>
        <div style="width:80px;height:80px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:600;"><?php echo e(strtoupper(mb_substr($user['username'],0,1))); ?></div>
        <?php endif; ?>
      </div>
      <div>
        <div class="font-bold text-lg"><?php echo e($user['username']); ?></div>
        <div class="text-muted text-sm mb-2"><?php echo e($user['email'] ?: '未设置邮箱'); ?></div>
        <button class="btn btn-sm" onclick="uploadAvatar()">更换头像</button>
        <input type="file" id="avatarInput" accept="image/*" style="display:none;" onchange="doUploadAvatar()">
      </div>
    </div>
    <form id="profileForm" onsubmit="return saveProfile(event)">
      <div class="grid-2">
        <div class="form-group"><label class="form-label">用户名</label><input class="form-control" name="username" value="<?php echo e($user['username']); ?>"></div>
        <div class="form-group"><label class="form-label">手机号</label><input class="form-control" name="phone" value="<?php echo e($user['phone'] ?? ''); ?>"></div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label class="form-label">邮箱</label><input class="form-control" name="email" value="<?php echo e($user['email'] ?? ''); ?>"></div>
        <div class="form-group"><label class="form-label">新密码 (留空不修改)</label><input class="form-control" type="password" name="new_password" placeholder="输入新密码"></div>
      </div>
      <div style="text-align:right;"><button type="submit" class="btn btn-primary" id="saveBtn">保存修改</button></div>
    </form>
  </div>
</div>
<script>
function uploadAvatar(){document.getElementById('avatarInput').click();}
function doUploadAvatar(){
  var f=document.getElementById('avatarInput').files[0];if(!f)return;
  var fd=new FormData();fd.append('file',f);
  var xhr=new XMLHttpRequest();
  xhr.open('POST','<?php echo site_url('user/avatar/upload'); ?>');
  xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
  xhr.onreadystatechange=function(){if(xhr.readyState!==4)return;try{var r=JSON.parse(xhr.responseText);if(r.code===0){gbToast.success('上传成功');setTimeout(function(){location.reload();},600);}else gbToast.error(r.msg||'上传失败');}catch(e){gbToast.error('上传失败');}};
  xhr.send(fd);
}
function saveProfile(e){e.preventDefault();var d={};new FormData(e.target).forEach(function(v,k){d[k]=v;});
  var b=document.getElementById('saveBtn');b.disabled=true;b.innerHTML='<span class="gb-loading gb-loading-sm"></span> 保存中';
  gbAjax({method:'POST',url:'<?php echo site_url('user/profile/update'); ?>',data:d,success:function(r){if(r.code===0)gbToast.success(r.msg);},complete:function(){b.disabled=false;b.innerHTML='保存修改';}});return false;}
</script>

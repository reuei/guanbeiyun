<?php /** 违法举报页 */ ?>
<section class="section">
  <div class="container">
    <div class="section-title" style="margin-bottom:24px;">
      <h2>违法举报</h2>
      <p>举报违法不良信息，共建清朗网络空间</p>
    </div>
    <div class="card" style="max-width:680px;margin:0 auto;">
      <div class="card-body">
        <div style="background:rgba(255,77,79,0.06);border:1px solid rgba(255,77,79,0.2);border-radius:6px;padding:12px 16px;margin-bottom:18px;font-size:13px;color:var(--text-2);line-height:1.7;">
          <b style="color:var(--danger);">举报须知：</b>请如实填写举报信息，恶意举报将承担相应责任。我们会对您的信息严格保密。
        </div>
        <form id="reportForm" onsubmit="return doSubmit(event)">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div class="form-group">
              <label class="form-label">您的称呼</label>
              <input type="text" class="form-control" name="name" id="rp_name" placeholder="选填">
            </div>
            <div class="form-group">
              <label class="form-label">联系方式</label>
              <input type="text" class="form-control" name="contact" id="rp_contact" placeholder="邮箱或手机号(选填)">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">举报目标网址 <span class="req">*</span></label>
            <input type="text" class="form-control" name="target_url" id="rp_url" placeholder="如 https://example.com">
            <div class="form-error"></div>
          </div>
          <div class="form-group">
            <label class="form-label">举报标题 <span class="req">*</span></label>
            <input type="text" class="form-control" name="title" id="rp_title" placeholder="简述举报内容">
            <div class="form-error"></div>
          </div>
          <div class="form-group">
            <label class="form-label">举报详情 <span class="req">*</span></label>
            <textarea class="form-control" name="content" id="rp_content" rows="6" placeholder="请详细描述违法事实(至少5个字符)"></textarea>
            <div class="form-error"></div>
          </div>
          <div class="form-group">
            <label class="form-label">人机验证 <span class="req">*</span></label>
            <div class="slider-captcha" data-slider-captcha data-on-success="onCaptchaOk"></div>
          </div>
          <button type="submit" class="btn btn-danger btn-block btn-lg" id="submitBtn">提交举报</button>
        </form>
      </div>
    </div>
  </div>
</section>
<script>
var captchaOk = false;
function onCaptchaOk() { captchaOk = true; }
(function(){
  var u=document.getElementById('rp_url'),t=document.getElementById('rp_title'),c=document.getElementById('rp_content');
  u.addEventListener('input',function(){ if(u.value.trim()) gbValidate.setSuccess(u); else gbValidate.setError(u,'请填写举报目标网址'); });
  t.addEventListener('input',function(){ if(t.value.trim()) gbValidate.setSuccess(t); else gbValidate.setError(t,'请输入举报标题'); });
  c.addEventListener('input',function(){ if(c.value.trim().length>=5) gbValidate.setSuccess(c); else gbValidate.setError(c,'内容至少5个字符'); });
})();
function doSubmit(e){
  e.preventDefault();
  var u=document.getElementById('rp_url'),t=document.getElementById('rp_title'),c=document.getElementById('rp_content');
  if(!u.value.trim()){gbValidate.setError(u,'请填写举报目标网址');return false;}
  if(!t.value.trim()){gbValidate.setError(t,'请输入举报标题');return false;}
  if(c.value.trim().length<5){gbValidate.setError(c,'内容至少5个字符');return false;}
  if(!captchaOk){gbToast.warning('请先完成人机验证');return false;}
  var btn=document.getElementById('submitBtn');
  btn.disabled=true;btn.innerHTML='<span class="gb-loading gb-loading-sm"></span> 提交中...';
  gbAjax({
    method:'POST',url:'<?php echo site_url('report'); ?>',
    data:{name:rp_name.value.trim(),contact:rp_contact.value.trim(),target_url:u.value.trim(),title:t.value.trim(),content:c.value.trim(),captcha_verified:1},
    success:function(res){ if(res.code===0){ gbToast.success(res.msg); document.getElementById('reportForm').reset(); captchaOk=false; } },
    complete:function(){ btn.disabled=false; btn.innerHTML='提交举报'; }
  });
  return false;
}
</script>

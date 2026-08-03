<?php /** 意见反馈页 */ ?>
<section class="section">
  <div class="container">
    <div class="section-title" style="margin-bottom:24px;">
      <h2>意见反馈</h2>
      <p>您的每一条建议我们都认真对待</p>
    </div>
    <div class="card" style="max-width:680px;margin:0 auto;">
      <div class="card-body">
        <form id="feedbackForm" onsubmit="return doSubmit(event)">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div class="form-group">
              <label class="form-label">您的称呼</label>
              <input type="text" class="form-control" name="name" id="fb_name" placeholder="选填">
            </div>
            <div class="form-group">
              <label class="form-label">联系方式</label>
              <input type="text" class="form-control" name="contact" id="fb_contact" placeholder="邮箱或手机号(选填)">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">反馈标题 <span class="req">*</span></label>
            <input type="text" class="form-control" name="title" id="fb_title" placeholder="简述您的反馈主题">
            <div class="form-error"></div>
          </div>
          <div class="form-group">
            <label class="form-label">反馈内容 <span class="req">*</span></label>
            <textarea class="form-control" name="content" id="fb_content" rows="6" placeholder="请详细描述您的反馈内容(至少5个字符)"></textarea>
            <div class="form-error"></div>
          </div>
          <div class="form-group">
            <label class="form-label">人机验证 <span class="req">*</span></label>
            <div class="slider-captcha" data-slider-captcha data-on-success="onCaptchaOk"></div>
          </div>
          <button type="submit" class="btn btn-primary btn-block btn-lg" id="submitBtn">提交反馈</button>
        </form>
      </div>
    </div>
  </div>
</section>
<script>
var captchaOk = false;
function onCaptchaOk() { captchaOk = true; }
(function(){
  var t = document.getElementById('fb_title'), c = document.getElementById('fb_content');
  t.addEventListener('input', function(){ if(t.value.trim()) gbValidate.setSuccess(t); else gbValidate.setError(t,'请输入反馈标题'); });
  c.addEventListener('input', function(){ if(c.value.trim().length>=5) gbValidate.setSuccess(c); else gbValidate.setError(c,'内容至少5个字符'); });
})();
function doSubmit(e) {
  e.preventDefault();
  var t = document.getElementById('fb_title'), c = document.getElementById('fb_content');
  if(!t.value.trim()){ gbValidate.setError(t,'请输入反馈标题'); return false; }
  if(c.value.trim().length<5){ gbValidate.setError(c,'内容至少5个字符'); return false; }
  if(!captchaOk){ gbToast.warning('请先完成人机验证'); return false; }
  var btn = document.getElementById('submitBtn');
  btn.disabled = true; btn.innerHTML = '<span class="gb-loading gb-loading-sm"></span> 提交中...';
  gbAjax({
    method: 'POST', url: '<?php echo site_url('feedback'); ?>',
    data: { name: fb_name.value.trim(), contact: fb_contact.value.trim(), title: t.value.trim(), content: c.value.trim(), captcha_verified: captchaOk ? 1 : 0 },
    success: function(res) {
      if (res.code === 0) { gbToast.success(res.msg); document.getElementById('feedbackForm').reset(); captchaOk=false; }
    },
    complete: function() { btn.disabled = false; btn.innerHTML = '提交反馈'; }
  });
  return false;
}
</script>

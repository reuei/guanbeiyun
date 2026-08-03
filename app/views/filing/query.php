<?php /** 备案查询页 */
$results = $results ?? null;
$keyword = $keyword ?? '';
?>
<section class="section">
  <div class="container">
    <div class="section-title" style="margin-bottom:24px;">
      <h2>ICP 备案查询</h2>
      <p>输入域名、备案号、主办单位或网站名称进行查询</p>
    </div>

    <div class="card" style="max-width:720px;margin:0 auto;">
      <div class="card-body">
        <form id="queryForm" onsubmit="return doQuery(event)">
          <div class="form-group">
            <label class="form-label">查询内容 <span class="req">*</span></label>
            <div class="input-group">
              <span class="input-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
              <input type="text" class="form-control" id="q" name="q" value="<?php echo e($keyword); ?>" placeholder="例如：example.com 或 京ICP备XXXX号">
            </div>
            <div class="form-error"></div>
          </div>

          <div class="form-group">
            <label class="form-label">人机验证 <span class="req">*</span></label>
            <div class="slider-captcha" data-slider-captcha data-on-success="onCaptchaOk"></div>
          </div>

          <button type="submit" class="btn btn-primary btn-block btn-lg" id="submitBtn">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            立即查询
          </button>
        </form>
      </div>
    </div>

    <!-- 结果 -->
    <div id="resultArea" style="max-width:960px;margin:30px auto 0;">
      <?php if ($results !== null): ?>
        <?php if ($results): ?>
          <div class="card">
            <div class="card-header">查询结果 (共 <?php echo count($results); ?> 条)</div>
            <div class="table-wrap" style="border:none;">
              <table class="table">
                <thead>
                  <tr><th>备案号</th><th>网站名称</th><th>域名</th><th>主办单位</th><th>性质</th><th>审核日期</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($results as $r): ?>
                  <tr>
                    <td><span class="tag tag-primary"><?php echo e($r['icp_no'] ?: '-'); ?></span></td>
                    <td><?php echo e($r['site_name']); ?></td>
                    <td><?php echo e($r['site_domain']); ?></td>
                    <td><?php echo e($r['owner_name']); ?></td>
                    <td><?php echo $r['owner_type'] == 1 ? '企业' : '个人'; ?></td>
                    <td class="text-muted"><?php echo e(date('Y-m-d', strtotime($r['created_at']))); ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php else: ?>
          <div class="card"><div class="empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <p>未查询到相关备案信息</p>
          </div></div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<script>
var captchaOk = false;
function onCaptchaOk() { captchaOk = true; }
(function(){
  var q = document.getElementById('q');
  q.addEventListener('input', function(){
    if (!q.value.trim()) { gbValidate.setError(q, '请输入查询内容'); }
    else { gbValidate.setSuccess(q); }
  });
})();
function doQuery(e) {
  e.preventDefault();
  var q = document.getElementById('q');
  if (!q.value.trim()) { gbValidate.setError(q, '请输入查询内容'); return false; }
  if (!captchaOk) { gbToast.warning('请先完成人机验证'); return false; }
  var btn = document.getElementById('submitBtn');
  btn.disabled = true; btn.innerHTML = '<span class="gb-loading gb-loading-sm"></span> 查询中...';
  gbAjax({
    method: 'POST',
    url: '<?php echo site_url('query'); ?>',
    data: { q: q.value.trim(), captcha_verified: captchaOk ? 1 : 0 },
    success: function(res) {
      if (res.code === 0) {
        var list = res.data.list || [];
        var html = '';
        if (list.length) {
          html = '<div class="card"><div class="card-header">查询结果 (共 '+list.length+' 条)</div><div class="table-wrap" style="border:none;"><table class="table"><thead><tr><th>备案号</th><th>网站名称</th><th>域名</th><th>主办单位</th><th>性质</th><th>审核日期</th></tr></thead><tbody>';
          list.forEach(function(r){
            html += '<tr><td><span class="tag tag-primary">'+(r.icp_no||'-')+'</span></td><td>'+r.site_name+'</td><td>'+r.site_domain+'</td><td>'+r.owner_name+'</td><td>'+(r.owner_type==1?'企业':'个人')+'</td><td class="text-muted">'+r.created_at.slice(0,10)+'</td></tr>';
          });
          html += '</tbody></table></div></div>';
        } else {
          html = '<div class="card"><div class="empty"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:64px;height:64px;margin:0 auto 12px;opacity:.4;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><p>未查询到相关备案信息</p></div></div>';
        }
        document.getElementById('resultArea').innerHTML = html;
        document.getElementById('resultArea').scrollIntoView({behavior:'smooth'});
      }
    },
    complete: function() { btn.disabled = false; btn.innerHTML = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg> 立即查询'; }
  });
  return false;
}
</script>

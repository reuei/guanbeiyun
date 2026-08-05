<?php /** 备案申请管理 */
$rows = $rows ?? []; $total = $total ?? 0; $page = $page ?? 1; $size = $size ?? 15; $status = $status ?? '';
$statusMap = [0 => ['审核中','badge-pending'], 1 => ['已通过','badge-success'], 2 => ['未通过','badge-danger'], 3 => ['已撤销','badge-info']];
$licenseUploadUrl = site_url('user/license/upload');
$applyUrl = site_url('user/filing/apply');
$detailUrl = site_url('user/filing/detail');
$assetBase = site_url('');
?>
<style>
  .type-cards { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-top:14px; }
  .type-card { border:2px solid var(--divider); border-radius:12px; padding:30px 20px; text-align:center; cursor:pointer; transition:border-color .15s, transform .15s, box-shadow .15s; background:var(--card,#fff); }
  .type-card:hover { border-color: var(--primary); transform: translateY(-2px); box-shadow: 0 8px 22px rgba(0,0,0,.08); }
  .type-card.selected { border-color: var(--primary); background:rgba(0,120,212,.06); }
  .type-card .tc-icon { width:54px; height:54px; margin:0 auto; color:var(--primary); }
  .type-card .tc-title { font-size:18px; font-weight:600; margin:12px 0 6px; }
  .type-card .tc-desc { font-size:13px; color:var(--text-muted); line-height:1.5; }
  .apply-step-title { font-size:14px; color:var(--text-muted); margin:0 0 4px; }
  .license-preview { margin-top:10px; max-width:240px; max-height:180px; border:1px solid var(--divider); border-radius:8px; object-fit:contain; display:none; }
  .upload-hint { font-size:12px; color:var(--text-muted); margin-top:6px; }
  .icp-banner { background:linear-gradient(135deg,#e8f5e9,#f1f8e9); border:1px solid #c5e1a5; border-radius:10px; padding:16px 18px; margin-bottom:18px; }
  .icp-banner .ib-label { font-size:12px; color:#558b2f; margin-bottom:4px; }
  .icp-banner .ib-no { font-size:22px; font-weight:700; color:#2e7d32; letter-spacing:.5px; word-break:break-all; }
  .icp-banner .ib-note { font-size:12px; color:#7cb342; margin-top:6px; }
  .detail-list .dl-value img { max-width:200px; max-height:150px; border:1px solid var(--divider); border-radius:6px; }
</style>
<div class="panel">
  <div class="panel-head">
    <span class="title">备案申请管理 <span class="tag tag-primary"><?php echo $total; ?></span></span>
    <div class="toolbar" style="margin:0;gap:10px;">
      <form method="get"><select name="status" class="form-control" style="width:auto;" onchange="this.form.submit()">
        <option value="">全部状态</option>
        <option value="0" <?php echo $status==='0'?'selected':''; ?>>审核中</option>
        <option value="1" <?php echo $status==='1'?'selected':''; ?>>已通过</option>
        <option value="2" <?php echo $status==='2'?'selected':''; ?>>未通过</option>
      </select></form>
      <button class="btn btn-primary btn-sm" onclick="openApplyModal()">+ 新建备案申请</button>
    </div>
  </div>
  <div class="table-wrap" style="border:none;">
    <table class="table">
      <thead><tr><th>ID</th><th>网站名称</th><th>域名</th><th>主办单位</th><th>性质</th><th>备案号</th><th>状态</th><th>申请时间</th><th>操作</th></tr></thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): $st = $statusMap[$r['status']] ?? ['未知','badge-info']; ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td><?php echo e($r['site_name']); ?></td>
          <td><?php echo e($r['site_domain']); ?></td>
          <td><?php echo e($r['owner_name']); ?></td>
          <td><span class="tag <?php echo $r['owner_type']==1?'tag-primary':''; ?>"><?php echo $r['owner_type']==1?'企业':'个人'; ?></span></td>
          <td><?php echo $r['icp_no']?e($r['icp_no']):'<span class="text-muted">-</span>'; ?></td>
          <td><span class="badge <?php echo $st[1]; ?>"><?php echo $st[0]; ?></span></td>
          <td class="text-muted text-sm"><?php echo e(date('Y-m-d H:i', strtotime($r['created_at']))); ?></td>
          <td><button class="btn btn-ghost btn-sm" onclick="showFilingDetail(<?php echo (int)$r['id']; ?>)">查看详情</button></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="9" class="empty">暂无备案申请，点击右上角"新建备案申请"</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $baseUrl = site_url('user/filings') . ($status!==''?'?status='.urlencode($status):''); require __DIR__ . '/../shared/pagination.php'; ?>

<!-- 新建备案申请 (两步) -->
<div class="modal-overlay" id="applyModal">
  <div class="modal-box lg">
    <div class="modal-head"><h3>新建备案申请</h3><span class="icon-btn" onclick="gbModal.close('applyModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body">
      <!-- 步骤1: 选择备案类型 -->
      <div class="apply-step" id="applyStep1">
        <p class="apply-step-title">第一步：请选择备案类型</p>
        <div class="type-cards">
          <div class="type-card" id="cardType2" onclick="chooseApplyType(2)">
            <svg class="tc-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a8 8 0 0 1 16 0v1"/></svg>
            <div class="tc-title">个人备案</div>
            <div class="tc-desc">以个人名义提交备案，需提供姓名及身份证信息</div>
          </div>
          <div class="type-card" id="cardType1" onclick="chooseApplyType(1)">
            <svg class="tc-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01"/></svg>
            <div class="tc-title">企业备案</div>
            <div class="tc-desc">以企业名义提交备案，需提供营业执照及企业地址</div>
          </div>
        </div>
      </div>
      <!-- 步骤2: 填写表单 -->
      <div class="apply-step" id="applyStep2" style="display:none;">
        <p class="apply-step-title">第二步：填写备案信息 <span id="applyTypeBadge" class="tag tag-primary" style="margin-left:8px;"></span></p>
        <form id="applyForm" onsubmit="return submitApply(event)">
          <input type="hidden" name="owner_type" id="ownerTypeInput" value="">
          <div class="grid-2">
            <div class="form-group"><label class="form-label">网站名称 <span class="req">*</span></label><input class="form-control" name="site_name" required placeholder="如 管备云官网"></div>
            <div class="form-group"><label class="form-label">网站域名 <span class="req">*</span></label><input class="form-control" name="site_domain" required placeholder="如 example.com"></div>
          </div>
          <div class="grid-2">
            <div class="form-group"><label class="form-label">网址 <span class="req">*</span></label><input class="form-control" name="site_url" required placeholder="https://example.com"></div>
            <div class="form-group"><label class="form-label">邮箱 <span class="req">*</span></label><input class="form-control" type="email" name="owner_email" required placeholder="联系邮箱"></div>
          </div>
          <div class="grid-2">
            <div class="form-group"><label class="form-label">手机号 <span class="req">*</span></label><input class="form-control" name="owner_phone" required placeholder="11位手机号"></div>
            <div class="form-group"><label class="form-label">网站语言</label><input class="form-control" name="language" value="中文"></div>
          </div>
          <!-- 个人备案字段 -->
          <div id="personFields" style="display:none;">
            <div class="grid-2">
              <div class="form-group"><label class="form-label">姓名 <span class="req">*</span></label><input class="form-control" name="owner_name" placeholder="真实姓名"></div>
              <div class="form-group"><label class="form-label">身份证号 <span class="text-muted">(选填)</span></label><input class="form-control" name="owner_id" placeholder="身份证号码"></div>
            </div>
          </div>
          <!-- 企业备案字段 -->
          <div id="companyFields" style="display:none;">
            <div class="grid-2">
              <div class="form-group"><label class="form-label">企业名称 <span class="req">*</span></label><input class="form-control" name="owner_name" placeholder="企业全称"></div>
              <div class="form-group"><label class="form-label">统一社会信用代码</label><input class="form-control" name="owner_id" placeholder="18位代码"></div>
            </div>
            <div class="form-group"><label class="form-label">企业地址 <span class="req">*</span></label><input class="form-control" name="owner_address" placeholder="企业办公地址"></div>
            <div class="form-group">
              <label class="form-label">企业资质/营业执照图片 <span class="req">*</span></label>
              <input type="file" class="form-control" id="licenseFile" accept="image/*" onchange="uploadLicense(this)">
              <input type="hidden" name="license_img" id="licenseImgInput" value="">
              <img id="licensePreview" class="license-preview" alt="营业执照预览">
              <div class="upload-hint" id="licenseHint">支持 jpg/png 等图片格式，上传后自动生成预览</div>
            </div>
          </div>
          <div class="grid-2">
            <div class="form-group"><label class="form-label">服务器IP</label><input class="form-control" name="server_ip" placeholder="选填"></div>
            <div class="form-group"><label class="form-label">网站内容类型</label><input class="form-control" name="content_type" placeholder="如 综合门户/电子商务"></div>
          </div>
          <div class="form-group"><label class="form-label">备注</label><textarea class="form-control" name="remark" rows="2" placeholder="补充说明"></textarea></div>
        </form>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn" onclick="gbModal.close('applyModal')">取消</button>
      <span id="applyFootStep1">
        <button class="btn btn-primary" id="applyNextBtn" disabled onclick="goApplyStep2()">下一步</button>
      </span>
      <span id="applyFootStep2" style="display:none;">
        <button class="btn" onclick="goApplyStep1()">上一步</button>
        <button class="btn btn-primary" onclick="document.getElementById('applyForm').requestSubmit()">提交申请</button>
      </span>
    </div>
  </div>
</div>

<!-- 备案详情弹窗 -->
<div class="modal-overlay" id="filingDetailModal">
  <div class="modal-box lg">
    <div class="modal-head"><h3>备案详情</h3><span class="icon-btn" onclick="gbModal.close('filingDetailModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body">
      <div id="filingIcpBox"></div>
      <div class="detail-list" id="filingDetail"></div>
    </div>
    <div class="modal-foot"><button class="btn" onclick="gbModal.close('filingDetailModal')">关闭</button></div>
  </div>
</div>

<script>
var GB_ASSET_BASE = '<?php echo $assetBase; ?>';
var applyOwnerType = 0;

/* ---------- 新建备案 (两步) ---------- */
function openApplyModal() {
  resetApplyForm();
  goApplyStep1();
  gbModal.open('applyModal');
}
function resetApplyForm() {
  applyOwnerType = 0;
  document.getElementById('ownerTypeInput').value = '';
  document.getElementById('applyNextBtn').disabled = true;
  document.getElementById('cardType1').classList.remove('selected');
  document.getElementById('cardType2').classList.remove('selected');
  var form = document.getElementById('applyForm');
  if (form) form.reset();
  document.getElementById('licenseImgInput').value = '';
  document.getElementById('licensePreview').style.display = 'none';
  document.getElementById('licensePreview').src = '';
}
function chooseApplyType(t) {
  applyOwnerType = t;
  document.getElementById('ownerTypeInput').value = t;
  document.getElementById('cardType1').classList.toggle('selected', t === 1);
  document.getElementById('cardType2').classList.toggle('selected', t === 2);
  document.getElementById('applyNextBtn').disabled = false;
}
function goApplyStep1() {
  document.getElementById('applyStep1').style.display = '';
  document.getElementById('applyStep2').style.display = 'none';
  document.getElementById('applyFootStep1').style.display = '';
  document.getElementById('applyFootStep2').style.display = 'none';
}
function setGroupDisabled(groupId, disabled) {
  var g = document.getElementById(groupId);
  if (!g) return;
  g.querySelectorAll('input,select,textarea').forEach(function (el) { el.disabled = disabled; });
}
function goApplyStep2() {
  if (!applyOwnerType) { gbToast.warning('请先选择备案类型'); return; }
  // 切换个人/企业字段 (隐藏组的输入禁用, 避免重复提交与无效必填校验)
  var isCompany = applyOwnerType === 1;
  var personBox = document.getElementById('personFields');
  var companyBox = document.getElementById('companyFields');
  personBox.style.display = isCompany ? 'none' : '';
  companyBox.style.display = isCompany ? '' : 'none';
  setGroupDisabled('personFields', isCompany);
  setGroupDisabled('companyFields', !isCompany);
  document.getElementById('applyTypeBadge').textContent = isCompany ? '企业备案' : '个人备案';
  document.getElementById('applyStep1').style.display = 'none';
  document.getElementById('applyStep2').style.display = '';
  document.getElementById('applyFootStep1').style.display = 'none';
  document.getElementById('applyFootStep2').style.display = '';
}
function uploadLicense(input) {
  if (!input.files || !input.files[0]) return;
  var fd = new FormData();
  fd.append('file', input.files[0]);
  document.getElementById('licenseHint').textContent = '上传中...';
  gbAjax({
    method: 'POST',
    url: '<?php echo $licenseUploadUrl; ?>',
    form: fd,
    success: function (res) {
      if (res && res.code === 0 && res.data && res.data.url) {
        document.getElementById('licenseImgInput').value = res.data.url;
        var prev = document.getElementById('licensePreview');
        prev.src = res.data.full || (GB_ASSET_BASE + res.data.url);
        prev.style.display = 'block';
        document.getElementById('licenseHint').textContent = '上传成功';
      } else {
        document.getElementById('licenseHint').textContent = '上传失败，请重试';
        input.value = '';
      }
    },
    fail: function () {
      document.getElementById('licenseHint').textContent = '上传失败，请重试';
      input.value = '';
    }
  });
}
function submitApply(e) {
  e.preventDefault();
  if (!applyOwnerType) { gbToast.warning('请选择备案类型'); return false; }
  if (applyOwnerType === 1 && !document.getElementById('licenseImgInput').value) {
    gbToast.warning('请上传企业资质/营业执照图片');
    return false;
  }
  var d = {};
  new FormData(e.target).forEach(function (v, k) { d[k] = v; });
  d.owner_type = applyOwnerType;
  gbAjax({
    method: 'POST',
    url: '<?php echo $applyUrl; ?>',
    data: d,
    success: function (r) {
      if (r && r.code === 0) {
        gbToast.success(r.msg || '备案申请已提交');
        gbModal.close('applyModal');
        setTimeout(function () { location.reload(); }, 900);
      }
    }
  });
  return false;
}

/* ---------- 备案详情 ---------- */
function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]; }); }
function statusBadge(st) {
  var map = { 0: ['审核中','badge-pending'], 1: ['已通过','badge-success'], 2: ['未通过','badge-danger'], 3: ['已撤销','badge-info'] };
  var m = map[st] || ['未知','badge-info'];
  return '<span class="badge ' + m[1] + '">' + m[0] + '</span>';
}
function dlItem(label, valueHtml) {
  return '<div class="dl-item"><div class="dl-label">' + esc(label) + '</div><div class="dl-value">' + valueHtml + '</div></div>';
}
function showFilingDetail(id) {
  document.getElementById('filingIcpBox').innerHTML = '';
  document.getElementById('filingDetail').innerHTML = '<div class="dl-item"><div class="dl-label">加载中</div><div class="dl-value">...</div></div>';
  gbModal.open('filingDetailModal');
  gbAjax({
    method: 'GET',
    url: '<?php echo $detailUrl; ?>?id=' + encodeURIComponent(id),
    success: function (res) {
      var f = res && res.code === 0 && res.data ? res.data.filing : null;
      if (!f) { document.getElementById('filingDetail').innerHTML = '<div class="dl-item"><div class="dl-label">提示</div><div class="dl-value">未找到备案记录</div></div>'; return; }
      // 备案号横幅 (仅已通过)
      var icpHtml = '';
      if (Number(f.status) === 1) {
        icpHtml = '<div class="icp-banner">' +
          '<div class="ib-label">备案号 (审核通过)</div>' +
          '<div class="ib-no">' + esc(f.icp_no || '未分配') + '</div>' +
          '<div class="ib-note">备案号格式: 管ICP备xxxxxxxx号</div>' +
          '</div>';
      }
      document.getElementById('filingIcpBox').innerHTML = icpHtml;

      var isCompany = Number(f.owner_type) === 1;
      var html = '';
      html += dlItem('备案ID', esc(f.id));
      html += dlItem('性质', isCompany ? '企业备案' : '个人备案');
      html += dlItem('网站名称', esc(f.site_name));
      html += dlItem('网站域名', esc(f.site_domain));
      html += dlItem('网址', esc(f.site_url) ? '<a href="' + esc(f.site_url) + '" target="_blank">' + esc(f.site_url) + '</a>' : '-');
      html += dlItem(isCompany ? '企业名称' : '姓名', esc(f.owner_name));
      html += dlItem(isCompany ? '统一社会信用代码' : '身份证号', esc(f.owner_id) || '-');
      if (isCompany) {
        html += dlItem('企业地址', esc(f.owner_address) || '-');
        if (f.license_img) {
          html += dlItem('营业执照', '<img src="' + esc(GB_ASSET_BASE + f.license_img) + '" alt="营业执照">');
        } else {
          html += dlItem('营业执照', '-');
        }
      }
      html += dlItem('邮箱', esc(f.owner_email) || '-');
      html += dlItem('手机号', esc(f.owner_phone) || '-');
      html += dlItem('服务器IP', esc(f.server_ip) || '-');
      html += dlItem('内容类型', esc(f.content_type) || '-');
      html += dlItem('网站语言', esc(f.language) || '-');
      html += dlItem('备注', esc(f.remark) || '-');
      html += dlItem('状态', statusBadge(f.status));
      html += dlItem('备案号', f.icp_no ? esc(f.icp_no) : '<span class="text-muted">待审核通过后分配</span>');
      html += dlItem('审核意见', esc(f.audit_remark) || '-');
      html += dlItem('申请时间', esc(f.created_at));
      document.getElementById('filingDetail').innerHTML = html;
    }
  });
}
</script>

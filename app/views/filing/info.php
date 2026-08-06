<?php
$filing = $filing ?? [];
$prefixImage = $prefixImage ?? null;
$certifications = $certifications ?? [];
$footerCode = $footerCode ?? '';
$sealImage = $sealImage ?? '';
$f = $filing;
$status = (int)($f['status'] ?? 0);
$statusMap = [
    0 => ['text' => '审核中', 'class' => 'tag-warning'],
    1 => ['text' => '已通过', 'class' => 'tag-success'],
    2 => ['text' => '未通过', 'class' => 'tag-danger'],
    3 => ['text' => '已撤销', 'class' => 'tag-muted'],
];
$statusInfo = $statusMap[$status] ?? ['text' => '未知', 'class' => 'tag-muted'];
$ownerTypeMap = [0 => '个人', 1 => '企业', 2 => '政府机关', 3 => '事业单位', 4 => '社会团体'];
$ownerType = $ownerTypeMap[(int)($f['owner_type'] ?? 0)] ?? '未知';
$infoUrlBase = site_config('filing_info_url', '');
$icpNo = $f['icp_no'] ?? '';
$pureNo = preg_replace('/[^\d]/', '', $icpNo);
$filingLink = $infoUrlBase ? rtrim($infoUrlBase, '/') . '/' . urlencode($pureNo) : site_url('filing/info/' . urlencode($pureNo ?: $icpNo));
$auditTeam = site_config('filing_audit_team', '管备云备案审核团队');
$filingNo = $f['filing_no'] ?? ('GBF-' . date('Y', strtotime($f['created_at'] ?? 'now')) . '-' . str_pad($pureNo, 6, '0', STR_PAD_LEFT));
?>
<style>
.filing-title-gradient {
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #1e40af 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 800;
    font-size: 32px;
    text-align: center;
    margin-bottom: 28px;
    letter-spacing: 2px;
}
.police-banner {
    background: linear-gradient(135deg, #0c2461 0%, #1e3799 50%, #0a3d91 100%);
    border-radius: 12px;
    padding: 22px 28px;
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    box-shadow: 0 6px 24px rgba(30, 58, 138, 0.35);
    border: 1px solid rgba(59, 130, 246, 0.4);
    position: relative;
    overflow: hidden;
}
.police-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%);
    pointer-events: none;
}
.police-badge-svg {
    width: 52px;
    height: 52px;
    flex-shrink: 0;
    filter: drop-shadow(0 2px 6px rgba(0,0,0,0.25));
}
.prefix-img-inline {
    height: 24px;
    vertical-align: middle;
    border-radius: 4px;
}
.icp-no-big {
    color: #fff;
    font-size: 28px;
    font-weight: 700;
    letter-spacing: 1px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}
.info-card {
    background: var(--card-bg, #fff);
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 14px;
    padding: 28px 32px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    position: relative;
    overflow: hidden;
}
.grid-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px 32px;
}
@media (max-width: 640px) {
    .grid-2 { grid-template-columns: 1fr; }
    .icp-no-big { font-size: 20px; }
    .police-banner { padding: 16px; gap: 10px; }
    .police-badge-svg { width: 40px; height: 40px; }
}
.info-row {
    display: flex;
    padding: 10px 0;
    border-bottom: 1px dashed var(--border, #e5e7eb);
}
.info-row:last-child { border-bottom: none; }
.info-label {
    flex: 0 0 110px;
    color: var(--text-muted, #6b7280);
    font-weight: 600;
    font-size: 14px;
}
.info-value {
    flex: 1;
    color: var(--text, #1f2937);
    font-size: 14px;
    word-break: break-all;
}
.section-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--text, #1f2937);
    margin: 0 0 16px 0;
    padding-left: 12px;
    border-left: 4px solid var(--primary, #3b82f6);
}
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 999px;
    font-weight: 600;
    font-size: 14px;
}
.tag-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
.tag-warning { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
.tag-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.tag-muted { background: #e5e7eb; color: #374151; border: 1px solid #d1d5db; }

.cert-gallery {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    margin-top: 12px;
}
.cert-item {
    width: 160px;
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 8px;
    overflow: hidden;
    background: var(--bg-soft, #f9fafb);
}
.cert-item img {
    width: 100%;
    height: 110px;
    object-fit: cover;
    display: block;
}
.cert-item .cert-name {
    padding: 8px 10px;
    font-size: 13px;
    text-align: center;
    color: var(--text, #1f2937);
    font-weight: 600;
}

.seal-stamp {
    position: absolute;
    right: 30px;
    bottom: 30px;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    border: 3px solid rgba(220, 38, 38, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    transform: rotate(-15deg);
    pointer-events: none;
    opacity: 0.35;
    z-index: 1;
    background: radial-gradient(circle, rgba(220, 38, 38, 0.08) 0%, transparent 70%);
}
.seal-stamp::before {
    content: '';
    position: absolute;
    inset: 8px;
    border-radius: 50%;
    border: 2px solid rgba(220, 38, 38, 0.3);
}
.seal-stamp-inner {
    text-align: center;
    color: rgba(220, 38, 38, 0.6);
    font-weight: 900;
    line-height: 1.3;
    font-size: 16px;
    letter-spacing: 2px;
}
.seal-star {
    font-size: 32px;
    line-height: 1;
    margin-bottom: 4px;
    display: block;
    color: rgba(220, 38, 38, 0.5);
}

.back-wrap {
    text-align: center;
    margin-top: 32px;
}
</style>

<section class="section" style="padding-top:32px;">
  <div class="container" style="max-width:1100px;">
    <h1 class="filing-title-gradient">管ICP备案信息公示</h1>

    <div class="police-banner">
      <svg class="police-badge-svg" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <linearGradient id="g1" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#fbbf24"/>
            <stop offset="100%" stop-color="#f59e0b"/>
          </linearGradient>
        </defs>
        <path d="M32 4 L38 14 L50 16 L42 26 L44 40 L32 34 L20 40 L22 26 L14 16 L26 14 Z" fill="url(#g1)" stroke="#b45309" stroke-width="1.5"/>
        <circle cx="32" cy="26" r="6" fill="#fef3c7" stroke="#b45309" stroke-width="1"/>
        <path d="M32 22 L34 26 L38 27 L35 30 L36 34 L32 32 L28 34 L29 30 L26 27 L30 26 Z" fill="#b45309"/>
        <rect x="22" y="40" width="20" height="18" rx="2" fill="#e5e7eb" stroke="#6b7280" stroke-width="1.2"/>
        <path d="M26 46 L32 44 L38 46 L37 52 L32 54 L27 52 Z" fill="#374151"/>
        <circle cx="32" cy="48" r="1.5" fill="#fbbf24"/>
      </svg>
      <div style="flex:1;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <?php if ($prefixImage && !empty($prefixImage['image'])): ?>
          <img src="<?php echo asset($prefixImage['image']); ?>" alt="prefix" class="prefix-img-inline">
        <?php endif; ?>
        <span class="icp-no-big"><?php echo e($icpNo); ?></span>
        <span class="status-badge <?php echo $statusInfo['class']; ?>">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg>
          <?php echo $statusInfo['text']; ?>
        </span>
      </div>
    </div>

    <div class="info-card">
      <?php if (!empty($sealImage)): ?>
      <!-- 后台上传的盖章图片 -->
      <div class="seal-stamp" style="background:none;border:none;opacity:1;">
        <img src="<?php echo asset($sealImage); ?>" alt="备案专用章" style="width:180px;height:180px;object-fit:contain;transform:rotate(-15deg);opacity:0.85;">
      </div>
      <?php else: ?>
      <!-- 默认盖章 (CSS 绘制) -->
      <div class="seal-stamp">
        <div class="seal-stamp-inner">
          <span class="seal-star">★</span>
          管备云<br>备案专用章
        </div>
      </div>
      <?php endif; ?>

      <h2 class="section-title">备案信息</h2>
      <div class="grid-2">
        <div class="info-row"><div class="info-label">备案号</div><div class="info-value"><a href="<?php echo e($filingLink); ?>" target="_blank" rel="noopener" style="color:var(--primary);text-decoration:none;"><?php echo e($icpNo); ?></a></div></div>
        <div class="info-row"><div class="info-label">备案编号</div><div class="info-value"><?php echo e($filingNo); ?></div></div>
        <div class="info-row"><div class="info-label">主办单位名称</div><div class="info-value"><?php echo e($f['owner_name'] ?? '-'); ?></div></div>
        <div class="info-row"><div class="info-label">主办单位类型</div><div class="info-value"><?php echo e($ownerType); ?></div></div>
        <div class="info-row"><div class="info-label">主体编号</div><div class="info-value"><?php echo e($f['owner_no'] ?? '-'); ?></div></div>
        <div class="info-row"><div class="info-label">联系方式</div><div class="info-value"><?php echo e($f['owner_phone'] ?? ($f['phone'] ?? '-')); ?></div></div>
        <div class="info-row"><div class="info-label">邮箱</div><div class="info-value"><?php echo e($f['owner_email'] ?? ($f['email'] ?? '-')); ?></div></div>
        <div class="info-row"><div class="info-label">网站域名</div><div class="info-value"><?php echo e($f['site_domain'] ?? '-'); ?></div></div>
        <div class="info-row"><div class="info-label">网站名称</div><div class="info-value"><?php echo e($f['site_name'] ?? '-'); ?></div></div>
        <div class="info-row"><div class="info-label">网站首页</div><div class="info-value"><?php
          $su = $f['site_url'] ?? '';
          if ($su) { echo '<a href="'.e($su).'" target="_blank" rel="noopener" style="color:var(--primary);">'.e($su).'</a>'; }
          else { echo '-'; }
        ?></div></div>
        <div class="info-row"><div class="info-label">网站语言</div><div class="info-value"><?php echo e($f['site_language'] ?? '中文简体'); ?></div></div>
        <div class="info-row"><div class="info-label">服务器IP</div><div class="info-value"><?php echo e($f['server_ip'] ?? '-'); ?></div></div>
        <div class="info-row"><div class="info-label">内容类型</div><div class="info-value"><?php echo e($f['content_type'] ?? '综合门户'); ?></div></div>
        <div class="info-row"><div class="info-label">备案状态</div><div class="info-value"><span class="status-badge <?php echo $statusInfo['class']; ?>"><?php echo $statusInfo['text']; ?></span></div></div>
        <div class="info-row"><div class="info-label">备案用户</div><div class="info-value"><?php echo e($f['username'] ?? '-'); ?></div></div>
        <div class="info-row"><div class="info-label">审核日期</div><div class="info-value"><?php echo e($f['audited_at'] ?? ($f['updated_at'] ?? '-')); ?></div></div>
        <div class="info-row"><div class="info-label">审核团队</div><div class="info-value"><?php echo e($auditTeam); ?></div></div>
        <div class="info-row" style="grid-column: 1 / -1;"><div class="info-label">审核意见</div><div class="info-value"><?php echo e($f['audit_remark'] ?? ($f['remark'] ?? '无')); ?></div></div>
      </div>

      <?php if (!empty($footerCode)): ?>
      <h2 class="section-title" style="margin-top:28px;">底部代码</h2>
      <div style="background:var(--bg-soft,#f9fafb);border:1px solid var(--border,#e5e7eb);border-radius:8px;padding:16px;">
        <div style="font-family:monospace;font-size:13px;white-space:pre-wrap;word-break:break-all;color:var(--text-2,#4b5563);"><?php echo e($footerCode); ?></div>
      </div>
      <div style="margin-top:10px;font-size:12px;color:var(--text-muted,#9ca3af);">注: 此代码由备案用户在网站底部添加, 用于备案标识展示。</div>
      <?php endif; ?>

      <?php if ($certifications): ?>
      <h2 class="section-title" style="margin-top:28px;">认证信息</h2>
      <div class="cert-gallery">
        <?php foreach ($certifications as $c): ?>
          <div class="cert-item">
            <?php if (!empty($c['image'])): ?>
              <img src="<?php echo asset($c['image']); ?>" alt="<?php echo e($c['name']); ?>">
            <?php else: ?>
              <div style="width:100%;height:110px;background:var(--bg-soft);display:flex;align-items:center;justify-content:center;color:var(--text-muted);font-size:13px;">
                <?php echo e($c['name']); ?>
              </div>
            <?php endif; ?>
            <div class="cert-name"><?php echo e($c['name']); ?></div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <div class="back-wrap">
        <button class="btn btn-ghost" onclick="history.back()">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
          返回上一页
        </button>
      </div>
    </div>
  </div>
</section>

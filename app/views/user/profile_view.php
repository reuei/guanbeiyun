<?php /** 公开个人中心 - 渲染于 default 布局 */
$profileUser = $profileUser ?? [];
$certs = $certs ?? [];
$filings = $filings ?? [];
$hitokoto = $hitokoto ?? '';
$uid = (int)($profileUser['id'] ?? 0);
$bg = !empty($profileUser['bg_image']) ? asset($profileUser['bg_image']) : ('https://picsum.photos/seed/' . $uid . '/1200/300');
$avatar = !empty($profileUser['avatar']) ? asset($profileUser['avatar']) : '';
$certTypeMap = ['enterprise' => '企业认证', 'personal' => '个人认证', 'partner' => '合作伙伴'];
?>
<section class="section" style="padding-top:24px;">
  <div class="container">
    <div class="profile-card">
      <div class="profile-banner" style="background-image:url('<?php echo e($bg); ?>');">
        <div class="profile-banner-mask"></div>
      </div>
      <div class="profile-head">
        <div class="profile-avatar">
          <?php if ($avatar): ?>
          <img src="<?php echo e($avatar); ?>" alt="<?php echo e($profileUser['username']); ?>">
          <?php else: ?>
          <div class="profile-avatar-fallback"><?php echo e(strtoupper(mb_substr($profileUser['username'] ?? 'U', 0, 1))); ?></div>
          <?php endif; ?>
        </div>
        <div class="profile-head-info">
          <h2 class="profile-username"><?php echo e($profileUser['username'] ?? ''); ?></h2>
          <div class="hitokoto">“<?php echo e($hitokoto); ?>”</div>
          <?php if ($certs): ?>
          <div class="cert-row">
            <?php foreach ($certs as $c):
              $certInfo = !empty($c['info']) ? $c['info'] : ($certTypeMap[$c['type']] ?? ($c['name'] ?? '已认证'));
              $certTitle = $c['name'] ?? ($certTypeMap[$c['type']] ?? '认证');
              $escapedInfo = htmlspecialchars($certInfo, ENT_QUOTES);
            ?>
            <span class="cert-item" title="<?php echo e($certTitle); ?>" onclick="gbToast.info('<?php echo $escapedInfo; ?>')">
              <?php if (!empty($c['image'])): ?>
              <img src="<?php echo e(asset($c['image'])); ?>" class="cert-icon" alt="<?php echo e($certTitle); ?>">
              <?php else: ?>
              <span class="cert-badge cert-<?php echo e($c['type'] ?? 'default'); ?>" style="<?php echo !empty($c['icon_style']) ? e($c['icon_style']) : ''; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9c2.5 0 4.76 1.02 6.39 2.66"/></svg>
              </span>
              <?php endif; ?>
            </span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="profile-body">
        <div class="panel">
          <div class="panel-head"><span class="title">基本信息</span></div>
          <div class="panel-body">
            <div class="detail-list">
              <?php if (!empty($profileUser['email'])): ?>
              <div class="dl-item"><div class="dl-label">邮箱</div><div class="dl-value"><?php echo e($profileUser['email']); ?></div></div>
              <?php endif; ?>
              <div class="dl-item"><div class="dl-label">手机号</div><div class="dl-value"><?php echo e(!empty($profileUser['phone']) ? $profileUser['phone'] : '-'); ?></div></div>
              <div class="dl-item"><div class="dl-label">注册时间</div><div class="dl-value"><?php echo e($profileUser['created_at'] ?? '-'); ?></div></div>
              <div class="dl-item"><div class="dl-label">个人简介</div><div class="dl-value"><?php echo e(!empty($profileUser['bio']) ? $profileUser['bio'] : '这个人很懒，什么都没留下'); ?></div></div>
            </div>
          </div>
        </div>

        <div class="panel" style="margin-top:18px;">
          <div class="panel-head"><span class="title">备案信息 <span class="tag tag-primary"><?php echo count($filings); ?></span></span></div>
          <div class="table-wrap" style="border:none;">
            <table class="table">
              <thead><tr><th>备案号</th><th>网站名称</th><th>域名</th></tr></thead>
              <tbody>
                <?php if ($filings): foreach ($filings as $f): ?>
                <tr>
                  <td><span class="tag tag-primary"><?php echo e($f['icp_no'] ?: '-'); ?></span></td>
                  <td class="text-sm"><?php echo e($f['site_name'] ?: '-'); ?></td>
                  <td class="text-sm">
                    <?php $url = $f['site_url'] ?: ($f['site_domain'] ? ('http://' . $f['site_domain']) : ''); ?>
                    <?php if ($url): ?>
                    <a href="<?php echo e($url); ?>" target="_blank" rel="noopener"><?php echo e($f['site_domain'] ?: $f['site_url']); ?></a>
                    <?php else: ?>
                    <?php echo e($f['site_domain'] ?: '-'); ?>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="3" class="empty">暂无备案信息</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<style>
.profile-card{background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;max-width:860px;margin:0 auto;}
.profile-banner{height:240px;background-size:cover;background-position:center;position:relative;}
.profile-banner-mask{position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,0) 40%,rgba(0,0,0,0.35) 100%);}
.profile-head{display:flex;align-items:flex-end;gap:20px;padding:0 28px;margin-top:-56px;position:relative;flex-wrap:wrap;}
.profile-avatar{width:96px;height:96px;border-radius:50%;border:4px solid var(--bg-elevated);overflow:hidden;flex-shrink:0;background:var(--bg);box-shadow:0 4px 14px rgba(0,0,0,0.12);}
.profile-avatar img{width:100%;height:100%;object-fit:cover;display:block;}
.profile-avatar-fallback{width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--primary);color:#fff;font-size:38px;font-weight:700;}
.profile-head-info{flex:1;min-width:0;padding-bottom:8px;}
.profile-username{font-size:24px;font-weight:700;margin:0;color:var(--text);}
.hitokoto{color:var(--text-muted);font-style:italic;font-size:14px;margin-top:6px;}
.cert-row{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;align-items:center;}
.cert-item{display:inline-flex;cursor:pointer;transition:transform 0.15s;}
.cert-item:hover{transform:translateY(-2px);}
.cert-icon{width:28px;height:28px;object-fit:contain;border-radius:4px;display:block;}
.cert-badge{width:28px;height:28px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:var(--primary);color:#fff;}
.cert-badge svg{width:16px;height:16px;}
.cert-enterprise{background:var(--primary);}
.cert-personal{background:var(--success,#22c55e);}
.cert-partner{background:var(--warning,#f59e0b);}
.profile-body{padding:18px 28px 28px;}
@media (max-width:640px){
  .profile-banner{height:180px;}
  .profile-head{padding:0 16px;margin-top:-44px;}
  .profile-avatar{width:72px;height:72px;border-width:3px;}
  .profile-avatar-fallback{font-size:30px;}
  .profile-username{font-size:20px;}
  .profile-body{padding:14px 16px 20px;}
}
</style>

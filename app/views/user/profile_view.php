<?php /** 公开个人中心 - 渲染于 default 布局 */
$profileUser = $profileUser ?? [];
$certs = $certs ?? [];
$filings = $filings ?? [];
$hitokoto = $hitokoto ?? '';
$uid = (int)($profileUser['id'] ?? 0);
// v4: 个人中心背景图每次刷新随机更换 (用户上传则使用上传图)
$bg = !empty($profileUser['bg_image']) ? asset($profileUser['bg_image']) : random_bg_image();
$avatar = !empty($profileUser['avatar']) ? asset($profileUser['avatar']) : '';
$certTypeMap = ['enterprise' => '企业认证', 'personal' => '个人认证', 'partner' => '合作伙伴'];
// 当前登录用户 (用于关注/私聊/举报/拉黑按钮)
$currentUser = current_user();
$isSelf = $currentUser && ((int)$currentUser['id'] === $uid);
$isFollowing = false; $isBlocked = false;
if ($currentUser && !$isSelf) {
    try {
        $f = db()->queryOne("SELECT id FROM " . db()->table('user_follows') . " WHERE user_id=? AND follow_id=?", [$currentUser['id'], $uid]);
        $isFollowing = $f ? true : false;
        $b = db()->queryOne("SELECT id FROM " . db()->table('user_blocks') . " WHERE user_id=? AND blocked_id=?", [$currentUser['id'], $uid]);
        $isBlocked = $b ? true : false;
    } catch (Throwable $e) {}
}
$followCount = 0; $fansCount = 0;
try {
    $followCount = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('user_follows') . " WHERE user_id=?", [$uid]);
    $fansCount = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('user_follows') . " WHERE follow_id=?", [$uid]);
} catch (Throwable $e) {}
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
          <div class="profile-stats">
            <span><b><?php echo $followCount; ?></b> 关注</span>
            <span><b><?php echo $fansCount; ?></b> 粉丝</span>
            <span><b><?php echo count($filings); ?></b> 备案</span>
          </div>
          <?php if ($certs): ?>
          <div class="cert-row">
            <?php foreach ($certs as $c):
              $certInfo = !empty($c['info']) ? $c['info'] : ($certTypeMap[$c['type']] ?? ($c['name'] ?? '已认证'));
              $certTitle = $c['name'] ?? ($certTypeMap[$c['type']] ?? '认证');
              $certImg = !empty($c['image']) ? asset($c['image']) : '';
            ?>
            <span class="cert-item" title="<?php echo e($certTitle); ?>" onclick='showCertDetail(<?php echo json_encode(["title" => $certTitle, "info" => $certInfo, "img" => $certImg, "type" => $c["type"] ?? "default"], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>)'>
              <?php if (!empty($c['image'])): ?>
              <img src="<?php echo e(asset($c['image'])); ?>" class="cert-icon" alt="<?php echo e($certTitle); ?>">
              <?php else: ?>
              <span class="cert-badge cert-<?php echo e($c['type'] ?? 'default'); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9c2.5 0 4.76 1.02 6.39 2.66"/></svg>
              </span>
              <?php endif; ?>
            </span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <?php if ($currentUser && !$isSelf): ?>
          <div class="profile-actions">
            <button class="btn btn-primary btn-sm" id="followBtn" onclick="toggleFollow(<?php echo $uid; ?>)"><?php echo $isFollowing ? '已关注' : '关注'; ?></button>
            <a class="btn btn-ghost btn-sm" href="<?php echo site_url('user/messages?to=' . $uid); ?>">私聊</a>
            <button class="btn btn-ghost btn-sm" id="blockBtn" onclick="toggleBlock(<?php echo $uid; ?>)"><?php echo $isBlocked ? '取消拉黑' : '拉黑'; ?></button>
            <button class="btn btn-danger btn-sm" onclick="reportUser(<?php echo $uid; ?>)">举报</button>
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
.profile-stats{display:flex;gap:18px;margin-top:10px;font-size:13px;color:var(--text-2);}
.profile-stats b{color:var(--text);font-size:15px;margin-right:4px;}
.cert-row{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;align-items:center;}
.cert-item{display:inline-flex;cursor:pointer;transition:transform 0.15s;}
.cert-item:hover{transform:translateY(-2px);}
.cert-icon{width:28px;height:28px;object-fit:contain;border-radius:4px;display:block;}
.cert-badge{width:28px;height:28px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:var(--primary);color:#fff;}
.cert-badge svg{width:16px;height:16px;}
.cert-enterprise{background:var(--primary);}
.cert-personal{background:var(--success,#22c55e);}
.cert-partner{background:var(--warning,#f59e0b);}
.profile-actions{display:flex;gap:8px;margin-top:14px;flex-wrap:wrap;}
.profile-body{padding:18px 28px 28px;}
.cert-detail-modal{position:fixed;inset:0;background:rgba(0,0,0,0.5);display:none;align-items:center;justify-content:center;z-index:9999;padding:20px;}
.cert-detail-modal.open{display:flex;}
.cert-detail-box{background:var(--bg-elevated);border-radius:12px;max-width:380px;width:100%;padding:24px;box-shadow:0 12px 40px rgba(0,0,0,0.2);}
.cert-detail-box .cd-icon{text-align:center;margin-bottom:14px;}
.cert-detail-box .cd-icon img{width:56px;height:56px;object-fit:contain;border-radius:8px;}
.cert-detail-box .cd-icon .cd-badge{display:inline-flex;width:56px;height:56px;border-radius:50%;background:var(--primary);color:#fff;align-items:center;justify-content:center;}
.cert-detail-box .cd-icon .cd-badge svg{width:28px;height:28px;}
.cert-detail-box h3{text-align:center;margin:0 0 8px;font-size:18px;color:var(--text);}
.cert-detail-box .cd-type{text-align:center;margin-bottom:14px;}
.cert-detail-box .cd-info{font-size:14px;color:var(--text-2);line-height:1.6;padding:12px;background:var(--bg-soft);border-radius:8px;}
.cert-detail-box .cd-close{margin-top:16px;text-align:center;}
@media (max-width:640px){
  .profile-banner{height:180px;}
  .profile-head{padding:0 16px;margin-top:-44px;}
  .profile-avatar{width:72px;height:72px;border-width:3px;}
  .profile-avatar-fallback{font-size:30px;}
  .profile-username{font-size:20px;}
  .profile-body{padding:14px 16px 20px;}
  .profile-actions{gap:6px;}
  .profile-actions .btn{flex:1;min-width:0;}
}
</style>

<!-- 认证详情弹窗 -->
<div class="cert-detail-modal" id="certDetailModal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="cert-detail-box">
    <div class="cd-icon" id="cdIcon"></div>
    <h3 id="cdTitle"></h3>
    <div class="cd-type" id="cdType"></div>
    <div class="cd-info" id="cdInfo"></div>
    <div class="cd-close"><button class="btn btn-primary" onclick="document.getElementById('certDetailModal').classList.remove('open')">关闭</button></div>
  </div>
</div>

<script>
function showCertDetail(data){
  var modal=document.getElementById('certDetailModal');
  var iconEl=document.getElementById('cdIcon');
  if(data.img){
    iconEl.innerHTML='<img src="'+data.img+'" alt="">';
  }else{
    iconEl.innerHTML='<span class="cd-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9c2.5 0 4.76 1.02 6.39 2.66"/></svg></span>';
  }
  document.getElementById('cdTitle').textContent=data.title||'认证信息';
  var typeMap={enterprise:'企业认证',personal:'个人认证',partner:'合作伙伴',default:'已认证'};
  document.getElementById('cdType').textContent=typeMap[data.type]||'已认证';
  document.getElementById('cdInfo').textContent=data.info||'该用户已完成平台认证';
  modal.classList.add('open');
}
function toggleFollow(uid){
  gbAjax({method:'POST',url:'<?php echo site_url('user/follow'); ?>',data:{target_id:uid},success:function(r){
    if(r&&r.code===0){
      var btn=document.getElementById('followBtn');
      btn.textContent=r.data.following?'已关注':'关注';
      gbToast.success(r.msg);
    }
  }});
}
function toggleBlock(uid){
  gbAjax({method:'POST',url:'<?php echo site_url('user/block'); ?>',data:{target_id:uid},success:function(r){
    if(r&&r.code===0){
      var btn=document.getElementById('blockBtn');
      btn.textContent=r.data.blocked?'取消拉黑':'拉黑';
      gbToast.success(r.msg);
    }
  }});
}
function reportUser(uid){
  var reason=prompt('请输入举报原因');
  if(!reason)return;
  gbAjax({method:'POST',url:'<?php echo site_url('user/report'); ?>',data:{target_id:uid,reason:reason},success:function(r){
    if(r&&r.code===0)gbToast.success(r.msg||'举报已提交');
  }});
}
</script>

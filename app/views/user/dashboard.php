<?php /** 用户总概览 */
$stats = $stats ?? [];
$notifications = $notifications ?? [];
$user = current_user();
?>
<div class="stat-grid">
  <div class="stat-card">
    <div class="sc-icon bg-primary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
    <div class="sc-info"><div class="sc-num"><?php echo $stats['filings'] ?? 0; ?></div><div class="sc-label">备案总数</div></div>
  </div>
  <div class="stat-card">
    <div class="sc-icon bg-warning"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
    <div class="sc-info"><div class="sc-num"><?php echo $stats['pending'] ?? 0; ?></div><div class="sc-label">审核中</div></div>
  </div>
  <div class="stat-card">
    <div class="sc-icon bg-success"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
    <div class="sc-info"><div class="sc-num"><?php echo $stats['passed'] ?? 0; ?></div><div class="sc-label">已通过</div></div>
  </div>
  <div class="stat-card">
    <div class="sc-icon bg-danger"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
    <div class="sc-info"><div class="sc-num"><?php echo $stats['tickets'] ?? 0; ?></div><div class="sc-label">工单数</div></div>
  </div>
</div>

<div class="uc-grid" style="grid-template-columns:1.4fr 1fr;gap:18px;">
  <div class="panel">
    <div class="panel-head"><span class="title">快捷操作</span></div>
    <div class="panel-body">
      <div class="grid-3" style="gap:14px;">
        <a href="<?php echo site_url('user/filings'); ?>" class="card" style="text-align:center;padding:20px 12px;">
          <div class="sc-icon bg-primary" style="margin:0 auto 10px;"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
          <div class="text-sm font-bold">申请备案</div>
        </a>
        <a href="<?php echo site_url('user/tickets'); ?>" class="card" style="text-align:center;padding:20px 12px;">
          <div class="sc-icon bg-success" style="margin:0 auto 10px;"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
          <div class="text-sm font-bold">提交工单</div>
        </a>
        <a href="<?php echo site_url('user/certification'); ?>" class="card" style="text-align:center;padding:20px 12px;">
          <div class="sc-icon bg-warning" style="margin:0 auto 10px;"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/><path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9c2.5 0 4.76 1.02 6.39 2.66"/></svg></div>
          <div class="text-sm font-bold">认证申请</div>
        </a>
      </div>
    </div>
  </div>
  <div class="panel">
    <div class="panel-head"><span class="title">消息通知</span></div>
    <div class="panel-body" style="padding:8px 0;">
      <?php if ($notifications): foreach ($notifications as $n): ?>
      <div style="padding:11px 20px;border-bottom:1px solid var(--divider);">
        <div class="text-sm font-bold"><?php echo e($n['title']); ?></div>
        <div class="text-muted text-sm" style="margin-top:2px;"><?php echo e(mb_substr(strip_tags($n['content']),0,40)); ?></div>
        <div class="text-muted" style="font-size:11px;margin-top:4px;"><?php echo e(time_ago($n['created_at'])); ?></div>
      </div>
      <?php endforeach; else: ?>
      <div class="empty">暂无通知</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="panel" style="margin-top:18px;">
  <div class="panel-head"><span class="title">账号信息</span></div>
  <div class="panel-body">
    <div class="detail-list">
      <div class="dl-item"><div class="dl-label">用户名</div><div class="dl-value"><?php echo e($user['username']); ?></div></div>
      <div class="dl-item"><div class="dl-label">邮箱</div><div class="dl-value"><?php echo e($user['email'] ?: '-'); ?></div></div>
      <div class="dl-item"><div class="dl-label">手机号</div><div class="dl-value"><?php echo e($user['phone'] ?: '-'); ?></div></div>
      <div class="dl-item"><div class="dl-label">最后登录</div><div class="dl-value"><?php echo e($user['last_login'] ?: '未登录'); ?></div></div>
      <div class="dl-item"><div class="dl-label">注册时间</div><div class="dl-value"><?php echo e($user['created_at']); ?></div></div>
    </div>
  </div>
</div>

<?php /** 后台总概览 */
$stats = $stats ?? [];
$recentFilings = $recentFilings ?? [];
$recentUsers = $recentUsers ?? [];
$statusMap = [0 => ['审核中', 'badge-pending'], 1 => ['已通过', 'badge-success'], 2 => ['未通过', 'badge-danger'], 3 => ['已撤销', 'badge-info']];
?>
<div class="stat-grid">
  <div class="stat-card">
    <div class="sc-icon bg-primary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg></div>
    <div class="sc-info"><div class="sc-num"><?php echo $stats['users'] ?? 0; ?></div><div class="sc-label">注册用户</div></div>
  </div>
  <div class="stat-card">
    <div class="sc-icon bg-success"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
    <div class="sc-info"><div class="sc-num"><?php echo $stats['filings'] ?? 0; ?></div><div class="sc-label">备案总数</div></div>
  </div>
  <div class="stat-card">
    <div class="sc-icon bg-warning"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
    <div class="sc-info"><div class="sc-num"><?php echo $stats['filingPending'] ?? 0; ?></div><div class="sc-label">待审核备案</div></div>
  </div>
  <div class="stat-card">
    <div class="sc-icon bg-danger"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
    <div class="sc-info"><div class="sc-num"><?php echo ($stats['feedbacks'] ?? 0) + ($stats['reports'] ?? 0); ?></div><div class="sc-label">反馈/举报</div></div>
  </div>
</div>

<div class="hide-mobile-grid" style="grid-template-columns:1.6fr 1fr;gap:18px;">
  <div class="panel">
    <div class="panel-head"><span class="title"><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg> 最近备案申请</span><a href="<?php echo site_url('admin/filings'); ?>" class="text-sm">查看全部</a></div>
    <div class="table-wrap" style="border:none;">
      <table class="table">
        <thead><tr><th>网站名称</th><th>域名</th><th>主办单位</th><th>状态</th><th>时间</th></tr></thead>
        <tbody>
          <?php if ($recentFilings): foreach ($recentFilings as $r): $st = $statusMap[$r['status']] ?? ['未知','badge-info']; ?>
          <tr>
            <td><?php echo e($r['site_name']); ?></td>
            <td><?php echo e($r['site_domain']); ?></td>
            <td><?php echo e($r['owner_name']); ?></td>
            <td><span class="badge <?php echo $st[1]; ?>"><?php echo $st[0]; ?></span></td>
            <td class="text-muted text-sm"><?php echo e(time_ago($r['created_at'])); ?></td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="5" class="text-center text-muted" style="padding:30px;">暂无数据</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="panel">
    <div class="panel-head"><span class="title"><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> 新注册用户</span></div>
    <div class="panel-body" style="padding:8px 0;">
      <?php if ($recentUsers): foreach ($recentUsers as $u): ?>
      <div style="display:flex;align-items:center;gap:12px;padding:11px 20px;border-bottom:1px solid var(--divider);">
        <div style="width:36px;height:36px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:600;"><?php echo e(strtoupper(mb_substr($u['username'],0,1))); ?></div>
        <div style="flex:1;min-width:0;"><div class="font-bold text-sm truncate"><?php echo e($u['username']); ?></div><div class="text-muted text-sm truncate"><?php echo e($u['email']); ?></div></div>
        <span class="text-muted text-sm"><?php echo e(time_ago($u['created_at'])); ?></span>
      </div>
      <?php endforeach; else: ?>
      <div class="empty">暂无数据</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="panel" style="margin-top:18px;">
  <div class="panel-head"><span class="title">备案状态分布</span></div>
  <div class="panel-body">
    <div style="display:flex;gap:20px;flex-wrap:wrap;">
      <?php
      $total = max(1, $stats['filings'] ?? 0);
      $items = [
        ['待审核', $stats['filingPending'] ?? 0, 'var(--warning)'],
        ['已通过', $stats['filingPassed'] ?? 0, 'var(--success)'],
        ['未通过', $stats['filingRejected'] ?? 0, 'var(--danger)'],
      ];
      foreach ($items as $it): $pct = round($it[1] / $total * 100); ?>
        <div style="flex:1;min-width:180px;">
          <div style="display:flex;justify-content:space-between;margin-bottom:6px;"><span class="text-sm"><?php echo $it[0]; ?></span><span class="text-sm font-bold"><?php echo $it[1]; ?> (<?php echo $pct; ?>%)</span></div>
          <div style="height:8px;background:var(--bg-soft);border-radius:4px;overflow:hidden;"><div style="height:100%;width:<?php echo $pct; ?>%;background:<?php echo $it[2]; ?>;transition:width .5s;"></div></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

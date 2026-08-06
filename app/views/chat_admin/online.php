<?php /** 在线用户列表 */
$rows = $rows ?? [];
$total = count($rows);
?>
<div class="panel">
  <div class="panel-head">
    <span class="title">
      <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      在线用户 (<?php echo (int)$total; ?>)
    </span>
    <button class="btn btn-ghost btn-sm" onclick="location.reload()">
      <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
      刷新
    </button>
  </div>
  <div class="panel-body">
    <div class="alert alert-info" style="background:#eff6ff;border:1px solid #dbeafe;color:#1e40af;padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      <b>提示:</b> 仅展示最近 30 秒内有活动的用户, 数据每刷新一次更新。
    </div>

    <div style="overflow-x:auto;">
      <table class="table" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:var(--bg-soft);text-align:left;">
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">UID</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">用户</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">头衔</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">等级</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">角色</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">最后活动</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($rows): foreach ($rows as $r):
            $role = $r['chat_role'] ?? 'user';
            $roleMap = [
              'user' => ['text' => '普通用户', 'bg' => '#e5e7eb', 'color' => '#374151'],
              'member' => ['text' => '成员', 'bg' => '#dbeafe', 'color' => '#1e40af'],
              'admin' => ['text' => '管理员', 'bg' => '#d1fae5', 'color' => '#065f46'],
              'super_admin' => ['text' => '超管', 'bg' => '#ede9fe', 'color' => '#6d28d9'],
              'platform_admin' => ['text' => '平台管理', 'bg' => 'linear-gradient(135deg,#1f2937,#92400e)', 'color' => '#fbbf24'],
            ];
            $ri = $roleMap[$role] ?? $roleMap['user'];
            $level = (int)($r['level'] ?? 1);
            $levelBg = level_bg_color($level);
            $lastActive = $r['last_active'] ?? '';
          ?>
          <tr style="border-bottom:1px solid var(--divider);">
            <td style="padding:10px 12px;font-size:13px;color:var(--text-muted);"><?php echo (int)($r['user_id'] ?? 0); ?></td>
            <td style="padding:10px 12px;">
              <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:30px;height:30px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;overflow:hidden;">
                  <?php if (!empty($r['avatar'])): ?><img src="<?php echo asset($r['avatar']); ?>" style="width:100%;height:100%;object-fit:cover;"><?php else: ?><?php echo e(strtoupper(mb_substr($r['username'] ?? '?', 0, 1))); ?><?php endif; ?>
                </div>
                <div style="font-size:13px;font-weight:600;color:var(--text);"><?php echo e($r['username'] ?? '已注销'); ?></div>
              </div>
            </td>
            <td style="padding:10px 12px;font-size:12px;"><?php if (!empty($r['title_text'])): ?><span style="display:inline-block;padding:1px 8px;font-size:11px;border-radius:3px;color:#fff;background:<?php echo e($r['title_bg'] ?: $levelBg); ?>;"><?php echo e($r['title_text']); ?></span><?php else: ?><span style="color:var(--text-muted);">-</span><?php endif; ?></td>
            <td style="padding:10px 12px;"><span style="display:inline-block;padding:1px 8px;font-size:11px;border-radius:3px;color:#fff;background:<?php echo e($levelBg); ?>;">Lv<?php echo (int)$level; ?></span></td>
            <td style="padding:10px 12px;"><span style="display:inline-block;padding:2px 8px;font-size:11px;border-radius:3px;font-weight:600;background:<?php echo e($ri['bg']); ?>;color:<?php echo e($ri['color']); ?>;"><?php echo e($ri['text']); ?></span></td>
            <td style="padding:10px 12px;font-size:12px;color:var(--text-muted);"><?php echo e($lastActive); ?></td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="6" style="padding:40px;text-align:center;color:var(--text-muted);">当前无在线用户</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

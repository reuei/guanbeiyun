<?php /** 在线用户列表页 */
$users = $users ?? [];
$roomId = (int)($roomId ?? 0);
$roomName = $roomName ?? '全部版块';
?>
<section class="section chat-online-page" style="padding:24px 16px;">
  <div style="max-width:900px;margin:0 auto;">
    <div style="margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
      <div>
        <h2 style="margin:0 0 6px;font-size:22px;color:var(--text);display:flex;align-items:center;gap:8px;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          在线用户
        </h2>
        <p style="margin:0;color:var(--text-muted);font-size:13px;">当前版块: <?php echo e($roomName); ?> (<?php echo count($users); ?> 人在线)</p>
      </div>
      <a href="<?php echo site_url('chat/online'); ?>" class="btn btn-ghost btn-sm">查看全部</a>
    </div>
    <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:12px;overflow:hidden;">
      <?php if ($users): ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:0;">
        <?php foreach ($users as $u):
          $uname = $u['username'] ?? '已注销';
          $uavatar = !empty($u['avatar']) ? asset($u['avatar']) : '';
          $level = (int)($u['level'] ?? 1);
          $titleText = $u['title_text'] ?? '';
          $role = $u['chat_role'] ?? 'user';
          $roleLabel = role_label($role);
          $bg = level_bg_color($level);
        ?>
        <a href="<?php echo site_url('u/' . (int)$u['user_id']); ?>" target="_blank" style="display:flex;align-items:center;gap:10px;padding:12px 14px;border-bottom:1px solid var(--divider);text-decoration:none;color:inherit;transition:background .2s;">
          <div style="width:38px;height:38px;border-radius:50%;overflow:hidden;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;flex-shrink:0;">
            <?php if ($uavatar): ?><img src="<?php echo e($uavatar); ?>" alt="<?php echo e($uname); ?>" style="width:100%;height:100%;object-fit:cover;"><?php else: ?><?php echo e(mb_substr($uname, 0, 1)); ?><?php endif; ?>
          </div>
          <div style="flex:1;min-width:0;">
            <div style="font-weight:600;color:var(--text);font-size:14px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
              <?php if ($titleText): ?><span style="display:inline-block;padding:0 6px;font-size:11px;border-radius:3px;color:#fff;margin-right:4px;background:<?php echo e($bg); ?>;"><?php echo e($titleText); ?></span><?php endif; ?>
              <span style="display:inline-block;padding:0 6px;font-size:11px;border-radius:3px;color:#fff;margin-right:4px;background:<?php echo e($bg); ?>;">Lv<?php echo $level; ?></span>
              <?php if ($roleLabel): ?><span style="display:inline-block;padding:0 6px;font-size:11px;border-radius:3px;background:<?php echo e($roleLabel['bg']); ?>;color:<?php echo e($roleLabel['color']); ?>;margin-right:4px;border:1px solid rgba(0,0,0,.05);"><?php echo e($roleLabel['text']); ?></span><?php endif; ?>
              <?php echo e($uname); ?>
            </div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:3px;">
              <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#22c55e;margin-right:4px;"></span>在线
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div style="padding:60px 20px;text-align:center;color:var(--text-muted);font-size:14px;">
        <div style="font-size:48px;margin-bottom:10px;opacity:.5;">😴</div>
        <div>当前没有在线用户</div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php /** 聊天版块列表页 - 选择区块后进入对应区块聊天 */
$rooms = $rooms ?? [];
?>
<section class="section chat-rooms-page" style="padding:24px 16px;">
  <div style="max-width:900px;margin:0 auto;">
    <div style="margin-bottom:20px;">
      <h2 style="margin:0 0 6px;font-size:22px;color:var(--text);display:flex;align-items:center;gap:8px;">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        选择区块
      </h2>
      <p style="margin:0;color:var(--text-muted);font-size:13px;">点击下方区块进入对应版块聊天</p>
    </div>
    <div class="rooms-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px;">
      <a class="room-card" href="<?php echo site_url('chat'); ?>" style="display:block;background:var(--card-bg);border:1px solid var(--border);border-radius:12px;padding:18px;text-decoration:none;color:inherit;transition:all .2s;">
        <div style="font-size:32px;margin-bottom:8px;">🏠</div>
        <div style="font-weight:600;font-size:15px;color:var(--text);">综合聊天</div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">不指定版块</div>
      </a>
      <?php if ($rooms): foreach ($rooms as $r):
        $icon = $r['icon'] ?: '💬';
        $name = e($r['name']);
        $desc = e($r['description'] ?? '');
        $online = (int)($r['online_count'] ?? 0);
      ?>
      <a class="room-card" href="<?php echo site_url('chat?room=' . (int)$r['id']); ?>" style="display:block;background:var(--card-bg);border:1px solid var(--border);border-radius:12px;padding:18px;text-decoration:none;color:inherit;transition:all .2s;position:relative;">
        <div style="font-size:32px;margin-bottom:8px;"><?php echo $icon; ?></div>
        <div style="font-weight:600;font-size:15px;color:var(--text);"><?php echo $name; ?></div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px;min-height:16px;"><?php echo $desc; ?></div>
        <div style="position:absolute;top:12px;right:12px;display:inline-flex;align-items:center;gap:4px;background:var(--primary-bg);color:var(--primary);padding:2px 8px;border-radius:10px;font-size:11px;">
          <span style="width:6px;height:6px;border-radius:50%;background:#22c55e;display:inline-block;"></span>
          <?php echo $online; ?> 在线
        </div>
      </a>
      <?php endforeach; else: ?>
      <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-muted);font-size:14px;">暂无版块</div>
      <?php endif; ?>
    </div>
  </div>
</section>
<style>
.room-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.08); border-color: var(--primary) !important; }
@media (max-width: 600px) { .rooms-grid { grid-template-columns: repeat(2, 1fr); } }
</style>

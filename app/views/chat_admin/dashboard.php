<?php /** 聊天室管理 - 概览 */
$stats = $stats ?? [];
$myRole = $myRole ?? 'admin';
$myIsSuper = $myIsSuper ?? false;
$globalMute = site_config('chat_global_mute', '0') == '1';
$roleLabel = role_label($myRole);
$roleText  = $roleLabel ? $roleLabel['text'] : '管理员';
?>
<div class="panel">
  <div class="panel-head">
    <span class="title">
      <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      聊天室管理概览
    </span>
    <div style="display:flex;gap:8px;align-items:center;">
      <span style="font-size:12px;color:var(--text-muted);">当前身份:</span>
      <span class="ca-role-badge" style="background:<?php echo e($roleLabel['bg']); ?>;color:<?php echo e($roleLabel['color']); ?>;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:600;border:1px solid rgba(0,0,0,.05);"><?php echo e($roleText); ?></span>
    </div>
  </div>
  <div class="panel-body">
    <div class="grid-4" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:20px;">
      <div class="stat-card" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);border-radius:12px;padding:18px;">
        <div style="font-size:11px;color:#1e40af;text-transform:uppercase;font-weight:600;letter-spacing:.5px;">聊天版块</div>
        <div style="font-size:28px;font-weight:700;color:#1e3a8a;margin-top:6px;"><?php echo (int)($stats['rooms'] ?? 0); ?></div>
        <a href="<?php echo site_url('admins/rooms'); ?>" style="font-size:12px;color:#3b82f6;text-decoration:none;margin-top:6px;display:inline-block;">管理 →</a>
      </div>
      <div class="stat-card" style="background:linear-gradient(135deg,#ecfdf5,#d1fae5);border-radius:12px;padding:18px;">
        <div style="font-size:11px;color:#065f46;text-transform:uppercase;font-weight:600;letter-spacing:.5px;">在线人数</div>
        <div style="font-size:28px;font-weight:700;color:#064e3b;margin-top:6px;"><?php echo (int)($stats['online'] ?? 0); ?></div>
        <a href="<?php echo site_url('admins/online'); ?>" style="font-size:12px;color:#10b981;text-decoration:none;margin-top:6px;display:inline-block;">查看 →</a>
      </div>
      <div class="stat-card" style="background:linear-gradient(135deg,#fef3c7,#fde68a);border-radius:12px;padding:18px;">
        <div style="font-size:11px;color:#92400e;text-transform:uppercase;font-weight:600;letter-spacing:.5px;">消息总数</div>
        <div style="font-size:28px;font-weight:700;color:#78350f;margin-top:6px;"><?php echo (int)($stats['messages'] ?? 0); ?></div>
        <a href="<?php echo site_url('admins/messages'); ?>" style="font-size:12px;color:#f59e0b;text-decoration:none;margin-top:6px;display:inline-block;">管理 →</a>
      </div>
      <div class="stat-card" style="background:linear-gradient(135deg,#fee2e2,#fecaca);border-radius:12px;padding:18px;">
        <div style="font-size:11px;color:#991b1b;text-transform:uppercase;font-weight:600;letter-spacing:.5px;">禁言中</div>
        <div style="font-size:28px;font-weight:700;color:#7f1d1d;margin-top:6px;"><?php echo (int)($stats['banned'] ?? 0); ?></div>
        <a href="<?php echo site_url('admins/banned'); ?>" style="font-size:12px;color:#ef4444;text-decoration:none;margin-top:6px;display:inline-block;">管理 →</a>
      </div>
      <div class="stat-card" style="background:linear-gradient(135deg,#f5f3ff,#ede9fe);border-radius:12px;padding:18px;">
        <div style="font-size:11px;color:#5b21b6;text-transform:uppercase;font-weight:600;letter-spacing:.5px;">管理员数</div>
        <div style="font-size:28px;font-weight:700;color:#4c1d95;margin-top:6px;"><?php echo (int)($stats['admins'] ?? 0); ?></div>
        <a href="<?php echo site_url('admins/titles'); ?>" style="font-size:12px;color:#8b5cf6;text-decoration:none;margin-top:6px;display:inline-block;">头衔管理 →</a>
      </div>
      <div class="stat-card" style="background:linear-gradient(135deg,#fdf2f8,#fce7f3);border-radius:12px;padding:18px;">
        <div style="font-size:11px;color:#9d174d;text-transform:uppercase;font-weight:600;letter-spacing:.5px;">生效公告</div>
        <div style="font-size:28px;font-weight:700;color:#831843;margin-top:6px;"><?php echo (int)($stats['announcements'] ?? 0); ?></div>
        <a href="<?php echo site_url('admins/announcements'); ?>" style="font-size:12px;color:#ec4899;text-decoration:none;margin-top:6px;display:inline-block;">公告管理 →</a>
      </div>
      <div class="stat-card" style="background:linear-gradient(135deg,#f0f9ff,#e0f2fe);border-radius:12px;padding:18px;">
        <div style="font-size:11px;color:#075985;text-transform:uppercase;font-weight:600;letter-spacing:.5px;">今日消息</div>
        <div style="font-size:28px;font-weight:700;color:#0c4a6e;margin-top:6px;"><?php echo (int)($stats['today_msgs'] ?? 0); ?></div>
        <div style="font-size:12px;color:#64748b;margin-top:6px;">今日活跃: <?php echo (int)($stats['today_users'] ?? 0); ?> 人</div>
      </div>
      <div class="stat-card" style="background:linear-gradient(135deg,<?php echo $globalMute ? '#fef3c7,#fde68a' : '#f0fdf4,#dcfce7'; ?>);border-radius:12px;padding:18px;">
        <div style="font-size:11px;color:<?php echo $globalMute ? '#92400e' : '#166534'; ?>;text-transform:uppercase;font-weight:600;letter-spacing:.5px;">全体禁言</div>
        <div style="font-size:18px;font-weight:700;color:<?php echo $globalMute ? '#78350f' : '#14532d'; ?>;margin-top:6px;"><?php echo $globalMute ? '已开启' : '未开启'; ?></div>
        <?php if ($myIsSuper): ?>
        <button type="button" class="btn btn-sm <?php echo $globalMute ? 'btn-success' : 'btn-warning'; ?>" style="margin-top:6px;font-size:12px;padding:4px 10px;" onclick="caToggleGlobalMute(<?php echo $globalMute ? 0 : 1; ?>)">
          <?php echo $globalMute ? '解除禁言' : '开启禁言'; ?>
        </button>
        <?php endif; ?>
      </div>
    </div>

    <h4 style="margin:24px 0 14px;padding-bottom:10px;border-bottom:1px solid var(--divider);">快捷操作</h4>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;">
      <a class="ca-quick-btn" href="<?php echo site_url('admins/rooms'); ?>" style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:18px;background:var(--card-bg);border:1px solid var(--border);border-radius:10px;text-decoration:none;color:inherit;transition:all .2s;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        <span style="font-size:13px;color:var(--text);">聊天版块</span>
      </a>
      <a class="ca-quick-btn" href="<?php echo site_url('admins/announcements'); ?>" style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:18px;background:var(--card-bg);border:1px solid var(--border);border-radius:10px;text-decoration:none;color:inherit;transition:all .2s;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ec4899" stroke-width="2"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>
        <span style="font-size:13px;color:var(--text);">发布公告</span>
      </a>
      <a class="ca-quick-btn" href="<?php echo site_url('admins/banned'); ?>" style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:18px;background:var(--card-bg);border:1px solid var(--border);border-radius:10px;text-decoration:none;color:inherit;transition:all .2s;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
        <span style="font-size:13px;color:var(--text);">禁言管理</span>
      </a>
      <a class="ca-quick-btn" href="<?php echo site_url('admins/titles'); ?>" style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:18px;background:var(--card-bg);border:1px solid var(--border);border-radius:10px;text-decoration:none;color:inherit;transition:all .2s;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2"><path d="M12 2l3 7h7l-5.5 4.5L18 21l-6-4-6 4 1.5-7.5L2 9h7z"/></svg>
        <span style="font-size:13px;color:var(--text);">用户头衔</span>
      </a>
      <a class="ca-quick-btn" href="<?php echo site_url('admins/messages'); ?>" style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:18px;background:var(--card-bg);border:1px solid var(--border);border-radius:10px;text-decoration:none;color:inherit;transition:all .2s;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <span style="font-size:13px;color:var(--text);">消息管理</span>
      </a>
      <a class="ca-quick-btn" href="<?php echo site_url('admins/online'); ?>" style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:18px;background:var(--card-bg);border:1px solid var(--border);border-radius:10px;text-decoration:none;color:inherit;transition:all .2s;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#06b6d4" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
        <span style="font-size:13px;color:var(--text);">在线用户</span>
      </a>
    </div>

    <h4 style="margin:24px 0 14px;padding-bottom:10px;border-bottom:1px solid var(--divider);">管理员权限说明</h4>
    <div style="background:var(--bg-soft);border-radius:8px;padding:14px 18px;font-size:13px;color:var(--text-2);line-height:1.8;">
      <div><b style="color:#065f46;">管理员</b>：创建聊天版块、发布全局公告、禁言用户、撤回消息、修改用户头衔(仅普通用户)</div>
      <?php if ($myIsSuper): ?>
      <div style="margin-top:6px;"><b style="color:#5b21b6;">超管</b>：在管理员基础上 + 发布全体禁言、封禁用户、发布弹窗公告、封用户为管理员/超管</div>
      <?php else: ?>
      <div style="margin-top:6px;opacity:.6;"><b>超管</b>：在管理员基础上 + 发布全体禁言、封禁用户、发布弹窗公告、封用户为管理员/超管 <span style="color:var(--text-muted);">(需超管权限)</span></div>
      <?php endif; ?>
    </div>
  </div>
</div>
<script>
function caToggleGlobalMute(enabled){
  gbAjax({method:'POST', url:'<?php echo site_url("admins/toggleGlobalMute"); ?>', data:{enabled:enabled?1:0},
    success:function(res){
      if(res.code===0){
        gbToast.success(res.msg);
        setTimeout(function(){ location.reload(); }, 600);
      } else {
        gbToast.error(res.msg || '操作失败');
      }
    }
  });
}
</script>

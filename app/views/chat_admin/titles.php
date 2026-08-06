<?php /** 用户头衔管理 */
$rows = $rows ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$size = $size ?? 20;
$kw = $kw ?? '';
$myIsSuper = $myIsSuper ?? false;
$totalPages = max(1, (int)ceil($total / $size));
$roleMap = [
  'user' => ['text' => '普通用户', 'bg' => '#e5e7eb', 'color' => '#374151'],
  'member' => ['text' => '成员', 'bg' => '#dbeafe', 'color' => '#1e40af'],
  'admin' => ['text' => '管理员', 'bg' => '#d1fae5', 'color' => '#065f46'],
  'super_admin' => ['text' => '超管', 'bg' => '#ede9fe', 'color' => '#5b21b6'],
  'platform_admin' => ['text' => '平台管理', 'bg' => 'linear-gradient(135deg,#1f2937,#f59e0b)', 'color' => '#fff'],
];
?>
<div class="panel">
  <div class="panel-head">
    <span class="title"><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3 7h7l-5.5 4.5L18 21l-6-4-6 4 1.5-7.5L2 9h7z"/></svg> 用户头衔管理</span>
    <form method="get" action="" style="display:flex;gap:6px;">
      <input class="form-control" type="text" name="kw" value="<?php echo e($kw); ?>" placeholder="搜索用户名/邮箱" style="width:200px;">
      <button class="btn btn-primary btn-sm" type="submit">搜索</button>
    </form>
  </div>
  <div class="panel-body">
    <div class="alert alert-info" style="background:#eff6ff;border:1px solid #dbeafe;color:#1e40af;padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      <b>头衔规则:</b> 等级 1-4 灰色, 5-9 蓝色, 10-19 绿色, 20-29 紫色, 30-49 红色, 50+ 橙金。
      可自定义头衔文字与背景色(留空按等级自动)。<?php if ($myIsSuper): ?>超管可封用户为管理员/超管。<?php else: ?>普通管理员只能修改普通用户的头衔。<?php endif; ?>
    </div>
    <div style="overflow-x:auto;">
      <table class="table" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:var(--bg-soft);text-align:left;">
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">UID</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">用户</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">头衔预览</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">文字</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">等级</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">角色</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">账号状态</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);text-align:right;">操作</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($rows): foreach ($rows as $r):
            $level = (int)($r['level'] ?? 1);
            $titleText = $r['title_text'] ?? '';
            $titleBg = $r['title_bg'] ?? '';
            $bg = $titleBg ?: level_bg_color($level);
            $role = $r['chat_role'] ?? 'user';
            $roleInfo = $roleMap[$role] ?? $roleMap['user'];
            $status = (int)($r['status'] ?? 1);
          ?>
          <tr style="border-bottom:1px solid var(--divider);">
            <td style="padding:10px 12px;font-size:13px;color:var(--text-muted);"><?php echo (int)$r['user_id']; ?></td>
            <td style="padding:10px 12px;">
              <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:28px;height:28px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;overflow:hidden;">
                  <?php if (!empty($r['avatar'])): ?><img src="<?php echo asset($r['avatar']); ?>" style="width:100%;height:100%;object-fit:cover;"><?php else: ?><?php echo e(strtoupper(mb_substr($r['username'] ?? '?', 0, 1))); ?><?php endif; ?>
                </div>
                <div>
                  <div style="font-size:13px;font-weight:600;color:var(--text);"><?php echo e($r['username'] ?? '已注销'); ?></div>
                  <div style="font-size:11px;color:var(--text-muted);"><?php echo e($r['email'] ?? ''); ?></div>
                </div>
              </div>
            </td>
            <td style="padding:10px 12px;">
              <?php if ($titleText): ?><span style="display:inline-block;padding:1px 6px;font-size:10px;border-radius:3px;color:#fff;margin-right:4px;background:<?php echo e($bg); ?>;"><?php echo e($titleText); ?></span><?php endif; ?>
              <span style="display:inline-block;padding:1px 6px;font-size:10px;border-radius:3px;color:#fff;background:<?php echo e($bg); ?>;">Lv<?php echo $level; ?></span>
              <?php if ($role !== 'user' && $role !== 'member'): ?><span style="display:inline-block;padding:1px 6px;font-size:10px;border-radius:3px;background:<?php echo e($roleInfo['bg']); ?>;color:<?php echo e($roleInfo['color']); ?>;margin-left:4px;border:1px solid rgba(0,0,0,.05);"><?php echo e($roleInfo['text']); ?></span><?php endif; ?>
            </td>
            <td style="padding:10px 12px;font-size:12px;color:var(--text-2);"><?php echo $titleText ? e($titleText) : '<span style="color:var(--text-muted);">-</span>'; ?></td>
            <td style="padding:10px 12px;font-size:13px;color:var(--text);font-weight:600;"><?php echo $level; ?></td>
            <td style="padding:10px 12px;"><span style="padding:2px 8px;border-radius:4px;font-size:11px;background:<?php echo e($roleInfo['bg']); ?>;color:<?php echo e($roleInfo['color']); ?>;border:1px solid rgba(0,0,0,.05);"><?php echo e($roleInfo['text']); ?></span></td>
            <td style="padding:10px 12px;"><?php if ($status === 1): ?><span style="padding:2px 8px;border-radius:4px;font-size:11px;background:#d1fae5;color:#065f46;">正常</span><?php else: ?><span style="padding:2px 8px;border-radius:4px;font-size:11px;background:#fee2e2;color:#991b1b;">已封禁</span><?php endif; ?></td>
            <td style="padding:10px 12px;text-align:right;white-space:nowrap;">
              <button class="btn btn-ghost btn-sm" onclick='caEditTitle(<?php echo htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8'); ?>)'>编辑头衔</button>
              <?php if ($myIsSuper && $role !== 'platform_admin'): ?>
                <?php if ($role === 'user' || $role === 'member'): ?>
                <button class="btn btn-success btn-sm" onclick="caSetRole(<?php echo (int)$r['user_id']; ?>, 'admin')">封为管理</button>
                <?php else: ?>
                <button class="btn btn-warning btn-sm" onclick="caSetRole(<?php echo (int)$r['user_id']; ?>, 'user')">取消管理</button>
                <?php endif; ?>
                <?php if ($status === 1): ?>
                <button class="btn btn-danger btn-sm" onclick="caBanAccount(<?php echo (int)$r['user_id']; ?>, 0)">封禁账号</button>
                <?php else: ?>
                <button class="btn btn-success btn-sm" onclick="caBanAccount(<?php echo (int)$r['user_id']; ?>, 1)">解封账号</button>
                <?php endif; ?>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="8" style="padding:40px;text-align:center;color:var(--text-muted);">暂无用户数据</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php if ($totalPages > 1): ?>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;font-size:13px;color:var(--text-2);">
      <div>共 <?php echo $total; ?> 条, 第 <?php echo $page; ?>/<?php echo $totalPages; ?> 页</div>
      <div style="display:flex;gap:6px;">
        <?php if ($page > 1): ?><a class="btn btn-ghost btn-sm" href="?kw=<?php echo urlencode($kw); ?>&page=<?php echo $page-1; ?>">上一页</a><?php endif; ?>
        <?php if ($page < $totalPages): ?><a class="btn btn-ghost btn-sm" href="?kw=<?php echo urlencode($kw); ?>&page=<?php echo $page+1; ?>">下一页</a><?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- 编辑头衔弹窗 -->
<div class="modal-overlay" id="caTitleModal" style="display:none;">
  <div class="modal-box" style="max-width:480px;">
    <div class="modal-head"><h3>编辑用户头衔</h3><span class="icon-btn" onclick="gbModal.close('caTitleModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body">
      <form id="caTitleForm" onsubmit="return caSaveTitle(event)">
        <input type="hidden" name="user_id" id="caTitleUserId" value="0">
        <div id="caTitleUserDisplay" style="padding:10px 12px;background:var(--bg-soft);border-radius:6px;margin-bottom:14px;font-size:13px;"></div>
        <div class="form-group">
          <label class="form-label">头衔文字</label>
          <input class="form-control" type="text" name="title_text" id="caTitleText" maxlength="50" placeholder="如: 大佬, 萌新, 等">
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">等级 (1-99)</label>
            <input class="form-control" type="number" name="level" id="caTitleLevel" min="1" max="99" value="1">
            <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">
              1-4灰, 5-9蓝, 10-19绿, 20-29紫, 30-49红, 50+橙金
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">自定义背景色</label>
            <input class="form-control" type="text" name="title_bg" id="caTitleBg" maxlength="50" placeholder="留空按等级">
            <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">支持 CSS 颜色或渐变</div>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">聊天室角色</label>
          <select class="form-control" name="chat_role" id="caTitleRole">
            <option value="user">普通用户</option>
            <option value="member">成员</option>
            <?php if ($myIsSuper): ?>
            <option value="admin">管理员</option>
            <option value="super_admin">超管</option>
            <?php endif; ?>
          </select>
          <?php if (!$myIsSuper): ?>
          <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">仅超管可设置管理员/超管角色</div>
          <?php endif; ?>
        </div>
        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:14px;">
          <button type="button" class="btn btn-ghost" onclick="gbModal.close('caTitleModal')">取消</button>
          <button type="submit" class="btn btn-primary" id="caTitleSaveBtn">保存</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function caEditTitle(data){
  document.getElementById('caTitleUserId').value = data.user_id;
  document.getElementById('caTitleUserDisplay').innerHTML = '<b>用户:</b> ' + (data.username || '已注销') + ' (UID: ' + data.user_id + ')';
  document.getElementById('caTitleText').value = data.title_text || '';
  document.getElementById('caTitleLevel').value = data.level || 1;
  document.getElementById('caTitleBg').value = data.title_bg || '';
  var role = data.chat_role || 'user';
  var roleSelect = document.getElementById('caTitleRole');
  for (var i = 0; i < roleSelect.options.length; i++){
    if (roleSelect.options[i].value === role){ roleSelect.selectedIndex = i; break; }
  }
  gbModal.open('caTitleModal');
}
function caSaveTitle(e){
  e.preventDefault();
  var fd = new FormData(e.target);
  var data = {}; fd.forEach(function(v,k){ data[k]=v; });
  var btn = document.getElementById('caTitleSaveBtn');
  btn.disabled = true; btn.innerHTML = '保存中...';
  gbAjax({method:'POST', url:'<?php echo site_url("admins/title/save"); ?>', data:data,
    success:function(res){
      if(res.code===0){
        gbToast.success(res.msg);
        setTimeout(function(){ location.reload(); }, 500);
      } else {
        gbToast.error(res.msg || '保存失败');
      }
    },
    complete:function(){ btn.disabled=false; btn.innerHTML='保存'; }
  });
  return false;
}
function caSetRole(userId, role){
  var msg = role === 'admin' ? '确认封该用户为聊天室管理员?\n(将通知用户)' : '确认取消该用户的管理员身份?\n(将恢复为普通用户, 通知用户)';
  if(!confirm(msg)) return;
  gbAjax({method:'POST', url:'<?php echo site_url("admins/role/set"); ?>', data:{user_id:userId, chat_role:role},
    success:function(res){
      if(res.code===0){ gbToast.success(res.msg); setTimeout(function(){ location.reload(); }, 500); }
      else { gbToast.error(res.msg || '操作失败'); }
    }
  });
}
function caBanAccount(userId, status){
  var msg = status === 0 ? '确认封禁该用户账号?\n(用户将无法登录, 同时被禁言, 通知用户)' : '确认解封该用户账号?';
  if(!confirm(msg)) return;
  gbAjax({method:'POST', url:'<?php echo site_url("admins/account/ban"); ?>', data:{user_id:userId, status:status},
    success:function(res){
      if(res.code===0){ gbToast.success(res.msg); setTimeout(function(){ location.reload(); }, 500); }
      else { gbToast.error(res.msg || '操作失败'); }
    }
  });
}
</script>

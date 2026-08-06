<?php /** 禁言用户管理 */
$rows = $rows ?? [];
$myIsSuper = $myIsSuper ?? false;
?>
<div class="panel">
  <div class="panel-head">
    <span class="title"><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg> 禁言用户管理</span>
    <button class="btn btn-primary btn-sm" onclick="caBanUserModal()"><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> 禁言用户</button>
  </div>
  <div class="panel-body">
    <div style="overflow-x:auto;">
      <table class="table" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:var(--bg-soft);text-align:left;">
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">ID</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">用户</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">原因</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">来源</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">截止时间</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">状态</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">禁言时间</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);text-align:right;">操作</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($rows): foreach ($rows as $r):
            $expired = strtotime($r['banned_until']) <= time();
            $sourceText = $r['source'] === 'auto' ? '自动' : '手动';
            $sourceBg = $r['source'] === 'auto' ? '#fef3c7' : '#dbeafe';
            $sourceColor = $r['source'] === 'auto' ? '#92400e' : '#1e40af';
          ?>
          <tr style="border-bottom:1px solid var(--divider);<?php echo $expired ? 'opacity:.5;' : ''; ?>">
            <td style="padding:10px 12px;font-size:13px;color:var(--text-muted);"><?php echo (int)$r['id']; ?></td>
            <td style="padding:10px 12px;">
              <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:28px;height:28px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;overflow:hidden;">
                  <?php if (!empty($r['avatar'])): ?><img src="<?php echo asset($r['avatar']); ?>" style="width:100%;height:100%;object-fit:cover;"><?php else: ?><?php echo e(strtoupper(mb_substr($r['username'] ?? '?', 0, 1))); ?><?php endif; ?>
                </div>
                <div>
                  <div style="font-size:13px;font-weight:600;color:var(--text);"><?php echo e($r['username'] ?? '已注销'); ?></div>
                  <div style="font-size:11px;color:var(--text-muted);">UID: <?php echo (int)$r['user_id']; ?></div>
                </div>
              </div>
            </td>
            <td style="padding:10px 12px;font-size:12px;color:var(--text-2);max-width:200px;"><?php echo e($r['reason']); ?></td>
            <td style="padding:10px 12px;"><span style="padding:2px 8px;border-radius:4px;font-size:11px;background:<?php echo $sourceBg; ?>;color:<?php echo $sourceColor; ?>;"><?php echo $sourceText; ?></span></td>
            <td style="padding:10px 12px;font-size:12px;color:<?php echo $expired ? '#991b1b' : 'var(--text)'; ?>;"><?php echo e($r['banned_until']); ?></td>
            <td style="padding:10px 12px;"><?php if ($expired): ?><span style="padding:2px 8px;border-radius:4px;font-size:11px;background:#e5e7eb;color:#374151;">已过期</span><?php else: ?><span style="padding:2px 8px;border-radius:4px;font-size:11px;background:#fee2e2;color:#991b1b;">禁言中</span><?php endif; ?></td>
            <td style="padding:10px 12px;font-size:12px;color:var(--text-muted);"><?php echo e($r['created_at']); ?></td>
            <td style="padding:10px 12px;text-align:right;">
              <?php if (!$expired): ?>
              <button class="btn btn-success btn-sm" onclick="caUnbanUser(<?php echo (int)$r['user_id']; ?>)">解除</button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="8" style="padding:40px;text-align:center;color:var(--text-muted);">暂无禁言记录</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- 禁言用户弹窗 -->
<div class="modal-overlay" id="caBanModal" style="display:none;">
  <div class="modal-box" style="max-width:460px;">
    <div class="modal-head"><h3>禁言用户</h3><span class="icon-btn" onclick="gbModal.close('caBanModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body">
      <form id="caBanForm" onsubmit="return caBanUser(event)">
        <div class="form-group">
          <label class="form-label">用户 ID *</label>
          <input class="form-control" type="number" name="user_id" id="caBanUserId" required min="1" placeholder="请输入用户 ID">
          <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">可在「用户头衔」中查找用户 ID</div>
        </div>
        <div class="form-group">
          <label class="form-label">禁言时长 *</label>
          <select class="form-control" name="duration" id="caBanDuration">
            <option value="10">10 分钟</option>
            <option value="30">30 分钟</option>
            <option value="60">1 小时</option>
            <option value="180">3 小时</option>
            <option value="1440">1 天</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">禁言原因</label>
          <textarea class="form-control" name="reason" id="caBanReason" rows="2" maxlength="200" placeholder="可选, 将通知用户"></textarea>
        </div>
        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:14px;">
          <button type="button" class="btn btn-ghost" onclick="gbModal.close('caBanModal')">取消</button>
          <button type="submit" class="btn btn-danger" id="caBanSaveBtn">确认禁言</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function caBanUserModal(){
  document.getElementById('caBanUserId').value = '';
  document.getElementById('caBanDuration').value = '10';
  document.getElementById('caBanReason').value = '';
  gbModal.open('caBanModal');
}
function caBanUser(e){
  e.preventDefault();
  var fd = new FormData(e.target);
  var data = {}; fd.forEach(function(v,k){ data[k]=v; });
  if(!data.user_id){ gbToast.warning('请输入用户 ID'); return false; }
  var btn = document.getElementById('caBanSaveBtn');
  btn.disabled = true; btn.innerHTML = '处理中...';
  gbAjax({method:'POST', url:'<?php echo site_url("admins/ban"); ?>', data:data,
    success:function(res){
      if(res.code===0){
        gbToast.success(res.msg);
        setTimeout(function(){ location.reload(); }, 500);
      } else {
        gbToast.error(res.msg || '禁言失败');
      }
    },
    complete:function(){ btn.disabled=false; btn.innerHTML='确认禁言'; }
  });
  return false;
}
function caUnbanUser(userId){
  if(!confirm('确认解除该用户的禁言?')) return;
  gbAjax({method:'POST', url:'<?php echo site_url("admins/unban"); ?>', data:{user_id:userId},
    success:function(res){
      if(res.code===0){ gbToast.success(res.msg); setTimeout(function(){ location.reload(); }, 500); }
      else { gbToast.error(res.msg || '解除失败'); }
    }
  });
}
</script>

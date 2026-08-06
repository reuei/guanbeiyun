<?php /** 聊天室公告管理 */
$rows = $rows ?? [];
$myIsSuper = $myIsSuper ?? false;
$rooms = chat_rooms_list();
?>
<div class="panel">
  <div class="panel-head">
    <span class="title"><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg> 聊天室公告管理</span>
    <button class="btn btn-primary btn-sm" onclick="caEditAnnouncement()"><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> 发布公告</button>
  </div>
  <div class="panel-body">
    <div class="alert alert-info" style="background:#eff6ff;border:1px solid #dbeafe;color:#1e40af;padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      <b>公告规则:</b> 全局公告展示在聊天室最上方, 字数超过 10 字默认滚动展示, 10 分钟内自动删除(可自定义时长)。
      <?php if ($myIsSuper): ?>超管可发布弹窗公告, 会给所有在线用户发送通知。<?php endif; ?>
    </div>
    <div style="overflow-x:auto;">
      <table class="table" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:var(--bg-soft);text-align:left;">
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">ID</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">类型</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">内容</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">版块</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">发布者</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">过期时间</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">状态</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);text-align:right;">操作</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($rows): foreach ($rows as $r):
            $expired = strtotime($r['expire_at']) <= time();
            $scopeText = $r['scope'] === 'popup' ? '弹窗' : '全局';
            $scopeColor = $r['scope'] === 'popup' ? '#7c3aed' : '#1e40af';
            $scopeBg = $r['scope'] === 'popup' ? '#ede9fe' : '#dbeafe';
          ?>
          <tr style="border-bottom:1px solid var(--divider);<?php echo $expired ? 'opacity:.5;' : ''; ?>">
            <td style="padding:10px 12px;font-size:13px;color:var(--text-muted);"><?php echo (int)$r['id']; ?></td>
            <td style="padding:10px 12px;"><span style="padding:3px 8px;border-radius:4px;font-size:11px;font-weight:600;background:<?php echo $scopeBg; ?>;color:<?php echo $scopeColor; ?>;"><?php echo $scopeText; ?></span></td>
            <td style="padding:10px 12px;font-size:13px;color:var(--text);max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?php echo e($r['content']); ?>"><?php echo e(mb_substr($r['content'], 0, 50)); ?><?php if (mb_strlen($r['content']) > 50) echo '...'; ?></td>
            <td style="padding:10px 12px;font-size:12px;color:var(--text-muted);"><?php echo $r['room_id'] ? '版块#'.$r['room_id'] : '全部'; ?></td>
            <td style="padding:10px 12px;font-size:12px;color:var(--text-2);"><?php echo e($r['username'] ?? '-'); ?></td>
            <td style="padding:10px 12px;font-size:12px;color:<?php echo $expired ? '#991b1b' : 'var(--text-muted)'; ?>;"><?php echo e($r['expire_at']); ?></td>
            <td style="padding:10px 12px;"><?php if ($expired): ?><span style="padding:2px 8px;border-radius:4px;font-size:11px;background:#e5e7eb;color:#374151;">已过期</span><?php else: ?><span style="padding:2px 8px;border-radius:4px;font-size:11px;background:#d1fae5;color:#065f46;">生效中</span><?php endif; ?></td>
            <td style="padding:10px 12px;text-align:right;">
              <button class="btn btn-danger btn-sm" onclick="caDeleteAnnouncement(<?php echo (int)$r['id']; ?>)">撤销</button>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="8" style="padding:40px;text-align:center;color:var(--text-muted);">暂无公告</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- 发布公告弹窗 -->
<div class="modal-overlay" id="caAnnModal" style="display:none;">
  <div class="modal-box" style="max-width:520px;">
    <div class="modal-head"><h3>发布公告</h3><span class="icon-btn" onclick="gbModal.close('caAnnModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body">
      <form id="caAnnForm" onsubmit="return caSaveAnnouncement(event)">
        <div class="form-group">
          <label class="form-label">公告内容 *</label>
          <textarea class="form-control" name="content" id="caAnnContent" rows="3" maxlength="500" required placeholder="请输入公告内容, 字数超过 10 字默认滚动展示"></textarea>
          <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">已输入 <span id="caAnnCount">0</span>/500</div>
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">公告类型</label>
            <select class="form-control" name="scope" id="caAnnScope">
              <option value="global">全局公告 (顶部展示)</option>
              <?php if ($myIsSuper): ?><option value="popup">弹窗公告 (通知在线用户)</option><?php endif; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">生效时长</label>
            <select class="form-control" name="duration_min" id="caAnnDuration">
              <option value="10">10 分钟</option>
              <option value="30">30 分钟</option>
              <option value="60">1 小时</option>
              <option value="180">3 小时</option>
              <option value="1440">24 小时</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">指定版块 (留空为全部)</label>
          <select class="form-control" name="room_id" id="caAnnRoom">
            <option value="0">全部版块</option>
            <?php foreach ($rooms as $rm): ?><option value="<?php echo (int)$rm['id']; ?>"><?php echo e($rm['name']); ?></option><?php endforeach; ?>
          </select>
        </div>
        <?php if (!$myIsSuper): ?>
        <div class="alert alert-warning" style="background:#fef3c7;border:1px solid #fcd34d;color:#92400e;padding:8px 12px;border-radius:6px;font-size:12px;margin-top:10px;">
          仅超管可发布弹窗公告, 普通管理员只能发布全局公告。
        </div>
        <?php endif; ?>
        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:14px;">
          <button type="button" class="btn btn-ghost" onclick="gbModal.close('caAnnModal')">取消</button>
          <button type="submit" class="btn btn-primary" id="caAnnSaveBtn">发布</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function caEditAnnouncement(){
  document.getElementById('caAnnContent').value = '';
  document.getElementById('caAnnCount').textContent = '0';
  document.getElementById('caAnnScope').value = 'global';
  document.getElementById('caAnnDuration').value = '10';
  document.getElementById('caAnnRoom').value = '0';
  gbModal.open('caAnnModal');
}
document.getElementById('caAnnContent').addEventListener('input', function(){
  document.getElementById('caAnnCount').textContent = this.value.length;
});
function caSaveAnnouncement(e){
  e.preventDefault();
  var fd = new FormData(e.target);
  var data = {}; fd.forEach(function(v,k){ data[k]=v; });
  if(!data.content || !data.content.trim()){ gbToast.warning('请输入公告内容'); return false; }
  var btn = document.getElementById('caAnnSaveBtn');
  btn.disabled = true; btn.innerHTML = '发布中...';
  gbAjax({method:'POST', url:'<?php echo site_url("admins/announcement/save"); ?>', data:data,
    success:function(res){
      if(res.code===0){
        gbToast.success(res.msg);
        setTimeout(function(){ location.reload(); }, 500);
      } else {
        gbToast.error(res.msg || '发布失败');
      }
    },
    complete:function(){ btn.disabled=false; btn.innerHTML='发布'; }
  });
  return false;
}
function caDeleteAnnouncement(id){
  if(!confirm('确认撤销该公告?')) return;
  gbAjax({method:'POST', url:'<?php echo site_url("admins/announcement/delete"); ?>', data:{id:id},
    success:function(res){
      if(res.code===0){ gbToast.success(res.msg); setTimeout(function(){ location.reload(); }, 500); }
      else { gbToast.error(res.msg || '撤销失败'); }
    }
  });
}
</script>

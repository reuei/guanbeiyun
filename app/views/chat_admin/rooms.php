<?php /** 聊天版块管理 */
$rooms = $rooms ?? [];
$myIsSuper = $myIsSuper ?? false;
?>
<div class="panel">
  <div class="panel-head">
    <span class="title"><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg> 聊天版块管理</span>
    <button class="btn btn-primary btn-sm" onclick="caEditRoom(0)"><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> 新建版块</button>
  </div>
  <div class="panel-body">
    <div style="overflow-x:auto;">
      <table class="table" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:var(--bg-soft);text-align:left;">
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">ID</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">图标</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">版块名称</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">描述</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">排序</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">在线</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">状态</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">创建时间</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);text-align:right;">操作</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($rooms): foreach ($rooms as $r): ?>
          <tr style="border-bottom:1px solid var(--divider);">
            <td style="padding:10px 12px;font-size:13px;color:var(--text-muted);"><?php echo (int)$r['id']; ?></td>
            <td style="padding:10px 12px;font-size:18px;"><?php echo e($r['icon'] ?? '💬'); ?></td>
            <td style="padding:10px 12px;font-size:13px;font-weight:600;color:var(--text);"><?php echo e($r['name']); ?></td>
            <td style="padding:10px 12px;font-size:12px;color:var(--text-muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo e($r['description'] ?? ''); ?></td>
            <td style="padding:10px 12px;font-size:13px;color:var(--text-2);"><?php echo (int)$r['sort']; ?></td>
            <td style="padding:10px 12px;"><span style="display:inline-flex;align-items:center;gap:4px;background:#dcfce7;color:#166534;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;"><span style="width:6px;height:6px;border-radius:50%;background:#22c55e;"></span><?php echo (int)($r['online_count'] ?? 0); ?></span></td>
            <td style="padding:10px 12px;"><?php if ((int)$r['status'] === 1): ?><span class="tag tag-success" style="padding:2px 8px;border-radius:4px;font-size:11px;background:#d1fae5;color:#065f46;">启用</span><?php else: ?><span class="tag tag-muted" style="padding:2px 8px;border-radius:4px;font-size:11px;background:#e5e7eb;color:#374151;">禁用</span><?php endif; ?></td>
            <td style="padding:10px 12px;font-size:12px;color:var(--text-muted);"><?php echo e($r['created_at'] ?? ''); ?></td>
            <td style="padding:10px 12px;text-align:right;white-space:nowrap;">
              <button class="btn btn-ghost btn-sm" onclick="caEditRoom(<?php echo (int)$r['id']; ?>, <?php echo htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8'); ?>)">编辑</button>
              <a class="btn btn-ghost btn-sm" href="<?php echo site_url('chat?room=' . (int)$r['id']); ?>" target="_blank">进入</a>
              <button class="btn btn-danger btn-sm" onclick="caDeleteRoom(<?php echo (int)$r['id']; ?>, '<?php echo e($r['name']); ?>')">删除</button>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="9" style="padding:40px;text-align:center;color:var(--text-muted);">暂无版块, 点击右上角新建</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- 编辑弹窗 -->
<div class="modal-overlay" id="caRoomModal" style="display:none;">
  <div class="modal-box" style="max-width:480px;">
    <div class="modal-head"><h3 id="caRoomModalTitle">新建版块</h3><span class="icon-btn" onclick="gbModal.close('caRoomModal')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span></div>
    <div class="modal-body">
      <form id="caRoomForm" onsubmit="return caSaveRoom(event)">
        <input type="hidden" name="id" id="caRoomId" value="0">
        <div class="form-group"><label class="form-label">版块图标 (emoji)</label><input class="form-control" name="icon" id="caRoomIcon" value="💬" maxlength="20" placeholder="如 💬"></div>
        <div class="form-group"><label class="form-label">版块名称 *</label><input class="form-control" name="name" id="caRoomName" required maxlength="100" placeholder="如: 综合聊天"></div>
        <div class="form-group"><label class="form-label">版块描述</label><textarea class="form-control" name="description" id="caRoomDesc" rows="2" maxlength="500" placeholder="可选, 500字符内"></textarea></div>
        <div class="grid-2">
          <div class="form-group"><label class="form-label">排序 (数字越大越靠前)</label><input class="form-control" type="number" name="sort" id="caRoomSort" value="0" min="0" max="9999"></div>
          <div class="form-group"><label class="form-label">状态</label><select class="form-control" name="status" id="caRoomStatus"><option value="1">启用</option><option value="0">禁用</option></select></div>
        </div>
        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:14px;">
          <button type="button" class="btn btn-ghost" onclick="gbModal.close('caRoomModal')">取消</button>
          <button type="submit" class="btn btn-primary" id="caRoomSaveBtn">保存</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function caEditRoom(id, data){
  document.getElementById('caRoomId').value = id || 0;
  document.getElementById('caRoomModalTitle').textContent = id ? '编辑版块' : '新建版块';
  if (id && data){
    document.getElementById('caRoomIcon').value = data.icon || '💬';
    document.getElementById('caRoomName').value = data.name || '';
    document.getElementById('caRoomDesc').value = data.description || '';
    document.getElementById('caRoomSort').value = data.sort || 0;
    document.getElementById('caRoomStatus').value = data.status != null ? data.status : 1;
  } else {
    document.getElementById('caRoomIcon').value = '💬';
    document.getElementById('caRoomName').value = '';
    document.getElementById('caRoomDesc').value = '';
    document.getElementById('caRoomSort').value = 0;
    document.getElementById('caRoomStatus').value = 1;
  }
  gbModal.open('caRoomModal');
}
function caSaveRoom(e){
  e.preventDefault();
  var fd = new FormData(e.target);
  var data = {}; fd.forEach(function(v,k){ data[k]=v; });
  var btn = document.getElementById('caRoomSaveBtn');
  btn.disabled = true; btn.innerHTML = '保存中...';
  gbAjax({method:'POST', url:'<?php echo site_url("admins/room/save"); ?>', data:data,
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
function caDeleteRoom(id, name){
  if(!confirm('确认删除版块 "' + name + '"?\n该版块下的所有消息将被标记撤回, 不可恢复!')) return;
  gbAjax({method:'POST', url:'<?php echo site_url("admins/room/delete"); ?>', data:{id:id},
    success:function(res){
      if(res.code===0){ gbToast.success(res.msg); setTimeout(function(){ location.reload(); }, 500); }
      else { gbToast.error(res.msg || '删除失败'); }
    }
  });
}
</script>

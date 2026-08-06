<?php /** 聊天消息管理 - 撤回/删除消息 */
$rows = $rows ?? [];
$rooms = $rooms ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$size = $size ?? 15;
$kw = $kw ?? '';
$roomId = $roomId ?? 0;
$myIsSuper = $myIsSuper ?? false;
$pages = max(1, (int)ceil($total / $size));
?>
<div class="panel">
  <div class="panel-head">
    <span class="title"><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> 聊天消息管理</span>
  </div>
  <div class="panel-body">
    <form method="get" action="" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;align-items:center;">
      <select class="form-control" name="room_id" style="width:160px;" onchange="this.form.submit()">
        <option value="0">全部版块</option>
        <?php if ($rooms): foreach ($rooms as $rm): ?>
        <option value="<?php echo (int)$rm['id']; ?>" <?php echo $roomId == $rm['id'] ? 'selected' : ''; ?>><?php echo e($rm['name']); ?></option>
        <?php endforeach; endif; ?>
      </select>
      <input class="form-control" type="text" name="kw" value="<?php echo e($kw); ?>" placeholder="搜索消息内容" style="width:220px;">
      <button class="btn btn-primary btn-sm" type="submit">搜索</button>
      <?php if ($kw !== '' || $roomId > 0): ?>
      <a class="btn btn-ghost btn-sm" href="<?php echo site_url('admins/messages'); ?>">重置</a>
      <?php endif; ?>
    </form>

    <div style="overflow-x:auto;">
      <table class="table" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:var(--bg-soft);text-align:left;">
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">ID</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">用户</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">内容</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">类型</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">状态</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);">时间</th>
            <th style="padding:10px 12px;font-size:13px;color:var(--text-2);border-bottom:1px solid var(--divider);text-align:right;">操作</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($rows): foreach ($rows as $r):
            $typeMap = ['text' => '文本', 'image' => '图片', 'emoji' => '表情', 'url' => '链接'];
            $typeText = $typeMap[$r['msg_type']] ?? $r['msg_type'];
            $recalled = (int)$r['is_recalled'] === 1;
            $content = $r['msg_type'] === 'image' ? '[图片]' : mb_substr($r['content'] ?? '', 0, 60);
          ?>
          <tr style="border-bottom:1px solid var(--divider);<?php echo $recalled ? 'opacity:.6;' : ''; ?>">
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
            <td style="padding:10px 12px;font-size:12px;color:var(--text-2);max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo e($content); ?></td>
            <td style="padding:10px 12px;"><span style="padding:2px 8px;border-radius:4px;font-size:11px;background:var(--bg-soft);color:var(--text-2);"><?php echo e($typeText); ?></span></td>
            <td style="padding:10px 12px;"><?php if ($recalled): ?><span style="padding:2px 8px;border-radius:4px;font-size:11px;background:#e5e7eb;color:#374151;">已撤回</span><?php else: ?><span style="padding:2px 8px;border-radius:4px;font-size:11px;background:#d1fae5;color:#065f46;">正常</span><?php endif; ?></td>
            <td style="padding:10px 12px;font-size:12px;color:var(--text-muted);"><?php echo e($r['created_at']); ?></td>
            <td style="padding:10px 12px;text-align:right;white-space:nowrap;">
              <?php if (!$recalled): ?>
              <button class="btn btn-warning btn-sm" onclick="caRecallMsg(<?php echo (int)$r['id']; ?>)">撤回</button>
              <?php endif; ?>
              <?php if ($myIsSuper): ?>
              <button class="btn btn-danger btn-sm" onclick="caDeleteMsg(<?php echo (int)$r['id']; ?>)">删除</button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="7" style="padding:40px;text-align:center;color:var(--text-muted);">暂无消息记录</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($pages > 1): ?>
    <div style="display:flex;justify-content:center;align-items:center;gap:6px;margin-top:20px;">
      <?php
      $baseQs = http_build_query(['kw' => $kw, 'room_id' => $roomId, 'size' => $size]);
      $prevPage = max(1, $page - 1);
      $nextPage = min($pages, $page + 1);
      ?>
      <a class="btn btn-ghost btn-sm" href="?<?php echo $baseQs; ?>&page=<?php echo $prevPage; ?>" <?php echo $page <= 1 ? 'disabled style="opacity:.5;pointer-events:none;"' : ''; ?>>上一页</a>
      <span style="font-size:13px;color:var(--text-2);"><?php echo (int)$page; ?> / <?php echo (int)$pages; ?> (共 <?php echo (int)$total; ?> 条)</span>
      <a class="btn btn-ghost btn-sm" href="?<?php echo $baseQs; ?>&page=<?php echo $nextPage; ?>" <?php echo $page >= $pages ? 'disabled style="opacity:.5;pointer-events:none;"' : ''; ?>>下一页</a>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
function caRecallMsg(id){
  if(!confirm('确认撤回该消息? 撤回后将通知对方。')) return;
  gbAjax({method:'POST', url:'<?php echo site_url("admins/message/recall"); ?>', data:{message_id:id},
    success:function(res){
      if(res.code===0){ gbToast.success(res.msg); setTimeout(function(){ location.reload(); }, 500); }
      else { gbToast.error(res.msg || '撤回失败'); }
    }
  });
}
function caDeleteMsg(id){
  if(!confirm('确认彻底删除该消息? 此操作不可恢复!')) return;
  gbAjax({method:'POST', url:'<?php echo site_url("admins/message/delete"); ?>', data:{message_id:id},
    success:function(res){
      if(res.code===0){ gbToast.success(res.msg); setTimeout(function(){ location.reload(); }, 500); }
      else { gbToast.error(res.msg || '删除失败'); }
    }
  });
}
</script>

<?php /** 聊天室页面 - v6 重构, 使用 chat 独立布局, 含表情/快捷语句/在线人数/头衔/管理员功能 */
$roomId = (int)($roomId ?? 0);
$roomName = $roomName ?? '';
$rooms = $rooms ?? [];
$myTitle = $myTitle ?? ['text' => '', 'level' => 1, 'bg' => '', 'role' => 'user'];
$globalMute = (bool)($globalMute ?? false);
$myRole = $myRole ?? 'user';
$myIsAdmin = in_array($myRole, ['admin', 'super_admin', 'platform_admin'], true);
$myIsSuper = in_array($myRole, ['super_admin', 'platform_admin'], true);
?>
<section class="chat-section" style="height:100%;display:flex;flex-direction:column;">
  <!-- 公告条 -->
  <div id="chatAnnouncementBar" style="display:none;background:linear-gradient(135deg,#fef3c7,#fde68a);color:#92400e;padding:8px 16px;font-size:13px;border-bottom:1px solid #fcd34d;align-items:center;gap:8px;">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>
    <div id="chatAnnouncementContent" style="flex:1;overflow:hidden;white-space:nowrap;"></div>
  </div>

  <!-- 全局禁言提示 -->
  <?php if ($globalMute && !$myIsSuper): ?>
  <div style="background:#fee2e2;color:#991b1b;padding:6px 16px;font-size:12px;border-bottom:1px solid #fca5a5;text-align:center;">
    ⚠ 聊天室已开启全体禁言, 普通用户暂时无法发送消息
  </div>
  <?php endif; ?>

  <!-- 消息列表 -->
  <div class="chat-body" id="chatBody" style="flex:1;overflow-y:auto;padding:14px 16px;display:flex;flex-direction:column;gap:10px;background:var(--bg-soft);">
    <div class="chat-loading" id="chatLoading" style="color:var(--text-muted);text-align:center;padding:30px 0;">加载中...</div>
  </div>

  <!-- 输入区 -->
  <div class="chat-input" id="chatInput" style="border-top:1px solid var(--divider);padding:8px 12px;background:var(--card-bg);flex-shrink:0;">
    <!-- 工具栏 -->
    <div style="position:relative;display:flex;gap:6px;margin-bottom:6px;align-items:center;flex-wrap:wrap;">
      <button type="button" class="chat-tool-btn" id="emojiBtn" title="表情" style="width:30px;height:30px;border-radius:6px;border:1px solid var(--border);background:var(--card-bg);cursor:pointer;color:var(--text-2);display:flex;align-items:center;justify-content:center;font-size:16px;">😀</button>
      <button type="button" class="chat-tool-btn" id="imgBtn" title="发送图片" style="width:30px;height:30px;border-radius:6px;border:1px solid var(--border);background:var(--card-bg);cursor:pointer;color:var(--text-2);display:flex;align-items:center;justify-content:center;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
      </button>
      <button type="button" class="chat-tool-btn" id="phraseBtn" title="快捷语句" style="width:30px;height:30px;border-radius:6px;border:1px solid var(--border);background:var(--card-bg);cursor:pointer;color:var(--text-2);display:flex;align-items:center;justify-content:center;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      </button>
      <input type="file" id="imgFile" accept="image/*" style="display:none">
      <?php if ($myIsSuper): ?>
      <label title="@全体成员" style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:var(--text-2);cursor:pointer;margin-left:auto;">
        <input type="checkbox" id="atAllToggle"> @全体
      </label>
      <?php endif; ?>
      <?php if ($roomId > 0): ?>
      <a href="<?php echo site_url('chat/rooms'); ?>" style="font-size:12px;color:var(--primary);text-decoration:none;margin-left:<?php echo $myIsSuper ? '8px' : 'auto'; ?>;">切换版块</a>
      <?php endif; ?>
      <!-- 表情面板 -->
      <div class="chat-emoji-panel" id="emojiPanel" style="display:none;position:absolute;bottom:38px;left:0;z-index:20;background:var(--card-bg);border:1px solid var(--border);border-radius:8px;padding:8px;display:flex;flex-wrap:wrap;gap:4px;width:280px;max-height:240px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,.1);"></div>
      <!-- 快捷语句面板 -->
      <div class="chat-phrase-panel" id="phrasePanel" style="display:none;position:absolute;bottom:38px;left:0;z-index:20;background:var(--card-bg);border:1px solid var(--border);border-radius:8px;padding:8px;width:280px;max-height:280px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,.1);">
        <div style="font-size:12px;color:var(--text-muted);margin-bottom:6px;display:flex;align-items:center;justify-content:space-between;">
          <span>快捷语句</span>
          <button type="button" id="addPhraseBtn" style="background:none;border:none;color:var(--primary);cursor:pointer;font-size:12px;padding:0;">+ 添加</button>
        </div>
        <div id="phraseList"></div>
      </div>
    </div>
    <!-- 输入框 -->
    <div style="display:flex;gap:8px;align-items:flex-end;">
      <textarea id="chatTextarea" placeholder="输入消息, Enter 发送, Shift+Enter 换行" rows="1" style="flex:1;resize:none;border:1px solid var(--border);border-radius:8px;padding:8px 10px;font-size:14px;line-height:1.5;max-height:120px;background:var(--card-bg);color:var(--text);font-family:inherit;"></textarea>
      <button type="button" class="btn btn-primary" id="sendBtn" style="flex-shrink:0;">发送</button>
    </div>
  </div>
</section>

<!-- 撤回消息确认对话框 -->
<div id="recallModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;">
  <div style="background:var(--card-bg);border-radius:12px;padding:20px;max-width:360px;width:90%;box-shadow:0 12px 40px rgba(0,0,0,.2);">
    <h3 style="margin:0 0 12px;font-size:16px;color:var(--text);">撤回消息</h3>
    <p style="margin:0 0 16px;font-size:13px;color:var(--text-2);">确定要撤回该消息吗? 撤回后将通知对方。</p>
    <div style="display:flex;gap:8px;justify-content:flex-end;">
      <button type="button" class="btn btn-ghost" id="recallCancel">取消</button>
      <button type="button" class="btn btn-danger" id="recallConfirm">确认撤回</button>
    </div>
  </div>
</div>

<!-- 在线用户弹窗 -->
<div id="onlineModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;">
  <div style="background:var(--card-bg);border-radius:12px;padding:0;max-width:480px;width:90%;max-height:80vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 12px 40px rgba(0,0,0,.2);">
    <div style="padding:14px 18px;border-bottom:1px solid var(--divider);display:flex;align-items:center;justify-content:space-between;">
      <h3 style="margin:0;font-size:16px;color:var(--text);">在线用户 (<span id="onlineModalCount">0</span>)</h3>
      <button type="button" onclick="document.getElementById('onlineModal').style.display='none'" style="background:none;border:none;cursor:pointer;color:var(--text-2);font-size:18px;">×</button>
    </div>
    <div id="onlineModalList" style="flex:1;overflow-y:auto;padding:8px;"></div>
  </div>
</div>

<style>
.chat-msg { display: flex; gap: 10px; align-items: flex-start; }
.chat-msg.is-me { flex-direction: row-reverse; }
.chat-avatar {
  width: 36px; height: 36px; border-radius: 50%; overflow: hidden; flex-shrink: 0;
  cursor: pointer; background: var(--primary); color: #fff;
  display: flex; align-items: center; justify-content: center; user-select: none;
}
.chat-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.chat-avatar-letter { font-size: 15px; font-weight: 600; }
.chat-main { flex: 1; min-width: 0; max-width: 78%; }
.chat-msg.is-me .chat-main { display:flex; flex-direction:column; align-items:flex-end; }
.chat-meta { display: flex; align-items: center; gap: 5px; flex-wrap: wrap; margin-bottom: 3px; }
.chat-msg.is-me .chat-meta { flex-direction: row-reverse; }
.chat-name { font-weight: 600; color: var(--text); font-size: 13px; }
.chat-time { color: var(--text-3); font-size: 11px; }
.chat-msg.is-me .chat-time { margin-left: 0; margin-right: 0; }
.title-badge {
  display: inline-block; padding: 0 6px; font-size: 10px; line-height: 16px; height: 16px;
  border-radius: 3px; color: #fff; font-weight: 600;
}
.role-badge {
  display: inline-block; padding: 0 6px; font-size: 10px; line-height: 16px; height: 16px;
  border-radius: 3px; font-weight: 600; border: 1px solid rgba(0,0,0,.05);
}
.chat-certs { display: inline-flex; gap: 3px; align-items: center; }
.cert-img { width: 14px; height: 14px; border-radius: 3px; cursor: pointer; vertical-align: middle; }
.cert-badge {
  display: inline-block; padding: 0 4px; font-size: 10px; line-height: 14px; height: 14px;
  border-radius: 3px; background: var(--primary-bg); color: var(--primary);
  cursor: pointer; border: 1px solid var(--primary-bg-2);
}
.chat-bubble {
  display: inline-block; padding: 8px 12px; border-radius: 12px;
  background: var(--card-bg); border: 1px solid var(--border);
  color: var(--text); font-size: 14px; line-height: 1.5; word-break: break-word;
  max-width: 100%; position: relative;
}
.chat-msg.is-me .chat-bubble { background: var(--primary); color: #fff; border-color: var(--primary); }
.chat-bubble a { color: var(--primary); }
.chat-msg.is-me .chat-bubble a { color: #fff; text-decoration: underline; }
.chat-img { max-width: 200px; max-height: 240px; border-radius: 8px; cursor: pointer; display: block; }
.chat-emoji { font-size: 24px; line-height: 1.4; }
.chat-link { color: var(--primary); }
.chat-reply {
  font-size: 11px; color: var(--text-2); background: var(--bg-soft);
  border-left: 3px solid var(--border); padding: 4px 8px; border-radius: 4px;
  margin-bottom: 4px; max-width: 100%; word-break: break-word;
}
.chat-recalled { color: var(--text-muted); font-style: italic; font-size: 12px; }
.chat-msg.is-recalled { opacity: .7; }
.chat-at-all {
  display: inline-block; padding: 1px 6px; font-size: 10px;
  background: #fee2e2; color: #991b1b; border-radius: 3px; margin-right: 4px;
}
.emoji-item, .phrase-item { font-size: 20px; cursor: pointer; padding: 4px 5px; border-radius: 4px; transition: background .15s; }
.emoji-item:hover { background: var(--bg-hover); }
.phrase-item { font-size: 13px; color: var(--text); display: flex; align-items: center; justify-content: space-between; gap: 6px; }
.phrase-item:hover { background: var(--bg-hover); }
.phrase-del { color: var(--danger); font-size: 14px; opacity: 0; transition: opacity .15s; }
.phrase-item:hover .phrase-del { opacity: 1; }
.chat-loading { color: var(--text-muted); text-align: center; padding: 30px 0; }
@keyframes announceScroll { 0%{transform:translateX(0);} 100%{transform:translateX(-100%);} }
.announce-scroll { display: inline-block; animation: announceScroll 18s linear infinite; padding-left: 100%; }
@media (max-width: 768px) {
  .chat-main { max-width: 85%; }
  .chat-img { max-width: 160px; }
}
</style>

<script>
(function () {
  var SITE_URL = '<?php echo site_url(); ?>';
  var CSRF     = '<?php echo csrf_token(); ?>';
  var ROOM_ID  = <?php echo (int)$roomId; ?>;
  var MY_UID   = <?php echo (int)(current_user()['id'] ?? 0); ?>;
  var IS_ADMIN = <?php echo $myIsAdmin ? 'true' : 'false'; ?>;
  var IS_SUPER = <?php echo $myIsSuper ? 'true' : 'false'; ?>;

  // v6: 更丰富的表情 (10 类共 80+ 个)
  var EMOJIS = [
    '😀','😃','😄','😁','😆','😅','🤣','😂','🙂','🙃','😉','😊','😇','🥰','😍','🤩','😘','😗','😚','😙',
    '😋','😛','😜','🤪','😝','🤑','🤗','🤭','🤫','🤔','🤐','🤨','😐','😑','😶','😏','😒','🙄','😬','🤥',
    '😌','😔','😪','🤤','😴','😷','🤒','🤕','🤢','🤮','🥵','🥶','😎','🤓','🧐','😕','😟','🙁','😮','😯',
    '😲','😳','🥺','😦','😧','😨','😰','😥','😢','😭','😱','😖','😣','😞','😓','😩','😫','🥱','😤','😡',
    '😠','🤬','😈','👿','💀','💩','🤡','👻','👽','🤖','🎃','😺','👍','👎','👌','✌️','🤞','🤟','🤘','👏',
    '🙌','🙏','🤝','💪','🔥','✨','🎉','🎊','🎁','💝','❤️','🧡','💛','💚','💙','💜','🤍','🤎','💔','✅','❌','⚠️'
  ];

  var chatBody   = document.getElementById('chatBody');
  var ta         = document.getElementById('chatTextarea');
  var sendBtn    = document.getElementById('sendBtn');
  var emojiBtn   = document.getElementById('emojiBtn');
  var emojiPanel = document.getElementById('emojiPanel');
  var imgBtn     = document.getElementById('imgBtn');
  var imgFile    = document.getElementById('imgFile');
  var phraseBtn  = document.getElementById('phraseBtn');
  var phrasePanel= document.getElementById('phrasePanel');
  var addPhraseBtn = document.getElementById('addPhraseBtn');
  var phraseList = document.getElementById('phraseList');
  var atAllToggle = document.getElementById('atAllToggle');
  var recallModal = document.getElementById('recallModal');
  var recallConfirm = document.getElementById('recallConfirm');
  var recallCancel = document.getElementById('recallCancel');
  var onlineModal = document.getElementById('onlineModal');
  var onlineModalList = document.getElementById('onlineModalList');
  var onlineModalCount = document.getElementById('onlineModalCount');
  var onlineBadgeCount = document.getElementById('chatOnlineCount');

  var lastMsgId = 0, firstMsgId = 0;
  var messagesById = {};
  var initialLoaded = false;
  var loadingHistory = false;
  var pendingRecallId = 0;

  /* ---------- 工具函数 ---------- */
  function esc(s) {
    s = (s == null ? '' : String(s));
    return s.replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function imgUrl(p) {
    if (!p) return '';
    return /^https?:\/\//.test(p) ? p : (SITE_URL + '/' + p.replace(/^\//, ''));
  }
  function truncate(s, n) {
    s = String(s || '');
    return s.length > n ? s.slice(0, n) + '…' : s;
  }
  function linkify(escaped) {
    return escaped.replace(/(https?:\/\/[^\s<]+)/g, function (u) {
      return '<a href="' + u + '" target="_blank" rel="noopener">' + u + '</a>';
    });
  }
  function insertAtCursor(field, text) {
    var v = field.value, s = field.selectionStart || 0, e = field.selectionEnd || 0;
    field.value = v.slice(0, s) + text + v.slice(e);
    field.selectionStart = field.selectionEnd = s + text.length;
  }
  function autoResize(el) { el.style.height = 'auto'; el.style.height = Math.min(el.scrollHeight, 120) + 'px'; }
  function scrollToBottom() { chatBody.scrollTop = chatBody.scrollHeight; }
  function nearBottom() { return chatBody.scrollHeight - chatBody.scrollTop - chatBody.clientHeight < 80; }

  /* ---------- 内容渲染 ---------- */
  function renderContent(m) {
    if (m.msg_type === 'image') {
      var src = m.content.indexOf('data:') === 0 ? m.content : imgUrl(m.content);
      return '<img class="chat-img" src="' + esc(src) + '" alt="图片">';
    }
    if (m.msg_type === 'emoji') {
      return '<span class="chat-emoji">' + esc(m.content) + '</span>';
    }
    if (m.msg_type === 'url') {
      return '<a class="chat-link" href="' + esc(m.content) + '" target="_blank" rel="noopener">' + esc(m.content) + '</a>';
    }
    return linkify(esc(m.content));
  }

  function buildMessage(m) {
    var el = document.createElement('div');
    el.className = 'chat-msg' + (m.is_recalled ? ' is-recalled' : '') + (m.user_id === MY_UID ? ' is-me' : '');
    el.setAttribute('data-id', m.id);
    el.setAttribute('data-uid', m.user_id);

    var avHtml;
    if (m.avatar) {
      avHtml = '<img src="' + esc(m.avatar) + '" alt="' + esc(m.username) + '">';
    } else {
      avHtml = '<span class="chat-avatar-letter">' + esc((m.username || '?').charAt(0)) + '</span>';
    }

    // 头衔标签
    var titleHtml = '';
    if (m.title) {
      if (m.title.text) {
        titleHtml += '<span class="title-badge" style="background:' + esc(m.title.bg) + ';">' + esc(m.title.text) + '</span>';
      }
      titleHtml += '<span class="title-badge" style="background:' + esc(m.title.bg) + ';">Lv' + m.title.level + '</span>';
    }
    if (m.role_label) {
      titleHtml += '<span class="role-badge" style="background:' + esc(m.role_label.bg) + ';color:' + esc(m.role_label.color) + ';">' + esc(m.role_label.text) + '</span>';
    }

    var certsHtml = '';
    if (m.certs && m.certs.length) {
      certsHtml = '<span class="chat-certs">';
      m.certs.forEach(function (c) {
        var info = c.info || c.name || '';
        if (c.image) {
          certsHtml += '<img class="cert-img" src="' + esc(imgUrl(c.image)) + '" title="' + esc(c.info || c.name || '') + '" data-info="' + esc(info) + '">';
        } else {
          certsHtml += '<span class="cert-badge ' + (c.icon_style ? esc(c.icon_style) : '') + '" title="' + esc(c.info || c.name || '') + '" data-info="' + esc(info) + '">' + esc(c.name || '') + '</span>';
        }
      });
      certsHtml += '</span>';
    }

    var replyHtml = '';
    if (m.reply_to) {
      var rm = messagesById[m.reply_to];
      var rc = rm ? truncate(rm.content, 40) : '已删除或不可见';
      replyHtml = '<div class="chat-reply">回复: ' + esc(rc) + '</div>';
    }

    var atAllHtml = m.is_at_all ? '<span class="chat-at-all">@全体</span>' : '';

    var contentHtml = m.is_recalled
      ? '<div class="chat-recalled">该消息已被撤回</div>'
      : '<div class="chat-bubble">' + atAllHtml + renderContent(m) + '</div>';

    el.innerHTML =
      '<div class="chat-avatar" data-uid="' + m.user_id + '" data-username="' + esc(m.username) + '" title="查看个人中心">' + avHtml + '</div>' +
      '<div class="chat-main">' +
        '<div class="chat-meta">' +
          '<span class="chat-name">' + esc(m.username) + '</span>' +
          titleHtml +
          certsHtml +
          '<span class="chat-time">' + esc(m.created_at_text || '') + '</span>' +
        '</div>' +
        replyHtml + contentHtml +
      '</div>';
    return el;
  }

  function addMessage(m, prepend) {
    messagesById[m.id] = m;
    if (m.id > lastMsgId) lastMsgId = m.id;
    if (firstMsgId === 0 || m.id < firstMsgId) firstMsgId = m.id;
    var el = buildMessage(m);
    if (prepend) chatBody.insertBefore(el, chatBody.firstChild);
    else chatBody.appendChild(el);
  }

  /* ---------- 拉取消息 ---------- */
  function loadMessages() {
    gbAjax({
      method: 'POST', url: SITE_URL + '/chat/messages', toast: false,
      data: { _csrf: CSRF, room_id: ROOM_ID },
      success: function (res) {
        var list = (res && res.data && res.data.messages) || [];
        var loading = document.getElementById('chatLoading');
        if (loading) loading.remove();
        if (!initialLoaded) {
          list.forEach(function (m) { addMessage(m, false); });
          initialLoaded = true;
          scrollToBottom();
        } else {
          var appended = false;
          list.forEach(function (m) {
            if (m.id > lastMsgId) { addMessage(m, false); appended = true; }
          });
          if (appended && nearBottom()) scrollToBottom();
        }
        // 在线人数
        if (res && res.data && typeof res.data.online_count !== 'undefined' && onlineBadgeCount) {
          onlineBadgeCount.textContent = res.data.online_count;
        }
      }
    });
  }

  function loadHistory() {
    if (loadingHistory || firstMsgId === 0) return;
    loadingHistory = true;
    var prevHeight = chatBody.scrollHeight;
    gbAjax({
      method: 'POST', url: SITE_URL + '/chat/history', toast: false,
      data: { _csrf: CSRF, before_id: firstMsgId, room_id: ROOM_ID },
      success: function (res) {
        var list = (res && res.data && res.data.messages) || [];
        list.forEach(function (m) {
          if (!messagesById[m.id]) addMessage(m, true);
        });
        chatBody.scrollTop += (chatBody.scrollHeight - prevHeight);
        if (list.length === 0) gbToast.info('没有更多消息了');
      },
      complete: function () { loadingHistory = false; }
    });
  }

  /* ---------- 发送 ---------- */
  function sendText() {
    var content = ta.value.trim();
    if (!content) return;
    var isAtAll = (atAllToggle && atAllToggle.checked) ? 1 : 0;
    sendBtn.disabled = true;
    gbAjax({
      method: 'POST', url: SITE_URL + '/chat/send',
      data: { _csrf: CSRF, content: content, msg_type: 'text', room_id: ROOM_ID, is_at_all: isAtAll },
      success: function (res) {
        if (res && res.code === 0) {
          ta.value = '';
          autoResize(ta);
          if (atAllToggle) atAllToggle.checked = false;
          if (res.data && res.data.message) {
            addMessage(res.data.message, false);
            scrollToBottom();
          }
        }
      },
      complete: function () { sendBtn.disabled = false; }
    });
  }

  function sendImage(dataUrl) {
    sendBtn.disabled = true;
    gbAjax({
      method: 'POST', url: SITE_URL + '/chat/send',
      data: { _csrf: CSRF, content: dataUrl, msg_type: 'image', room_id: ROOM_ID },
      success: function (res) {
        if (res && res.code === 0 && res.data && res.data.message) {
          addMessage(res.data.message, false);
          scrollToBottom();
        }
      },
      complete: function () { sendBtn.disabled = false; }
    });
  }

  /* ---------- 表情面板 ---------- */
  EMOJIS.forEach(function (em) {
    var b = document.createElement('span');
    b.className = 'emoji-item';
    b.textContent = em;
    b.addEventListener('click', function () { insertAtCursor(ta, em); ta.focus(); });
    emojiPanel.appendChild(b);
  });
  emojiBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    emojiPanel.style.display = emojiPanel.style.display === 'none' ? 'flex' : 'none';
    phrasePanel.style.display = 'none';
  });
  document.addEventListener('click', function (e) {
    if (!e.target.closest('#emojiPanel') && !e.target.closest('#emojiBtn')) emojiPanel.style.display = 'none';
  });

  /* ---------- 快捷语句 ---------- */
  function loadPhrases() {
    gbAjax({
      method: 'GET', url: SITE_URL + '/chat/quick_phrases', toast: false,
      data: { _csrf: CSRF },
      success: function (res) {
        var list = (res && res.data && res.data.phrases) || [];
        phraseList.innerHTML = '';
        if (list.length === 0) {
          phraseList.innerHTML = '<div style="padding:14px;text-align:center;color:var(--text-muted);font-size:12px;">暂无快捷语句, 点击 + 添加</div>';
          return;
        }
        list.forEach(function (p) {
          var item = document.createElement('div');
          item.className = 'phrase-item';
          item.innerHTML = '<span style="flex:1;word-break:break-all;">' + esc(p.content) + '</span>' +
            '<span class="phrase-del" data-id="' + p.id + '" title="删除">×</span>';
          item.querySelector('span:first-child').addEventListener('click', function () {
            insertAtCursor(ta, p.content);
            ta.focus();
            phrasePanel.style.display = 'none';
          });
          item.querySelector('.phrase-del').addEventListener('click', function (e) {
            e.stopPropagation();
            if (!confirm('删除该快捷语句?')) return;
            gbAjax({
              method: 'POST', url: SITE_URL + '/chat/quick_phrase/delete',
              data: { _csrf: CSRF, id: p.id },
              success: function () { loadPhrases(); }
            });
          });
          phraseList.appendChild(item);
        });
      }
    });
  }
  phraseBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    phrasePanel.style.display = phrasePanel.style.display === 'none' ? 'block' : 'none';
    emojiPanel.style.display = 'none';
    if (phrasePanel.style.display === 'block') loadPhrases();
  });
  document.addEventListener('click', function (e) {
    if (!e.target.closest('#phrasePanel') && !e.target.closest('#phraseBtn')) phrasePanel.style.display = 'none';
  });
  addPhraseBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    var content = prompt('请输入快捷语句内容 (最多200字):');
    if (!content || !content.trim()) return;
    gbAjax({
      method: 'POST', url: SITE_URL + '/chat/quick_phrase/save',
      data: { _csrf: CSRF, content: content.trim() },
      success: function () { loadPhrases(); gbToast.success('已添加'); }
    });
  });

  /* ---------- 图片上传 ---------- */
  imgBtn.addEventListener('click', function () { imgFile.click(); });
  imgFile.addEventListener('change', function () {
    var f = this.files[0];
    this.value = '';
    if (!f) return;
    if (f.size > 500 * 1024) { gbToast.warning('图片过大, 请控制在 500KB 以内'); return; }
    var reader = new FileReader();
    reader.onload = function () { sendImage(reader.result); };
    reader.readAsDataURL(f);
  });

  /* ---------- 输入交互 ---------- */
  ta.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendText(); }
  });
  ta.addEventListener('input', function () { autoResize(ta); });
  sendBtn.addEventListener('click', sendText);

  /* ---------- 滚动加载历史 ---------- */
  chatBody.addEventListener('scroll', function () {
    if (chatBody.scrollTop < 20) loadHistory();
  });

  /* ---------- 头像: 点击进个人中心 / 长按 @提及 ---------- */
  var pressTimer = null, didLong = false;
  function startPress(av) {
    didLong = false;
    clearTimeout(pressTimer);
    pressTimer = setTimeout(function () {
      didLong = true;
      var uname = av.getAttribute('data-username') || '';
      insertAtCursor(ta, '@' + uname + ' ');
      ta.focus();
      gbToast.info('@' + uname);
    }, 600);
  }
  function endPress() { clearTimeout(pressTimer); }
  chatBody.addEventListener('pointerdown', function (e) {
    var av = e.target.closest('.chat-avatar');
    if (av) startPress(av);
    var bubble = e.target.closest('.chat-bubble');
    if (bubble && IS_ADMIN) {
      // 管理员长按消息气泡撤回
      var msgEl = bubble.closest('.chat-msg');
      if (msgEl) {
        var mid = msgEl.getAttribute('data-id');
        var uid = parseInt(msgEl.getAttribute('data-uid'), 10);
        if (uid !== MY_UID) {
          // 长按他人消息显示撤回菜单
          pressTimer = setTimeout(function () {
            didLong = true;
            pendingRecallId = parseInt(mid, 10);
            recallModal.style.display = 'flex';
          }, 600);
        }
      }
    }
  });
  chatBody.addEventListener('pointerup', endPress);
  chatBody.addEventListener('pointercancel', endPress);
  chatBody.addEventListener('pointerleave', endPress);

  /* 委托: 头像点击 / 认证图标 / 图片预览 */
  chatBody.addEventListener('click', function (e) {
    var av = e.target.closest('.chat-avatar');
    if (av) {
      if (didLong) { didLong = false; e.preventDefault(); return; }
      var uid = av.getAttribute('data-uid');
      if (uid) window.open(SITE_URL + '/u/' + uid, '_blank');
      return;
    }
    var cert = e.target.closest('.cert-img, .cert-badge');
    if (cert) { gbToast.info(cert.getAttribute('data-info') || '认证信息'); return; }
    var img = e.target.closest('.chat-img');
    if (img) { window.open(img.src, '_blank'); return; }
  });

  /* ---------- 撤回消息 ---------- */
  recallCancel.addEventListener('click', function () {
    recallModal.style.display = 'none';
    pendingRecallId = 0;
  });
  recallConfirm.addEventListener('click', function () {
    if (!pendingRecallId) return;
    gbAjax({
      method: 'POST', url: SITE_URL + '/chat/recall',
      data: { _csrf: CSRF, message_id: pendingRecallId },
      success: function (res) {
        if (res && res.code === 0) {
          // 前端立即移除该消息
          var msgEl = chatBody.querySelector('.chat-msg[data-id="' + pendingRecallId + '"]');
          if (msgEl) {
            var main = msgEl.querySelector('.chat-main');
            if (main) {
              var recalled = main.querySelector('.chat-recalled');
              if (recalled) {
                recalled.style.display = 'block';
              } else {
                var bubble = main.querySelector('.chat-bubble');
                if (bubble) bubble.outerHTML = '<div class="chat-recalled">该消息已被撤回</div>';
              }
            }
            msgEl.classList.add('is-recalled');
          }
          gbToast.success('已撤回');
        }
      },
      complete: function () {
        recallModal.style.display = 'none';
        pendingRecallId = 0;
      }
    });
  });

  /* ---------- 公告 ---------- */
  function loadAnnouncements() {
    gbAjax({
      method: 'GET', url: SITE_URL + '/chat/announcements', toast: false,
      data: { room: ROOM_ID },
      success: function (res) {
        var bar = document.getElementById('chatAnnouncementBar');
        var content = document.getElementById('chatAnnouncementContent');
        if (!bar || !content) return;
        var globalList = (res && res.data && res.data.global) || [];
        if (globalList.length > 0) {
          var ann = globalList[0];
          var text = ann.content || '';
          bar.style.display = 'flex';
          // 字数超 10 字滚动
          if (text.length > 10) {
            content.innerHTML = '<span class="announce-scroll">' + esc(text) + '</span>';
          } else {
            content.textContent = text;
          }
        } else {
          bar.style.display = 'none';
        }
        // 弹窗公告
        var popupList = (res && res.data && res.data.popup) || [];
        popupList.forEach(function (p) {
          // 同一公告每个会话只弹一次
          var key = 'gb_popup_ann_' + p.id;
          if (!sessionStorage.getItem(key)) {
            sessionStorage.setItem(key, '1');
            gbToast.info('📢 ' + (p.content || ''), 6000);
          }
        });
      }
    });
  }

  /* ---------- 在线用户弹窗 ---------- */
  window.chatShowOnline = function () {
    onlineModal.style.display = 'flex';
    onlineModalList.innerHTML = '<div style="padding:20px;text-align:center;color:var(--text-muted);">加载中...</div>';
    gbAjax({
      method: 'GET', url: SITE_URL + '/chat/online/list', toast: false,
      data: { room: ROOM_ID },
      success: function (res) {
        var list = (res && res.data && res.data.users) || [];
        onlineModalCount.textContent = (res && res.data && res.data.count) || list.length;
        if (list.length === 0) {
          onlineModalList.innerHTML = '<div style="padding:30px;text-align:center;color:var(--text-muted);font-size:13px;">😴 当前无在线用户</div>';
          return;
        }
        var html = '';
        list.forEach(function (u) {
          var av = u.avatar ? '<img src="' + esc(u.avatar) + '" style="width:100%;height:100%;object-fit:cover;">' : esc((u.username || '?').charAt(0));
          var roleLabel = '';
          var roleMap = {
            'admin': '<span class="role-badge" style="background:#d1fae5;color:#065f46;">管理员</span>',
            'super_admin': '<span class="role-badge" style="background:#ede9fe;color:#5b21b6;">超管</span>',
            'platform_admin': '<span class="role-badge" style="background:linear-gradient(135deg,#1f2937,#f59e0b);color:#fff;">平台管理</span>'
          };
          if (roleMap[u.chat_role]) roleLabel = roleMap[u.chat_role];
          html += '<a href="' + SITE_URL + '/u/' + u.user_id + '" target="_blank" style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-bottom:1px solid var(--divider);text-decoration:none;color:inherit;">' +
            '<div style="width:34px;height:34px;border-radius:50%;overflow:hidden;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;flex-shrink:0;">' + av + '</div>' +
            '<div style="flex:1;min-width:0;"><div style="font-size:13px;font-weight:600;color:var(--text);">' + roleLabel + esc(u.username) + '</div>' +
            '<div style="font-size:11px;color:var(--text-muted);">Lv' + (u.level || 1) + (u.title_text ? ' · ' + esc(u.title_text) : '') + '</div></div>' +
            '<span style="width:6px;height:6px;border-radius:50%;background:#22c55e;"></span></a>';
        });
        onlineModalList.innerHTML = html;
      }
    });
  };
  // 点击背景关闭在线用户弹窗
  onlineModal.addEventListener('click', function (e) {
    if (e.target === onlineModal) onlineModal.style.display = 'none';
  });

  /* ---------- 心跳 ---------- */
  function heartbeat() {
    gbAjax({
      method: 'POST', url: SITE_URL + '/chat/heartbeat', toast: false,
      data: { _csrf: CSRF, room_id: ROOM_ID },
      success: function (res) {
        if (res && res.data && typeof res.data.online_count !== 'undefined' && onlineBadgeCount) {
          onlineBadgeCount.textContent = res.data.online_count;
        }
      }
    });
  }

  /* ---------- 启动 ---------- */
  autoResize(ta);
  function start() {
    loadMessages();
    loadAnnouncements();
    heartbeat();
    setInterval(loadMessages, 3000);
    setInterval(heartbeat, 15000);
    setInterval(loadAnnouncements, 30000);
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
</script>

<?php /** 私信页面 - 渲染于 user 布局 */
$conversations = $conversations ?? [];
$partner = $partner ?? null;
$toId = (int)($toId ?? 0);
?>
<section class="section pm-section">
  <div class="container pm-container">
    <div class="pm-wrap" id="pmWrap">
      <!-- 左侧会话列表 -->
      <aside class="pm-sidebar" id="pmSidebar">
        <div class="pm-sidebar-head">
          <h3>💬 私信</h3>
        </div>
        <div class="pm-conv-list" id="pmConvList">
          <?php if ($conversations): foreach ($conversations as $c):
            $cid = (int)($c['peer_id'] ?? 0);
            $uname = $c['username'] ?? '已注销';
            $uavatar = !empty($c['avatar']) ? asset($c['avatar']) : '';
            $preview = $c['last_content'] ?? '';
            $unread = (int)($c['unread'] ?? 0);
            $fromMe = !empty($c['last_from_me']);
            $active = ($toId && $cid === $toId) ? ' is-active' : '';
          ?>
          <div class="pm-conv<?php echo $active; ?>" data-peer="<?php echo $cid; ?>" data-name="<?php echo e($uname); ?>" data-avatar="<?php echo e($uavatar); ?>">
            <div class="pm-conv-avatar">
              <?php if ($uavatar): ?>
              <img src="<?php echo e($uavatar); ?>" alt="<?php echo e($uname); ?>">
              <?php else: ?>
              <span class="pm-avatar-fallback"><?php echo e(mb_substr($uname, 0, 1)); ?></span>
              <?php endif; ?>
              <?php if ($unread > 0): ?><span class="pm-unread"><?php echo $unread > 99 ? '99+' : $unread; ?></span><?php endif; ?>
            </div>
            <div class="pm-conv-main">
              <div class="pm-conv-top">
                <span class="pm-conv-name"><?php echo e($uname); ?></span>
              </div>
              <div class="pm-conv-preview"><?php echo e($fromMe ? '我：' : ''); ?><?php echo e(mb_substr($preview, 0, 30)); ?></div>
            </div>
          </div>
          <?php endforeach; else: ?>
          <div class="pm-empty">暂无私信会话</div>
          <?php endif; ?>
        </div>
      </aside>

      <!-- 右侧聊天面板 -->
      <main class="pm-main" id="pmMain">
        <?php if ($toId && $partner): ?>
          <div class="pm-chat-header" id="pmChatHeader">
            <div class="pm-chat-back" id="pmChatBack" title="返回会话列表">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </div>
            <div class="pm-chat-avatar">
              <?php $pavatar = !empty($partner['avatar']) ? asset($partner['avatar']) : ''; ?>
              <?php if ($pavatar): ?>
              <img src="<?php echo e($pavatar); ?>" alt="<?php echo e($partner['username']); ?>">
              <?php else: ?>
              <span class="pm-avatar-fallback"><?php echo e(mb_substr($partner['username'], 0, 1)); ?></span>
              <?php endif; ?>
            </div>
            <div class="pm-chat-info">
              <div class="pm-chat-name"><?php echo e($partner['username']); ?></div>
              <a class="pm-chat-link" href="<?php echo e(site_url('u/' . $toId)); ?>" target="_blank" rel="noopener">查看个人中心</a>
            </div>
          </div>
          <div class="pm-chat-body" id="pmChatBody">
            <div class="pm-loading" id="pmLoading">加载中...</div>
          </div>
          <div class="pm-chat-input" id="pmChatInput">
            <div class="pm-toolbar">
              <button type="button" class="pm-tool-btn" id="pmEmojiBtn" title="表情">😀</button>
              <button type="button" class="pm-tool-btn" id="pmImgBtn" title="发送图片">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              </button>
              <input type="file" id="pmImgFile" accept="image/*" style="display:none">
              <div class="pm-emoji-panel" id="pmEmojiPanel" style="display:none"></div>
            </div>
            <div class="pm-input-row">
              <textarea id="pmTextarea" class="pm-textarea" placeholder="输入消息, Enter 发送, Shift+Enter 换行" rows="1"></textarea>
              <button type="button" class="btn btn-primary pm-send-btn" id="pmSendBtn">发送</button>
            </div>
          </div>
        <?php else: ?>
          <div class="pm-placeholder" id="pmPlaceholder">
            <div class="pm-placeholder-icon">✉️</div>
            <div class="pm-placeholder-text">选择左侧会话开始私聊</div>
            <div class="pm-placeholder-sub">或在 <a href="<?php echo e(site_url('user/dashboard')); ?>">工作台</a> 中访问其他用户个人中心发起私聊</div>
          </div>
        <?php endif; ?>
      </main>
    </div>
  </div>
</section>

<style>
.pm-section { padding: 16px 0; }
.pm-container { max-width: 1000px; }
.pm-wrap {
  display: flex; gap: 0; height: calc(100vh - 180px); min-height: 480px;
  background: var(--card-bg); border: 1px solid var(--border);
  border-radius: 12px; overflow: hidden;
}
/* 左侧会话列表 */
.pm-sidebar {
  width: 280px; flex-shrink: 0; display: flex; flex-direction: column;
  border-right: 1px solid var(--divider); background: var(--bg-soft);
}
.pm-sidebar-head {
  padding: 14px 16px; border-bottom: 1px solid var(--divider);
}
.pm-sidebar-head h3 { margin: 0; font-size: 16px; color: var(--text); font-weight: 600; }
.pm-conv-list { flex: 1; overflow-y: auto; }
.pm-conv {
  display: flex; gap: 10px; align-items: center; padding: 12px 14px;
  cursor: pointer; border-bottom: 1px solid var(--divider);
  transition: background var(--transition);
}
.pm-conv:hover { background: var(--bg-hover); }
.pm-conv.is-active { background: var(--primary-bg); }
.pm-conv-avatar {
  position: relative; width: 40px; height: 40px; border-radius: 50%;
  overflow: hidden; flex-shrink: 0; background: var(--primary); color: #fff;
  display: flex; align-items: center; justify-content: center; user-select: none;
}
.pm-conv-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pm-avatar-fallback { font-size: 16px; font-weight: 600; }
.pm-unread {
  position: absolute; top: -4px; right: -4px; min-width: 18px; height: 18px;
  padding: 0 5px; border-radius: 9px; background: var(--danger); color: #fff;
  font-size: 11px; line-height: 18px; text-align: center; border: 2px solid var(--card-bg);
}
.pm-conv-main { flex: 1; min-width: 0; }
.pm-conv-top { display: flex; align-items: center; justify-content: space-between; gap: 6px; }
.pm-conv-name { font-size: 14px; font-weight: 600; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.pm-conv-preview {
  font-size: 12px; color: var(--text-muted); margin-top: 3px;
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.pm-empty { padding: 40px 16px; text-align: center; color: var(--text-muted); font-size: 13px; }
/* 右侧聊天面板 */
.pm-main { flex: 1; display: flex; flex-direction: column; min-width: 0; background: var(--card-bg); }
.pm-placeholder {
  flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: 8px; color: var(--text-muted); padding: 30px; text-align: center;
}
.pm-placeholder-icon { font-size: 56px; opacity: .6; }
.pm-placeholder-text { font-size: 15px; color: var(--text-2); }
.pm-placeholder-sub { font-size: 12px; color: var(--text-muted); }
.pm-placeholder-sub a { color: var(--primary); }
.pm-chat-header {
  display: flex; align-items: center; gap: 10px; padding: 12px 16px;
  border-bottom: 1px solid var(--divider); background: var(--bg-soft);
}
.pm-chat-back { display: none; cursor: pointer; color: var(--text-2); padding: 4px; border-radius: 6px; }
.pm-chat-back:hover { background: var(--bg-hover); }
.pm-chat-avatar {
  width: 36px; height: 36px; border-radius: 50%; overflow: hidden; flex-shrink: 0;
  background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center;
}
.pm-chat-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pm-chat-info { flex: 1; min-width: 0; }
.pm-chat-name { font-size: 15px; font-weight: 600; color: var(--text); }
.pm-chat-link { font-size: 12px; color: var(--primary); }
.pm-chat-body {
  flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 12px;
  background: var(--bg-soft);
}
.pm-loading { color: var(--text-muted); text-align: center; padding: 30px 0; }
.pm-msg { display: flex; gap: 8px; align-items: flex-start; max-width: 80%; }
.pm-msg.is-me { flex-direction: row-reverse; align-self: flex-end; }
.pm-msg-avatar {
  width: 32px; height: 32px; border-radius: 50%; overflow: hidden; flex-shrink: 0;
  background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center;
  user-select: none; font-size: 14px; font-weight: 600;
}
.pm-msg-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pm-msg-content { min-width: 0; display: flex; flex-direction: column; gap: 4px; }
.pm-msg.is-me .pm-msg-content { align-items: flex-end; }
.pm-msg-meta { font-size: 11px; color: var(--text-muted); }
.pm-msg-bubble {
  padding: 8px 12px; border-radius: 12px; font-size: 14px; line-height: 1.5;
  word-break: break-word; max-width: 100%; background: var(--card-bg);
  border: 1px solid var(--border); color: var(--text);
}
.pm-msg.is-me .pm-msg-bubble { background: var(--primary); color: #fff; border-color: var(--primary); }
.pm-msg-bubble a { color: var(--primary); }
.pm-msg.is-me .pm-msg-bubble a { color: #fff; text-decoration: underline; }
.pm-msg-img { max-width: 220px; max-height: 220px; border-radius: 8px; cursor: pointer; display: block; border: 1px solid var(--border); }
.pm-msg-emoji { font-size: 28px; line-height: 1.4; }
.pm-chat-input { border-top: 1px solid var(--divider); padding: 10px 12px; background: var(--card-bg); }
.pm-toolbar { position: relative; display: flex; gap: 6px; margin-bottom: 6px; }
.pm-tool-btn {
  width: 30px; height: 30px; border-radius: 6px; border: 1px solid var(--border);
  background: var(--card-bg); cursor: pointer; color: var(--text-2);
  display: flex; align-items: center; justify-content: center; font-size: 16px;
}
.pm-tool-btn:hover { background: var(--bg-hover); }
.pm-emoji-panel {
  position: absolute; bottom: 38px; left: 0; z-index: 20;
  background: var(--card-bg); border: 1px solid var(--border); border-radius: 8px;
  padding: 8px; display: flex; flex-wrap: wrap; gap: 4px; width: 240px;
  box-shadow: 0 4px 12px rgba(0,0,0,.1);
}
.pm-emoji-item { font-size: 22px; cursor: pointer; padding: 3px 4px; border-radius: 4px; }
.pm-emoji-item:hover { background: var(--bg-hover); }
.pm-input-row { display: flex; gap: 8px; align-items: flex-end; }
.pm-textarea {
  flex: 1; resize: none; border: 1px solid var(--border); border-radius: 8px;
  padding: 8px 10px; font-size: 14px; line-height: 1.5; max-height: 120px;
  background: var(--card-bg); color: var(--text); font-family: inherit;
}
.pm-textarea:focus { outline: none; border-color: var(--primary); }
.pm-send-btn { flex-shrink: 0; }
/* 响应式: 移动端会话列表与聊天面板上下排列 */
@media (max-width: 768px) {
  .pm-wrap { flex-direction: column; height: calc(100vh - 160px); min-height: 420px; border-radius: 0; border-left: 0; border-right: 0; }
  .pm-sidebar { width: 100%; height: 38%; border-right: 0; border-bottom: 1px solid var(--divider); }
  .pm-main { height: 62%; }
  .pm-chat-back { display: flex; }
  .pm-msg { max-width: 88%; }
  /* 移动端默认显示会话列表, 选中后切换到聊天 */
  .pm-wrap.pm-show-chat .pm-sidebar { display: none; }
  .pm-wrap.pm-show-chat .pm-main { display: flex; }
  .pm-wrap:not(.pm-show-chat) .pm-main:has(.pm-placeholder) { display: none; }
}
</style>

<script>
(function () {
  var SITE_URL = '<?php echo site_url(); ?>';
  var CSRF     = '<?php echo csrf_token(); ?>';
  var INIT_TO  = <?php echo $toId ? (int)$toId : '0'; ?>;
  var INIT_NAME = <?php echo json_encode($partner['username'] ?? '', JSON_UNESCAPED_UNICODE); ?>;
  var INIT_AVATAR = <?php echo json_encode(!empty($partner) && !empty($partner['avatar']) ? asset($partner['avatar']) : '', JSON_UNESCAPED_SLASHES); ?>;

  var EMOJIS = ['😀','😂','😍','😎','😢','😡','👍','👎','❤️','🔥','🎉','🎁','✅','❌','⚠️','👋','🙏','💪','🌟'];

  var wrap       = document.getElementById('pmWrap');
  var convList   = document.getElementById('pmConvList');
  var chatBody   = document.getElementById('pmChatBody');
  var ta         = document.getElementById('pmTextarea');
  var sendBtn    = document.getElementById('pmSendBtn');
  var emojiBtn   = document.getElementById('pmEmojiBtn');
  var emojiPanel = document.getElementById('pmEmojiPanel');
  var imgBtn     = document.getElementById('pmImgBtn');
  var imgFile    = document.getElementById('pmImgFile');
  var chatBack   = document.getElementById('pmChatBack');

  var peerId = INIT_TO;
  var peerName = INIT_NAME;
  var peerAvatar = INIT_AVATAR;
  var messagesById = {};
  var lastLoadedId = 0;
  var initialized = false;
  var pollTimer = null;

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
  function scrollToBottom() { if (chatBody) chatBody.scrollTop = chatBody.scrollHeight; }
  function nearBottom() { return chatBody && (chatBody.scrollHeight - chatBody.scrollTop - chatBody.clientHeight < 80); }

  /* ---------- 渲染消息 ---------- */
  function renderContent(m) {
    if (m.msg_type === 'image') {
      var src = m.content.indexOf('data:') === 0 ? m.content : imgUrl(m.content);
      return '<img class="pm-msg-img" src="' + esc(src) + '" alt="图片">';
    }
    if (m.msg_type === 'emoji') {
      return '<span class="pm-msg-emoji">' + esc(m.content) + '</span>';
    }
    return linkify(esc(m.content));
  }

  function buildMessage(m) {
    var el = document.createElement('div');
    el.className = 'pm-msg' + (m.is_me ? ' is-me' : '');
    el.setAttribute('data-id', m.id);

    var avHtml;
    if (m.avatar) {
      avHtml = '<img src="' + esc(m.avatar) + '" alt="' + esc(m.username) + '">';
    } else {
      avHtml = '<span>' + esc((m.username || '?').charAt(0)) + '</span>';
    }

    var contentHtml = '<div class="pm-msg-bubble">' + renderContent(m) + '</div>';
    var metaHtml = '<div class="pm-msg-meta">' + esc(m.created_at || '') + '</div>';

    el.innerHTML =
      '<div class="pm-msg-avatar">' + avHtml + '</div>' +
      '<div class="pm-msg-content">' + metaHtml + contentHtml + '</div>';
    return el;
  }

  function addMessage(m) {
    if (messagesById[m.id]) return;
    messagesById[m.id] = m;
    if (m.id > lastLoadedId) lastLoadedId = m.id;
    if (chatBody) chatBody.appendChild(buildMessage(m));
  }

  function renderFullList(list) {
    if (!chatBody) return;
    chatBody.innerHTML = '';
    messagesById = {};
    lastLoadedId = 0;
    list.forEach(function (m) { addMessage(m); });
    scrollToBottom();
  }

  /* ---------- 拉取消息 ---------- */
  function loadChat() {
    if (!peerId || !chatBody) return;
    gbAjax({
      method: 'POST', url: SITE_URL + '/user/messages/chat', toast: false,
      data: { _csrf: CSRF, peer_id: peerId },
      success: function (res) {
        var list = (res && res.data && res.data.messages) || [];
        var loading = document.getElementById('pmLoading');
        if (loading) loading.remove();
        if (!initialized) {
          renderFullList(list);
          initialized = true;
        } else {
          var appended = false;
          list.forEach(function (m) {
            if (!messagesById[m.id]) { addMessage(m); appended = true; }
          });
          if (appended && nearBottom()) scrollToBottom();
          // 更新未读红点 (已标记为已读)
          var conv = convList ? convList.querySelector('.pm-conv[data-peer="' + peerId + '"]') : null;
          if (conv) {
            var dot = conv.querySelector('.pm-unread');
            if (dot) dot.remove();
          }
        }
      }
    });
  }

  /* ---------- 发送 ---------- */
  function sendText() {
    if (!peerId) { gbToast.warning('请先选择一个会话'); return; }
    var content = ta.value.trim();
    if (!content) return;
    sendBtn.disabled = true;
    gbAjax({
      method: 'POST', url: SITE_URL + '/user/messages/send',
      data: { _csrf: CSRF, to_id: peerId, content: content, msg_type: 'text' },
      success: function (res) {
        if (res && res.code === 0) {
          ta.value = '';
          autoResize(ta);
          loadChat();
        }
      },
      complete: function () { sendBtn.disabled = false; }
    });
  }

  function sendImage(dataUrl) {
    if (!peerId) { gbToast.warning('请先选择一个会话'); return; }
    sendBtn.disabled = true;
    gbAjax({
      method: 'POST', url: SITE_URL + '/user/messages/send',
      data: { _csrf: CSRF, to_id: peerId, content: dataUrl, msg_type: 'image' },
      success: function (res) {
        if (res && res.code === 0) loadChat();
      },
      complete: function () { sendBtn.disabled = false; }
    });
  }

  /* ---------- 表情面板 ---------- */
  if (emojiPanel) {
    EMOJIS.forEach(function (em) {
      var b = document.createElement('span');
      b.className = 'pm-emoji-item';
      b.textContent = em;
      b.addEventListener('click', function () { insertAtCursor(ta, em); ta.focus(); });
      emojiPanel.appendChild(b);
    });
  }
  if (emojiBtn) {
    emojiBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      emojiPanel.style.display = emojiPanel.style.display === 'none' ? 'block' : 'none';
    });
    document.addEventListener('click', function (e) {
      if (!e.target.closest('#pmEmojiPanel') && !e.target.closest('#pmEmojiBtn')) emojiPanel.style.display = 'none';
    });
  }

  /* ---------- 图片上传 (内联 data URL, 限制 ~500KB) ---------- */
  if (imgBtn) {
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
  }

  /* ---------- 输入交互 ---------- */
  if (ta) {
    ta.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendText(); }
    });
    ta.addEventListener('input', function () { autoResize(ta); });
  }
  if (sendBtn) sendBtn.addEventListener('click', sendText);

  /* ---------- 选择会话 ---------- */
  function selectConversation(peer, name, avatar) {
    peerId = peer; peerName = name || ''; peerAvatar = avatar || '';
    initialized = false;
    lastLoadedId = 0;
    messagesById = {};
    if (chatBody) chatBody.innerHTML = '<div class="pm-loading">加载中...</div>';
    // 标记当前激活项
    if (convList) {
      var all = convList.querySelectorAll('.pm-conv');
      all.forEach(function (el) {
        el.classList.toggle('is-active', parseInt(el.getAttribute('data-peer'), 10) === peer);
      });
    }
    // 移动端切换到聊天视图
    if (wrap) wrap.classList.add('pm-show-chat');
    loadChat();
  }

  if (convList) {
    convList.addEventListener('click', function (e) {
      var conv = e.target.closest('.pm-conv');
      if (!conv) return;
      var peer = parseInt(conv.getAttribute('data-peer'), 10);
      if (!peer || peer === peerId) {
        if (peer && peer === peerId && wrap) wrap.classList.add('pm-show-chat');
        return;
      }
      var name = conv.getAttribute('data-name') || '';
      var avatar = conv.getAttribute('data-avatar') || '';
      selectConversation(peer, name, avatar);
    });
  }

  /* ---------- 移动端返回按钮 ---------- */
  if (chatBack) {
    chatBack.addEventListener('click', function () {
      if (wrap) wrap.classList.remove('pm-show-chat');
    });
  }

  /* ---------- 聊天气泡图片预览 ---------- */
  if (chatBody) {
    chatBody.addEventListener('click', function (e) {
      var img = e.target.closest('.pm-msg-img');
      if (img) window.open(img.src, '_blank');
    });
  }

  /* ---------- 启动 ---------- */
  function start() {
    if (ta) autoResize(ta);
    if (peerId) {
      loadChat();
      pollTimer = setInterval(loadChat, 3000);
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
</script>

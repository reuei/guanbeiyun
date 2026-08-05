<?php /** 聊天室页面 */ ?>
<section class="chat-section">
  <div class="chat-wrap" id="chatWrap">
    <!-- 顶部 -->
    <div class="chat-header">
      <div class="chat-title"><h2>💬 聊天室</h2></div>
      <div class="chat-online" id="chatOnline">在线交流中</div>
    </div>

    <!-- 消息列表 -->
    <div class="chat-body" id="chatBody">
      <div class="chat-loading" id="chatLoading">加载中...</div>
    </div>

    <!-- 输入区 -->
    <div class="chat-input" id="chatInput">
      <div class="chat-toolbar">
        <button type="button" class="chat-tool-btn" id="emojiBtn" title="表情">😀</button>
        <button type="button" class="chat-tool-btn" id="imgBtn" title="发送图片">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        </button>
        <input type="file" id="imgFile" accept="image/*" style="display:none">
        <div class="chat-emoji-panel" id="emojiPanel" style="display:none"></div>
      </div>
      <div class="chat-input-row">
        <textarea id="chatTextarea" class="chat-textarea" placeholder="输入消息, Enter 发送, Shift+Enter 换行" rows="1"></textarea>
        <button type="button" class="btn btn-primary chat-send-btn" id="sendBtn">发送</button>
      </div>
    </div>
  </div>
</section>

<style>
.chat-section { padding: 16px 0; }
.chat-wrap {
  max-width: 900px; margin: 0 auto; min-height: 70vh;
  display: flex; flex-direction: column;
  background: var(--card-bg); border: 1px solid var(--border);
  border-radius: 12px; overflow: hidden;
}
.chat-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 18px; border-bottom: 1px solid var(--divider); background: var(--bg-soft);
}
.chat-header h2 { margin: 0; font-size: 18px; color: var(--text); }
.chat-online { font-size: 13px; color: var(--text-2); }
.chat-body {
  flex: 1 1 auto; overflow-y: auto; padding: 16px;
  display: flex; flex-direction: column; gap: 12px;
  min-height: 320px; max-height: 60vh;
}
.chat-loading { color: var(--text-2); text-align: center; padding: 30px 0; }
.chat-msg { display: flex; gap: 10px; align-items: flex-start; }
.chat-avatar {
  width: 38px; height: 38px; border-radius: 50%; overflow: hidden; flex-shrink: 0;
  cursor: pointer; background: var(--primary); color: #fff;
  display: flex; align-items: center; justify-content: center; user-select: none;
}
.chat-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.chat-avatar-letter { font-size: 16px; font-weight: 600; }
.chat-main { flex: 1; min-width: 0; }
.chat-meta { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-bottom: 3px; }
.chat-name { font-weight: 600; color: var(--text); font-size: 14px; }
.chat-time { color: var(--text-3); font-size: 12px; margin-left: auto; }
.chat-certs { display: inline-flex; gap: 3px; align-items: center; }
.cert-img { width: 16px; height: 16px; border-radius: 3px; cursor: pointer; vertical-align: middle; }
.cert-badge {
  display: inline-block; padding: 0 5px; font-size: 11px; line-height: 16px; height: 16px;
  border-radius: 3px; background: var(--primary-bg); color: var(--primary);
  cursor: pointer; border: 1px solid var(--primary-bg-2);
}
.chat-content { color: var(--text); font-size: 14px; line-height: 1.6; word-break: break-word; }
.chat-content a { color: var(--primary); }
.chat-img { max-width: 200px; max-height: 240px; border-radius: 6px; cursor: pointer; border: 1px solid var(--border); display: block; }
.chat-emoji { font-size: 24px; }
.chat-link { color: var(--primary); }
.chat-reply {
  font-size: 12px; color: var(--text-2); background: var(--bg-soft);
  border-left: 3px solid var(--border); padding: 4px 8px; border-radius: 4px;
  margin-bottom: 4px; max-width: 100%; word-break: break-word;
}
.chat-recalled { color: var(--text-muted); font-style: italic; font-size: 13px; }
.chat-msg.is-recalled { opacity: .7; }
.chat-input { border-top: 1px solid var(--divider); padding: 10px 12px; background: var(--card-bg); }
.chat-toolbar { position: relative; display: flex; gap: 6px; margin-bottom: 6px; }
.chat-tool-btn {
  width: 30px; height: 30px; border-radius: 6px; border: 1px solid var(--border);
  background: var(--card-bg); cursor: pointer; color: var(--text-2);
  display: flex; align-items: center; justify-content: center; font-size: 16px;
}
.chat-tool-btn:hover { background: var(--bg-hover); }
.chat-emoji-panel {
  position: absolute; bottom: 38px; left: 0; z-index: 20;
  background: var(--card-bg); border: 1px solid var(--border); border-radius: 8px;
  padding: 8px; display: flex; flex-wrap: wrap; gap: 4px; width: 240px;
  box-shadow: 0 4px 12px rgba(0,0,0,.1);
}
.emoji-item { font-size: 22px; cursor: pointer; padding: 3px 4px; border-radius: 4px; }
.emoji-item:hover { background: var(--bg-hover); }
.chat-input-row { display: flex; gap: 8px; align-items: flex-end; }
.chat-textarea {
  flex: 1; resize: none; border: 1px solid var(--border); border-radius: 8px;
  padding: 8px 10px; font-size: 14px; line-height: 1.5; max-height: 120px;
  background: var(--card-bg); color: var(--text); font-family: inherit;
}
.chat-textarea:focus { outline: none; border-color: var(--primary); }
.chat-send-btn { flex-shrink: 0; }
@media (max-width: 768px) {
  .chat-wrap { min-height: 78vh; max-width: 100%; border-radius: 0; border-left: 0; border-right: 0; }
  .chat-body { max-height: none; }
}
</style>

<script>
(function () {
  var SITE_URL = '<?php echo site_url(); ?>';
  var CSRF     = '<?php echo csrf_token(); ?>';

  var EMOJIS = ['😀','😂','😍','😎','😢','😡','👍','👎','❤️','🔥','🎉','🎁','✅','❌','⚠️','👋','🙏','💪','🌟'];

  var chatBody   = document.getElementById('chatBody');
  var ta         = document.getElementById('chatTextarea');
  var sendBtn    = document.getElementById('sendBtn');
  var emojiBtn   = document.getElementById('emojiBtn');
  var emojiPanel = document.getElementById('emojiPanel');
  var imgBtn     = document.getElementById('imgBtn');
  var imgFile    = document.getElementById('imgFile');

  var lastMsgId = 0, firstMsgId = 0;
  var messagesById = {};
  var initialLoaded = false;
  var loadingHistory = false;

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
    el.className = 'chat-msg' + (m.is_recalled ? ' is-recalled' : '');
    el.setAttribute('data-id', m.id);

    var avHtml;
    if (m.avatar) {
      avHtml = '<img src="' + esc(m.avatar) + '" alt="' + esc(m.username) + '">';
    } else {
      avHtml = '<span class="chat-avatar-letter">' + esc((m.username || '?').charAt(0)) + '</span>';
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

    var contentHtml = m.is_recalled
      ? '<div class="chat-recalled">该消息已被撤回</div>'
      : '<div class="chat-content">' + renderContent(m) + '</div>';

    el.innerHTML =
      '<div class="chat-avatar" data-uid="' + m.user_id + '" data-username="' + esc(m.username) + '" title="查看个人中心">' + avHtml + '</div>' +
      '<div class="chat-main">' +
        '<div class="chat-meta">' +
          '<span class="chat-name">' + esc(m.username) + '</span>' +
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
      data: { _csrf: CSRF },
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
      }
    });
  }

  function loadHistory() {
    if (loadingHistory || firstMsgId === 0) return;
    loadingHistory = true;
    var prevHeight = chatBody.scrollHeight;
    gbAjax({
      method: 'POST', url: SITE_URL + '/chat/history', toast: false,
      data: { _csrf: CSRF, before_id: firstMsgId },
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
    sendBtn.disabled = true;
    gbAjax({
      method: 'POST', url: SITE_URL + '/chat/send',
      data: { _csrf: CSRF, content: content, msg_type: 'text' },
      success: function (res) {
        if (res && res.code === 0) {
          ta.value = '';
          autoResize(ta);
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
      data: { _csrf: CSRF, content: dataUrl, msg_type: 'image' },
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
    emojiPanel.style.display = emojiPanel.style.display === 'none' ? 'block' : 'none';
  });
  document.addEventListener('click', function (e) {
    if (!e.target.closest('#emojiPanel') && !e.target.closest('#emojiBtn')) emojiPanel.style.display = 'none';
  });

  /* ---------- 图片上传 (内联 data URL, 限制 ~500KB) ---------- */
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

  /* ---------- 启动 (等待 app.js 加载, gbAjax/gbToast 就绪) ---------- */
  autoResize(ta);
  function start() {
    loadMessages();
    setInterval(loadMessages, 3000);
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
</script>

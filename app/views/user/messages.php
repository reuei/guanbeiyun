<?php /** 私信页面 - v6 重构, 使用 chat 独立布局, 含表情、快捷语句、布局适配 */
$conversations = $conversations ?? [];
$partner = $partner ?? null;
$toId = (int)($toId ?? 0);
?>
<section class="pm-section" style="height:100%;display:flex;">
  <div class="pm-wrap" id="pmWrap" style="flex:1;display:flex;overflow:hidden;background:var(--card-bg);">
    <!-- 左侧会话列表 -->
    <aside class="pm-sidebar" id="pmSidebar" style="width:260px;flex-shrink:0;display:flex;flex-direction:column;border-right:1px solid var(--divider);background:var(--bg-soft);">
      <div style="padding:14px 16px;border-bottom:1px solid var(--divider);">
        <h3 style="margin:0;font-size:16px;color:var(--text);font-weight:600;display:flex;align-items:center;gap:6px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          私信
        </h3>
      </div>
      <div class="pm-conv-list" id="pmConvList" style="flex:1;overflow-y:auto;">
        <?php if ($conversations): foreach ($conversations as $c):
          $cid = (int)($c['peer_id'] ?? 0);
          $uname = $c['username'] ?? '已注销';
          $uavatar = !empty($c['avatar']) ? asset($c['avatar']) : '';
          $preview = $c['last_content'] ?? '';
          $unread = (int)($c['unread'] ?? 0);
          $fromMe = !empty($c['last_from_me']);
          $active = ($toId && $cid === $toId) ? ' is-active' : '';
        ?>
        <div class="pm-conv<?php echo $active; ?>" data-peer="<?php echo $cid; ?>" data-name="<?php echo e($uname); ?>" data-avatar="<?php echo e($uavatar); ?>" style="display:flex;gap:10px;align-items:center;padding:12px 14px;cursor:pointer;border-bottom:1px solid var(--divider);transition:background .2s;">
          <div style="position:relative;width:38px;height:38px;border-radius:50%;overflow:hidden;flex-shrink:0;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;user-select:none;">
            <?php if ($uavatar): ?>
            <img src="<?php echo e($uavatar); ?>" alt="<?php echo e($uname); ?>" style="width:100%;height:100%;object-fit:cover;display:block;">
            <?php else: ?>
            <span style="font-size:15px;font-weight:600;"><?php echo e(mb_substr($uname, 0, 1)); ?></span>
            <?php endif; ?>
            <?php if ($unread > 0): ?><span style="position:absolute;top:-4px;right:-4px;min-width:18px;height:18px;padding:0 5px;border-radius:9px;background:var(--danger);color:#fff;font-size:11px;line-height:18px;text-align:center;border:2px solid var(--card-bg);"><?php echo $unread > 99 ? '99+' : $unread; ?></span><?php endif; ?>
          </div>
          <div style="flex:1;min-width:0;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:6px;">
              <span style="font-size:14px;font-weight:600;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo e($uname); ?></span>
            </div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:3px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo e($fromMe ? '我：' : ''); ?><?php echo e(mb_substr($preview, 0, 30)); ?></div>
          </div>
        </div>
        <?php endforeach; else: ?>
        <div style="padding:40px 16px;text-align:center;color:var(--text-muted);font-size:13px;">暂无私信会话</div>
        <?php endif; ?>
      </div>
    </aside>

    <!-- 右侧聊天面板 -->
    <main class="pm-main" id="pmMain" style="flex:1;display:flex;flex-direction:column;min-width:0;background:var(--card-bg);">
      <?php if ($toId && $partner): ?>
        <div class="pm-chat-header" id="pmChatHeader" style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid var(--divider);background:var(--bg-soft);flex-shrink:0;">
          <div class="pm-chat-back" id="pmChatBack" title="返回会话列表" style="display:none;cursor:pointer;color:var(--text-2);padding:4px;border-radius:6px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
          </div>
          <div style="width:36px;height:36px;border-radius:50%;overflow:hidden;flex-shrink:0;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;">
            <?php $pavatar = !empty($partner['avatar']) ? asset($partner['avatar']) : ''; ?>
            <?php if ($pavatar): ?>
            <img src="<?php echo e($pavatar); ?>" alt="<?php echo e($partner['username']); ?>" style="width:100%;height:100%;object-fit:cover;display:block;">
            <?php else: ?>
            <span style="font-size:14px;font-weight:600;"><?php echo e(mb_substr($partner['username'], 0, 1)); ?></span>
            <?php endif; ?>
          </div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:15px;font-weight:600;color:var(--text);"><?php echo e($partner['username']); ?></div>
            <a style="font-size:12px;color:var(--primary);text-decoration:none;" href="<?php echo e(site_url('u/' . $toId)); ?>" target="_blank" rel="noopener">查看个人中心</a>
          </div>
        </div>
        <div class="pm-chat-body" id="pmChatBody" style="flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:12px;background:var(--bg-soft);">
          <div style="color:var(--text-muted);text-align:center;padding:30px 0;" id="pmLoading">加载中...</div>
        </div>
        <div class="pm-chat-input" id="pmChatInput" style="border-top:1px solid var(--divider);padding:8px 12px;background:var(--card-bg);flex-shrink:0;">
          <div style="position:relative;display:flex;gap:6px;margin-bottom:6px;align-items:center;">
            <button type="button" id="pmEmojiBtn" title="表情" style="width:30px;height:30px;border-radius:6px;border:1px solid var(--border);background:var(--card-bg);cursor:pointer;color:var(--text-2);display:flex;align-items:center;justify-content:center;font-size:16px;">😀</button>
            <button type="button" id="pmImgBtn" title="发送图片" style="width:30px;height:30px;border-radius:6px;border:1px solid var(--border);background:var(--card-bg);cursor:pointer;color:var(--text-2);display:flex;align-items:center;justify-content:center;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            </button>
            <button type="button" id="pmPhraseBtn" title="快捷语句" style="width:30px;height:30px;border-radius:6px;border:1px solid var(--border);background:var(--card-bg);cursor:pointer;color:var(--text-2);display:flex;align-items:center;justify-content:center;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </button>
            <input type="file" id="pmImgFile" accept="image/*" style="display:none">
            <!-- 表情面板 -->
            <div id="pmEmojiPanel" style="display:none;position:absolute;bottom:38px;left:0;z-index:20;background:var(--card-bg);border:1px solid var(--border);border-radius:8px;padding:8px;display:flex;flex-wrap:wrap;gap:4px;width:280px;max-height:240px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,.1);"></div>
            <!-- 快捷语句面板 -->
            <div id="pmPhrasePanel" style="display:none;position:absolute;bottom:38px;left:0;z-index:20;background:var(--card-bg);border:1px solid var(--border);border-radius:8px;padding:8px;width:280px;max-height:280px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,.1);">
              <div style="font-size:12px;color:var(--text-muted);margin-bottom:6px;display:flex;align-items:center;justify-content:space-between;">
                <span>快捷语句</span>
                <button type="button" id="pmAddPhraseBtn" style="background:none;border:none;color:var(--primary);cursor:pointer;font-size:12px;padding:0;">+ 添加</button>
              </div>
              <div id="pmPhraseList"></div>
            </div>
          </div>
          <div style="display:flex;gap:8px;align-items:flex-end;">
            <textarea id="pmTextarea" placeholder="输入消息, Enter 发送, Shift+Enter 换行" rows="1" style="flex:1;resize:none;border:1px solid var(--border);border-radius:8px;padding:8px 10px;font-size:14px;line-height:1.5;max-height:120px;background:var(--card-bg);color:var(--text);font-family:inherit;"></textarea>
            <button type="button" class="btn btn-primary" id="pmSendBtn" style="flex-shrink:0;">发送</button>
          </div>
        </div>
      <?php else: ?>
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;color:var(--text-muted);padding:30px;text-align:center;">
          <div style="font-size:56px;opacity:.6;">✉️</div>
          <div style="font-size:15px;color:var(--text-2);">选择左侧会话开始私聊</div>
          <div style="font-size:12px;color:var(--text-muted);">或在其他用户个人中心发起私聊</div>
        </div>
      <?php endif; ?>
    </main>
  </div>
</section>

<style>
.pm-conv:hover { background: var(--bg-hover); }
.pm-conv.is-active { background: var(--primary-bg); }
.pm-msg { display: flex; gap: 8px; align-items: flex-start; max-width: 80%; }
.pm-msg.is-me { flex-direction: row-reverse; align-self: flex-end; }
.pm-msg-avatar {
  width: 32px; height: 32px; border-radius: 50%; overflow: hidden; flex-shrink: 0;
  background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center;
  user-select: none; font-size: 13px; font-weight: 600;
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
.pm-emoji-item, .pm-phrase-item { font-size: 20px; cursor: pointer; padding: 4px 5px; border-radius: 4px; transition: background .15s; }
.pm-emoji-item:hover { background: var(--bg-hover); }
.pm-phrase-item { font-size: 13px; color: var(--text); display: flex; align-items: center; justify-content: space-between; gap: 6px; }
.pm-phrase-item:hover { background: var(--bg-hover); }
.pm-phrase-del { color: var(--danger); font-size: 14px; opacity: 0; transition: opacity .15s; }
.pm-phrase-item:hover .pm-phrase-del { opacity: 1; }
/* 移动端 */
@media (max-width: 768px) {
  .pm-sidebar { width: 100% !important; }
  .pm-wrap { flex-direction: column; }
  .pm-sidebar { height: 38%; border-right: 0 !important; border-bottom: 1px solid var(--divider); }
  .pm-main { height: 62%; }
  #pmChatBack { display: flex !important; }
  .pm-msg { max-width: 88%; }
  .pm-wrap.pm-show-chat .pm-sidebar { display: none; }
  .pm-wrap.pm-show-chat .pm-main { display: flex; }
}
</style>

<script>
(function () {
  var SITE_URL = '<?php echo site_url(); ?>';
  var CSRF     = '<?php echo csrf_token(); ?>';
  var INIT_TO  = <?php echo $toId ? (int)$toId : '0'; ?>;
  var INIT_NAME = <?php echo json_encode($partner['username'] ?? '', JSON_UNESCAPED_UNICODE); ?>;
  var INIT_AVATAR = <?php echo json_encode(!empty($partner) && !empty($partner['avatar']) ? asset($partner['avatar']) : '', JSON_UNESCAPED_SLASHES); ?>;

  // v6: 更丰富的表情
  var EMOJIS = [
    '😀','😃','😄','😁','😆','😅','🤣','😂','🙂','🙃','😉','😊','😇','🥰','😍','🤩','😘','😗','😚','😙',
    '😋','😛','😜','🤪','😝','🤑','🤗','🤭','🤫','🤔','🤐','🤨','😐','😑','😶','😏','😒','🙄','😬','🤥',
    '😌','😔','😪','🤤','😴','😷','🤒','🤕','🤢','🤮','🥵','🥶','😎','🤓','🧐','😕','😟','🙁','😮','😯',
    '😲','😳','🥺','😦','😧','😨','😰','😥','😢','😭','😱','😖','😣','😞','😓','😩','😫','🥱','😤','😡',
    '😠','🤬','😈','👿','💀','💩','🤡','👻','👽','🤖','🎃','😺','👍','👎','👌','✌️','🤞','🤟','🤘','👏',
    '🙌','🙏','🤝','💪','🔥','✨','🎉','🎊','🎁','💝','❤️','🧡','💛','💚','💙','💜','🤍','🤎','💔','✅','❌','⚠️'
  ];

  var wrap       = document.getElementById('pmWrap');
  var convList   = document.getElementById('pmConvList');
  var chatBody   = document.getElementById('pmChatBody');
  var ta         = document.getElementById('pmTextarea');
  var sendBtn    = document.getElementById('pmSendBtn');
  var emojiBtn   = document.getElementById('pmEmojiBtn');
  var emojiPanel = document.getElementById('pmEmojiPanel');
  var imgBtn     = document.getElementById('pmImgBtn');
  var imgFile    = document.getElementById('pmImgFile');
  var phraseBtn  = document.getElementById('pmPhraseBtn');
  var phrasePanel= document.getElementById('pmPhrasePanel');
  var addPhraseBtn = document.getElementById('pmAddPhraseBtn');
  var phraseList = document.getElementById('pmPhraseList');
  var chatBack   = document.getElementById('pmChatBack');

  var peerId = INIT_TO;
  var peerName = INIT_NAME;
  var peerAvatar = INIT_AVATAR;
  var messagesById = {};
  var lastLoadedId = 0;
  var initialized = false;
  var pollTimer = null;

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
          var conv = convList ? convList.querySelector('.pm-conv[data-peer="' + peerId + '"]') : null;
          if (conv) {
            var dot = conv.querySelector('span[style*="danger"]') || conv.querySelector('.pm-unread');
            // 简单方式: 通过 querySelector 找不到特定类, 保留旧逻辑
          }
        }
      }
    });
  }

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

  /* 表情面板 */
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
      emojiPanel.style.display = emojiPanel.style.display === 'none' ? 'flex' : 'none';
      if (phrasePanel) phrasePanel.style.display = 'none';
    });
    document.addEventListener('click', function (e) {
      if (!e.target.closest('#pmEmojiPanel') && !e.target.closest('#pmEmojiBtn')) emojiPanel.style.display = 'none';
    });
  }

  /* 快捷语句面板 */
  function loadPhrases() {
    gbAjax({
      method: 'GET', url: SITE_URL + '/chat/quick_phrases', toast: false,
      data: { _csrf: CSRF },
      success: function (res) {
        var list = (res && res.data && res.data.phrases) || [];
        if (!phraseList) return;
        phraseList.innerHTML = '';
        if (list.length === 0) {
          phraseList.innerHTML = '<div style="padding:14px;text-align:center;color:var(--text-muted);font-size:12px;">暂无快捷语句, 点击 + 添加</div>';
          return;
        }
        list.forEach(function (p) {
          var item = document.createElement('div');
          item.className = 'pm-phrase-item';
          item.innerHTML = '<span style="flex:1;word-break:break-all;">' + esc(p.content) + '</span>' +
            '<span class="pm-phrase-del" data-id="' + p.id + '" title="删除">×</span>';
          item.querySelector('span:first-child').addEventListener('click', function () {
            insertAtCursor(ta, p.content);
            ta.focus();
            phrasePanel.style.display = 'none';
          });
          item.querySelector('.pm-phrase-del').addEventListener('click', function (e) {
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
  if (phraseBtn) {
    phraseBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      phrasePanel.style.display = phrasePanel.style.display === 'none' ? 'block' : 'none';
      if (emojiPanel) emojiPanel.style.display = 'none';
      if (phrasePanel.style.display === 'block') loadPhrases();
    });
    document.addEventListener('click', function (e) {
      if (!e.target.closest('#pmPhrasePanel') && !e.target.closest('#pmPhraseBtn')) phrasePanel.style.display = 'none';
    });
  }
  if (addPhraseBtn) {
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
  }

  /* 图片上传 */
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

  /* 输入交互 */
  if (ta) {
    ta.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendText(); }
    });
    ta.addEventListener('input', function () { autoResize(ta); });
  }
  if (sendBtn) sendBtn.addEventListener('click', sendText);

  function selectConversation(peer, name, avatar) {
    peerId = peer; peerName = name || ''; peerAvatar = avatar || '';
    initialized = false;
    lastLoadedId = 0;
    messagesById = {};
    if (chatBody) chatBody.innerHTML = '<div style="color:var(--text-muted);text-align:center;padding:30px 0;">加载中...</div>';
    if (convList) {
      var all = convList.querySelectorAll('.pm-conv');
      all.forEach(function (el) {
        el.classList.toggle('is-active', parseInt(el.getAttribute('data-peer'), 10) === peer);
      });
    }
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

  if (chatBack) {
    chatBack.addEventListener('click', function () {
      if (wrap) wrap.classList.remove('pm-show-chat');
    });
  }

  if (chatBody) {
    chatBody.addEventListener('click', function (e) {
      var img = e.target.closest('.pm-msg-img');
      if (img) window.open(img.src, '_blank');
    });
  }

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

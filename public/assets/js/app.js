/* =========================================================
   管备云备案系统 - 核心交互 JS
   ========================================================= */
(function () {
  'use strict';

  /* ---------- 全屏加载 (页面进入) ---------- */
  /* 兼容: 既能匹配 layout 中已有的 .page-loader, 也能匹配 JS 动态创建的 #gb-page-loader */
  function getPageLoader() {
    return document.getElementById('gb-page-loader') || document.querySelector('.page-loader');
  }
  function showPageLoader() {
    if (getPageLoader()) return;
    var loader = document.createElement('div');
    loader.id = 'gb-page-loader';
    loader.className = 'page-loader';
    loader.innerHTML = '<div class="gb-loading gb-loading-lg"></div><div class="page-loader-text">加载中...</div>';
    document.body.appendChild(loader);
  }
  var _loaderHidden = false;
  function hidePageLoader() {
    if (_loaderHidden) return;
    var loader = getPageLoader();
    if (loader) {
      _loaderHidden = true;
      loader.classList.add('hide');
      setTimeout(function () { if (loader && loader.parentNode) loader.parentNode.removeChild(loader); }, 300);
    }
  }
  /* 多重保障: load 事件 + DOMContentLoaded + 3 秒超时强制进入 */
  if (document.readyState === 'complete') {
    hidePageLoader();
  } else {
    window.addEventListener('load', hidePageLoader);
    document.addEventListener('DOMContentLoaded', function () {
      /* DOM 就绪后稍等即隐藏, 避免等待图片等慢资源 */
      setTimeout(hidePageLoader, 100);
    });
  }
  /* 3 秒后强制隐藏, 无论如何都进入网页 */
  setTimeout(hidePageLoader, 3000);

  /* ---------- Toast 全局提示 ---------- */
  function ensureToastContainer() {
    var c = document.getElementById('gb-toast-container');
    if (!c) {
      c = document.createElement('div');
      c.id = 'gb-toast-container';
      c.className = 'toast-container';
      document.body.appendChild(c);
    }
    return c;
  }
  var ICONS = {
    success: '<svg class="toast-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>',
    error: '<svg class="toast-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
    warning: '<svg class="toast-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
    info: '<svg class="toast-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
  };
  window.GBToast = function (msg, type, duration) {
    type = type || 'info';
    duration = duration || 2600;
    var c = ensureToastContainer();
    var t = document.createElement('div');
    t.className = 'toast toast-' + type;
    t.innerHTML = (ICONS[type] || ICONS.info) + '<span>' + msg + '</span>';
    c.appendChild(t);
    setTimeout(function () {
      t.classList.add('out');
      setTimeout(function () { t.remove(); }, 250);
    }, duration);
  };
  window.gbToast = {
    success: function (m, d) { GBToast(m, 'success', d); },
    error: function (m, d) { GBToast(m, 'error', d); },
    warning: function (m, d) { GBToast(m, 'warning', d); },
    info: function (m, d) { GBToast(m, 'info', d); }
  };

  /* ---------- 主题切换 (跟随系统 + 手动) ---------- */
  function getStoredTheme() { return localStorage.getItem('gb-theme') || 'auto'; }
  function applyTheme(theme) {
    if (theme === 'auto') {
      document.documentElement.removeAttribute('data-theme');
    } else {
      document.documentElement.setAttribute('data-theme', theme);
    }
  }
  applyTheme(getStoredTheme());
  window.gbToggleTheme = function () {
    var cur = getStoredTheme();
    var sysDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    var isDark;
    if (cur === 'auto') { isDark = !sysDark; }
    else { isDark = (cur === 'light'); }
    var next = isDark ? 'dark' : 'light';
    localStorage.setItem('gb-theme', next);
    applyTheme(next);
    gbToast.info(isDark ? '已切换至夜间模式' : '已切换至日间模式');
  };
  // 监听系统主题变化
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
    if (getStoredTheme() === 'auto') applyTheme('auto');
  });

  /* ---------- 汉堡菜单 ---------- */
  function initHamburger() {
    var btn = document.querySelector('.hamburger');
    var menu = document.querySelector('.hamburger-menu');
    if (!btn || !menu) return;
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      btn.classList.toggle('active');
      menu.classList.toggle('open');
    });
    document.addEventListener('click', function (e) {
      if (!menu.contains(e.target) && !btn.contains(e.target)) {
        btn.classList.remove('active');
        menu.classList.remove('open');
      }
    });
  }
  document.addEventListener('DOMContentLoaded', initHamburger);

  /* ---------- 二维码弹窗 ---------- */
  window.gbShowQR = function (title, imgSrc) {
    var modal = document.getElementById('qr-modal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'qr-modal';
      modal.className = 'qr-modal';
      modal.innerHTML = '<div class="qr-box"><h4 id="qr-title"></h4><div id="qr-img-wrap"></div><div style="margin-top:14px;"><button class="btn btn-block" onclick="gbCloseQR()">关闭</button></div></div>';
      document.body.appendChild(modal);
      modal.addEventListener('click', function (e) { if (e.target === modal) gbCloseQR(); });
    }
    document.getElementById('qr-title').textContent = title;
    var wrap = document.getElementById('qr-img-wrap');
    if (imgSrc) {
      wrap.innerHTML = '<img src="' + imgSrc + '" alt="' + title + '">';
    } else {
      wrap.innerHTML = '<div class="qr-empty">暂未配置二维码</div>';
    }
    modal.classList.add('open');
  };
  window.gbCloseQR = function () {
    var m = document.getElementById('qr-modal');
    if (m) m.classList.remove('open');
  };

  /* ---------- 通用 Modal ---------- */
  window.gbModal = {
    open: function (id) { var m = document.getElementById(id); if (m) m.classList.add('open'); },
    close: function (id) { var m = document.getElementById(id); if (m) m.classList.remove('open'); }
  };

  /* ---------- 管理后台侧边栏折叠 ---------- */
  function initSidebar() {
    document.querySelectorAll('.menu-item.has-sub').forEach(function (item) {
      item.addEventListener('click', function (e) {
        if (e.target.closest('a')) return;
        var sub = item.nextElementSibling;
        if (sub && sub.classList.contains('menu-sub')) {
          item.classList.toggle('expanded');
          sub.classList.toggle('open');
        }
      });
    });
    // 移动端切换
    var toggle = document.querySelector('.toggle-sidebar');
    var sidebar = document.querySelector('.admin-sidebar, .user-sidebar');
    var overlay = document.querySelector('.sidebar-overlay');
    if (toggle && sidebar) {
      toggle.addEventListener('click', function () {
        sidebar.classList.add('open');
        if (overlay) overlay.classList.add('open');
      });
    }
    if (overlay) {
      overlay.addEventListener('click', function () {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
      });
    }
  }
  document.addEventListener('DOMContentLoaded', initSidebar);

  /* ---------- 表单实时校验容器 ---------- */
  window.gbValidate = {
    // 设置错误
    setError: function (input, msg) {
      input.classList.remove('form-success');
      input.classList.add('error');
      var err = input.parentNode.querySelector('.form-error');
      if (!err) {
        err = document.createElement('div');
        err.className = 'form-error';
        input.parentNode.appendChild(err);
      }
      err.textContent = msg;
    },
    setSuccess: function (input) {
      input.classList.remove('error');
      input.classList.add('form-success');
      var err = input.parentNode.querySelector('.form-error');
      if (err) err.textContent = '';
    },
    clear: function (input) {
      input.classList.remove('error', 'form-success');
      var err = input.parentNode.querySelector('.form-error');
      if (err) err.textContent = '';
    }
  };

  /* ---------- AJAX 封装 ---------- */
  window.gbAjax = function (opts) {
    opts = opts || {};
    var method = (opts.method || 'GET').toUpperCase();
    var url = opts.url;
    var headers = { 'X-Requested-With': 'XMLHttpRequest' };
    if (opts.headers) Object.assign(headers, opts.headers);
    var body = null;

    if (method === 'POST') {
      if (opts.json) {
        headers['Content-Type'] = 'application/json';
        body = JSON.stringify(opts.json);
      } else if (opts.form) {
        body = opts.form;
      } else if (opts.data) {
        headers['Content-Type'] = 'application/x-www-form-urlencoded';
        body = Object.keys(opts.data).map(function (k) {
          return encodeURIComponent(k) + '=' + encodeURIComponent(opts.data[k]);
        }).join('&');
      }
    }
    var xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    Object.keys(headers).forEach(function (k) { xhr.setRequestHeader(k, headers[k]); });
    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;
      var data = null;
      try { data = JSON.parse(xhr.responseText); } catch (e) {}
      if (xhr.status >= 200 && xhr.status < 300) {
        if (data && data.code !== undefined && data.code !== 0 && opts.toast !== false) {
          gbToast.error(data.msg || '操作失败');
        }
        opts.success && opts.success(data, xhr);
      } else {
        if (opts.toast !== false) gbToast.error((data && data.msg) || '请求失败 (' + xhr.status + ')');
        opts.fail && opts.fail(data, xhr);
      }
      opts.complete && opts.complete(xhr);
    };
    xhr.send(body);
  };

  /* ---------- 表单提交 (带加载态) ---------- */
  window.gbSubmit = function (form, opts) {
    opts = opts || {};
    var btn = form.querySelector('[type=submit]');
    var oldHtml = btn ? btn.innerHTML : '';
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="gb-loading gb-loading-sm"></span> 提交中...'; }
    var data = new FormData(form);
    gbAjax({
      method: 'POST',
      url: opts.url || form.action,
      form: data,
      success: function (res) {
        if (res && res.code === 0) {
          gbToast.success(res.msg || '操作成功');
          if (opts.success) opts.success(res);
          else if (res.data && res.data.redirect) location.href = res.data.redirect;
        } else if (opts.success) {
          opts.success(res);
        }
      },
      complete: function () {
        if (btn) { btn.disabled = false; btn.innerHTML = oldHtml; }
      }
    });
  };

  // 公告弹窗关闭
  window.closeAnnounce = function () {
    var m = document.getElementById('announce-modal');
    if (m) { m.classList.remove('open'); sessionStorage.setItem('gb_announce_closed', '1'); }
  };
})();

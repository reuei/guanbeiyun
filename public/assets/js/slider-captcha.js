/* =========================================================
   人机验证滑块组件
   - 从左向右滑动
   - 左侧圆形图片随拖动旋转，越往右旋转角度越大
   - 前景逐渐变绿
   - 验证前显示"请拖动滑块完成验证"白色透明文字 + 流体动画
   ========================================================= */
(function () {
  'use strict';

  var DEFAULT_IMG = 'data:image/svg+xml;utf8,' + encodeURIComponent(
    '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 36 36">' +
    '<rect width="36" height="36" rx="18" fill="#1677ff"/>' +
    '<path d="M18 8a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm0 4a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm0 6c2.2 0 4 1.3 4 3v1h-8v-1c0-1.7 1.8-3 4-3z" fill="#fff"/>' +
    '</svg>'
  );

  function SliderCaptcha(el, opts) {
    this.el = typeof el === 'string' ? document.querySelector(el) : el;
    this.opts = opts || {};
    this.verified = false;
    this.imageSrc = this.opts.image || (window.GB_SITE_CONFIG && window.GB_SITE_CONFIG.captcha_image) || DEFAULT_IMG;
    this.onSuccess = this.opts.onSuccess || function () {};
    this.build();
    this.bind();
  }

  SliderCaptcha.prototype.build = function () {
    this.el.classList.add('slider-captcha');
    this.el.innerHTML =
      '<div class="slider-track"></div>' +
      '<div class="slider-tip"><span class="tip-text">请拖动滑块完成验证</span></div>' +
      '<div class="slider-handle">' +
        '<img class="slider-img" src="' + this.imageSrc + '" alt="">' +
      '</div>';
    this.track = this.el.querySelector('.slider-track');
    this.tip = this.el.querySelector('.slider-tip');
    this.handle = this.el.querySelector('.slider-handle');
    this.img = this.handle.querySelector('.slider-img');
    this.maxX = this.el.clientWidth - this.handle.offsetWidth;
    if (this.maxX <= 0) this.maxX = this.el.offsetWidth - 50;
  };

  SliderCaptcha.prototype.bind = function () {
    var self = this;
    var startX = 0, currentX = 0, dragging = false;

    function down(e) {
      if (self.verified) return;
      self.build && (self.maxX = self.el.clientWidth - self.handle.offsetWidth);
      dragging = true;
      startX = (e.touches ? e.touches[0].clientX : e.clientX) - currentX;
      self.handle.style.transition = 'none';
      self.track.style.transition = 'none';
      self.img.style.transition = 'none';
      e.preventDefault();
    }
    function move(e) {
      if (!dragging) return;
      var x = (e.touches ? e.touches[0].clientX : e.clientX) - startX;
      if (x < 0) x = 0;
      if (x > self.maxX) x = self.maxX;
      currentX = x;
      var pct = self.maxX > 0 ? (x / self.maxX) : 0;
      self.handle.style.left = x + 'px';
      self.track.style.width = (x + self.handle.offsetWidth) + 'px';
      // 旋转: 0 -> 360deg
      self.img.style.transform = 'rotate(' + (pct * 360) + 'deg)';
      // 文字渐隐
      self.tip.style.opacity = (1 - pct * 1.4);
      e.preventDefault();
    }
    function up() {
      if (!dragging) return;
      dragging = false;
      var pct = self.maxX > 0 ? (currentX / self.maxX) : 0;
      if (pct >= 0.97) {
        self.success();
      } else {
        self.reset();
      }
    }

    // 鼠标
    this.handle.addEventListener('mousedown', down);
    document.addEventListener('mousemove', move);
    document.addEventListener('mouseup', up);
    // 触摸
    this.handle.addEventListener('touchstart', down, { passive: false });
    document.addEventListener('touchmove', move, { passive: false });
    document.addEventListener('touchend', up);

    this._down = down; this._move = move; this._up = up;
  };

  SliderCaptcha.prototype.success = function () {
    this.verified = true;
    this.el.classList.add('verified');
    this.handle.classList.add('verified');
    this.handle.style.left = this.maxX + 'px';
    this.track.style.width = '100%';
    this.img.style.transform = 'rotate(360deg)';
    this.tip.style.opacity = 0;
    this.tip.querySelector('.tip-text').textContent = '验证通过';
    var self = this;
    setTimeout(function () {
      self.el.classList.add('verified');
      self.tip.style.opacity = 1;
    }, 50);
    this.onSuccess();
  };

  SliderCaptcha.prototype.reset = function () {
    var self = this;
    this.handle.style.transition = 'left 0.3s ease';
    this.track.style.transition = 'width 0.3s ease';
    this.img.style.transition = 'transform 0.3s ease';
    this.handle.style.left = '0px';
    this.track.style.width = '0px';
    this.img.style.transform = 'rotate(0deg)';
    this.tip.style.opacity = 1;
    setTimeout(function () {
      self.handle.style.transition = '';
      self.track.style.transition = '';
    }, 320);
  };

  SliderCaptcha.prototype.destroy = function () {
    document.removeEventListener('mousemove', this._move);
    document.removeEventListener('mouseup', this._up);
    document.removeEventListener('touchmove', this._move);
    document.removeEventListener('touchend', this._up);
  };

  window.SliderCaptcha = SliderCaptcha;

  // 自动初始化
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-slider-captcha]').forEach(function (el) {
      if (el._slider) return;
      var opts = {};
      if (el.dataset.image) opts.image = el.dataset.image;
      if (el.dataset.onSuccess) opts.onSuccess = new Function(el.dataset.onSuccess);
      el._slider = new SliderCaptcha(el, opts);
    });
  });
})();

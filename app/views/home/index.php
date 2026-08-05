<?php /** 首页 */
$stats = $stats ?? ['total' => 0, 'passed' => 0, 'today' => 0];
$partners = $partners ?? [];
$invalids = $invalids ?? [];
$articles = $articles ?? [];
?>
<!-- Hero -->
<section class="hero">
  <div class="container">
    <div class="hero-inner">
      <div class="hero-text">
        <h1>专业<span class="hl">ICP备案</span><br>一站式服务平台</h1>
        <p>管备云备案系统致力于为用户提供专业、高效、便捷的ICP备案服务。在线查询备案信息、提交备案申请、反馈与举报，全程数字化管理，安全可靠。</p>
        <div class="hero-actions">
          <a href="<?php echo site_url('query'); ?>" class="btn btn-primary btn-lg">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            备案查询
          </a>
          <a href="<?php echo site_url('register'); ?>" class="btn btn-lg">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
            立即注册
          </a>
        </div>
        <div class="hero-stats">
          <div class="hero-stat"><div class="num"><?php echo number_format($stats['total']); ?></div><div class="label">已备案网站</div></div>
          <div class="hero-stat"><div class="num"><?php echo number_format($stats['today']); ?></div><div class="label">今日申请</div></div>
          <div class="hero-stat"><div class="num">99.9%</div><div class="label">服务可用率</div></div>
        </div>
      </div>
      <div class="hero-visual">
        <div class="hero-card">
          <div class="row">
            <div class="icon-box"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
            <div style="flex:1"><div class="font-bold text-sm">备案安全</div><div class="text-muted text-sm">全程加密保护</div></div>
            <span class="tag tag-success"><span class="status-dot success"></span>已认证</span>
          </div>
          <div class="row">
            <div class="icon-box"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
            <div style="flex:1"><div class="font-bold text-sm">快速审核</div><div class="text-muted text-sm">24小时内响应</div></div>
            <span class="tag tag-primary">高效</span>
          </div>
          <div class="row">
            <div class="icon-box"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
            <div style="flex:1"><div class="font-bold text-sm">合规保障</div><div class="text-muted text-sm">符合工信部规范</div></div>
            <span class="tag tag-primary">合规</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 功能特色 -->
<section class="section">
  <div class="container">
    <div class="section-title">
      <h2>核心功能</h2>
      <p>全流程备案管理，安全合规高效</p>
    </div>
    <div class="feature-grid">
      <div class="feature-card">
        <div class="f-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></div>
        <h3>备案查询</h3>
        <p>支持按域名、备案号查询ICP备案信息，实时获取最新状态</p>
      </div>
      <div class="feature-card">
        <div class="f-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
        <h3>在线申请</h3>
        <p>用户中心在线提交备案申请，实时跟踪审核进度与状态</p>
      </div>
      <div class="feature-card">
        <div class="f-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
        <h3>反馈举报</h3>
        <p>意见反馈与违法举报通道，共建清朗网络空间</p>
      </div>
      <div class="feature-card">
        <div class="f-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9c2.5 0 4.76 1.02 6.39 2.66"/></svg></div>
        <h3>认证管理</h3>
        <p>企业认证、个人认证、合作伙伴申请一站式管理</p>
      </div>
    </div>
  </div>
</section>

<!-- 办理流程 -->
<section class="section bg-soft">
  <div class="container">
    <div class="section-title">
      <h2>办理流程</h2>
      <p>四步完成ICP备案申请</p>
    </div>
    <div class="steps">
      <div class="step">
        <div class="step-num">1</div>
        <h4>注册登录</h4>
        <p>注册账号并完成登录</p>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <h4>提交申请</h4>
        <p>填写网站备案信息</p>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <h4>审核处理</h4>
        <p>管理员审核备案资料</p>
      </div>
      <div class="step">
        <div class="step-num">4</div>
        <h4>备案完成</h4>
        <p>获取ICP备案号</p>
      </div>
    </div>
  </div>
</section>

<!-- 合作方轮播样式 -->
<style>
.partner-carousel { position: relative; background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; }
.pc-viewport { overflow: hidden; }
.pc-track { display: flex; transition: transform .5s ease; }
.pc-slide { min-width: 100%; box-sizing: border-box; display: flex; flex-direction: column; }
.pc-slide[data-link] { cursor: pointer; }
.pc-img { width: 100%; height: 260px; overflow: hidden; background: var(--bg-soft); }
.pc-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pc-content { padding: 18px 22px; }
.pc-content h3 { margin: 0 0 6px; font-size: 18px; color: var(--text); }
.pc-content p { margin: 0 0 14px; color: var(--text-2); }
.pc-content .pc-link { margin-top: 2px; }
.pc-arrow { position: absolute; top: 130px; transform: translateY(-50%); width: 38px; height: 38px; border-radius: var(--radius-full); background: var(--card-bg); border: 1px solid var(--border); color: var(--text); display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,.12); transition: background .2s, color .2s, border-color .2s; z-index: 2; padding: 0; }
.pc-arrow svg { width: 18px; height: 18px; }
.pc-arrow:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
.pc-prev { left: 12px; }
.pc-next { right: 12px; }
.pc-dots { display: flex; justify-content: center; gap: 8px; padding: 0 22px 16px; }
.pc-dot { width: 8px; height: 8px; border-radius: var(--radius-full); background: var(--border); cursor: pointer; transition: background .2s, width .2s; border: none; padding: 0; }
.pc-dot:hover { background: var(--text-2); }
.pc-dot.active { background: var(--primary); width: 22px; }
.partner-empty { padding: 40px 20px; text-align: center; color: var(--text-2); display: flex; flex-direction: column; align-items: center; gap: 10px; }
.partner-empty p { margin: 0; }
@media (max-width: 768px) {
  .pc-img { height: 200px; }
  .pc-arrow { top: 100px; width: 34px; height: 34px; }
  .pc-content { padding: 14px 16px; }
}
</style>

<!-- 公示信息 -->
<section class="section">
  <div class="container">
    <div class="section-title">
      <h2>公示信息</h2>
      <p>合作方公示与备案失效公示</p>
    </div>

    <?php if ($partners): ?>
    <div class="partner-carousel" id="partnerCarousel">
      <div class="pc-viewport">
        <div class="pc-track">
          <?php foreach ($partners as $p): ?>
          <div class="pc-slide" <?php echo !empty($p['link']) ? 'data-link="' . e($p['link']) . '"' : ''; ?>>
            <?php if (!empty($p['image'])): ?>
            <div class="pc-img"><img src="<?php echo asset($p['image']); ?>" alt="<?php echo e($p['title']); ?>"></div>
            <?php endif; ?>
            <div class="pc-content">
              <h3><?php echo e($p['title']); ?></h3>
              <?php if (!empty($p['content'])): ?>
              <p class="text-sm text-muted"><?php echo e($p['content']); ?></p>
              <?php endif; ?>
              <?php if (!empty($p['link'])): ?>
              <a class="btn btn-primary pc-link" href="<?php echo e($p['link']); ?>" target="_blank" rel="noopener">访问网站</a>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php if (count($partners) > 1): ?>
      <button type="button" class="pc-arrow pc-prev" aria-label="上一个合作方">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
      </button>
      <button type="button" class="pc-arrow pc-next" aria-label="下一个合作方">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </button>
      <div class="pc-dots">
        <?php foreach ($partners as $i => $p): ?>
        <button type="button" class="pc-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-i="<?php echo $i; ?>" aria-label="第 <?php echo $i + 1; ?> 个合作方"></button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="partner-carousel partner-empty">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="40" height="40"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
      <p>暂无合作方公示信息</p>
    </div>
    <?php endif; ?>

    <div class="publicity-card" style="margin-top:20px;">
      <div class="ph"><span class="status-dot danger"></span> 失效/违规公示</div>
      <div class="pb">
        <?php if ($invalids): foreach ($invalids as $p): ?>
          <div class="item"><span class="text-danger"><?php echo e($p['title']); ?></span><span class="text-muted text-sm"><?php echo e(date('Y-m-d', strtotime($p['created_at']))); ?></span></div>
        <?php endforeach; else: ?>
          <div class="item text-muted">暂无公示信息</div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</section>

<!-- 最新公告 -->
<?php if ($articles): ?>
<section class="section bg-soft">
  <div class="container">
    <div class="section-title"><h2>最新公告</h2><p>系统公告与资讯</p></div>
    <div class="card">
      <div class="card-body" style="padding:8px 0">
        <?php foreach ($articles as $a): ?>
          <a href="<?php echo site_url('article/'.$a['id']); ?>" style="display:flex;align-items:center;justify-content:space-between;padding:14px 22px;border-bottom:1px solid var(--divider);transition:background .2s;" onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background=''">
            <span style="display:flex;align-items:center;gap:10px;"><span class="status-dot info"></span> <?php echo e($a['title']); ?></span>
            <span class="text-muted text-sm"><?php echo e(date('Y-m-d', strtotime($a['created_at']))); ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="section">
  <div class="container">
    <div class="cta-band">
      <div>
        <h3>立即开始您的备案申请</h3>
        <p>注册账号，享受便捷的在线备案服务</p>
      </div>
      <a href="<?php echo site_url('register'); ?>" class="btn btn-lg">免费注册</a>
    </div>
  </div>
</section>

<!-- 合作方轮播逻辑 -->
<script>
(function(){
  var root = document.getElementById('partnerCarousel');
  if (!root) return;
  var track = root.querySelector('.pc-track');
  var slides = root.querySelectorAll('.pc-slide');
  var dots = root.querySelectorAll('.pc-dot');
  var prevBtn = root.querySelector('.pc-prev');
  var nextBtn = root.querySelector('.pc-next');
  var count = slides.length;
  if (!count || !track) return;
  var idx = 0, timer = null;
  var INTERVAL = 4000;

  function go(i){
    idx = (i + count) % count;
    track.style.transform = 'translateX(' + (-idx * 100) + '%)';
    dots.forEach(function(d, di){ d.classList.toggle('active', di === idx); });
  }
  function nextSlide(){ go(idx + 1); }
  function prevSlide(){ go(idx - 1); }
  function start(){ stop(); if (count > 1) timer = setInterval(nextSlide, INTERVAL); }
  function stop(){ if (timer) { clearInterval(timer); timer = null; } }

  if (nextBtn) nextBtn.addEventListener('click', function(){ nextSlide(); start(); });
  if (prevBtn) prevBtn.addEventListener('click', function(){ prevSlide(); start(); });
  dots.forEach(function(d, di){ d.addEventListener('click', function(){ go(di); start(); }); });

  slides.forEach(function(s){
    s.addEventListener('click', function(e){
      if (e.target.closest('a')) return;
      var link = s.getAttribute('data-link');
      if (link) window.open(link, '_blank', 'noopener');
    });
  });

  // 悬停暂停自动轮播
  root.addEventListener('mouseenter', stop);
  root.addEventListener('mouseleave', start);

  go(0);
  start();
})();
</script>

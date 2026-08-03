<?php /** 文章详情页 */
$article = $article ?? [];
$catName = $catName ?? '文章';
?>
<section class="section">
  <div class="container">
    <div class="card" style="max-width:860px;margin:0 auto;">
      <div class="card-body" style="padding:36px 40px;">
        <div style="margin-bottom:16px;">
          <span class="tag tag-primary"><?php echo e($catName); ?></span>
        </div>
        <h1 style="font-size:26px;font-weight:700;margin-bottom:14px;line-height:1.4;"><?php echo e($article['title'] ?? ''); ?></h1>
        <div style="display:flex;align-items:center;gap:16px;color:var(--text-muted);font-size:13px;margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid var(--divider);">
          <span><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><?php echo e(date('Y-m-d', strtotime($article['created_at'] ?? 'now'))); ?></span>
          <span><svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg><?php echo (int)($article['views'] ?? 0); ?> 阅读</span>
        </div>
        <div class="article-content">
          <?php echo $article['content'] ?? ''; ?>
        </div>
      </div>
    </div>
    <div style="text-align:center;margin-top:24px;">
      <a href="<?php echo site_url(); ?>" class="btn">返回首页</a>
    </div>
  </div>
</section>

<?php /** 备案公示页 */ $rows = $rows ?? []; ?>
<section class="section">
  <div class="container">
    <div class="section-title" style="margin-bottom:24px;">
      <h1>备案公示</h1>
      <p>本页展示已通过审核的备案网站公示信息，包含备案号、网址、备案用户及内容等。</p>
    </div>

    <?php if ($rows): ?>
      <div class="card">
        <div class="card-body" style="padding:0;">
          <div class="table-wrap" style="border:none;">
            <table class="table">
              <thead>
                <tr>
                  <th>备案号</th>
                  <th>网址</th>
                  <th>类型</th>
                  <th>状态</th>
                  <th>备案用户</th>
                  <th>内容</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($rows as $r): ?>
                <tr>
                  <td><span class="tag tag-primary"><?php echo e($r['icp_no'] ?: '-'); ?></span></td>
                  <td class="text-sm">
                    <?php if (!empty($r['link'])): ?>
                      <a href="<?php echo e($r['link']); ?>" target="_blank" rel="noopener"><?php echo e($r['link']); ?></a>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                  <td><span class="badge badge-info">备案公示</span></td>
                  <td><span class="badge badge-success">正常</span></td>
                  <td class="text-sm">
                    <a href="<?php echo e(site_url('u/' . (int)($r['user_id'] ?? 0))); ?>" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:8px;">
                      <?php if (!empty($r['avatar'])): ?>
                        <img src="<?php echo asset($r['avatar']); ?>" alt="" style="width:24px;height:24px;border-radius:50%;object-fit:cover;border:1px solid var(--border);">
                      <?php endif; ?>
                      <span><?php echo e($r['username'] ?: '匿名'); ?></span>
                    </a>
                  </td>
                  <td class="text-sm"><?php echo !empty($r['content']) ? e($r['content']) : '<span class="text-muted">-</span>'; ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="card">
        <div class="card-body">
          <div class="empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:64px;height:64px;margin:0 auto 12px;opacity:.4;"><path d="M9 12l2 2 4-4"/><path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"/></svg>
            <p>暂无备案公示信息</p>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

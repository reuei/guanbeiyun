<?php /** 分页组件 - 变量: $page, $size, $total, $baseUrl(含?) */
$page = $page ?? 1; $size = $size ?? 15; $total = $total ?? 0; $baseUrl = $baseUrl ?? '?';
$totalPages = max(1, ceil($total / $size));
if ($totalPages <= 1) return;
$build = function($p) use ($baseUrl) {
    $sep = strpos($baseUrl, '?') !== false ? '&' : '?';
    return $baseUrl . $sep . 'page=' . $p;
};
$start = max(1, $page - 2);
$end = min($totalPages, $page + 2);
?>
<div class="pagination">
  <a class="page-item <?php echo $page<=1?'disabled':''; ?>" href="<?php echo $page>1?$build($page-1):'javascript:;'; ?>">‹</a>
  <?php if ($start > 1): ?>
    <a class="page-item" href="<?php echo $build(1); ?>">1</a>
    <?php if ($start > 2): ?><span class="page-item disabled">...</span><?php endif; ?>
  <?php endif; ?>
  <?php for ($i = $start; $i <= $end; $i++): ?>
    <a class="page-item <?php echo $i==$page?'active':''; ?>" href="<?php echo $build($i); ?>"><?php echo $i; ?></a>
  <?php endfor; ?>
  <?php if ($end < $totalPages): ?>
    <?php if ($end < $totalPages - 1): ?><span class="page-item disabled">...</span><?php endif; ?>
    <a class="page-item" href="<?php echo $build($totalPages); ?>"><?php echo $totalPages; ?></a>
  <?php endif; ?>
  <a class="page-item <?php echo $page>=$totalPages?'disabled':''; ?>" href="<?php echo $page<$totalPages?$build($page+1):'javascript:;'; ?>">›</a>
</div>

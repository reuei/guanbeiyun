<?php /** 数据大屏 */
$stats = $stats ?? [];
?>
<div class="bigscreen">
  <div style="text-align:center;margin-bottom:24px;">
    <h1 style="font-size:28px;color:#fff;letter-spacing:4px;">管备云备案系统 · 数据大屏</h1>
    <p style="color:#5b8def;margin-top:6px;font-size:13px;">实时数据监控 <?php echo e(date('Y-m-d H:i:s')); ?></p>
  </div>
  <div class="bs-grid" style="margin-bottom:16px;">
    <div class="bs-panel">
      <div class="bs-title"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg> 用户总数</div>
      <div class="bs-num"><?php echo $stats['users'] ?? 0; ?></div>
    </div>
    <div class="bs-panel">
      <div class="bs-title"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg> 备案总数</div>
      <div class="bs-num"><?php echo $stats['filings'] ?? 0; ?></div>
    </div>
    <div class="bs-panel">
      <div class="bs-title"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg> 今日新增</div>
      <div class="bs-num"><?php echo $stats['today'] ?? 0; ?></div>
    </div>
  </div>
  <div class="bs-grid" style="margin-bottom:16px;">
    <div class="bs-panel">
      <div class="bs-title">备案审核</div>
      <div style="display:flex;flex-direction:column;gap:14px;margin-top:10px;">
        <div><div style="display:flex;justify-content:space-between;color:#c9d1d9;font-size:13px;margin-bottom:6px;"><span>待审核</span><span><?php echo $stats['filingPending'] ?? 0; ?></span></div><div style="height:6px;background:rgba(255,255,255,0.1);border-radius:3px;"><div style="height:100%;width:<?php echo min(100, ($stats['filingPending']??0)/max(1,$stats['filings']??1)*100); ?>%;background:#faad14;border-radius:3px;"></div></div></div>
        <div><div style="display:flex;justify-content:space-between;color:#c9d1d9;font-size:13px;margin-bottom:6px;"><span>已通过</span><span><?php echo $stats['filingPassed'] ?? 0; ?></span></div><div style="height:6px;background:rgba(255,255,255,0.1);border-radius:3px;"><div style="height:100%;width:<?php echo min(100, ($stats['filingPassed']??0)/max(1,$stats['filings']??1)*100); ?>%;background:#00b96b;border-radius:3px;"></div></div></div>
        <div><div style="display:flex;justify-content:space-between;color:#c9d1d9;font-size:13px;margin-bottom:6px;"><span>未通过</span><span><?php echo $stats['filingRejected'] ?? 0; ?></span></div><div style="height:6px;background:rgba(255,255,255,0.1);border-radius:3px;"><div style="height:100%;width:<?php echo min(100, ($stats['filingRejected']??0)/max(1,$stats['filings']??1)*100); ?>%;background:#ff4d4f;border-radius:3px;"></div></div></div>
      </div>
    </div>
    <div class="bs-panel">
      <div class="bs-title">工单与反馈</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px;">
        <div style="text-align:center;padding:14px;background:rgba(91,141,239,0.1);border-radius:6px;"><div style="font-size:24px;font-weight:700;color:#5b8def;"><?php echo $stats['tickets'] ?? 0; ?></div><div style="font-size:12px;color:#8b949e;margin-top:4px;">工单总数</div></div>
        <div style="text-align:center;padding:14px;background:rgba(255,77,79,0.1);border-radius:6px;"><div style="font-size:24px;font-weight:700;color:#ff7a45;"><?php echo $stats['ticketsPending'] ?? 0; ?></div><div style="font-size:12px;color:#8b949e;margin-top:4px;">待处理工单</div></div>
        <div style="text-align:center;padding:14px;background:rgba(0,185,107,0.1);border-radius:6px;"><div style="font-size:24px;font-weight:700;color:#52c41a;"><?php echo $stats['feedbacks'] ?? 0; ?></div><div style="font-size:12px;color:#8b949e;margin-top:4px;">用户反馈</div></div>
        <div style="text-align:center;padding:14px;background:rgba(250,173,20,0.1);border-radius:6px;"><div style="font-size:24px;font-weight:700;color:#faad14;"><?php echo $stats['reports'] ?? 0; ?></div><div style="font-size:12px;color:#8b949e;margin-top:4px;">违法举报</div></div>
      </div>
    </div>
    <div class="bs-panel">
      <div class="bs-title">系统状态</div>
      <div style="display:flex;flex-direction:column;gap:12px;margin-top:14px;">
        <div style="display:flex;justify-content:space-between;align-items:center;"><span style="color:#8b949e;font-size:13px;">系统运行</span><span style="color:#52c41a;font-size:13px;">● 正常</span></div>
        <div style="display:flex;justify-content:space-between;align-items:center;"><span style="color:#8b949e;font-size:13px;">数据库</span><span style="color:#52c41a;font-size:13px;">● 正常</span></div>
        <div style="display:flex;justify-content:space-between;align-items:center;"><span style="color:#8b949e;font-size:13px;">PHP版本</span><span style="color:#5b8def;font-size:13px;"><?php echo PHP_VERSION; ?></span></div>
        <div style="display:flex;justify-content:space-between;align-items:center;"><span style="color:#8b949e;font-size:13px;">系统版本</span><span style="color:#5b8def;font-size:13px;">v1.0.1</span></div>
        <div style="display:flex;justify-content:space-between;align-items:center;"><span style="color:#8b949e;font-size:13px;">服务器时间</span><span style="color:#5b8def;font-size:13px;" id="bsTime"><?php echo date('H:i:s'); ?></span></div>
      </div>
    </div>
  </div>
</div>
<script>setInterval(function(){var d=new Date();var t=d.getHours()+':'+String(d.getMinutes()).padStart(2,'0')+':'+String(d.getSeconds()).padStart(2,'0');var el=document.getElementById('bsTime');if(el)el.textContent=t;},1000);</script>

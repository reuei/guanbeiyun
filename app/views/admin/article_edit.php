<?php /** 文章编辑 */
$article = $article ?? [];
?>
<div class="panel">
  <div class="panel-head"><span class="title"><?php echo $article['id']?'编辑文章':'新建文章'; ?></span><a href="<?php echo site_url('admin/articles'); ?>" class="text-sm">← 返回列表</a></div>
  <div class="panel-body">
    <form id="artForm" onsubmit="return saveArt(event)">
      <input type="hidden" name="id" value="<?php echo (int)$article['id']; ?>">
      <div class="grid-2">
        <div class="form-group"><label class="form-label">标题 <span class="req">*</span></label><input class="form-control" name="title" value="<?php echo e($article['title']); ?>" placeholder="文章标题" required></div>
        <div class="form-group"><label class="form-label">别名(slug)</label><input class="form-control" name="slug" value="<?php echo e($article['slug']); ?>" placeholder="如 privacy (选填)"></div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label class="form-label">分类</label><select class="form-control" name="category">
          <option value="article" <?php echo ($article['category']??'')==='article'?'selected':''; ?>>系统公告</option>
          <option value="privacy" <?php echo ($article['category']??'')==='privacy'?'selected':''; ?>>隐私政策</option>
          <option value="policy" <?php echo ($article['category']??'')==='policy'?'selected':''; ?>>用户协议</option>
        </select></div>
        <div class="form-group"><label class="form-label">状态</label><select class="form-control" name="status">
          <option value="1" <?php echo ($article['status']??1)==1?'selected':''; ?>>发布</option>
          <option value="0" <?php echo ($article['status']??1)==0?'selected':''; ?>>草稿</option>
        </select></div>
      </div>
      <div class="form-group">
        <label class="form-label">内容 (支持HTML) <span class="req">*</span></label>
        <div style="border:1px solid var(--border-2);border-radius:6px;overflow:hidden;">
          <div style="display:flex;gap:4px;padding:6px;background:var(--bg-soft);border-bottom:1px solid var(--border);flex-wrap:wrap;">
            <button type="button" class="btn btn-sm" onclick="execCmd('bold')"><b>B</b></button>
            <button type="button" class="btn btn-sm" onclick="execCmd('italic')"><i>I</i></button>
            <button type="button" class="btn btn-sm" onclick="execCmd('underline')"><u>U</u></button>
            <button type="button" class="btn btn-sm" onclick="execCmd('insertUnorderedList')">• 列表</button>
            <button type="button" class="btn btn-sm" onclick="execCmd('formatBlock','<h3>')">标题</button>
            <button type="button" class="btn btn-sm" onclick="execCmd('formatBlock','<p>')">段落</button>
            <button type="button" class="btn btn-sm" onclick="addLink()">链接</button>
          </div>
          <textarea class="form-control" name="content" id="contentArea" style="border:none;border-radius:0;min-height:340px;font-family:monospace;" placeholder="输入文章内容(支持HTML)..." required><?php echo e($article['content']); ?></textarea>
        </div>
      </div>
      <div style="text-align:right;">
        <a href="<?php echo site_url('admin/articles'); ?>" class="btn">取消</a>
        <button type="submit" class="btn btn-primary" id="saveBtn">保存文章</button>
      </div>
    </form>
  </div>
</div>
<script>
function execCmd(cmd,val){document.getElementById('contentArea').focus();var t=document.getElementById('contentArea');var s=t.selectionStart,e=t.selectionEnd;var sel=t.value.substring(s,e);var wrap={bold:['<b>','</b>'],italic:['<i>','</i>'],underline:['<u>','</u>']}[cmd];if(wrap){t.value=t.value.substring(0,s)+wrap[0]+sel+wrap[1]+t.value.substring(e);}else if(cmd==='insertUnorderedList'){t.value=t.value.substring(0,s)+'<ul><li>'+sel+'</li></ul>'+t.value.substring(e);}else if(cmd==='formatBlock'){t.value=t.value.substring(0,s)+val+sel+(val==='<h3>'?'</h3>':'</p>')+t.value.substring(e);}}
function addLink(){var u=prompt('链接地址:');if(u){var t=document.getElementById('contentArea');var s=t.selectionStart,e=t.selectionEnd;t.value=t.value.substring(0,s)+'<a href="'+u+'">'+(t.value.substring(s,e)||u)+'</a>'+t.value.substring(e);}}
function saveArt(e){e.preventDefault();var d={};new FormData(e.target).forEach(function(v,k){d[k]=v;});
  var b=document.getElementById('saveBtn');b.disabled=true;b.innerHTML='<span class="gb-loading gb-loading-sm"></span> 保存中';
  gbAjax({method:'POST',url:'<?php echo site_url('admin/article/save'); ?>',data:d,success:function(r){if(r.code===0){gbToast.success(r.msg);if(r.data.redirect)setTimeout(function(){location.href=r.data.redirect;},600);}},complete:function(){b.disabled=false;b.innerHTML='保存文章';}});return false;}
</script>

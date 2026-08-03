<?php
/** 文章控制器 */
class ArticleController extends Controller
{
    public function show($id)
    {
        try {
            $article = db()->queryOne("SELECT * FROM " . db()->table('articles') . " WHERE id = ? OR slug = ? LIMIT 1", [$id, $id]);
            if (!$article) {
                http_response_code(404);
                require GB_ROOT . '/app/views/errors/404.php';
                return;
            }
            db()->execute("UPDATE " . db()->table('articles') . " SET views = views + 1 WHERE id = ?", [$article['id']]);
            $article['views'] += 1;
        } catch (Throwable $e) {
            $article = ['id' => 0, 'title' => '文章不存在', 'content' => '<p>文章内容暂时无法加载。</p>', 'created_at' => date('Y-m-d H:i:s')];
        }
        $catNames = ['article' => '系统公告', 'privacy' => '隐私政策', 'policy' => '用户协议'];
        $this->view('article/show', [
            'pageTitle' => ($article['title'] ?? '文章') . ' - ' . site_config('site_name'),
            'active' => '',
            'article' => $article,
            'catName' => $catNames[$article['category'] ?? 'article'] ?? '文章',
        ]);
    }
}

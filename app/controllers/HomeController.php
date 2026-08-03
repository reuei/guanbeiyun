<?php
/**
 * 首页控制器
 */
class HomeController extends Controller
{
    public function index()
    {
        // 统计数据
        $stats = ['total' => 0, 'passed' => 0, 'today' => 0];
        try {
            $stats['total'] = db()->count('filings', "status = 1");
            $stats['passed'] = db()->count('filings', "status = 1");
            $stats['today'] = db()->count('filings', "DATE(created_at) = CURDATE()");
        } catch (Throwable $e) {}

        // 公示信息
        $partners = [];
        $invalids = [];
        try {
            $partners = db()->query("SELECT * FROM " . db()->table('publicity') . " WHERE type='partner' AND status=1 ORDER BY sort DESC,id DESC LIMIT 8");
            $invalids = db()->query("SELECT * FROM " . db()->table('publicity') . " WHERE type='invalid' AND status=1 ORDER BY id DESC LIMIT 8");
        } catch (Throwable $e) {}

        // 公告文章
        $articles = [];
        try {
            $articles = db()->query("SELECT id,title,slug,created_at FROM " . db()->table('articles') . " WHERE category='article' AND status=1 ORDER BY id DESC LIMIT 5");
        } catch (Throwable $e) {}

        $this->view('home/index', [
            'pageTitle' => site_config('site_title', '管备云备案系统'),
            'active' => 'home',
            'stats' => $stats,
            'partners' => $partners,
            'invalids' => $invalids,
            'articles' => $articles,
        ]);
    }
}

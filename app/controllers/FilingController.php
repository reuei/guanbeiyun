<?php
/**
 * 备案查询控制器
 */
class FilingController extends Controller
{
    public function query()
    {
        $results = null;
        $keyword = trim(input('q', ''));
        if ($keyword !== '') {
            try {
                $like = "%$keyword%";
                $results = db()->query(
                    "SELECT f.icp_no, f.site_name, f.site_domain, f.owner_name, f.owner_type, f.created_at, f.status
                     FROM " . db()->table('filings') . " f
                     WHERE f.status = 1 AND (f.site_domain LIKE ? OR f.icp_no LIKE ? OR f.owner_name LIKE ? OR f.site_name LIKE ?)
                     ORDER BY f.id DESC LIMIT 50",
                    [$like, $like, $like, $like]
                );
            } catch (Throwable $e) {
                $results = [];
            }
        }
        $this->view('filing/query', [
            'pageTitle' => '备案查询 - ' . site_config('site_name'),
            'active' => 'query',
            'results' => $results,
            'keyword' => $keyword,
        ]);
    }

    public function doQuery()
    {
        $keyword = trim(input('q', ''));
        $captcha = (int)input('captcha_verified', 0);
        if (!$captcha) fail('请先完成人机验证');
        if (!$keyword) fail('请输入查询关键词');
        try {
            $like = "%$keyword%";
            $rows = db()->query(
                "SELECT icp_no, site_name, site_domain, owner_name, owner_type, created_at
                 FROM " . db()->table('filings') . "
                 WHERE status = 1 AND (site_domain LIKE ? OR icp_no LIKE ? OR owner_name LIKE ? OR site_name LIKE ?)
                 ORDER BY id DESC LIMIT 50",
                [$like, $like, $like, $like]
            );
            ok(['list' => $rows], '查询成功');
        } catch (Throwable $e) {
            fail('查询失败');
        }
    }
}

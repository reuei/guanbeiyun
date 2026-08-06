<?php
/**
 * 备案查询控制器
 */

function get_filing_info_url($icp_no)
{
    $base = site_config('filing_info_url', 'http://icp.uiyoi.icu/');
    return rtrim($base, '/') . '/' . urlencode($icp_no);
}

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

    public function infoPage($icp_no)
    {
        $icp_no = trim(urldecode($icp_no));
        if (!$icp_no) {
            $this->show404();
            return;
        }

        // 修复404: 同时按纯数字和完整备案号查询
        $pureNo = preg_replace('/[^\d]/', '', $icp_no);
        try {
            $filing = db()->queryOne(
                "SELECT f.*, u.username, u.email, u.phone, u.avatar, u.certifications as user_certifications
                 FROM " . db()->table('filings') . " f
                 LEFT JOIN " . db()->table('users') . " u ON u.id = f.user_id
                 WHERE f.icp_no = ? OR f.icp_no = ? OR f.icp_no LIKE ? LIMIT 1",
                [$icp_no, '管ICP备' . $pureNo . '号', '%' . $pureNo . '%']
            );
        } catch (Throwable $e) {
            $filing = null;
        }

        if (!$filing) {
            $this->show404();
            return;
        }

        $prefixImage = null;
        try {
            $prefixImage = db()->queryOne(
                "SELECT * FROM " . db()->table('icp_images') . " WHERE status=1 ORDER BY sort DESC, id ASC LIMIT 1"
            );
        } catch (Throwable $e) {}

        $certifications = [];
        if (!empty($filing['user_id'])) {
            $certifications = user_certifications($filing['user_id']);
        }

        // v6: 获取底部代码和盖章
        $footerCode = site_config('footer_code', '');
        $sealImage = site_config('filing_seal_image', '');

        $this->view('filing/info', [
            'pageTitle' => '管ICP备案信息公示 - ' . $icp_no,
            'active' => 'query',
            'filing' => $filing,
            'prefixImage' => $prefixImage,
            'certifications' => $certifications,
            'footerCode' => $footerCode,
            'sealImage' => $sealImage,
        ]);
    }

    private function show404()
    {
        http_response_code(404);
        $this->view('errors/404', [
            'pageTitle' => '404 页面不存在',
            'active' => '',
        ]);
    }
}

<?php
/**
 * 公示页控制器
 */
class PublicityController extends Controller
{
    /** 备案公示页 GET /publicity/filing */
    public function filing()
    {
        $rows = [];
        try {
            $rows = db()->query(
                "SELECT p.*, u.username, u.avatar FROM "
                . db()->table('publicity') . " p LEFT JOIN "
                . db()->table('users') . " u ON u.id=p.user_id "
                . "WHERE p.type='filing' AND p.status=1 ORDER BY p.sort DESC, p.id DESC"
            );
        } catch (Throwable $e) {
            $rows = [];
        }
        $this->view('publicity/filing', [
            'pageTitle' => '备案公示 - ' . site_config('site_name'),
            'active'    => 'publicity-filing',
            'rows'      => $rows,
        ]);
    }

    /** 失效网站公示页 GET /publicity/invalid */
    public function invalid()
    {
        $rows = [];
        try {
            $rows = db()->query(
                "SELECT p.*, u.username, u.avatar FROM "
                . db()->table('publicity') . " p LEFT JOIN "
                . db()->table('users') . " u ON u.id=p.user_id "
                . "WHERE p.type='invalid' AND p.status=1 ORDER BY p.sort DESC, p.id DESC"
            );
        } catch (Throwable $e) {
            $rows = [];
        }
        $this->view('publicity/invalid', [
            'pageTitle' => '失效网站公示 - ' . site_config('site_name'),
            'active'    => 'publicity-invalid',
            'rows'      => $rows,
        ]);
    }
}

<?php
/**
 * API 控制器 - 提供数据接口
 */
class ApiController extends Controller
{
    /** 公共统计接口 */
    public function stats()
    {
        $data = ['total' => 0, 'passed' => 0, 'today' => 0];
        try {
            $data['total'] = db()->count('filings', 'status = 1');
            $data['passed'] = $data['total'];
            $data['today'] = db()->count('filings', 'DATE(created_at) = CURDATE()');
        } catch (Throwable $e) {}
        ok($data);
    }

    /** 用户通知接口 */
    public function notifications()
    {
        $uid = current_user()['id'] ?? 0;
        if (!$uid) fail('请先登录', 401);
        try {
            $rows = db()->query("SELECT * FROM " . db()->table('notifications') . " WHERE user_id=0 OR user_id=? ORDER BY id DESC LIMIT 20", [$uid]);
            ok(['list' => $rows]);
        } catch (Throwable $e) {
            ok(['list' => []]);
        }
    }
}

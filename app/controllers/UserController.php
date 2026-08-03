<?php
/**
 * 用户中心控制器
 */
class UserController extends Controller
{
    public function __construct()
    {
        $path = request_path();
        if (strpos($path, '/user') !== 0) return;
        if (!is_logged_in()) {
            if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'json') !== false || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
                fail('请先登录', 401);
            }
            redirect(site_url('login'));
        }
    }

    public function index()
    {
        redirect(site_url('user/dashboard'));
    }

    /** 总概览 */
    public function dashboard()
    {
        $u = current_user();
        $stats = ['filings' => 0, 'pending' => 0, 'passed' => 0, 'feedbacks' => 0, 'tickets' => 0];
        try {
            $stats['filings'] = db()->count('filings', 'user_id = ?', [$u['id']]);
            $stats['pending'] = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('filings') . " WHERE user_id=? AND status=0", [$u['id']]);
            $stats['passed'] = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('filings') . " WHERE user_id=? AND status=1", [$u['id']]);
            $stats['feedbacks'] = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('feedbacks') . " WHERE user_id=?", [$u['id']]);
            $stats['tickets'] = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('tickets') . " WHERE user_id=?", [$u['id']]);
        } catch (Throwable $e) {}
        $notifications = [];
        try {
            $notifications = db()->query("SELECT * FROM " . db()->table('notifications') . " WHERE user_id=0 OR user_id=? ORDER BY id DESC LIMIT 5", [$u['id']]);
        } catch (Throwable $e) {}
        $this->view('user/dashboard', [
            'pageTitle' => '总概览', 'crumb' => '工作台 / 总概览',
            'activeMenu' => 'workbench', 'activeSub' => 'uc-dashboard',
            'stats' => $stats, 'notifications' => $notifications,
        ], 'user');
    }

    /** 备案申请管理 */
    public function filings()
    {
        $u = current_user();
        [$page, $size, $offset] = page_params();
        $status = input('status', '');
        $where = 'user_id = ?'; $params = [$u['id']];
        if ($status !== '') { $where .= " AND status = ?"; $params[] = (int)$status; }
        $total = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('filings') . " WHERE $where", $params);
        $rows = db()->query("SELECT * FROM " . db()->table('filings') . " WHERE $where ORDER BY id DESC LIMIT $offset,$size", $params);
        $this->view('user/filings', [
            'pageTitle' => '备案申请管理', 'crumb' => '备案管理 / 备案申请',
            'activeMenu' => 'filing', 'activeSub' => 'uc-filings',
            'rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size, 'status' => $status,
        ], 'user');
    }

    public function applyFiling()
    {
        $u = current_user();
        $data = [
            'user_id' => $u['id'],
            'site_name' => trim(input('site_name', '')),
            'site_domain' => trim(input('site_domain', '')),
            'site_url' => trim(input('site_url', '')),
            'owner_name' => trim(input('owner_name', '')),
            'owner_type' => (int)input('owner_type', 1),
            'owner_id' => trim(input('owner_id', '')),
            'owner_phone' => trim(input('owner_phone', '')),
            'owner_email' => trim(input('owner_email', '')),
            'server_ip' => trim(input('server_ip', '')),
            'content_type' => trim(input('content_type', '')),
            'language' => trim(input('language', '中文')),
            'remark' => trim(input('remark', '')),
            'status' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        if (!$data['site_name'] || !$data['site_domain'] || !$data['owner_name']) fail('网站名称、域名、主办单位必填');
        if (!preg_match('/^([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/', $data['site_domain'])) fail('域名格式不正确');
        db()->insert('filings', $data);
        log_op("提交备案申请: {$data['site_name']} ({$data['site_domain']})");
        ok([], '备案申请已提交，等待审核');
    }

    /** 反馈与举报管理 */
    public function feedbackList()
    {
        $u = current_user();
        [$page, $size, $offset] = page_params();
        $total = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('feedbacks') . " WHERE user_id=?", [$u['id']]);
        $rows = db()->query("SELECT * FROM " . db()->table('feedbacks') . " WHERE user_id=? ORDER BY id DESC LIMIT $offset,$size", [$u['id']]);
        $this->view('user/feedback', [
            'pageTitle' => '反馈与举报', 'crumb' => '备案管理 / 反馈与举报',
            'activeMenu' => 'filing', 'activeSub' => 'uc-feedback',
            'rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size,
        ], 'user');
    }

    /** 工单管理 */
    public function tickets()
    {
        $u = current_user();
        [$page, $size, $offset] = page_params();
        $total = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('tickets') . " WHERE user_id=?", [$u['id']]);
        $rows = db()->query("SELECT * FROM " . db()->table('tickets') . " WHERE user_id=? ORDER BY id DESC LIMIT $offset,$size", [$u['id']]);
        $this->view('user/tickets', [
            'pageTitle' => '工单管理', 'crumb' => '工单管理 / 我的工单',
            'activeMenu' => 'ticket', 'activeSub' => 'uc-tickets',
            'rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size,
        ], 'user');
    }

    public function createTicket()
    {
        $u = current_user();
        $title = trim(input('title', ''));
        $category = input('category', 'general');
        $priority = (int)input('priority', 1);
        $content = trim(input('content', ''));
        if (!$title || !$content) fail('标题和内容必填');
        $id = db()->insert('tickets', [
            'user_id' => $u['id'], 'title' => $title, 'category' => $category, 'priority' => $priority,
            'status' => 0, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        db()->insert('ticket_replies', ['ticket_id' => $id, 'user_id' => $u['id'], 'role' => 'user', 'content' => $content, 'created_at' => date('Y-m-d H:i:s')]);
        log_op("创建工单: {$title}");
        ok([], '工单已提交');
    }

    public function replyTicket()
    {
        $u = current_user();
        $id = (int)input('id', 0);
        $content = trim(input('content', ''));
        if (!$content) fail('请输入回复内容');
        $ticket = db()->queryOne("SELECT * FROM " . db()->table('tickets') . " WHERE id=? AND user_id=?", [$id, $u['id']]);
        if (!$ticket) fail('工单不存在');
        db()->insert('ticket_replies', ['ticket_id' => $id, 'user_id' => $u['id'], 'role' => 'user', 'content' => $content, 'created_at' => date('Y-m-d H:i:s')]);
        db()->update('tickets', ['status' => 0, 'updated_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $id]);
        ok([], '回复成功');
    }

    /** 个人信息配置 */
    public function profile()
    {
        $u = current_user();
        // 重新查询最新数据
        $user = db()->queryOne("SELECT * FROM " . db()->table('users') . " WHERE id = ?", [$u['id']]);
        $this->view('user/profile', [
            'pageTitle' => '信息配置', 'crumb' => '用户配置 / 信息配置',
            'activeMenu' => 'settings', 'activeSub' => 'uc-profile',
            'user' => $user,
        ], 'user');
    }

    public function updateProfile()
    {
        $u = current_user();
        $username = trim(input('username', ''));
        $phone = trim(input('phone', ''));
        $email = trim(input('email', ''));
        $newPass = input('new_password', '');
        if (!$username) fail('用户名不能为空');
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) fail('邮箱格式不正确');
        if ($phone && !preg_match('/^1[3-9]\d{9}$/', $phone)) fail('手机号格式不正确');
        $data = ['username' => $username, 'phone' => $phone, 'email' => $email, 'updated_at' => date('Y-m-d H:i:s')];
        if ($newPass) {
            if (strlen($newPass) < 6) fail('新密码至少6位');
            $data['password'] = hash_password($newPass);
        }
        db()->update('users', $data, 'id = :id', ['id' => $u['id']]);
        // 更新 session
        $updated = array_merge($u, $data);
        unset($updated['password']);
        $_SESSION['gb_user'] = $updated;
        log_op('更新个人信息');
        ok([], '保存成功');
    }

    public function uploadAvatar()
    {
        $u = current_user();
        if (empty($_FILES['file'])) fail('请选择文件');
        $file = $_FILES['file'];
        if ($file['error'] !== UPLOAD_ERR_OK) fail('上传失败');
        if ($file['size'] > config('upload.max_size')) fail('文件过大');
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, config('upload.allow'))) fail('不支持的文件类型');
        $dir = config('upload.path') . '/avatar';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $name = 'avatar_' . $u['id'] . '_' . time() . '.' . $ext;
        $path = $dir . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $path)) fail('保存失败');
        $rel = 'uploads/avatar/' . $name;
        db()->update('users', ['avatar' => $rel], 'id = :id', ['id' => $u['id']]);
        $_SESSION['gb_user']['avatar'] = $rel;
        ok(['url' => $rel, 'full' => asset($rel)], '上传成功');
    }

    /** 认证管理 */
    public function certification()
    {
        $u = current_user();
        [$page, $size, $offset] = page_params();
        $total = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('applications') . " WHERE user_id=? AND type IN ('enterprise','personal')", [$u['id']]);
        $rows = db()->query("SELECT * FROM " . db()->table('applications') . " WHERE user_id=? AND type IN ('enterprise','personal') ORDER BY id DESC LIMIT $offset,$size", [$u['id']]);
        $this->view('user/certification', [
            'pageTitle' => '认证管理', 'crumb' => '用户配置 / 认证管理',
            'activeMenu' => 'settings', 'activeSub' => 'uc-cert',
            'rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size,
        ], 'user');
    }

    public function applyCert()
    {
        $u = current_user();
        $type = input('type', 'personal');
        if (!in_array($type, ['enterprise', 'personal'])) fail('类型无效');
        $data = [
            'user_id' => $u['id'],
            'type' => $type,
            'name' => trim(input('name', '')),
            'id_card' => trim(input('id_card', '')),
            'phone' => trim(input('phone', '')),
            'email' => trim(input('email', '')),
            'company' => trim(input('company', '')),
            'license_no' => trim(input('license_no', '')),
            'intro' => trim(input('intro', '')),
            'status' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        if (!$data['name']) fail('请输入名称');
        db()->insert('applications', $data);
        log_op("提交{$type}认证申请: {$data['name']}");
        ok([], '认证申请已提交');
    }

    /** 合作伙伴申请 */
    public function partner()
    {
        $u = current_user();
        [$page, $size, $offset] = page_params();
        $total = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('applications') . " WHERE user_id=? AND type='partner'", [$u['id']]);
        $rows = db()->query("SELECT * FROM " . db()->table('applications') . " WHERE user_id=? AND type='partner' ORDER BY id DESC LIMIT $offset,$size", [$u['id']]);
        $this->view('user/partner', [
            'pageTitle' => '合作伙伴申请', 'crumb' => '用户配置 / 合作伙伴申请',
            'activeMenu' => 'settings', 'activeSub' => 'uc-partner',
            'rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size,
        ], 'user');
    }

    public function applyPartner()
    {
        $u = current_user();
        $data = [
            'user_id' => $u['id'],
            'type' => 'partner',
            'name' => trim(input('name', '')),
            'company' => trim(input('company', '')),
            'phone' => trim(input('phone', '')),
            'email' => trim(input('email', '')),
            'contact' => trim(input('contact', '')),
            'intro' => trim(input('intro', '')),
            'status' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        if (!$data['name']) fail('请输入名称');
        db()->insert('applications', $data);
        log_op("提交合作伙伴申请: {$data['name']}");
        ok([], '申请已提交');
    }

    /** 用户日志 */
    public function logs()
    {
        $u = current_user();
        [$page, $size, $offset] = page_params();
        $total = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('logs') . " WHERE user_id=?", [$u['id']]);
        $rows = db()->query("SELECT * FROM " . db()->table('logs') . " WHERE user_id=? ORDER BY id DESC LIMIT $offset,$size", [$u['id']]);
        $this->view('user/logs', [
            'pageTitle' => '我的日志', 'crumb' => '日志管理 / 我的日志',
            'activeMenu' => 'logs', 'activeSub' => 'uc-logs',
            'rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size,
        ], 'user');
    }
}

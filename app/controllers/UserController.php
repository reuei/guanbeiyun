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
        // v3 两步备案: 必要信息(邮箱/手机号/网址/网站名称/备注) + 个人/企业资质
        $ownerType = (int)input('owner_type', 1);
        $ownerName = trim(input('owner_name', ''));
        $ownerId   = trim(input('owner_id', ''));
        $ownerPhone = trim(input('owner_phone', ''));
        $ownerEmail = trim(input('owner_email', ''));
        $ownerAddress = trim(input('owner_address', ''));
        $licenseImg = trim(input('license_img', ''));
        $siteUrl = trim(input('site_url', ''));

        // 基础必填
        if (!$ownerEmail) fail('邮箱必填');
        if (!filter_var($ownerEmail, FILTER_VALIDATE_EMAIL)) fail('邮箱格式不正确');
        if (!$ownerPhone) fail('手机号必填');
        if (!preg_match('/^1[3-9]\d{9}$/', $ownerPhone)) fail('手机号格式不正确');
        if (!$siteUrl) fail('网址必填');
        if (!$ownerName) fail($ownerType === 2 ? '姓名必填' : '企业名称必填');

        // 企业备案要求
        if ($ownerType === 1) {
            if (!$ownerAddress) fail('企业地址必填');
            if (!$licenseImg) fail('请上传企业资质/营业执照图片');
        }

        $data = [
            'user_id' => $u['id'],
            'site_name' => trim(input('site_name', '')),
            'site_domain' => trim(input('site_domain', '')),
            'site_url' => $siteUrl,
            'owner_name' => $ownerName,
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'owner_phone' => $ownerPhone,
            'owner_email' => $ownerEmail,
            'owner_address' => $ownerAddress,
            'license_img' => $licenseImg,
            'server_ip' => trim(input('server_ip', '')),
            'content_type' => trim(input('content_type', '')),
            'language' => trim(input('language', '中文')),
            'remark' => trim(input('remark', '')),
            'status' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        if (!$data['site_name']) fail('网站名称必填');
        if (!$data['site_domain']) fail('网站域名必填');
        if (!preg_match('/^([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/', $data['site_domain'])) fail('域名格式不正确');
        // 备案号由后台审核通过后自动分配 (管ICP备xxxxxxxx号)
        db()->insert('filings', $data);
        log_op("提交备案申请: {$data['site_name']} ({$data['site_domain']})");
        // v4: 自动通知管理员有新备案申请
        send_admin_notification('新备案申请', "用户 {$u['username']} 提交了新备案申请：{$data['site_name']} ({$data['site_domain']})", 'filing');
        ok([], '备案申请已提交，等待审核。审核通过后将自动分配备案号 (格式: 管ICP备xxxxxxxx号)');
    }

    /** 用户查看自己的备案详情 */
    public function filingDetail()
    {
        $u = current_user();
        $id = (int)input('id', 0);
        $f = db()->queryOne("SELECT * FROM " . db()->table('filings') . " WHERE id = ? AND user_id = ?", [$id, $u['id']]);
        if (!$f) fail('备案记录不存在');
        ok(['filing' => $f]);
    }

    /** 用户查看自己的反馈详情 */
    public function feedbackDetail()
    {
        $u = current_user();
        $id = (int)input('id', 0);
        $fb = db()->queryOne("SELECT * FROM " . db()->table('feedbacks') . " WHERE id = ? AND user_id = ?", [$id, $u['id']]);
        if (!$fb) fail('记录不存在');
        ok(['feedback' => $fb]);
    }

    /** 上传背景图 */
    public function uploadBg()
    {
        $u = current_user();
        if (empty($_FILES['file'])) fail('请选择文件');
        $file = $_FILES['file'];
        if ($file['error'] !== UPLOAD_ERR_OK) fail('上传失败');
        if ($file['size'] > config('upload.max_size')) fail('文件过大');
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, config('upload.allow'))) fail('不支持的文件类型');
        $dir = config('upload.path') . '/bg';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $name = 'bg_' . $u['id'] . '_' . time() . '.' . $ext;
        $path = $dir . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $path)) fail('保存失败');
        $rel = 'uploads/bg/' . $name;
        db()->update('users', ['bg_image' => $rel], 'id = :id', ['id' => $u['id']]);
        $_SESSION['gb_user']['bg_image'] = $rel;
        ok(['url' => $rel, 'full' => asset($rel)], '上传成功');
    }

    /** 上传企业资质图片 (备案用) */
    public function uploadLicense()
    {
        $u = current_user();
        if (empty($_FILES['file'])) fail('请选择文件');
        $file = $_FILES['file'];
        if ($file['error'] !== UPLOAD_ERR_OK) fail('上传失败');
        if ($file['size'] > config('upload.max_size')) fail('文件过大');
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, config('upload.allow'))) fail('不支持的文件类型');
        $dir = config('upload.path') . '/license';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $name = 'lic_' . $u['id'] . '_' . time() . '.' . $ext;
        $path = $dir . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $path)) fail('保存失败');
        $rel = 'uploads/license/' . $name;
        ok(['url' => $rel, 'full' => asset($rel)], '上传成功');
    }

    /** 通知列表页 */
    public function notifications()
    {
        $u = current_user();
        [$page, $size, $offset] = page_params();
        $total = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('notifications') . " WHERE user_id=0 OR user_id=?", [$u['id']]);
        $rows = db()->query("SELECT * FROM " . db()->table('notifications') . " WHERE user_id=0 OR user_id=? ORDER BY id DESC LIMIT $offset,$size", [$u['id']]);
        // 标记已读状态
        $readIds = [];
        try {
            $reads = db()->query("SELECT notification_id FROM " . db()->table('notification_reads') . " WHERE user_id=?", [$u['id']]);
            foreach ($reads as $r) $readIds[$r['notification_id']] = 1;
        } catch (Throwable $e) {}
        foreach ($rows as &$r) {
            $r['is_read'] = ($r['user_id'] == $u['id']) ? $r['is_read'] : (isset($readIds[$r['id']]) ? 1 : 0);
        }
        unset($r);
        $this->view('user/notifications', [
            'pageTitle' => '消息通知', 'crumb' => '工作台 / 消息通知',
            'activeMenu' => 'workbench', 'activeSub' => 'uc-notifications',
            'rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size,
        ], 'user');
    }

    public function readNotification()
    {
        $u = current_user();
        $id = (int)input('id', 0);
        $n = db()->queryOne("SELECT * FROM " . db()->table('notifications') . " WHERE id = ?", [$id]);
        if (!$n) fail('通知不存在');
        if ($n['user_id'] == $u['id']) {
            db()->update('notifications', ['is_read' => 1], 'id = :id', ['id' => $id]);
        } else {
            // 全体通知, 写入 reads 表
            try {
                db()->insert('notification_reads', ['notification_id' => $id, 'user_id' => $u['id'], 'read_at' => date('Y-m-d H:i:s')]);
            } catch (Throwable $e) {}
        }
        ok([], '已标记已读');
    }

    public function readAllNotifications()
    {
        $u = current_user();
        $p = db()->prefix();
        db()->execute("UPDATE {$p}notifications SET is_read=1 WHERE user_id=?", [$u['id']]);
        // 全体通知写入 reads
        $rows = db()->query("SELECT id FROM {$p}notifications WHERE user_id=0");
        foreach ($rows as $r) {
            try {
                db()->insert('notification_reads', ['notification_id' => $r['id'], 'user_id' => $u['id'], 'read_at' => date('Y-m-d H:i:s')]);
            } catch (Throwable $e) {}
        }
        ok([], '已全部标记已读');
    }

    public function unreadCount()
    {
        $u = current_user();
        ok(['count' => unread_notification_count($u['id'])]);
    }

    /** 申请账号注销 (一个用户一个月只能一次) */
    public function applyDeletion()
    {
        $u = current_user();
        $reason = trim(input('reason', ''));
        if (!$reason) fail('请填写注销理由');
        if (mb_strlen($reason) < 5) fail('注销理由至少5个字');
        // 一个月内只能申请一次
        $p = db()->prefix();
        $recent = db()->queryOne(
            "SELECT id, created_at FROM {$p}account_deletions WHERE user_id = ? AND created_at >= (NOW() - INTERVAL 1 MONTH) ORDER BY id DESC LIMIT 1",
            [$u['id']]
        );
        if ($recent) fail('一个用户一个月只能申请一次注销，最近申请时间：' . $recent['created_at']);
        db()->insert('account_deletions', [
            'user_id' => $u['id'],
            'reason' => $reason,
            'status' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        log_op('提交账号注销申请');
        // v4: 自动通知管理员有新注销申请
        send_admin_notification('新账号注销申请', "用户 {$u['username']} 提交了账号注销申请", 'account');
        ok([], '注销申请已提交，等待审核');
    }

    /** 公开个人中心 GET /u/{id} */
    public function profileView($id)
    {
        $id = (int)$id;
        if ($id <= 0) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }
        $user = db()->queryOne("SELECT id, username, avatar, bg_image, bio, email, phone, created_at FROM " . db()->table('users') . " WHERE id = ? AND status = 1", [$id]);
        if (!$user) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }
        // v4: 拉黑后不可见该用户
        $viewer = current_user();
        if ($viewer && (int)$viewer['id'] !== $id) {
            if (is_blocked_by($viewer['id'], $id)) {
                http_response_code(403);
                echo '<section class="section"><div class="container"><div class="empty">您已拉黑该用户，无法查看</div></div></section>';
                return;
            }
            if (is_blocked_by($id, $viewer['id'])) {
                http_response_code(403);
                echo '<section class="section"><div class="container"><div class="empty">该用户已设置隐私，无法查看</div></div></section>';
                return;
            }
        }
        $certs = user_certifications($id);
        // 用户已通过备案
        $filings = [];
        try {
            $filings = db()->query("SELECT icp_no, site_name, site_domain, site_url FROM " . db()->table('filings') . " WHERE user_id = ? AND status = 1 ORDER BY id DESC", [$id]);
        } catch (Throwable $e) {}
        $this->view('user/profile_view', [
            'pageTitle' => $user['username'] . ' 的个人中心',
            'active' => 'profile',
            'profileUser' => $user,
            'certs' => $certs,
            'filings' => $filings,
            'hitokoto' => hitokoto(),
        ]);
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
        $allowedCategories = ['general', 'filing', 'account', 'bug', 'suggestion', 'other'];
        if (!in_array($category, $allowedCategories, true)) {
            $category = 'general';
        }
        $priority = (int)input('priority', 1);
        $content = trim(strip_tags(input('content', '')));
        if (!$title || !$content) fail('标题和内容必填');
        $id = db()->insert('tickets', [
            'user_id' => $u['id'], 'title' => $title, 'category' => $category, 'priority' => $priority,
            'status' => 0, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        if (!$id) {
            $row = db()->queryOne("SELECT LAST_INSERT_ID() AS id");
            $id = $row ? (int)$row['id'] : 0;
        }
        if ($id) {
            db()->insert('ticket_replies', ['ticket_id' => $id, 'user_id' => $u['id'], 'role' => 'user', 'content' => $content, 'created_at' => date('Y-m-d H:i:s')]);
        }
        log_op("创建工单: {$title}");
        send_admin_notification('新工单提交', "用户 {$u['username']} 提交了新工单：{$title}", 'ticket');
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
        // v4: 自动通知管理员工单有新回复
        send_admin_notification('工单新回复', "用户 {$u['username']} 回复了工单「{$ticket['title']}」", 'ticket');
        ok([], '回复成功');
    }

    /** 用户查看自己的工单详情 (仅限本人工单) */
    public function ticketDetail()
    {
        $u = current_user();
        $id = (int)input('id', 0);
        $ticket = db()->queryOne("SELECT * FROM " . db()->table('tickets') . " WHERE id = ? AND user_id = ?", [$id, $u['id']]);
        if (!$ticket) fail('工单不存在');
        $replies = db()->query("SELECT * FROM " . db()->table('ticket_replies') . " WHERE ticket_id = ? ORDER BY id ASC", [$id]);
        array_unshift($replies, ['role' => 'user', 'content' => $ticket['title'], 'created_at' => $ticket['created_at']]);
        ok(['ticket' => $ticket, 'replies' => $replies]);
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
        // v4: 自动通知管理员有新认证申请
        send_admin_notification('新认证申请', "用户 {$u['username']} 提交了{$type}认证申请：{$data['name']}", 'cert');
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
        // v4: 自动通知管理员有新合作方申请
        send_admin_notification('新合作方申请', "用户 {$u['username']} 提交了合作伙伴申请：{$data['name']}", 'cert');
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

// v4: 关注/取消关注
public function follow()
{
    $u = current_user();
    $targetId = (int)input('target_id', 0);
    if (!$targetId || $targetId == $u['id']) fail('参数错误');
    $target = db()->queryOne("SELECT id, username FROM " . db()->table('users') . " WHERE id=? AND status=1", [$targetId]);
    if (!$target) fail('用户不存在');
    $existing = db()->queryOne("SELECT id FROM " . db()->table('user_follows') . " WHERE user_id=? AND follow_id=?", [$u['id'], $targetId]);
    if ($existing) {
        db()->delete('user_follows', 'id=?', [$existing['id']]);
        ok(['following' => false], '已取消关注');
    } else {
        db()->insert('user_follows', ['user_id' => $u['id'], 'follow_id' => $targetId, 'created_at' => date('Y-m-d H:i:s')]);
        send_notification($targetId, '新粉丝通知', "用户 {$u['username']} 关注了您", 'follow', site_url('u/' . $u['id']));
        log_op("关注用户: {$target['username']}");
        ok(['following' => true], '关注成功');
    }
}

// v4: 拉黑/取消拉黑
public function block()
{
    $u = current_user();
    $targetId = (int)input('target_id', 0);
    if (!$targetId || $targetId == $u['id']) fail('参数错误');
    $existing = db()->queryOne("SELECT id FROM " . db()->table('user_blocks') . " WHERE user_id=? AND blocked_id=?", [$u['id'], $targetId]);
    if ($existing) {
        db()->delete('user_blocks', 'id=?', [$existing['id']]);
        ok(['blocked' => false], '已取消拉黑');
    } else {
        db()->insert('user_blocks', ['user_id' => $u['id'], 'blocked_id' => $targetId, 'created_at' => date('Y-m-d H:i:s')]);
        // 拉黑同时取消关注
        db()->delete('user_follows', 'user_id=? AND follow_id=?', [$u['id'], $targetId]);
        log_op('拉黑用户 #' . $targetId);
        ok(['blocked' => true], '已拉黑');
    }
}

// v4: 举报用户
public function report()
{
    $u = current_user();
    $targetId = (int)input('target_id', 0);
    $reason = trim(input('reason', ''));
    if (!$targetId) fail('参数错误');
    if (!$reason) fail('请填写举报原因');
    if (mb_strlen($reason) < 5) fail('举报原因至少5个字');
    $target = db()->queryOne("SELECT id, username FROM " . db()->table('users') . " WHERE id=?", [$targetId]);
    if (!$target) fail('用户不存在');
    db()->insert('user_reports', [
        'user_id' => $u['id'], 'target_id' => $targetId, 'reason' => $reason,
        'status' => 0, 'created_at' => date('Y-m-d H:i:s'),
    ]);
    send_admin_notification('新用户举报', "用户 {$u['username']} 举报了用户 {$target['username']}：{$reason}", 'report');
    log_op("举报用户: {$target['username']}");
    ok([], '举报已提交，等待处理');
}

// v4: 点赞/取消点赞 (用户)
public function like()
{
    $u = current_user();
    $targetId = (int)input('target_id', 0);
    $targetType = input('target_type', 'user');
    if (!in_array($targetType, ['user', 'message'])) fail('类型无效');
    if (!$targetId) fail('参数错误');
    $existing = db()->queryOne("SELECT id FROM " . db()->table('user_likes') . " WHERE user_id=? AND target_id=? AND target_type=?", [$u['id'], $targetId, $targetType]);
    if ($existing) {
        db()->delete('user_likes', 'id=?', [$existing['id']]);
        ok(['liked' => false], '已取消点赞');
    } else {
        db()->insert('user_likes', ['user_id' => $u['id'], 'target_id' => $targetId, 'target_type' => $targetType, 'created_at' => date('Y-m-d H:i:s')]);
        ok(['liked' => true], '点赞成功');
    }
}

// v4: 私信列表页 GET /user/messages
public function messages()
{
    $u = current_user();
    $toId = (int)input('to', 0);
    $partner = null;
    if ($toId) {
        $partner = db()->queryOne("SELECT id, username, avatar FROM " . db()->table('users') . " WHERE id=? AND status=1", [$toId]);
    }
    // 会话列表 (最近私聊过的用户)
    $p = db()->prefix();
    $conversations = [];
    try {
        $conversations = db()->query(
            "SELECT IF(from_id=?, to_id, from_id) AS peer_id, MAX(created_at) AS last_time "
            . "FROM {$p}private_messages WHERE from_id=? OR to_id=? "
            . "GROUP BY peer_id ORDER BY last_time DESC",
            [$u['id'], $u['id'], $u['id']]
        );
        foreach ($conversations as &$c) {
            $peer = db()->queryOne("SELECT id, username, avatar FROM " . db()->table('users') . " WHERE id=?", [$c['peer_id']]);
            $c['username'] = $peer['username'] ?? '已注销';
            $c['avatar'] = $peer['avatar'] ?? '';
            $lastMsg = db()->queryOne("SELECT content, from_id, is_read, created_at FROM {$p}private_messages WHERE (from_id=? AND to_id=?) OR (from_id=? AND to_id=?) ORDER BY id DESC LIMIT 1", [$u['id'], $c['peer_id'], $c['peer_id'], $u['id']]);
            $c['last_content'] = $lastMsg['content'] ?? '';
            $c['last_from_me'] = $lastMsg && $lastMsg['from_id'] == $u['id'];
            $c['unread'] = (int)db()->queryScalar("SELECT COUNT(*) FROM {$p}private_messages WHERE from_id=? AND to_id=? AND is_read=0", [$c['peer_id'], $u['id']]);
        }
        unset($c);
    } catch (Throwable $e) {}
    // v6: 私信改用 chat 独立布局 (无平台公共头部和页脚)
    $this->view('user/messages', [
        'pageTitle' => '私信 - ' . site_config('site_name', '管备云备案系统'),
        'active'    => 'messages',
        'conversations' => $conversations, 'partner' => $partner, 'toId' => $toId,
    ], 'chat');
}

// v4: 获取与某用户的对话消息 GET /user/messages/chat
public function messageChat()
{
    $u = current_user();
    $peerId = (int)input('peer_id', 0);
    if (!$peerId) fail('参数错误');
    // 检查拉黑关系 (互相拉黑则不可私聊)
    if (is_blocked_by($u['id'], $peerId)) fail('您已拉黑对方，无法私聊');
    if (is_blocked_by($peerId, $u['id'])) fail('对方已拉黑您，无法私聊');
    // 标记对方发来的消息为已读
    db()->execute("UPDATE " . db()->table('private_messages') . " SET is_read=1 WHERE from_id=? AND to_id=? AND is_read=0", [$peerId, $u['id']]);
    $rows = db()->query(
        "SELECT m.*, u.username, u.avatar FROM " . db()->table('private_messages') . " m "
        . "LEFT JOIN " . db()->table('users') . " u ON u.id=m.from_id "
        . "WHERE (m.from_id=? AND m.to_id=?) OR (m.from_id=? AND m.to_id=?) "
        . "ORDER BY m.id ASC LIMIT 100",
        [$u['id'], $peerId, $peerId, $u['id']]
    );
    $list = [];
    foreach ($rows as $r) {
        $list[] = [
            'id' => (int)$r['id'],
            'from_id' => (int)$r['from_id'],
            'to_id' => (int)$r['to_id'],
            'content' => (string)$r['content'],
            'msg_type' => $r['msg_type'] ?? 'text',
            'is_me' => $r['from_id'] == $u['id'],
            'username' => $r['username'] ?? '',
            'avatar' => !empty($r['avatar']) ? asset($r['avatar']) : '',
            'created_at' => $r['created_at'],
        ];
    }
    ok(['messages' => $list]);
}

// v4: 发送私信 POST /user/messages/send
public function sendMessage()
{
    $u = current_user();
    $toId = (int)input('to_id', 0);
    $content = trim(input('content', ''));
    $msgType = input('msg_type', 'text');
    if (!in_array($msgType, ['text', 'image', 'emoji'])) $msgType = 'text';
    if (!$toId) fail('参数错误');
    if (!$content) fail('消息内容不能为空');
    $target = db()->queryOne("SELECT id, username FROM " . db()->table('users') . " WHERE id=? AND status=1", [$toId]);
    if (!$target) fail('用户不存在');
    if ($toId == $u['id']) fail('不能给自己发私信');
    // 拉黑检查
    if (is_blocked_by($u['id'], $toId)) fail('您已拉黑对方');
    if (is_blocked_by($toId, $u['id'])) fail('对方已拉黑您');
    db()->insert('private_messages', [
        'from_id' => $u['id'], 'to_id' => $toId, 'content' => $content,
        'msg_type' => $msgType, 'is_read' => 0, 'created_at' => date('Y-m-d H:i:s'),
    ]);
    send_notification($toId, '新私信通知', "用户 {$u['username']} 给您发送了一条私信：" . mb_substr($content, 0, 30), 'message', site_url('user/messages?to=' . $u['id']));
    log_op("发送私信给 {$target['username']}");
    ok([], '发送成功');
}

    public function blacklist()
    {
        $u = current_user();
        [$page, $size, $offset] = page_params();
        $p = db()->prefix();
        $total = (int)db()->queryScalar("SELECT COUNT(*) FROM {$p}user_blocks WHERE user_id=?", [$u['id']]);
        $rows = db()->query(
            "SELECT b.id, b.blocked_id, b.created_at, u.username, u.avatar, u.email "
            . "FROM {$p}user_blocks b LEFT JOIN {$p}users u ON u.id=b.blocked_id "
            . "WHERE b.user_id=? ORDER BY b.id DESC LIMIT $offset,$size",
            [$u['id']]
        );
        $this->view('user/blacklist', [
            'pageTitle' => '黑名单管理', 'crumb' => '用户配置 / 黑名单',
            'activeMenu' => 'settings', 'activeSub' => 'uc-blacklist',
            'rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size,
        ], 'user');
    }

    public function unblock()
    {
        $u = current_user();
        $targetId = (int)input('target_id', 0);
        if (!$targetId || $targetId == $u['id']) fail('参数错误');
        $existing = db()->queryOne("SELECT id FROM " . db()->table('user_blocks') . " WHERE user_id=? AND blocked_id=?", [$u['id'], $targetId]);
        if ($existing) {
            db()->delete('user_blocks', 'id=?', [$existing['id']]);
        }
        log_op('取消拉黑用户 #' . $targetId);
        ok(['blocked' => false], '已取消拉黑');
    }
}

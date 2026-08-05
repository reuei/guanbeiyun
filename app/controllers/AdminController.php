<?php
/**
 * 后台管理控制器
 */
class AdminController extends Controller
{
    public function __construct()
    {
        // 登录页与登出不需要鉴权
        $noAuth = ['/admin/login', '/admin/logout'];
        $path = request_path();
        if (in_array($path, $noAuth, true)) return;
        if (!is_admin_logged_in()) {
            if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'json') !== false || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
                fail('请先登录', 401);
            }
            redirect(site_url('admin/login'));
        }
    }

    /** 后台登录页 */
    public function login()
    {
        if (is_admin_logged_in()) redirect(site_url('admin/dashboard'));
        $this->raw('admin/login', ['pageTitle' => '管理员登录']);
    }

    public function doLogin()
    {
        $username = trim(input('username', ''));
        $password = input('password', '');
        $captcha = (int)input('captcha_verified', 0);
        if (!$username || !$password) fail('请输入账号和密码');
        if (!$captcha) fail('请先完成人机验证');
        try {
            $admin = db()->queryOne("SELECT * FROM " . db()->table('admins') . " WHERE username = ? LIMIT 1", [$username]);
            if (!$admin || !verify_password($password, $admin['password'])) {
                log_action('login', "管理员登录失败: {$username}", 0, 'admin');
                fail('账号或密码错误');
            }
            db()->update('admins', ['last_login' => date('Y-m-d H:i:s'), 'last_ip' => $_SERVER['REMOTE_ADDR'] ?? ''], 'id = :id', ['id' => $admin['id']]);
            unset($admin['password']);
            $_SESSION['gb_admin'] = $admin;
            log_action('login', "管理员 {$username} 登录成功", $admin['id'], 'admin');
            ok(['redirect' => site_url('admin/dashboard')], '登录成功');
        } catch (Throwable $e) {
            fail('系统异常: ' . $e->getMessage());
        }
    }

    public function logout()
    {
        $a = admin_user();
        if ($a) log_action('login', "管理员 {$a['username']} 退出登录", $a['id'], 'admin');
        unset($_SESSION['gb_admin']);
        redirect(site_url('admin/login'));
    }

    public function index()
    {
        redirect(site_url('admin/dashboard'));
    }

    /** 总概览 */
    public function dashboard()
    {
        $stats = $this->gatherStats();
        $recentFilings = [];
        $recentUsers = [];
        try {
            $recentFilings = db()->query("SELECT f.*, u.username FROM " . db()->table('filings') . " f LEFT JOIN " . db()->table('users') . " u ON u.id=f.user_id ORDER BY f.id DESC LIMIT 6");
            $recentUsers = db()->query("SELECT username, email, created_at FROM " . db()->table('users') . " ORDER BY id DESC LIMIT 6");
        } catch (Throwable $e) {}
        $this->view('admin/dashboard', [
            'pageTitle' => '总概览', 'crumb' => '工作台 / 总概览',
            'activeMenu' => 'workbench', 'activeSub' => 'dashboard',
            'stats' => $stats, 'recentFilings' => $recentFilings, 'recentUsers' => $recentUsers,
        ], 'admin');
    }

    /** 数据大屏 */
    public function bigscreen()
    {
        $stats = $this->gatherStats();
        $this->view('admin/bigscreen', [
            'pageTitle' => '数据大屏', 'crumb' => '工作台 / 数据大屏',
            'activeMenu' => 'workbench', 'activeSub' => 'bigscreen',
            'stats' => $stats,
        ], 'admin');
    }

    /** 用户管理 */
    public function users()
    {
        [$page, $size, $offset] = page_params();
        $kw = trim(input('kw', ''));
        $where = '1=1'; $params = [];
        if ($kw) { $where .= " AND (username LIKE ? OR email LIKE ? OR phone LIKE ?)"; $p = "%$kw%"; $params = [$p, $p, $p]; }
        $total = db()->count('users', $where, $params);
        $rows = db()->query("SELECT * FROM " . db()->table('users') . " WHERE $where ORDER BY id DESC LIMIT $offset,$size", $params);
        $this->view('admin/users', [
            'pageTitle' => '用户管理', 'crumb' => '用户管理 / 用户管理',
            'activeMenu' => 'users', 'activeSub' => 'users',
            'rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size, 'kw' => $kw,
        ], 'admin');
    }

    /** 备案管理 */
    public function filings()
    {
        [$page, $size, $offset] = page_params();
        $status = input('status', '');
        $kw = trim(input('kw', ''));
        $where = '1=1'; $params = [];
        if ($status !== '') { $where .= " AND f.status = ?"; $params[] = (int)$status; }
        if ($kw) { $where .= " AND (f.site_name LIKE ? OR f.site_domain LIKE ? OR f.icp_no LIKE ? OR f.owner_name LIKE ?)"; $p = "%$kw%"; array_push($params, $p, $p, $p, $p); }
        $total = db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('filings') . " f WHERE $where", $params);
        $rows = db()->query("SELECT f.*, u.username FROM " . db()->table('filings') . " f LEFT JOIN " . db()->table('users') . " u ON u.id=f.user_id WHERE $where ORDER BY f.id DESC LIMIT $offset,$size", $params);
        $this->view('admin/filings', [
            'pageTitle' => '备案管理', 'crumb' => '用户管理 / 备案管理',
            'activeMenu' => 'users', 'activeSub' => 'filings',
            'rows' => $rows, 'total' => (int)$total, 'page' => $page, 'size' => $size, 'status' => $status, 'kw' => $kw,
        ], 'admin');
    }

    /** 切换用户状态 */
    public function toggleUser()
    {
        $id = (int)input('id', 0);
        $status = (int)input('status', 0);
        if (!$id) fail('参数错误');
        db()->update('users', ['status' => $status ? 1 : 0, 'updated_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $id]);
        log_action('operation', "修改用户 #{$id} 状态为" . ($status ? '正常' : '禁用'), admin_user()['id'], 'admin');
        ok([], '已更新');
    }

    /** 工单详情 */
    public function ticketDetail()
    {
        $id = (int)input('id', 0);
        $ticket = db()->queryOne("SELECT * FROM " . db()->table('tickets') . " WHERE id = ?", [$id]);
        if (!$ticket) fail('工单不存在');
        $replies = db()->query("SELECT * FROM " . db()->table('ticket_replies') . " WHERE ticket_id = ? ORDER BY id ASC", [$id]);
        // 第一条是工单内容本身
        array_unshift($replies, ['role' => 'user', 'content' => $ticket['title'] . "\n\n" . '', 'created_at' => $ticket['created_at']]);
        ok(['ticket' => $ticket, 'replies' => $replies]);
    }

    /** 反馈回复 */
    public function replyFeedback()
    {
        $id = (int)input('id', 0);
        $status = (int)input('status', 1);
        $reply = trim(input('reply', ''));
        if (!$id) fail('参数错误');
        db()->update('feedbacks', ['status' => $status, 'reply' => $reply, 'replied_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $id]);
        // v4: 自动通知用户反馈/举报处理结果
        $fb = db()->queryOne("SELECT * FROM " . db()->table('feedbacks') . " WHERE id = ?", [$id]);
        if ($fb && $fb['user_id']) {
            $typeText = $fb['type'] === 'report' ? '举报' : '反馈';
            send_notification($fb['user_id'], "{$typeText}处理结果通知", "您的{$typeText}「{$fb['title']}」已处理，回复：{$reply}", 'feedback', site_url('user/feedback'));
        }
        log_action('operation', "处理反馈/举报 #{$id}", admin_user()['id'], 'admin');
        ok([], '已处理');
    }

    public function auditFiling()
    {
        $id = (int)input('id', 0);
        $status = (int)input('status', 0);
        $icpNo = trim(input('icp_no', ''));
        $remark = trim(input('audit_remark', ''));
        if (!in_array($status, [1, 2, 3])) fail('状态无效');
        $filing = db()->queryOne("SELECT * FROM " . db()->table('filings') . " WHERE id = ?", [$id]);
        if (!$filing) fail('备案记录不存在');
        $data = ['status' => $status, 'audit_remark' => $remark, 'audited_at' => date('Y-m-d H:i:s'), 'audited_by' => admin_user()['id'], 'updated_at' => date('Y-m-d H:i:s')];
        // 通过审核自动分配 管ICP备xxxxxxxx号 格式的备案号 (确保不重复)
        if ($status === 1) {
            if ($icpNo) {
                $data['icp_no'] = $icpNo;
            } elseif (!$filing['icp_no']) {
                $data['icp_no'] = gen_icp_no();
            }
        }
        db()->update('filings', $data, 'id = :id', ['id' => $id]);
        // 通过审核后自动同步到备案公示
        if ($status === 1) {
            $this->syncFilingPublicity($id, $filing, $data['icp_no'] ?? $filing['icp_no']);
        }
        // v4: 自动通知用户备案审核结果
        $statusText = [1 => '已通过', 2 => '未通过', 3 => '已撤销'][$status] ?? '未知';
        $finalIcp = $data['icp_no'] ?? $filing['icp_no'] ?? '';
        $notifyContent = "您的备案申请「{$filing['site_name']}」审核结果：{$statusText}";
        if ($status === 1 && $finalIcp) {
            $notifyContent .= "，备案号：{$finalIcp}";
        }
        if ($remark) $notifyContent .= "，审核意见：{$remark}";
        send_notification($filing['user_id'], '备案审核结果通知', $notifyContent, 'filing', site_url('user/filings'));
        log_action('operation', "审核备案 #{$id} 状态={$status}", admin_user()['id'], 'admin');
        ok([], '审核完成');
    }

    /** 备案通过后同步到公示表 (type=filing) */
    private function syncFilingPublicity($filingId, $filing, $icpNo)
    {
        try {
            $p = db()->prefix();
            $exists = db()->queryOne("SELECT id FROM {$p}publicity WHERE type='filing' AND title = ?", ['备案#' . $filingId]);
            $data = [
                'type'    => 'filing',
                'title'   => '备案#' . $filingId,
                'content' => $filing['site_name'] . ' - ' . $filing['site_domain'],
                'link'    => $filing['site_url'] ?: ('http://' . $filing['site_domain']),
                'icp_no'  => $icpNo,
                'user_id' => $filing['user_id'],
                'status'  => 1,
                'sort'    => 0,
            ];
            if ($exists) {
                db()->update('publicity', $data, 'id = :id', ['id' => $exists['id']]);
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                db()->insert('publicity', $data);
            }
        } catch (Throwable $e) {}
    }

    /** 备案详情 (后台) */
    public function filingDetail()
    {
        $id = (int)input('id', 0);
        $f = db()->queryOne("SELECT f.*, u.username, u.avatar FROM " . db()->table('filings') . " f LEFT JOIN " . db()->table('users') . " u ON u.id=f.user_id WHERE f.id = ?", [$id]);
        if (!$f) fail('备案记录不存在');
        ok(['filing' => $f]);
    }

    /** 反馈详情 (后台) */
    public function feedbackDetail()
    {
        $id = (int)input('id', 0);
        $fb = db()->queryOne("SELECT * FROM " . db()->table('feedbacks') . " WHERE id = ?", [$id]);
        if (!$fb) fail('记录不存在');
        ok(['feedback' => $fb]);
    }

    /** 网站维护配置 */
    public function maintenance()
    {
        $this->view('admin/maintenance', [
            'pageTitle' => '网站维护', 'crumb' => '系统配置 / 网站维护',
            'activeMenu' => 'system', 'activeSub' => 'maintenance',
            'cfg' => site_config(),
        ], 'admin');
    }

    public function saveMaintenance()
    {
        $this->setConfig('maintenance_enabled', (int)input('maintenance_enabled', 0));
        $this->setConfig('maintenance_title', trim(input('maintenance_title', '')));
        $this->setConfig('maintenance_content', input('maintenance_content', ''));
        $this->setConfig('maintenance_recover_time', trim(input('maintenance_recover_time', '')));
        log_action('operation', '更新网站维护配置 enabled=' . (int)input('maintenance_enabled', 0), admin_user()['id'], 'admin');
        ok([], '保存成功');
    }

    /** 备案公示管理 (单独页) */
    public function publicityFiling()
    {
        $this->listPublicity('filing', '备案公示管理', 'pub-filing');
    }
    /** 失效网站公示管理 (单独页) */
    public function publicityInvalid()
    {
        $this->listPublicity('invalid', '失效网站公示管理', 'pub-invalid');
    }
    private function listPublicity($type, $title, $sub)
    {
        [$page, $size, $offset] = page_params();
        $kw = trim(input('kw', ''));
        $where = "type = ?"; $params = [$type];
        if ($kw) { $where .= " AND (title LIKE ? OR content LIKE ? OR icp_no LIKE ?)"; $p = "%$kw%"; array_push($params, $p, $p, $p); }
        $total = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('publicity') . " WHERE $where", $params);
        $rows = db()->query("SELECT p.*, u.username FROM " . db()->table('publicity') . " p LEFT JOIN " . db()->table('users') . " u ON u.id=p.user_id WHERE $where ORDER BY p.sort DESC, p.id DESC LIMIT $offset,$size", $params);
        $this->view('admin/publicity_list', [
            'pageTitle' => $title, 'crumb' => '认证管理 / ' . $title,
            'activeMenu' => 'auth', 'activeSub' => $sub,
            'rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size, 'kw' => $kw, 'pubType' => $type,
        ], 'admin');
    }

    /** 认证图片配置列表 */
    public function certifications()
    {
        [$page, $size, $offset] = page_params();
        $total = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('certifications'));
        $rows = db()->query("SELECT * FROM " . db()->table('certifications') . " ORDER BY sort DESC, id DESC LIMIT $offset,$size");
        $this->view('admin/certifications', [
            'pageTitle' => '认证图片配置', 'crumb' => '认证管理 / 认证图片配置',
            'activeMenu' => 'auth', 'activeSub' => 'certifications',
            'rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size,
        ], 'admin');
    }

    public function saveCertification()
    {
        $id = (int)input('id', 0);
        $data = [
            'name'       => trim(input('name', '')),
            'image'      => trim(input('image', '')),
            'info'       => trim(input('info', '')),
            'icon_style' => trim(input('icon_style', 'default')),
            'sort'       => (int)input('sort', 0),
            'status'     => (int)input('status', 1),
        ];
        if (!$data['name']) fail('请输入认证名称');
        if ($id) {
            db()->update('certifications', $data, 'id = :id', ['id' => $id]);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            db()->insert('certifications', $data);
        }
        log_action('operation', "保存认证配置 {$data['name']}", admin_user()['id'], 'admin');
        ok([], '保存成功');
    }

    public function deleteCertification()
    {
        $id = (int)input('id', 0);
        db()->delete('certifications', 'id = ?', [$id]);
        ok([], '已删除');
    }

    /** 账号注销申请管理 */
    public function deletions()
    {
        [$page, $size, $offset] = page_params();
        $status = input('status', '');
        $where = '1=1'; $params = [];
        if ($status !== '') { $where .= " AND d.status = ?"; $params[] = (int)$status; }
        $total = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('account_deletions') . " d WHERE $where", $params);
        $rows = db()->query("SELECT d.*, u.username, u.email FROM " . db()->table('account_deletions') . " d LEFT JOIN " . db()->table('users') . " u ON u.id=d.user_id WHERE $where ORDER BY d.id DESC LIMIT $offset,$size", $params);
        $this->view('admin/deletions', [
            'pageTitle' => '注销申请管理', 'crumb' => '用户管理 / 注销申请管理',
            'activeMenu' => 'users', 'activeSub' => 'deletions',
            'rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size, 'status' => $status,
        ], 'admin');
    }

    public function auditDeletion()
    {
        $id = (int)input('id', 0);
        $status = (int)input('status', 0);
        $remark = trim(input('audit_remark', ''));
        if (!in_array($status, [1, 2])) fail('状态无效');
        $del = db()->queryOne("SELECT * FROM " . db()->table('account_deletions') . " WHERE id = ?", [$id]);
        if (!$del) fail('申请不存在');
        db()->update('account_deletions', ['status' => $status, 'audit_remark' => $remark, 'audited_at' => date('Y-m-d H:i:s'), 'audited_by' => admin_user()['id']], 'id = :id', ['id' => $id]);
        // 通过则禁用用户
        if ($status === 1) {
            db()->update('users', ['status' => 0, 'updated_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $del['user_id']]);
        }
        // v4: 自动通知用户注销申请审核结果
        $statusText = $status === 1 ? '已通过' : '已驳回';
        $notifyContent = "您的账号注销申请审核结果：{$statusText}";
        if ($remark) $notifyContent .= "，审核意见：{$remark}";
        send_notification($del['user_id'], '账号注销申请结果通知', $notifyContent, 'account', site_url('user'));
        log_action('operation', "审核注销申请 #{$id} 状态={$status}", admin_user()['id'], 'admin');
        ok([], '审核完成');
    }

    /** 聊天室消息管理 */
    public function chat()
    {
        [$page, $size, $offset] = page_params();
        $kw = trim(input('kw', ''));
        $where = '1=1'; $params = [];
        if ($kw) { $where .= " AND (m.content LIKE ? OR u.username LIKE ?)"; $p = "%$kw%"; array_push($params, $p, $p); }
        $total = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('chat_messages') . " m WHERE $where", $params);
        $rows = db()->query("SELECT m.*, u.username, u.avatar FROM " . db()->table('chat_messages') . " m LEFT JOIN " . db()->table('users') . " u ON u.id=m.user_id WHERE $where ORDER BY m.id DESC LIMIT $offset,$size", $params);
        $this->view('admin/chat_messages', [
            'pageTitle' => '聊天室消息管理', 'crumb' => '聊天室 / 消息管理',
            'activeMenu' => 'chat', 'activeSub' => 'chat-messages',
            'rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size, 'kw' => $kw,
            'cfg' => site_config(),
        ], 'admin');
    }

    /** 保存聊天室设置 (发送频率/刷屏阈值/违禁词策略) */
    public function saveChatConfig()
    {
        $keys = [
            'chat_rate_limit', 'chat_spam_threshold', 'chat_spam_ban_min',
            'chat_violation_window', 'chat_violation_limit', 'chat_violation_ban_min',
        ];
        foreach ($keys as $k) {
            $this->setConfig($k, (int)input($k, 0));
        }
        log_action('operation', '更新聊天室设置', admin_user()['id'], 'admin');
        ok([], '保存成功');
    }

    public function deleteChatMessage()
    {
        $id = (int)input('id', 0);
        if (!$id) fail('参数错误');
        db()->update('chat_messages', ['is_recalled' => 1], 'id = :id', ['id' => $id]);
        log_action('operation', "撤回聊天消息 #{$id}", admin_user()['id'], 'admin');
        ok([], '已撤回');
    }

    /** 聊天室禁言用户管理 */
    public function chatBanned()
    {
        [$page, $size, $offset] = page_params();
        $total = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('chat_banned') . " b WHERE b.banned_until > NOW()");
        $rows = db()->query("SELECT b.*, u.username FROM " . db()->table('chat_banned') . " b LEFT JOIN " . db()->table('users') . " u ON u.id=b.user_id WHERE b.banned_until > NOW() ORDER BY b.id DESC LIMIT $offset,$size");
        $this->view('admin/chat_banned', [
            'pageTitle' => '禁言用户管理', 'crumb' => '聊天室 / 禁言用户',
            'activeMenu' => 'chat', 'activeSub' => 'chat-banned',
            'rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size,
        ], 'admin');
    }

    public function banUser()
    {
        $userId = (int)input('user_id', 0);
        $mins = max(1, (int)input('minutes', 60));
        $reason = trim(input('reason', '后台手动禁言'));
        if (!$userId) fail('参数错误');
        $banUntil = date('Y-m-d H:i:s', time() + $mins * 60);
        db()->insert('chat_banned', [
            'user_id' => $userId,
            'reason' => $reason,
            'banned_until' => $banUntil,
            'source' => 'manual',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        // v4: 自动通知用户被禁言 (警告信息)
        send_notification($userId, '聊天室禁言警告通知', "您已被管理员禁言，截止时间：{$banUntil}，原因：{$reason}", 'warning', site_url('chat'));
        log_action('operation', "禁言用户 #{$userId} {$mins}分钟", admin_user()['id'], 'admin');
        ok([], '已禁言');
    }

    public function unbanUser()
    {
        $userId = (int)input('user_id', 0);
        if (!$userId) fail('参数错误');
        db()->execute("UPDATE " . db()->table('chat_banned') . " SET banned_until = NOW() WHERE user_id = ? AND banned_until > NOW()", [$userId]);
        log_action('operation', "解禁用户 #{$userId}", admin_user()['id'], 'admin');
        ok([], '已解禁');
    }

    /** 聊天室违禁词管理 */
    public function chatWords()
    {
        [$page, $size, $offset] = page_params();
        $total = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('chat_words'));
        $rows = db()->query("SELECT * FROM " . db()->table('chat_words') . " ORDER BY id DESC LIMIT $offset,$size");
        $this->view('admin/chat_words', [
            'pageTitle' => '违禁词管理', 'crumb' => '聊天室 / 违禁词',
            'activeMenu' => 'chat', 'activeSub' => 'chat-words',
            'rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size,
        ], 'admin');
    }

    public function saveChatWord()
    {
        $word = trim(input('word', ''));
        if (!$word) fail('请输入违禁词');
        try {
            db()->insert('chat_words', ['word' => $word, 'created_at' => date('Y-m-d H:i:s')]);
        } catch (Throwable $e) {
            fail('该违禁词已存在');
        }
        ok([], '已添加');
    }

    public function deleteChatWord()
    {
        $id = (int)input('id', 0);
        db()->delete('chat_words', 'id = ?', [$id]);
        ok([], '已删除');
    }

    /** 申请管理 (用户中心申请: 认证) */
    public function applications()
    {
        [$page, $size, $offset] = page_params();
        $type = input('type', '');
        $where = '1=1'; $params = [];
        if ($type) { $where .= " AND type = ?"; $params[] = $type; }
        $total = db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('applications') . " WHERE $where", $params);
        $rows = db()->query("SELECT a.*, u.username FROM " . db()->table('applications') . " a LEFT JOIN " . db()->table('users') . " u ON u.id=a.user_id WHERE $where ORDER BY a.id DESC LIMIT $offset,$size", $params);
        $this->view('admin/applications', [
            'pageTitle' => '申请管理', 'crumb' => '用户管理 / 申请管理',
            'activeMenu' => 'users', 'activeSub' => 'applications',
            'rows' => $rows, 'total' => (int)$total, 'page' => $page, 'size' => $size, 'type' => $type,
        ], 'admin');
    }

    public function auditApp()
    {
        $id = (int)input('id', 0);
        $status = (int)input('status', 0);
        $remark = trim(input('audit_remark', ''));
        if (!in_array($status, [1, 2])) fail('状态无效');
        $app = db()->queryOne("SELECT * FROM " . db()->table('applications') . " WHERE id = ?", [$id]);
        if (!$app) fail('申请不存在');
        db()->update('applications', ['status' => $status, 'audit_remark' => $remark, 'audited_at' => date('Y-m-d H:i:s'), 'audited_by' => admin_user()['id']], 'id = :id', ['id' => $id]);
        // v4: 自动通知用户认证/合作方申请审核结果
        $typeMap = ['enterprise' => '企业认证', 'personal' => '个人认证', 'partner' => '合作伙伴'];
        $typeText = $typeMap[$app['type']] ?? '申请';
        $statusText = $status === 1 ? '已通过' : '未通过';
        $notifyContent = "您的{$typeText}申请「{$app['name']}」审核结果：{$statusText}";
        if ($remark) $notifyContent .= "，审核意见：{$remark}";
        $link = $app['type'] === 'partner' ? site_url('user/partner') : site_url('user/certification');
        send_notification($app['user_id'], "{$typeText}申请结果通知", $notifyContent, 'cert', $link);
        log_action('operation', "审核申请 #{$id} 状态={$status}", admin_user()['id'], 'admin');
        ok([], '审核完成');
    }

    /** 反馈管理 */
    public function feedbacks()
    {
        $this->listFeedback('feedback', '反馈管理');
    }
    /** 举报管理 */
    public function reports()
    {
        $this->listFeedback('report', '举报管理');
    }
    private function listFeedback($type, $title)
    {
        [$page, $size, $offset] = page_params();
        $kw = trim(input('kw', ''));
        $where = "type = ?"; $params = [$type];
        if ($kw) { $where .= " AND (title LIKE ? OR content LIKE ?)"; $p = "%$kw%"; array_push($params, $p, $p); }
        $total = db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('feedbacks') . " WHERE $where", $params);
        $rows = db()->query("SELECT * FROM " . db()->table('feedbacks') . " WHERE $where ORDER BY id DESC LIMIT $offset,$size", $params);
        $this->view('admin/feedbacks', [
            'pageTitle' => $title, 'crumb' => '用户管理 / ' . $title,
            'activeMenu' => 'users', 'activeSub' => $type === 'feedback' ? 'feedbacks' : 'reports',
            'rows' => $rows, 'total' => (int)$total, 'page' => $page, 'size' => $size, 'kw' => $kw, 'fbType' => $type,
        ], 'admin');
    }

    /** 工单管理 */
    public function tickets()
    {
        [$page, $size, $offset] = page_params();
        $status = input('status', '');
        $where = '1=1'; $params = [];
        if ($status !== '') { $where .= " AND status = ?"; $params[] = (int)$status; }
        $total = db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('tickets') . " WHERE $where", $params);
        $rows = db()->query("SELECT t.*, u.username FROM " . db()->table('tickets') . " t LEFT JOIN " . db()->table('users') . " u ON u.id=t.user_id WHERE $where ORDER BY t.id DESC LIMIT $offset,$size", $params);
        $this->view('admin/tickets', [
            'pageTitle' => '工单管理', 'crumb' => '用户管理 / 工单管理',
            'activeMenu' => 'users', 'activeSub' => 'tickets',
            'rows' => $rows, 'total' => (int)$total, 'page' => $page, 'size' => $size, 'status' => $status,
        ], 'admin');
    }

    public function replyTicket()
    {
        $id = (int)input('id', 0);
        $content = trim(input('content', ''));
        if (!$content) fail('请输入回复内容');
        $ticket = db()->queryOne("SELECT * FROM " . db()->table('tickets') . " WHERE id = ?", [$id]);
        if (!$ticket) fail('工单不存在');
        db()->insert('ticket_replies', ['ticket_id' => $id, 'user_id' => admin_user()['id'], 'role' => 'admin', 'content' => $content, 'created_at' => date('Y-m-d H:i:s')]);
        db()->update('tickets', ['status' => 1, 'updated_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $id]);
        // v4: 自动通知用户工单回复
        send_notification($ticket['user_id'], '工单回复通知', "您的工单「{$ticket['title']}」有新回复：{$content}", 'ticket', site_url('user/tickets'));
        log_action('operation', "回复工单 #{$id}", admin_user()['id'], 'admin');
        ok([], '回复成功');
    }

    /** 网站配置 */
    public function siteConfig()
    {
        $this->view('admin/siteconfig', [
            'pageTitle' => '网站配置', 'crumb' => '系统配置 / 网站配置',
            'activeMenu' => 'system', 'activeSub' => 'siteconfig',
            'cfg' => site_config(),
        ], 'admin');
    }

    public function saveSiteConfig()
    {
        $fields = ['site_name','site_title','site_keywords','site_description','footer_intro','icp_info','copyright','tech_support','tech_support_url','theme_color','qq_image','wechat_image','kuaishou_image','site_logo','site_favicon','site_thumbnail','captcha_image'];
        foreach ($fields as $f) {
            $v = input($f, '');
            $this->setConfig($f, $v);
        }
        log_action('operation', '更新网站配置', admin_user()['id'], 'admin');
        ok([], '保存成功');
    }

    /** 文件上传 */
    public function upload()
    {
        if (empty($_FILES['file'])) fail('请选择文件');
        $file = $_FILES['file'];
        if ($file['error'] !== UPLOAD_ERR_OK) fail('上传失败');
        if ($file['size'] > config('upload.max_size')) fail('文件过大');
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, config('upload.allow'))) fail('不支持的文件类型');
        $dir = config('upload.path') . '/' . date('Ym');
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $name = date('YmdHis') . '_' . random_str(6) . '.' . $ext;
        $path = $dir . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $path)) fail('保存失败');
        $rel = 'uploads/' . date('Ym') . '/' . $name;
        ok(['url' => $rel, 'full' => asset($rel)], '上传成功');
    }

    public function deleteUpload()
    {
        $path = input('path', '');
        $full = config('upload.path') . '/../' . $path;
        $full = realpath($full);
        $base = realpath(config('upload.path'));
        if ($full && $base && strpos($full, $base) === 0 && is_file($full)) {
            @unlink($full);
            ok([], '已删除');
        }
        fail('文件不存在');
    }

    /** 公告配置 */
    public function announcement()
    {
        $this->view('admin/announcement', [
            'pageTitle' => '公告配置', 'crumb' => '系统配置 / 公告配置',
            'activeMenu' => 'system', 'activeSub' => 'announcement',
            'cfg' => site_config(),
        ], 'admin');
    }

    public function saveAnnouncement()
    {
        $this->setConfig('announcement_enabled', (int)input('announcement_enabled', 0));
        $this->setConfig('announcement_title', input('announcement_title', ''));
        $this->setConfig('announcement_content', input('announcement_content', ''));
        log_action('operation', '更新公告配置', admin_user()['id'], 'admin');
        ok([], '保存成功');
    }

    /** 通知管理 */
    public function notify()
    {
        [$page, $size, $offset] = page_params();
        $total = db()->count('notifications');
        $rows = db()->query("SELECT * FROM " . db()->table('notifications') . " ORDER BY id DESC LIMIT $offset,$size");
        $this->view('admin/notify', [
            'pageTitle' => '消息通知', 'crumb' => '系统配置 / 公告配置',
            'activeMenu' => 'system', 'activeSub' => 'announcement',
            'rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size,
        ], 'admin');
    }

    public function sendNotify()
    {
        $uid = (int)input('user_id', 0);
        $title = trim(input('title', ''));
        $content = trim(input('content', ''));
        $type = input('type', 'system');
        if (!$title || !$content) fail('标题和内容必填');
        db()->insert('notifications', ['user_id' => $uid, 'title' => $title, 'content' => $content, 'type' => $type, 'created_at' => date('Y-m-d H:i:s')]);
        log_action('operation', "发送通知: {$title}", admin_user()['id'], 'admin');
        ok([], '发送成功');
    }

    /** v4: 后台消息通知列表 (管理员自身收到的通知) */
    public function adminNotifications()
    {
        [$page, $size, $offset] = page_params();
        $total = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('admin_notifications'));
        $rows = db()->query("SELECT * FROM " . db()->table('admin_notifications') . " ORDER BY id DESC LIMIT $offset,$size");
        $this->view('admin/admin_notifications', [
            'pageTitle' => '消息通知', 'crumb' => '工作台 / 消息通知',
            'activeMenu' => 'workbench', 'activeSub' => 'admin-notify',
            'rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size,
        ], 'admin');
    }

    /** v4: 标记单条管理员通知为已读 */
    public function readAdminNotification()
    {
        $id = (int)input('id', 0);
        if (!$id) fail('参数错误');
        db()->execute("UPDATE " . db()->table('admin_notifications') . " SET is_read=1 WHERE id=?", [$id]);
        ok([], '已标记为已读');
    }

    /** v4: 全部标记为已读 */
    public function readAllAdminNotifications()
    {
        db()->execute("UPDATE " . db()->table('admin_notifications') . " SET is_read=1 WHERE is_read=0");
        ok([], '全部已读');
    }

    /** v4: 删除管理员通知 */
    public function deleteAdminNotification()
    {
        $id = (int)input('id', 0);
        if (!$id) fail('参数错误');
        db()->delete('admin_notifications', 'id=?', [$id]);
        ok([], '已删除');
    }

    /** v4: 后台 ICP 备案号前图片管理 */
    public function icpImages()
    {
        [$page, $size, $offset] = page_params();
        $total = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('icp_images'));
        $rows = db()->query("SELECT * FROM " . db()->table('icp_images') . " ORDER BY sort DESC, id ASC LIMIT $offset,$size");
        $this->view('admin/icp_images', [
            'pageTitle' => 'ICP备案号前图片', 'crumb' => '认证管理 / ICP备案号前图片',
            'activeMenu' => 'auth', 'activeSub' => 'icp-images',
            'rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size,
        ], 'admin');
    }

    public function saveIcpImage()
    {
        $id = (int)input('id', 0);
        $data = [
            'name'   => trim(input('name', '')),
            'image'  => trim(input('image', '')),
            'link'   => trim(input('link', '')),
            'sort'   => (int)input('sort', 0),
            'status' => (int)input('status', 1),
        ];
        if (!$data['name']) fail('请输入图片名称');
        if (!$data['image']) fail('请上传图片');
        if ($id) {
            db()->update('icp_images', $data, 'id = :id', ['id' => $id]);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            db()->insert('icp_images', $data);
        }
        log_action('operation', "保存ICP图片配置 {$data['name']}", admin_user()['id'], 'admin');
        ok([], '保存成功');
    }

    public function deleteIcpImage()
    {
        $id = (int)input('id', 0);
        db()->delete('icp_images', 'id = ?', [$id]);
        ok([], '已删除');
    }

    /** v4: 后台私信查看 (管理员可查看用户私聊内容) */
    public function privateMessages()
    {
        [$page, $size, $offset] = page_params();
        $kw = trim(input('kw', ''));
        $fromId = (int)input('from_id', 0);
        $toId = (int)input('to_id', 0);
        $where = '1=1'; $params = [];
        if ($fromId) { $where .= " AND m.from_id=?"; $params[] = $fromId; }
        if ($toId) { $where .= " AND m.to_id=?"; $params[] = $toId; }
        if ($kw) { $where .= " AND m.content LIKE ?"; $params[] = "%$kw%"; }
        $total = (int)db()->queryScalar(
            "SELECT COUNT(*) FROM " . db()->table('private_messages') . " m WHERE $where", $params
        );
        $rows = db()->query(
            "SELECT m.*, uf.username AS from_name, uf.avatar AS from_avatar, ut.username AS to_name, ut.avatar AS to_avatar "
            . "FROM " . db()->table('private_messages') . " m "
            . "LEFT JOIN " . db()->table('users') . " uf ON uf.id=m.from_id "
            . "LEFT JOIN " . db()->table('users') . " ut ON ut.id=m.to_id "
            . "WHERE $where ORDER BY m.id DESC LIMIT $offset,$size", $params
        );
        $this->view('admin/private_messages', [
            'pageTitle' => '私信查看', 'crumb' => '用户管理 / 私信查看',
            'activeMenu' => 'users', 'activeSub' => 'private-messages',
            'rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size,
            'kw' => $kw, 'fromId' => $fromId, 'toId' => $toId,
        ], 'admin');
    }

    public function deletePrivateMessage()
    {
        $id = (int)input('id', 0);
        if (!$id) fail('参数错误');
        db()->delete('private_messages', 'id=?', [$id]);
        log_action('operation', "删除私信 #{$id}", admin_user()['id'], 'admin');
        ok([], '已删除');
    }

    /** v4: 用户举报管理 (用户之间举报) */
    public function userReports()
    {
        [$page, $size, $offset] = page_params();
        $status = input('status', '');
        $where = '1=1'; $params = [];
        if ($status !== '') { $where .= " AND r.status=?"; $params[] = (int)$status; }
        $total = (int)db()->queryScalar(
            "SELECT COUNT(*) FROM " . db()->table('user_reports') . " r WHERE $where", $params
        );
        $rows = db()->query(
            "SELECT r.*, uf.username AS reporter_name, ut.username AS target_name "
            . "FROM " . db()->table('user_reports') . " r "
            . "LEFT JOIN " . db()->table('users') . " uf ON uf.id=r.user_id "
            . "LEFT JOIN " . db()->table('users') . " ut ON ut.id=r.target_id "
            . "WHERE $where ORDER BY r.id DESC LIMIT $offset,$size", $params
        );
        $this->view('admin/user_reports', [
            'pageTitle' => '用户举报管理', 'crumb' => '用户管理 / 用户举报管理',
            'activeMenu' => 'users', 'activeSub' => 'user-reports',
            'rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size, 'status' => $status,
        ], 'admin');
    }

    public function auditUserReport()
    {
        $id = (int)input('id', 0);
        $status = (int)input('status', 0);
        $remark = trim(input('remark', ''));
        if (!in_array($status, [1, 2], true)) fail('状态无效'); // 1=已处理 2=已驳回
        $row = db()->queryOne("SELECT * FROM " . db()->table('user_reports') . " WHERE id=?", [$id]);
        if (!$row) fail('举报记录不存在');
        db()->update('user_reports', ['status' => $status, 'remark' => $remark, 'handled_at' => date('Y-m-d H:i:s')], 'id=:id', ['id' => $id]);
        $statusText = $status === 1 ? '已处理' : '已驳回';
        $notifyContent = "您举报的用户「#{$row['target_id']}」处理结果：{$statusText}";
        if ($remark) $notifyContent .= "，处理意见：{$remark}";
        send_notification($row['user_id'], '举报处理结果通知', $notifyContent, 'report', site_url('user/feedback'));
        log_action('operation', "处理用户举报 #{$id}：{$statusText}", admin_user()['id'], 'admin');
        ok([], '处理成功');
    }

    /** 文章管理 */
    public function articles()
    {
        [$page, $size, $offset] = page_params();
        $cat = input('cat', '');
        $where = '1=1'; $params = [];
        if ($cat) { $where .= " AND category = ?"; $params[] = $cat; }
        $total = db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('articles') . " WHERE $where", $params);
        $rows = db()->query("SELECT * FROM " . db()->table('articles') . " WHERE $where ORDER BY id DESC LIMIT $offset,$size", $params);
        $this->view('admin/articles', [
            'pageTitle' => '文章管理', 'crumb' => '系统配置 / 文章管理',
            'activeMenu' => 'system', 'activeSub' => 'articles',
            'rows' => $rows, 'total' => (int)$total, 'page' => $page, 'size' => $size, 'cat' => $cat,
        ], 'admin');
    }

    public function articleEdit()
    {
        $id = (int)input('id', 0);
        $article = ['id' => 0, 'title' => '', 'slug' => '', 'category' => 'article', 'content' => '', 'status' => 1];
        if ($id) {
            $article = db()->queryOne("SELECT * FROM " . db()->table('articles') . " WHERE id = ?", [$id]);
            if (!$article) $article = ['id' => 0, 'title' => '', 'slug' => '', 'category' => 'article', 'content' => '', 'status' => 1];
        }
        $this->view('admin/article_edit', [
            'pageTitle' => '编辑文章', 'crumb' => '系统配置 / 文章管理',
            'activeMenu' => 'system', 'activeSub' => 'articles',
            'article' => $article,
        ], 'admin');
    }

    public function articleSave()
    {
        $id = (int)input('id', 0);
        $data = [
            'title' => trim(input('title', '')),
            'slug' => trim(input('slug', '')) ?: null,
            'category' => input('category', 'article'),
            'content' => input('content', ''),
            'status' => (int)input('status', 1),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if (!$data['title'] || !$data['content']) fail('标题和内容必填');
        if ($id) {
            db()->update('articles', $data, 'id = :id', ['id' => $id]);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $id = db()->insert('articles', $data);
        }
        log_action('operation', "保存文章 #{$id} {$data['title']}", admin_user()['id'], 'admin');
        ok(['redirect' => site_url('admin/articles')], '保存成功');
    }

    public function articleDelete()
    {
        $id = (int)input('id', 0);
        db()->delete('articles', 'id = ?', [$id]);
        log_action('operation', "删除文章 #{$id}", admin_user()['id'], 'admin');
        ok([], '已删除');
    }

    /** 邮箱配置 */
    public function mailConfig()
    {
        $this->view('admin/mail', [
            'pageTitle' => '邮箱配置', 'crumb' => '系统配置 / 邮箱配置',
            'activeMenu' => 'system', 'activeSub' => 'mail',
            'cfg' => site_config(),
        ], 'admin');
    }

    public function saveMail()
    {
        $fields = ['mail_enabled','mail_host','mail_port','mail_user','mail_pass','mail_from','mail_from_name','mail_reg_login'];
        foreach ($fields as $f) $this->setConfig($f, input($f, ''));
        $this->setConfig('mail_enabled', (int)input('mail_enabled', 0));
        $this->setConfig('mail_reg_login', (int)input('mail_reg_login', 0));
        log_action('operation', '更新邮箱配置', admin_user()['id'], 'admin');
        ok([], '保存成功');
    }

    public function testMail()
    {
        $to = trim(input('to', ''));
        if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) fail('请输入有效邮箱');
        $cfg = site_config();
        if (empty($cfg['mail_host']) || empty($cfg['mail_user'])) fail('请先配置SMTP信息');
        $sent = @mail($to, '管备云测试邮件', '这是一封来自管备云备案系统的测试邮件。', 'From: ' . ($cfg['mail_from'] ?: $cfg['mail_user']));
        if ($sent) ok([], '测试邮件已发送');
        fail('邮件发送失败，请检查SMTP配置');
    }

    /** 聚合登录配置 */
    public function oauth()
    {
        $this->view('admin/oauth', [
            'pageTitle' => '聚合登录配置', 'crumb' => '系统配置 / 聚合登录配置',
            'activeMenu' => 'system', 'activeSub' => 'oauth',
            'cfg' => site_config(),
        ], 'admin');
    }

    public function saveOauth()
    {
        $this->setConfig('oauth_enabled', (int)input('oauth_enabled', 0));
        foreach (['oauth_qq_id','oauth_qq_secret','oauth_qq_callback','oauth_wechat_id','oauth_wechat_secret','oauth_wechat_callback','oauth_alipay_id','oauth_alipay_secret','oauth_alipay_callback'] as $f) {
            $this->setConfig($f, trim(input($f, '')));
        }
        // 若启用聚合登录, 校验至少配置一个平台
        if ((int)input('oauth_enabled', 0) === 1) {
            $hasQq = input('oauth_qq_id') && input('oauth_qq_secret');
            $hasWx = input('oauth_wechat_id') && input('oauth_wechat_secret');
            $hasAp = input('oauth_alipay_id') && input('oauth_alipay_secret');
            if (!$hasQq && !$hasWx && !$hasAp) {
                fail('开启聚合登录需至少完整配置一个平台的 APPID 与 Secret');
            }
        }
        log_action('operation', '更新聚合登录配置', admin_user()['id'], 'admin');
        ok([], '保存成功');
    }

    /** 认证申请管理 */
    public function certApply()
    {
        $this->applyList(['enterprise', 'personal'], '申请管理', 'cert-apply');
    }
    /** 合作方申请管理 */
    public function partnerApply()
    {
        $this->applyList(['partner'], '合作方申请管理', 'partner-apply');
    }
    private function applyList(array $types, $title, $sub)
    {
        [$page, $size, $offset] = page_params();
        $in = implode(',', array_map(fn($t) => "'$t'", $types));
        $total = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('applications') . " WHERE type IN ($in)");
        $rows = db()->query("SELECT a.*, u.username FROM " . db()->table('applications') . " a LEFT JOIN " . db()->table('users') . " u ON u.id=a.user_id WHERE a.type IN ($in) ORDER BY a.id DESC LIMIT $offset,$size");
        $this->view('admin/apply_list', [
            'pageTitle' => $title, 'crumb' => '认证管理 / ' . $title,
            'activeMenu' => 'auth', 'activeSub' => $sub,
            'rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size,
        ], 'admin');
    }

    public function auditCertApp()
    {
        return $this->auditApp();
    }

    /** 首页公示管理 */
    public function publicity()
    {
        [$page, $size, $offset] = page_params();
        $type = input('type', '');
        $where = '1=1'; $params = [];
        if ($type) { $where .= " AND type = ?"; $params[] = $type; }
        $total = db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('publicity') . " WHERE $where", $params);
        $rows = db()->query("SELECT * FROM " . db()->table('publicity') . " WHERE $where ORDER BY id DESC LIMIT $offset,$size", $params);
        $this->view('admin/publicity', [
            'pageTitle' => '首页公示管理', 'crumb' => '认证管理 / 首页公示管理',
            'activeMenu' => 'auth', 'activeSub' => 'publicity',
            'rows' => $rows, 'total' => (int)$total, 'page' => $page, 'size' => $size, 'type' => $type,
        ], 'admin');
    }

    public function savePublicity()
    {
        $id = (int)input('id', 0);
        $type = input('type', 'partner');
        $data = [
            'type' => $type,
            'title' => trim(input('title', '')),
            'content' => input('content', ''),
            'link' => trim(input('link', '')),
            'status' => (int)input('status', 1),
            'sort' => (int)input('sort', 0),
            'image' => trim(input('image', '')),
            'icp_no' => trim(input('icp_no', '')),
            'reason' => trim(input('reason', '')),
        ];
        // user_id 备案用户 (允许为空)
        $uid = (int)input('user_id', 0);
        $data['user_id'] = $uid > 0 ? $uid : null;
        // 合作方类型若上传了图片则保存
        if ($type === 'partner' && !empty($_FILES['image_file'])) {
            try {
                $rel = $this->handleUpload('image_file', 'publicity');
                $data['image'] = $rel;
            } catch (Throwable $e) {}
        }
        if (!$data['title']) fail('请输入标题');
        if ($id) {
            db()->update('publicity', $data, 'id = :id', ['id' => $id]);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            db()->insert('publicity', $data);
        }
        log_action('operation', "保存公示 {$data['title']}", admin_user()['id'], 'admin');
        ok([], '保存成功');
    }

    /** 通用文件上传 (返回相对路径) */
    private function handleUpload($field, $subDir)
    {
        if (empty($_FILES[$field])) return '';
        $file = $_FILES[$field];
        if ($file['error'] !== UPLOAD_ERR_OK) return '';
        if ($file['size'] > config('upload.max_size')) fail('文件过大');
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, config('upload.allow'))) fail('不支持的文件类型');
        $dir = config('upload.path') . '/' . $subDir;
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $name = $subDir . '_' . date('YmdHis') . '_' . random_str(6) . '.' . $ext;
        $path = $dir . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $path)) fail('保存失败');
        return 'uploads/' . $subDir . '/' . $name;
    }

    public function deletePublicity()
    {
        $id = (int)input('id', 0);
        db()->delete('publicity', 'id = ?', [$id]);
        ok([], '已删除');
    }

    /** 日志 */
    public function systemLogs()
    {
        $this->showLogs('system', '系统日志', 'log-system');
    }
    public function loginLogs()
    {
        $this->showLogs('login', '登录日志', 'log-login');
    }
    public function operationLogs()
    {
        $this->showLogs('operation', '操作日志', 'log-operation');
    }
    private function showLogs($type, $title, $sub)
    {
        [$page, $size, $offset] = page_params();
        $total = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('logs') . " WHERE type = ?", [$type]);
        $rows = db()->query("SELECT * FROM " . db()->table('logs') . " WHERE type = ? ORDER BY id DESC LIMIT $offset,$size", [$type]);
        $this->view('admin/logs', [
            'pageTitle' => $title, 'crumb' => '日志管理 / ' . $title,
            'activeMenu' => 'logs', 'activeSub' => $sub,
            'rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size, 'logType' => $type,
        ], 'admin');
    }

    public function clearLogs()
    {
        $type = input('type', '');
        if ($type) db()->execute("DELETE FROM " . db()->table('logs') . " WHERE type = ?", [$type]);
        else db()->execute("DELETE FROM " . db()->table('logs'));
        log_action('operation', "清空日志 type={$type}", admin_user()['id'], 'admin');
        ok([], '已清空');
    }

    // ===== 辅助 =====
    private function gatherStats()
    {
        $s = ['users' => 0, 'filings' => 0, 'filingPending' => 0, 'filingPassed' => 0, 'filingRejected' => 0, 'feedbacks' => 0, 'reports' => 0, 'tickets' => 0, 'ticketsPending' => 0, 'today' => 0];
        try {
            $p = db()->prefix();
            $s['users'] = (int)db()->queryScalar("SELECT COUNT(*) FROM {$p}users");
            $s['filings'] = (int)db()->queryScalar("SELECT COUNT(*) FROM {$p}filings");
            $s['filingPending'] = (int)db()->queryScalar("SELECT COUNT(*) FROM {$p}filings WHERE status=0");
            $s['filingPassed'] = (int)db()->queryScalar("SELECT COUNT(*) FROM {$p}filings WHERE status=1");
            $s['filingRejected'] = (int)db()->queryScalar("SELECT COUNT(*) FROM {$p}filings WHERE status=2");
            $s['feedbacks'] = (int)db()->queryScalar("SELECT COUNT(*) FROM {$p}feedbacks WHERE type='feedback'");
            $s['reports'] = (int)db()->queryScalar("SELECT COUNT(*) FROM {$p}feedbacks WHERE type='report'");
            $s['tickets'] = (int)db()->queryScalar("SELECT COUNT(*) FROM {$p}tickets");
            $s['ticketsPending'] = (int)db()->queryScalar("SELECT COUNT(*) FROM {$p}tickets WHERE status=0");
            $s['today'] = (int)db()->queryScalar("SELECT COUNT(*) FROM {$p}filings WHERE DATE(created_at)=CURDATE()");
        } catch (Throwable $e) {}
        return $s;
    }

    private function setConfig($name, $value)
    {
        set_site_config($name, $value);
    }
}

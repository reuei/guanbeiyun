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
        if ($status === 1 && $icpNo) $data['icp_no'] = $icpNo;
        elseif ($status === 1 && !$filing['icp_no']) $data['icp_no'] = $this->genIcpNo();
        db()->update('filings', $data, 'id = :id', ['id' => $id]);
        log_action('operation', "审核备案 #{$id} 状态={$status}", admin_user()['id'], 'admin');
        ok([], '审核完成');
    }

    private function genIcpNo()
    {
        $p = db()->prefix();
        $year = date('Y');
        $seq = (int)db()->queryScalar("SELECT COUNT(*) FROM {$p}filings WHERE status=1") + 1000;
        return "京ICP备{$year}{$seq}号";
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
        db()->update('applications', ['status' => $status, 'audit_remark' => $remark, 'audited_at' => date('Y-m-d H:i:s'), 'audited_by' => admin_user()['id']], 'id = :id', ['id' => $id]);
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
        foreach (['oauth_qq_id','oauth_qq_secret','oauth_wechat_id','oauth_wechat_secret','oauth_alipay_id','oauth_alipay_secret'] as $f) {
            $this->setConfig($f, input($f, ''));
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
        $data = [
            'type' => input('type', 'partner'),
            'title' => trim(input('title', '')),
            'content' => input('content', ''),
            'link' => trim(input('link', '')),
            'status' => (int)input('status', 1),
            'sort' => (int)input('sort', 0),
        ];
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
        $exists = db()->queryOne("SELECT id FROM " . db()->table('config') . " WHERE name = ?", [$name]);
        if ($exists) {
            db()->update('config', ['value' => $value, 'updated_at' => date('Y-m-d H:i:s')], 'name = :n', ['n' => $name]);
        } else {
            db()->insert('config', ['name' => $name, 'value' => $value, 'updated_at' => date('Y-m-d H:i:s')]);
        }
    }
}

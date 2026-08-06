<?php
/**
 * 聊天室管理后台控制器 (v6)
 *
 * 路径: /admins (与平台后台 /admin 分开, 仅聊天室管理员/超管/平台管理可访问)
 *
 * 功能:
 *   管理员 (admin):
 *     - 创建聊天版块
 *     - 发布全局公告 (展示在聊天室最上方, 字数超10字滚动, 10分钟内自动删除)
 *     - 禁言用户 (10分钟/30分钟/1小时/3小时/1天) + 通知用户
 *     - 撤回成员消息 (长按消息选择撤回, 复用 ChatController@recall)
 *     - 封用户头衔 (修改 title_text/level/title_bg)
 *     - @全体成员 (复用 ChatController@send)
 *
 *   超管 (super_admin/platform_admin) 在管理员基础上增加:
 *     - 发布全体禁言 (chat_global_mute 配置)
 *     - 封禁用户 (修改用户状态 status=0, 后台可处理)
 *     - 发布聊天室弹窗公告 (scope=popup)
 *     - 封用户为管理 (修改 chat_role)
 */
class ChatAdminController extends Controller
{
    public function __construct()
    {
        $noAuth = ['/admins/login', '/admins/doLogin', '/admins/logout'];
        $path = request_path();
        if (in_array($path, $noAuth, true)) return;

        // 优先使用管理员后台登录态, 否则使用聊天室角色
        if (is_admin_logged_in()) {
            // 平台后台管理员默认视为平台管理
            return;
        }

        if (!is_logged_in()) {
            if ($this->isAjax()) fail('请先登录', 401);
            redirect(site_url('admins/login'));
        }

        $userId = (int)(current_user()['id'] ?? 0);
        if (!is_chat_admin($userId)) {
            if ($this->isAjax()) fail('无权访问聊天室管理后台', 403);
            redirect(site_url('chat'));
        }
    }

    /** 是否 AJAX */
    private function isAjax()
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') return true;
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return strpos($accept, 'json') !== false;
    }

    /** 当前操作者角色 (admin/super_admin/platform_admin) */
    private function myRole()
    {
        if (is_admin_logged_in() && !is_logged_in()) return 'platform_admin';
        $uid = (int)(current_user()['id'] ?? 0);
        $role = chat_user_role($uid);
        return in_array($role, ['admin', 'super_admin', 'platform_admin'], true) ? $role : 'admin';
    }

    /** 当前操作者是否超管 */
    private function myIsSuper()
    {
        $role = $this->myRole();
        return in_array($role, ['super_admin', 'platform_admin'], true);
    }

    /** 当前操作者 ID */
    private function myId()
    {
        if (is_admin_logged_in() && !is_logged_in()) return (int)(admin_user()['id'] ?? 0);
        return (int)(current_user()['id'] ?? 0);
    }

    /** 登录页 (独立页面, 不使用任何布局) */
    public function login()
    {
        // 已登录平台后台管理员, 直接进入聊天室后台 (视为平台管理)
        if (is_admin_logged_in()) {
            redirect(site_url('admins'));
        }
        // 已登录前台用户且为聊天室管理员, 直接进入
        if (is_logged_in()) {
            $uid = (int)(current_user()['id'] ?? 0);
            if (is_chat_admin($uid)) redirect(site_url('admins'));
        }
        // 使用 null 布局, 完全独立页面
        $this->view('chat_admin/login', [
            'pageTitle' => '聊天室管理登录',
            'active'    => 'chat',
            'site'      => site_config(),
        ], null);
    }

    /** 处理登录 - 同时支持平台后台 admin 账号和前台聊天室管理员账号 */
    public function doLogin()
    {
        $username = trim(input('username', ''));
        $password = input('password', '');
        if (!$username || !$password) fail('请输入账号和密码');
        try {
            // 1. 优先尝试平台后台 admins 表 (平台管理)
            $admin = db()->queryOne("SELECT * FROM " . db()->table('admins') . " WHERE username = ? LIMIT 1", [$username]);
            if ($admin && verify_password($password, $admin['password'])) {
                db()->update('admins', ['last_login' => date('Y-m-d H:i:s'), 'last_ip' => $_SERVER['REMOTE_ADDR'] ?? ''], 'id = :id', ['id' => $admin['id']]);
                unset($admin['password']);
                $_SESSION['gb_admin'] = $admin;
                log_action('login', "平台管理员登录聊天室后台: {$username}", (int)$admin['id'], 'admin');
                ok(['redirect' => site_url('admins')], '登录成功');
                return;
            }
            // 2. 回退到前台 users 表 (聊天室管理员/超管)
            $user = db()->queryOne("SELECT * FROM " . db()->table('users') . " WHERE username = ? AND status = 1 LIMIT 1", [$username]);
            if (!$user || !verify_password($password, $user['password'])) {
                fail('账号或密码错误');
            }
            if (!is_chat_admin((int)$user['id'])) {
                fail('您没有聊天室管理权限');
            }
            unset($user['password']);
            $_SESSION['gb_user'] = $user;
            log_action('login', "聊天室管理员登录: {$username}", (int)$user['id'], 'user');
            ok(['redirect' => site_url('admins')], '登录成功');
        } catch (Throwable $e) {
            fail('系统异常: ' . $e->getMessage());
        }
    }

    public function logout()
    {
        // 仅清除聊天室后台登录态, 不影响平台后台 /admin 的登录
        unset($_SESSION['gb_user']);
        // 若仅是平台后台 admin 登录(无前台用户), 同样清除, 回到登录页
        if (is_admin_logged_in() && !is_logged_in()) {
            unset($_SESSION['gb_admin']);
        }
        redirect(site_url('admins/login'));
    }

    /** 默认跳到概览 */
    public function index()
    {
        redirect(site_url('admins/dashboard'));
    }

    /** 概览 */
    public function dashboard()
    {
        $stats = $this->gatherStats();
        $this->view('chat_admin/dashboard', [
            'pageTitle'  => '聊天室管理 - 概览',
            'active'     => 'admins',
            'activeSub'  => 'dashboard',
            'myRole'     => $this->myRole(),
            'myIsSuper'  => $this->myIsSuper(),
            'stats'      => $stats,
        ], 'chat_admin');
    }

    /** 统计数据 */
    private function gatherStats()
    {
        $stats = [
            'rooms'         => 0,
            'messages'      => 0,
            'online'        => 0,
            'admins'        => 0,
            'banned'        => 0,
            'announcements' => 0,
            'today_msgs'    => 0,
            'today_users'   => 0,
        ];
        try {
            $p = db()->prefix();
            $stats['rooms']         = (int)db()->queryScalar("SELECT COUNT(*) FROM {$p}chat_rooms");
            $stats['messages']      = (int)db()->queryScalar("SELECT COUNT(*) FROM {$p}chat_messages WHERE is_recalled=0");
            $stats['online']        = (int)db()->queryScalar("SELECT COUNT(*) FROM {$p}chat_online WHERE last_active >= (NOW() - INTERVAL 30 SECOND)");
            $stats['admins']        = (int)db()->queryScalar("SELECT COUNT(*) FROM {$p}user_titles WHERE chat_role IN ('admin','super_admin','platform_admin')");
            $stats['banned']        = (int)db()->queryScalar("SELECT COUNT(*) FROM {$p}chat_banned WHERE banned_until > NOW()");
            $stats['announcements'] = (int)db()->queryScalar("SELECT COUNT(*) FROM {$p}chat_announcements WHERE expire_at > NOW()");
            $stats['today_msgs']    = (int)db()->queryScalar("SELECT COUNT(*) FROM {$p}chat_messages WHERE DATE(created_at) = CURDATE()");
            $stats['today_users']   = (int)db()->queryScalar("SELECT COUNT(DISTINCT user_id) FROM {$p}chat_messages WHERE DATE(created_at) = CURDATE()");
        } catch (Throwable $e) {}
        return $stats;
    }

    // ===== 聊天版块管理 =====

    /** 版块列表 */
    public function rooms()
    {
        $rooms = db()->query("SELECT r.*, (SELECT COUNT(*) FROM " . db()->table('chat_online') . " o WHERE o.room_id = r.id AND o.last_active >= (NOW() - INTERVAL 30 SECOND)) AS online_count FROM " . db()->table('chat_rooms') . " r ORDER BY r.sort DESC, r.id ASC");
        $this->view('chat_admin/rooms', [
            'pageTitle' => '聊天版块管理',
            'active'    => 'admins',
            'activeSub' => 'rooms',
            'myRole'    => $this->myRole(),
            'myIsSuper' => $this->myIsSuper(),
            'rooms'     => $rooms,
        ], 'chat_admin');
    }

    /** 保存版块 (新增/修改) */
    public function saveRoom()
    {
        $id   = (int)input('id', 0);
        $name = trim((string)input('name', ''));
        $desc = trim((string)input('description', ''));
        $icon = trim((string)input('icon', '💬'));
        $sort = (int)input('sort', 0);
        $status = (int)input('status', 1) ? 1 : 0;

        if ($name === '') fail('版块名称不能为空');
        if (mb_strlen($name) > 100) fail('版块名称过长');
        if (mb_strlen($desc) > 500) fail('版块描述过长');

        if ($id > 0) {
            $exist = db()->queryOne("SELECT id FROM " . db()->table('chat_rooms') . " WHERE id=?", [$id]);
            if (!$exist) fail('版块不存在');
            db()->update('chat_rooms', [
                'name' => $name, 'description' => $desc, 'icon' => $icon,
                'sort' => $sort, 'status' => $status,
            ], 'id = :id', ['id' => $id]);
            log_action('operation', "修改聊天版块 #{$id} ({$name})", $this->myId(), 'admin');
            ok([], '修改成功');
        } else {
            $newId = db()->insert('chat_rooms', [
                'name' => $name, 'description' => $desc, 'icon' => $icon,
                'sort' => $sort, 'status' => $status, 'created_by' => $this->myId(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            log_action('operation', "创建聊天版块 #{$newId} ({$name})", $this->myId(), 'admin');
            ok(['id' => $newId], '创建成功');
        }
    }

    /** 删除版块 */
    public function deleteRoom()
    {
        $id = (int)input('id', 0);
        if (!$id) fail('参数错误');
        db()->delete('chat_rooms', 'id=?', [$id]);
        // 同步删除该版块的消息 (软删, 标记撤回)
        try {
            db()->execute("UPDATE " . db()->table('chat_messages') . " SET is_recalled=1, recalled_by=?, recalled_at=? WHERE room_id=?", [$this->myId(), date('Y-m-d H:i:s'), $id]);
        } catch (Throwable $e) {}
        log_action('operation', "删除聊天版块 #{$id}", $this->myId(), 'admin');
        ok([], '已删除');
    }

    // ===== 公告管理 =====

    /** 公告列表/发布 */
    public function announcements()
    {
        $rows = db()->query("SELECT a.*, u.username FROM " . db()->table('chat_announcements') . " a LEFT JOIN " . db()->table('users') . " u ON u.id = a.created_by ORDER BY a.id DESC LIMIT 100");
        // 清理过期
        try {
            db()->execute("DELETE FROM " . db()->table('chat_announcements') . " WHERE expire_at <= NOW()");
        } catch (Throwable $e) {}
        $this->view('chat_admin/announcements', [
            'pageTitle' => '聊天室公告管理',
            'active'    => 'admins',
            'activeSub' => 'announcements',
            'myRole'    => $this->myRole(),
            'myIsSuper' => $this->myIsSuper(),
            'rows'      => $rows,
        ], 'chat_admin');
    }

    /** 发布公告 */
    public function saveAnnouncement()
    {
        $content = trim((string)input('content', ''));
        $scope   = (string)input('scope', 'global');
        $roomId  = (int)input('room_id', 0);
        $durationMin = (int)input('duration_min', 10);

        if ($content === '') fail('公告内容不能为空');
        if (mb_strlen($content) > 500) fail('公告内容过长(500字符以内)');

        // 超管才能发弹窗公告
        if ($scope === 'popup' && !$this->myIsSuper()) {
            $scope = 'global';
        }
        if (!in_array($scope, ['global', 'popup'], true)) $scope = 'global';

        // 持续时间: 默认 10 分钟, 最长 24 小时
        $durationMin = max(1, min(1440, $durationMin));
        $expireAt = date('Y-m-d H:i:s', time() + $durationMin * 60);

        $newId = db()->insert('chat_announcements', [
            'content'    => $content,
            'scope'      => $scope,
            'room_id'    => $roomId > 0 ? $roomId : null,
            'created_by' => $this->myId(),
            'expire_at'  => $expireAt,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // 弹窗公告: 给全体在线用户发通知
        if ($scope === 'popup') {
            try {
                $users = db()->query("SELECT DISTINCT user_id FROM " . db()->table('chat_online') . " WHERE last_active >= (NOW() - INTERVAL 30 SECOND)");
                foreach ($users as $u) {
                    send_notification((int)$u['user_id'], '聊天室公告', mb_substr($content, 0, 100), 'chat', site_url('chat'));
                }
            } catch (Throwable $e) {}
        }

        log_action('operation', "发布聊天室{$scope}公告 #{$newId}", $this->myId(), 'admin');
        ok(['id' => $newId, 'expire_at' => $expireAt], '发布成功');
    }

    /** 删除公告 (提前撤销) */
    public function deleteAnnouncement()
    {
        $id = (int)input('id', 0);
        if (!$id) fail('参数错误');
        db()->delete('chat_announcements', 'id=?', [$id]);
        ok([], '已删除');
    }

    // ===== 用户头衔管理 =====

    /** 用户头衔列表 */
    public function titles()
    {
        $kw = trim((string)input('kw', ''));
        [$page, $size, $offset] = page_params();
        $where = '1=1';
        $params = [];
        if ($kw !== '') {
            $where .= " AND (u.username LIKE ? OR u.email LIKE ?)";
            $p = "%{$kw}%";
            array_push($params, $p, $p);
        }
        $sql = "SELECT u.id AS user_id, u.username, u.email, u.avatar, u.status, u.created_at,
                       t.title_text, t.level, t.title_bg, t.chat_role, t.updated_at
                FROM " . db()->table('users') . " u
                LEFT JOIN " . db()->table('user_titles') . " t ON t.user_id = u.id
                WHERE {$where} ORDER BY u.id DESC LIMIT {$offset},{$size}";
        $rows = db()->query($sql, $params);

        $total = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('users') . " u WHERE {$where}", $params);

        $this->view('chat_admin/titles', [
            'pageTitle' => '用户头衔管理',
            'active'    => 'admins',
            'activeSub' => 'titles',
            'myRole'    => $this->myRole(),
            'myIsSuper' => $this->myIsSuper(),
            'rows'      => $rows,
            'total'     => $total,
            'page'      => $page,
            'size'      => $size,
            'kw'        => $kw,
        ], 'chat_admin');
    }

    /** 保存用户头衔 (文字/等级/背景) */
    public function saveTitle()
    {
        $userId = (int)input('user_id', 0);
        if (!$userId) fail('参数错误');

        $titleText = trim((string)input('title_text', ''));
        $level     = (int)input('level', 1);
        $titleBg   = trim((string)input('title_bg', ''));
        $chatRole  = (string)input('chat_role', 'user');

        if (mb_strlen($titleText) > 50) fail('头衔文字过长');
        if ($level < 1 || $level > 99) fail('等级范围 1-99');
        if (mb_strlen($titleBg) > 50) $titleBg = '';

        // 角色权限校验: 仅超管可封管理员/超管
        if (!$this->myIsSuper()) {
            // 普通管理员只能改 user/member 的头衔, 不能改 admin/super/platform
            $current = chat_user_role($userId);
            if (in_array($current, ['admin', 'super_admin', 'platform_admin'], true)) {
                fail('无权修改管理员的头衔', 403);
            }
            // 也不能直接把别人升级为管理员
            if (in_array($chatRole, ['admin', 'super_admin', 'platform_admin'], true)) {
                fail('仅超管可封管理员', 403);
            }
            $chatRole = in_array($chatRole, ['user', 'member'], true) ? $chatRole : 'user';
        } else {
            // 超管不能撤销自己或更高权限 (platform_admin 不可被超管操作)
            $current = chat_user_role($userId);
            if ($current === 'platform_admin' && $this->myRole() !== 'platform_admin') {
                fail('无权修改平台管理员', 403);
            }
            if (!in_array($chatRole, ['user', 'member', 'admin', 'super_admin', 'platform_admin'], true)) {
                $chatRole = 'user';
            }
        }

        $exist = db()->queryOne("SELECT id FROM " . db()->table('user_titles') . " WHERE user_id=?", [$userId]);
        $data = [
            'title_text' => $titleText,
            'level'      => $level,
            'title_bg'   => $titleBg,
            'chat_role'  => $chatRole,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($exist) {
            db()->update('user_titles', $data, 'id = :id', ['id' => $exist['id']]);
        } else {
            $data['user_id'] = $userId;
            db()->insert('user_titles', $data);
        }

        // 如果封为管理员/超管, 通知用户
        if (in_array($chatRole, ['admin', 'super_admin', 'platform_admin'], true)) {
            $roleLabel = role_label($chatRole);
            $roleText = $roleLabel ? $roleLabel['text'] : $chatRole;
            send_notification($userId, '您已被任命为聊天室' . $roleText, '管理员已将您任命为聊天室' . $roleText . '，请遵守聊天室规范并协助管理。', 'chat', site_url('admins'));
        }

        log_action('operation', "修改用户 #{$userId} 头衔: {$titleText}/Lv{$level}/{$chatRole}", $this->myId(), 'admin');
        ok([], '保存成功');
    }

    /** 封用户为管理员 (超管) */
    public function setRole()
    {
        if (!$this->myIsSuper()) fail('仅超管可执行此操作', 403);
        $userId = (int)input('user_id', 0);
        $chatRole = (string)input('chat_role', 'admin');
        if (!$userId) fail('参数错误');
        if (!in_array($chatRole, ['user', 'member', 'admin', 'super_admin'], true)) fail('角色无效');

        // 不能修改 platform_admin
        $current = chat_user_role($userId);
        if ($current === 'platform_admin') fail('无权修改平台管理员', 403);

        $exist = db()->queryOne("SELECT id FROM " . db()->table('user_titles') . " WHERE user_id=?", [$userId]);
        if ($exist) {
            db()->update('user_titles', ['chat_role' => $chatRole, 'updated_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $exist['id']]);
        } else {
            db()->insert('user_titles', ['user_id' => $userId, 'chat_role' => $chatRole, 'level' => 1, 'title_text' => '', 'title_bg' => '', 'updated_at' => date('Y-m-d H:i:s')]);
        }

        $roleLabel = role_label($chatRole);
        $roleText = $roleLabel ? $roleLabel['text'] : $chatRole;
        $msg = ($chatRole === 'user' || $chatRole === 'member')
            ? '您的聊天室' . ($chatRole === 'member' ? '成员' : '普通用户') . '身份已恢复'
            : '您已被任命为聊天室' . $roleText;
        send_notification($userId, '聊天室身份变更', $msg, 'chat', site_url('admins'));

        log_action('operation', "封用户 #{$userId} 为 {$chatRole}", $this->myId(), 'admin');
        ok([], '已设置');
    }

    // ===== 禁言管理 =====

    /** 禁言用户列表 */
    public function banned()
    {
        $rows = db()->query("SELECT b.*, u.username, u.avatar FROM " . db()->table('chat_banned') . " b LEFT JOIN " . db()->table('users') . " u ON u.id = b.user_id ORDER BY b.id DESC LIMIT 200");
        $this->view('chat_admin/banned', [
            'pageTitle' => '禁言用户管理',
            'active'    => 'admins',
            'activeSub' => 'banned',
            'myRole'    => $this->myRole(),
            'myIsSuper' => $this->myIsSuper(),
            'rows'      => $rows,
        ], 'chat_admin');
    }

    /** 禁言用户 (10分钟/30分钟/1小时/3小时/1天) */
    public function banUser()
    {
        $userId = (int)input('user_id', 0);
        $duration = (int)input('duration', 10); // 分钟
        $reason = trim((string)input('reason', ''));

        if (!$userId) fail('参数错误');
        if (!in_array($duration, [10, 30, 60, 180, 1440], true)) {
            // 允许自定义, 但限制在 1-10080 (一周) 分钟
            $duration = max(1, min(10080, $duration));
        }

        // 不能禁言超管/平台管理
        $targetRole = chat_user_role($userId);
        if (in_array($targetRole, ['super_admin', 'platform_admin'], true)) {
            fail('无权禁言超管', 403);
        }
        // 普通管理员不能禁言其他管理员
        if (!$this->myIsSuper() && $targetRole === 'admin') {
            fail('无权禁言其他管理员', 403);
        }

        $bannedUntil = date('Y-m-d H:i:s', time() + $duration * 60);
        db()->insert('chat_banned', [
            'user_id'      => $userId,
            'reason'       => $reason !== '' ? $reason : '管理员手动禁言',
            'banned_until' => $bannedUntil,
            'source'       => 'manual',
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        // 通知用户
        $durText = $this->durationText($duration);
        send_notification($userId, '您已被禁言', "您已被聊天室管理员禁言 {$durText}，截止时间：{$bannedUntil}。原因：{$reason}", 'chat', site_url('chat'));

        log_action('operation', "禁言用户 #{$userId} {$durText}", $this->myId(), 'admin');
        ok(['banned_until' => $bannedUntil], '已禁言 ' . $durText);
    }

    /** 解除禁言 */
    public function unbanUser()
    {
        $userId = (int)input('user_id', 0);
        if (!$userId) fail('参数错误');
        db()->execute("UPDATE " . db()->table('chat_banned') . " SET banned_until = NOW() WHERE user_id=? AND banned_until > NOW()", [$userId]);
        send_notification($userId, '禁言已解除', '您的聊天室禁言已被管理员解除', 'chat', site_url('chat'));
        log_action('operation', "解除禁言用户 #{$userId}", $this->myId(), 'admin');
        ok([], '已解除');
    }

    private function durationText($min)
    {
        if ($min < 60) return $min . '分钟';
        if ($min < 1440) return ($min / 60) . '小时';
        return ($min / 1440) . '天';
    }

    // ===== 消息管理 =====

    /** 消息列表 (可撤回) */
    public function messages()
    {
        $kw = trim((string)input('kw', ''));
        $roomId = (int)input('room_id', 0);
        [$page, $size, $offset] = page_params();
        $where = '1=1';
        $params = [];
        if ($roomId > 0) {
            $where .= " AND m.room_id = ?";
            $params[] = $roomId;
        }
        if ($kw !== '') {
            $where .= " AND m.content LIKE ?";
            $params[] = "%{$kw}%";
        }
        $total = (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('chat_messages') . " m WHERE {$where}", $params);
        $rows = db()->query("SELECT m.*, u.username, u.avatar FROM " . db()->table('chat_messages') . " m LEFT JOIN " . db()->table('users') . " u ON u.id = m.user_id WHERE {$where} ORDER BY m.id DESC LIMIT {$offset},{$size}", $params);

        $rooms = chat_rooms_list();
        $this->view('chat_admin/messages', [
            'pageTitle' => '聊天消息管理',
            'active'    => 'admins',
            'activeSub' => 'messages',
            'myRole'    => $this->myRole(),
            'myIsSuper' => $this->myIsSuper(),
            'rows'      => $rows,
            'total'     => $total,
            'page'      => $page,
            'size'      => $size,
            'kw'        => $kw,
            'roomId'    => $roomId,
            'rooms'     => $rooms,
        ], 'chat_admin');
    }

    /** 撤回消息 */
    public function recallMessage()
    {
        $messageId = (int)input('message_id', 0);
        if (!$messageId) fail('参数错误');

        $msg = db()->queryOne("SELECT id, user_id, content, is_recalled FROM " . db()->table('chat_messages') . " WHERE id=?", [$messageId]);
        if (!$msg) fail('消息不存在');
        if ($msg['is_recalled']) fail('消息已被撤回');

        $msgUserRole = chat_user_role((int)$msg['user_id']);
        if (!$this->myIsSuper() && in_array($msgUserRole, ['super_admin', 'platform_admin'], true)) {
            fail('无权撤回超管消息', 403);
        }

        db()->execute("UPDATE " . db()->table('chat_messages') . " SET is_recalled=1, recalled_by=?, recalled_at=? WHERE id=?", [$this->myId(), date('Y-m-d H:i:s'), $messageId]);

        if ((int)$msg['user_id'] !== $this->myId()) {
            send_notification((int)$msg['user_id'], '消息被撤回', '您在聊天室发送的消息 "' . mb_substr($msg['content'], 0, 30) . '..." 被管理员撤回', 'chat', site_url('chat'));
        }
        log_action('operation', "撤回聊天消息 #{$messageId}", $this->myId(), 'admin');
        ok([], '已撤回');
    }

    /** 删除消息 (彻底删除, 仅超管) */
    public function deleteMessage()
    {
        if (!$this->myIsSuper()) fail('仅超管可彻底删除消息', 403);
        $messageId = (int)input('message_id', 0);
        if (!$messageId) fail('参数错误');
        db()->delete('chat_messages', 'id=?', [$messageId]);
        log_action('operation', "删除聊天消息 #{$messageId}", $this->myId(), 'admin');
        ok([], '已删除');
    }

    // ===== 全局禁言 (超管) =====

    /** 全局禁言开关 (超管) */
    public function toggleGlobalMute()
    {
        if (!$this->myIsSuper()) fail('仅超管可执行此操作', 403);
        $enabled = (int)input('enabled', 0) ? 1 : 0;
        $this->setConfig('chat_global_mute', $enabled);
        log_action('operation', '聊天室全体禁言 ' . ($enabled ? '开启' : '关闭'), $this->myId(), 'admin');
        // 通知全体在线用户
        try {
            $users = db()->query("SELECT DISTINCT user_id FROM " . db()->table('chat_online') . " WHERE last_active >= (NOW() - INTERVAL 30 SECOND)");
            foreach ($users as $u) {
                $uid = (int)$u['user_id'];
                if ($uid === $this->myId()) continue;
                send_notification($uid, '聊天室全体禁言' . ($enabled ? '已开启' : '已解除'), $enabled ? '管理员已开启全体禁言，普通用户暂时无法发送消息' : '全体禁言已解除，您可以正常发言了', 'chat', site_url('chat'));
            }
        } catch (Throwable $e) {}
        ok([], $enabled ? '全体禁言已开启' : '全体禁言已关闭');
    }

    // ===== 封禁用户 (超管, 修改 status=0) =====

    /** 封禁用户 (停用账号, 仅超管) */
    public function banAccount()
    {
        if (!$this->myIsSuper()) fail('仅超管可执行此操作', 403);
        $userId = (int)input('user_id', 0);
        $status = (int)input('status', 0) ? 1 : 0;
        if (!$userId) fail('参数错误');

        $targetRole = chat_user_role($userId);
        if (in_array($targetRole, ['super_admin', 'platform_admin'], true)) {
            fail('无权封禁超管', 403);
        }

        db()->update('users', ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $userId]);
        if ($status === 0) {
            // 同步加入禁言表 (无限期)
            db()->insert('chat_banned', [
                'user_id'      => $userId,
                'reason'       => '账号已被封禁',
                'banned_until' => date('Y-m-d H:i:s', time() + 365 * 86400),
                'source'       => 'manual',
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        }
        log_action('operation', "封禁用户 #{$userId} status={$status}", $this->myId(), 'admin');
        ok([], $status ? '已解封' : '已封禁');
    }

    // ===== 在线用户 =====

    /** 在线用户 */
    public function online()
    {
        $rows = chat_online_users(0);
        $this->view('chat_admin/online', [
            'pageTitle' => '在线用户',
            'active'    => 'admins',
            'activeSub' => 'online',
            'myRole'    => $this->myRole(),
            'myIsSuper' => $this->myIsSuper(),
            'rows'      => $rows,
        ], 'chat_admin');
    }

    // ===== 工具: 设置配置 =====

    private function setConfig($name, $value)
    {
        try {
            set_site_config($name, $value);
        } catch (Throwable $e) {}
    }
}

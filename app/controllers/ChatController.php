<?php
/**
 * 聊天室控制器 (v6 重构)
 *
 * 提供聊天室页面及消息相关 AJAX 接口:
 *   GET  /chat                  聊天室页面 (需登录, 独立布局, 支持 room_id)
 *   GET  /chat/rooms            聊天版块列表页面
 *   GET  /chat/online           在线用户页面
 *   POST /chat/messages         最新约 50 条消息 (按版块过滤)
 *   POST /chat/history          向上滚动加载更早的历史消息
 *   POST /chat/send             发送消息 (含禁言/频率/刷屏/违禁词检测, 支持 @全体)
 *   POST /chat/heartbeat        在线心跳
 *   GET  /chat/online/count     在线人数
 *   GET  /chat/online/list      在线用户列表 (JSON)
 *   GET  /chat/announcements    当前生效的公告
 *   GET  /chat/quick_phrases    用户快捷语句
 *   POST /chat/quick_phrase/save   添加/修改快捷语句
 *   POST /chat/quick_phrase/delete  删除快捷语句
 *   POST /chat/recall          撤回消息 (管理员/超管)
 *   POST /chat/at_all          @全体成员 (超管)
 */
class ChatController extends Controller
{
    public function __construct()
    {
        if (!is_logged_in()) {
            if ($this->isAjax()) {
                fail('请先登录', 401);
            }
            redirect(site_url('login'));
        }
    }

    /** 是否 AJAX 请求 */
    private function isAjax()
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return strpos($accept, 'json') !== false;
    }

    /** 当前用户聊天角色 */
    private function myRole()
    {
        return chat_user_role(current_user()['id'] ?? 0);
    }

    /** 聊天室页面 */
    public function index()
    {
        $user = current_user();
        $roomId = (int)input('room', 0);
        $roomName = '';
        $room = null;
        if ($roomId > 0) {
            try {
                $room = db()->queryOne(
                    "SELECT * FROM " . db()->table('chat_rooms') . " WHERE id=? AND status=1",
                    [$roomId]
                );
                if ($room) {
                    $roomName = $room['name'];
                } else {
                    $roomId = 0;
                }
            } catch (Throwable $e) {
                $roomId = 0;
            }
        }
        // 默认版块
        if ($roomId === 0) {
            $defaultRoom = (int)site_config('chat_default_room', 0);
            if ($defaultRoom > 0) {
                try {
                    $r = db()->queryOne(
                        "SELECT * FROM " . db()->table('chat_rooms') . " WHERE id=? AND status=1",
                        [$defaultRoom]
                    );
                    if ($r) {
                        $roomId = $defaultRoom;
                        $room = $r;
                        $roomName = $r['name'];
                    }
                } catch (Throwable $e) {}
            }
        }
        // 当前用户头衔信息
        $titleInfo = user_title_info($user['id']);
        // 全局禁言状态
        $globalMute = site_config('chat_global_mute', '0') == '1';

        // 心跳更新在线状态
        chat_update_online($user['id'], $roomId);

        $this->view('chat/index', [
            'pageTitle'  => '聊天室 - ' . site_config('site_name', '管备云备案系统'),
            'active'     => 'chat',
            'roomId'     => $roomId,
            'room'       => $room,
            'roomName'   => $roomName,
            'rooms'      => chat_rooms_list(),
            'myTitle'    => $titleInfo,
            'globalMute' => $globalMute,
            'myRole'     => $this->myRole(),
        ], 'chat');
    }

    /** 聊天版块列表页面 */
    public function rooms()
    {
        $rooms = chat_rooms_list();
        // 在线人数统计
        foreach ($rooms as &$r) {
            $r['online_count'] = chat_online_count((int)$r['id']);
        }
        unset($r);
        $this->view('chat/rooms', [
            'pageTitle' => '选择区块 - 聊天室',
            'active'    => 'chat',
            'rooms'     => $rooms,
        ], 'chat');
    }

    /** 在线用户页面 */
    public function online()
    {
        $roomId = (int)input('room', 0);
        $users = chat_online_users($roomId);
        $roomName = '全部版块';
        if ($roomId > 0) {
            try {
                $r = db()->queryOne("SELECT name FROM " . db()->table('chat_rooms') . " WHERE id=?", [$roomId]);
                if ($r) $roomName = $r['name'];
            } catch (Throwable $e) {}
        }
        $this->view('chat/online', [
            'pageTitle' => '在线用户 - 聊天室',
            'active'    => 'chat',
            'users'     => $users,
            'roomId'    => $roomId,
            'roomName'  => $roomName,
        ], 'chat');
    }

    /** 在线心跳 (前端每 15 秒调用一次) */
    public function heartbeat()
    {
        $user = current_user();
        $roomId = (int)input('room_id', 0);
        chat_update_online($user['id'], $roomId);
        ok([
            'online_count' => chat_online_count($roomId),
            'server_time'  => date('Y-m-d H:i:s'),
        ]);
    }

    /** 在线人数 (JSON) */
    public function onlineCount()
    {
        $roomId = (int)input('room', 0);
        ok(['count' => chat_online_count($roomId)]);
    }

    /** 在线用户列表 (JSON) */
    public function onlineList()
    {
        $roomId = (int)input('room', 0);
        $rows = chat_online_users($roomId);
        $list = [];
        foreach ($rows as $r) {
            $list[] = [
                'user_id'    => (int)$r['user_id'],
                'username'   => $r['username'] ?? '已注销',
                'avatar'     => !empty($r['avatar']) ? asset($r['avatar']) : '',
                'level'      => (int)($r['level'] ?? 1),
                'title_text' => $r['title_text'] ?? '',
                'chat_role'  => $r['chat_role'] ?? 'user',
                'last_active'=> $r['last_active'] ?? '',
            ];
        }
        ok(['users' => $list, 'count' => count($list)]);
    }

    /** 当前生效的公告 (JSON) */
    public function announcements()
    {
        $roomId = (int)input('room', 0);
        $global = chat_active_announcements($roomId, 'global');
        $popup  = chat_active_announcements($roomId, 'popup');
        // 清理过期公告
        try {
            db()->execute("DELETE FROM " . db()->table('chat_announcements') . " WHERE expire_at <= NOW()");
        } catch (Throwable $e) {}
        ok(['global' => $global, 'popup' => $popup]);
    }

    /** 用户快捷语句 (JSON) */
    public function quickPhrases()
    {
        $user = current_user();
        $rows = chat_quick_phrases($user['id']);
        $list = [];
        foreach ($rows as $r) {
            $list[] = [
                'id'      => (int)$r['id'],
                'content' => $r['content'],
                'sort'    => (int)($r['sort'] ?? 0),
            ];
        }
        ok(['phrases' => $list]);
    }

    /** 添加/修改快捷语句 */
    public function saveQuickPhrase()
    {
        $user = current_user();
        $id = (int)input('id', 0);
        $content = trim((string)input('content', ''));
        if ($content === '') fail('内容不能为空');
        if (mb_strlen($content) > 200) fail('内容过长');
        if ($id > 0) {
            // 修改
            $exist = db()->queryOne(
                "SELECT id FROM " . db()->table('chat_quick_phrases') . " WHERE id=? AND user_id=?",
                [$id, $user['id']]
            );
            if (!$exist) fail('快捷语句不存在');
            db()->execute(
                "UPDATE " . db()->table('chat_quick_phrases') . " SET content=? WHERE id=?",
                [$content, $id]
            );
            ok(['id' => $id], '修改成功');
        } else {
            // 新增 (限制每用户最多 50 条)
            $count = (int)db()->queryScalar(
                "SELECT COUNT(*) FROM " . db()->table('chat_quick_phrases') . " WHERE user_id=?",
                [$user['id']]
            );
            if ($count >= 50) fail('最多只能添加 50 条快捷语句');
            $newId = db()->insert('chat_quick_phrases', [
                'user_id'    => $user['id'],
                'content'    => $content,
                'sort'       => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            ok(['id' => $newId], '添加成功');
        }
    }

    /** 删除快捷语句 */
    public function deleteQuickPhrase()
    {
        $user = current_user();
        $id = (int)input('id', 0);
        if (!$id) fail('参数错误');
        db()->delete('chat_quick_phrases', 'id=? AND user_id=?', [$id, $user['id']]);
        ok([], '已删除');
    }

    /** 最新消息 (约 50 条, 排除已撤回, 按版块过滤) */
    public function messages()
    {
        $roomId = (int)input('room_id', 0);
        $sql = "SELECT m.id, m.user_id, m.content, m.msg_type, m.reply_to, m.is_recalled, m.is_at_all, m.recalled_by, m.created_at, u.username, u.avatar "
             . "FROM " . db()->table('chat_messages') . " m "
             . "LEFT JOIN " . db()->table('users') . " u ON u.id = m.user_id "
             . "WHERE m.is_recalled = 0 ";
        $params = [];
        if ($roomId > 0) {
            $sql .= "AND m.room_id = ? ";
            $params[] = $roomId;
        } else {
            $sql .= "AND (m.room_id = 0 OR m.room_id IS NULL) ";
        }
        $sql .= "ORDER BY m.id DESC LIMIT 50";
        $rows = db()->query($sql, $params);
        $list = [];
        if (is_array($rows)) {
            $rows = array_reverse($rows);
            foreach ($rows as $row) {
                $list[] = $this->formatMessage($row);
            }
        }
        // 同步心跳
        chat_update_online(current_user()['id'], $roomId);
        ok([
            'messages'     => $list,
            'online_count' => chat_online_count($roomId),
        ]);
    }

    /** 历史消息 (向上滚动加载, before_id 之前的 50 条) */
    public function history()
    {
        $beforeId = (int)input('before_id', 0);
        $roomId = (int)input('room_id', 0);
        if ($beforeId <= 0) {
            ok(['messages' => []]);
        }
        $sql = "SELECT m.id, m.user_id, m.content, m.msg_type, m.reply_to, m.is_recalled, m.is_at_all, m.recalled_by, m.created_at, u.username, u.avatar "
             . "FROM " . db()->table('chat_messages') . " m "
             . "LEFT JOIN " . db()->table('users') . " u ON u.id = m.user_id "
             . "WHERE m.is_recalled = 0 AND m.id < ? ";
        $params = [$beforeId];
        if ($roomId > 0) {
            $sql .= "AND m.room_id = ? ";
            $params[] = $roomId;
        } else {
            $sql .= "AND (m.room_id = 0 OR m.room_id IS NULL) ";
        }
        $sql .= "ORDER BY m.id DESC LIMIT 50";
        $rows = db()->query($sql, $params);
        $list = [];
        if (is_array($rows)) {
            $rows = array_reverse($rows);
            foreach ($rows as $row) {
                $list[] = $this->formatMessage($row);
            }
        }
        ok(['messages' => $list]);
    }

    /** 发送消息 */
    public function send()
    {
        $user   = current_user();
        $userId = (int)$user['id'];

        $content = trim((string)input('content', ''));
        $msgType = (string)input('msg_type', 'text');
        $replyTo = input('reply_to', null);
        $roomId  = (int)input('room_id', 0);
        $isAtAll = (int)input('is_at_all', 0);

        if (!in_array($msgType, ['text', 'image', 'emoji', 'url'], true)) {
            $msgType = 'text';
        }
        if ($replyTo !== null && $replyTo !== '') {
            $replyTo = (int)$replyTo;
            if ($replyTo <= 0) {
                $replyTo = null;
            }
        } else {
            $replyTo = null;
        }

        // 图片内容: 允许相对路径(uploads/)或 data URL (前端内联上传)
        if ($msgType === 'image') {
            if ($content === ''
                || (strpos($content, 'uploads/') !== 0 && strpos($content, 'data:image/') !== 0)) {
                fail('图片地址无效');
            }
        } elseif ($content === '') {
            fail('消息内容不能为空');
        }

        // 全局禁言检查 (超管/平台管理除外)
        $globalMute = site_config('chat_global_mute', '0') == '1';
        if ($globalMute && !is_chat_super($userId)) {
            fail('聊天室已开启全体禁言, 暂时无法发送消息');
        }

        // 禁言检查
        [$banned, $bannedUntil, $reason] = chat_user_banned($userId);
        if ($banned) {
            fail('您已被禁言，截止时间：' . $bannedUntil . '，原因：' . $reason);
        }

        // @全体成员 仅超管/平台管理可用
        if ($isAtAll) {
            if (!is_chat_super($userId)) {
                $isAtAll = 0;
            }
        }

        // 频率限制: 最近 60 秒内的消息数
        $recentCount = (int)db()->queryScalar(
            "SELECT COUNT(*) FROM " . db()->table('chat_messages')
            . " WHERE user_id = ? AND created_at >= (NOW() - INTERVAL 60 SECOND)",
            [$userId]
        );
        $rateLimit = (int)site_config('chat_rate_limit', 10);
        if ($rateLimit > 0 && $recentCount >= $rateLimit) {
            fail('发送过于频繁，每分钟限' . $rateLimit . '条');
        }

        // 刷屏检测: 超过阈值自动禁言
        $spamThreshold = (int)site_config('chat_spam_threshold', 50);
        if ($spamThreshold > 0 && $recentCount >= $spamThreshold) {
            $banBase  = (int)site_config('chat_spam_ban_min', 60);
            $banMins  = $banBase * random_int(1, 3);
            $banUntil = date('Y-m-d H:i:s', time() + $banMins * 60);
            db()->insert('chat_banned', [
                'user_id'      => $userId,
                'reason'       => '刷屏自动禁言',
                'banned_until' => $banUntil,
                'source'       => 'auto',
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
            fail('检测到刷屏，已被自动禁言');
        }

        // 违禁词检测
        $matchedWord = null;
        foreach (chat_forbidden_words() as $w) {
            if ($w === '') {
                continue;
            }
            if (mb_stripos($content, $w) !== false) {
                $matchedWord = $w;
                break;
            }
        }

        if ($matchedWord !== null) {
            $messageId = db()->insert('chat_messages', [
                'user_id'     => $userId,
                'content'     => $content,
                'msg_type'    => $msgType,
                'reply_to'    => $replyTo,
                'room_id'     => $roomId,
                'is_at_all'   => 0,
                'is_recalled' => 1,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
            db()->insert('chat_violations', [
                'user_id'    => $userId,
                'message_id' => $messageId,
                'word'       => $matchedWord,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $window = max(1, (int)site_config('chat_violation_window', 30));
            $violationCount = (int)db()->queryScalar(
                "SELECT COUNT(*) FROM " . db()->table('chat_violations')
                . " WHERE user_id = ? AND created_at >= (NOW() - INTERVAL $window MINUTE)",
                [$userId]
            );
            $violationLimit = (int)site_config('chat_violation_limit', 5);
            if ($violationLimit > 0 && $violationCount >= $violationLimit) {
                $banBase  = (int)site_config('chat_violation_ban_min', 60);
                $banMins  = $banBase * random_int(1, 5);
                $banUntil = date('Y-m-d H:i:s', time() + $banMins * 60);
                db()->insert('chat_banned', [
                    'user_id'      => $userId,
                    'reason'       => '违禁词累计违规自动禁言',
                    'banned_until' => $banUntil,
                    'source'       => 'auto',
                    'created_at'   => date('Y-m-d H:i:s'),
                ]);
                fail('消息含违禁词已撤回，累计违规已被禁言至 ' . $banUntil);
            }
            fail('消息包含违禁词，已自动撤回并警告');
        }

        // 正常插入
        $messageId = db()->insert('chat_messages', [
            'user_id'     => $userId,
            'content'     => $content,
            'msg_type'    => $msgType,
            'reply_to'    => $replyTo,
            'room_id'     => $roomId,
            'is_at_all'   => $isAtAll,
            'is_recalled' => 0,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        // @全体成员: 给全体用户发通知
        if ($isAtAll) {
            try {
                send_notification(0, '全体成员通知', $user['username'] . ' 在聊天室@了全体成员: ' . mb_substr($content, 0, 50), 'chat', site_url('chat' . ($roomId ? '?room=' . $roomId : '')));
            } catch (Throwable $e) {}
        }

        // 心跳更新
        chat_update_online($userId, $roomId);

        $row = db()->queryOne(
            "SELECT m.id, m.user_id, m.content, m.msg_type, m.reply_to, m.is_recalled, m.is_at_all, m.recalled_by, m.created_at, u.username, u.avatar "
            . "FROM " . db()->table('chat_messages') . " m "
            . "LEFT JOIN " . db()->table('users') . " u ON u.id = m.user_id "
            . "WHERE m.id = ?",
            [$messageId]
        );
        ok(['message' => $this->formatMessage($row)], '发送成功');
    }

    /** 撤回消息 (管理员/超管) */
    public function recall()
    {
        $user = current_user();
        $userId = (int)$user['id'];
        $messageId = (int)input('message_id', 0);
        if (!$messageId) fail('参数错误');
        if (!is_chat_admin($userId)) fail('无权限', 403);

        $msg = db()->queryOne(
            "SELECT id, user_id, content, is_recalled FROM " . db()->table('chat_messages') . " WHERE id=?",
            [$messageId]
        );
        if (!$msg) fail('消息不存在');
        if ($msg['is_recalled']) fail('消息已被撤回');

        // 普通管理员只能撤回他人消息, 不能撤回超管消息
        $msgUserRole = chat_user_role((int)$msg['user_id']);
        if (!is_chat_super($userId) && in_array($msgUserRole, ['super_admin', 'platform_admin'], true)) {
            fail('无权撤回超管消息', 403);
        }

        db()->execute(
            "UPDATE " . db()->table('chat_messages') . " SET is_recalled=1, recalled_by=?, recalled_at=? WHERE id=?",
            [$userId, date('Y-m-d H:i:s'), $messageId]
        );
        // 通知被撤回的用户
        if ((int)$msg['user_id'] !== $userId) {
            send_notification(
                (int)$msg['user_id'],
                '消息被撤回',
                '您在聊天室发送的消息 "' . mb_substr($msg['content'], 0, 30) . '" 被管理员撤回',
                'chat',
                site_url('chat')
            );
        }
        log_action('operation', "撤回聊天室消息 #$messageId", $userId, 'admin');
        ok([], '已撤回');
    }

    /** 格式化单条消息 (含用户信息与认证标识, 头衔等级) */
    private function formatMessage($row)
    {
        if (!$row) {
            return null;
        }
        $avatar  = !empty($row['avatar']) ? asset($row['avatar']) : '';
        $replyTo = $row['reply_to'] ?? null;
        $replyTo = ($replyTo === null || $replyTo === '') ? null : (int)$replyTo;
        $userId  = (int)$row['user_id'];

        // 头衔信息
        $titleInfo = user_title_info($userId);
        $roleLabel = role_label($titleInfo['role']);

        return [
            'id'              => (int)$row['id'],
            'user_id'         => $userId,
            'username'        => $row['username'] ?? '',
            'avatar'          => $avatar,
            'content'         => (string)($row['content'] ?? ''),
            'msg_type'        => $row['msg_type'] ?? 'text',
            'reply_to'        => $replyTo,
            'is_recalled'     => (int)($row['is_recalled'] ?? 0),
            'is_at_all'       => (int)($row['is_at_all'] ?? 0),
            'recalled_by'     => isset($row['recalled_by']) ? (int)$row['recalled_by'] : null,
            'created_at'      => $row['created_at'] ?? null,
            'created_at_text' => !empty($row['created_at']) ? date('Y-m-d H:i:s', strtotime($row['created_at'])) : '',
            'certs'           => user_certifications($userId),
            'title'           => [
                'text'  => $titleInfo['text'],
                'level' => $titleInfo['level'],
                'bg'    => $titleInfo['bg'] ?: level_bg_color($titleInfo['level']),
                'role'  => $titleInfo['role'],
            ],
            'role_label'      => $roleLabel,
        ];
    }
}

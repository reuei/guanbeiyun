<?php
/**
 * 聊天室控制器
 *
 * 提供聊天室页面及消息相关 AJAX 接口:
 *   GET  /chat            页面渲染 (需登录)
 *   POST /chat/messages   最新约 50 条消息
 *   POST /chat/history    向上滚动加载更早的历史消息
 *   POST /chat/send       发送消息 (含禁言/频率/刷屏/违禁词检测)
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

    /** 聊天室页面 */
    public function index()
    {
        $this->view('chat/index', [
            'pageTitle' => '聊天室 - ' . site_config('site_name', '管备云备案系统'),
            'active'    => 'chat',
        ]);
    }

    /** 最新消息 (约 50 条, 排除已撤回) */
    public function messages()
    {
        $rows = db()->query(
            "SELECT m.id, m.user_id, m.content, m.msg_type, m.reply_to, m.is_recalled, m.created_at, u.username, u.avatar "
            . "FROM " . db()->table('chat_messages') . " m "
            . "LEFT JOIN " . db()->table('users') . " u ON u.id = m.user_id "
            . "WHERE m.is_recalled = 0 "
            . "ORDER BY m.id DESC LIMIT 50"
        );
        $list = [];
        if (is_array($rows)) {
            $rows = array_reverse($rows); // 转为时间正序, 便于前端自上而下渲染
            foreach ($rows as $row) {
                $list[] = $this->formatMessage($row);
            }
        }
        ok(['messages' => $list]);
    }

    /** 历史消息 (向上滚动加载, before_id 之前的 50 条) */
    public function history()
    {
        $beforeId = (int)input('before_id', 0);
        if ($beforeId <= 0) {
            ok(['messages' => []]);
        }
        $rows = db()->query(
            "SELECT m.id, m.user_id, m.content, m.msg_type, m.reply_to, m.is_recalled, m.created_at, u.username, u.avatar "
            . "FROM " . db()->table('chat_messages') . " m "
            . "LEFT JOIN " . db()->table('users') . " u ON u.id = m.user_id "
            . "WHERE m.is_recalled = 0 AND m.id < ? "
            . "ORDER BY m.id DESC LIMIT 50",
            [$beforeId]
        );
        $list = [];
        if (is_array($rows)) {
            $rows = array_reverse($rows); // 时间正序, 前端整体 prepend
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

        // 禁言检查
        [$banned, $bannedUntil, $reason] = chat_user_banned($userId);
        if ($banned) {
            fail('您已被禁言，截止时间：' . $bannedUntil . '，原因：' . $reason);
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
            $banBase  = (int)site_config('chat_spam_ban_min', 60); // 基准分钟 (默认 60 即 1 小时)
            $banMins  = $banBase * random_int(1, 3);               // 1~3 倍基准 (默认即 1~3 小时)
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
            // 插入消息但立即标记为撤回
            $messageId = db()->insert('chat_messages', [
                'user_id'     => $userId,
                'content'     => $content,
                'msg_type'    => $msgType,
                'reply_to'    => $replyTo,
                'is_recalled' => 1,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
            // 记录违规
            db()->insert('chat_violations', [
                'user_id'    => $userId,
                'message_id' => $messageId,
                'word'       => $matchedWord,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            // 累计违规判定 (窗口内达上限则自动禁言)
            $window = max(1, (int)site_config('chat_violation_window', 30));
            $violationCount = (int)db()->queryScalar(
                "SELECT COUNT(*) FROM " . db()->table('chat_violations')
                . " WHERE user_id = ? AND created_at >= (NOW() - INTERVAL $window MINUTE)",
                [$userId]
            );
            $violationLimit = (int)site_config('chat_violation_limit', 5);
            if ($violationLimit > 0 && $violationCount >= $violationLimit) {
                $banBase  = (int)site_config('chat_violation_ban_min', 60); // 基准分钟
                $banMins  = $banBase * random_int(1, 5);                    // 1~5 倍基准 (默认即 1~5 小时)
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
            'is_recalled' => 0,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        $row = db()->queryOne(
            "SELECT m.id, m.user_id, m.content, m.msg_type, m.reply_to, m.is_recalled, m.created_at, u.username, u.avatar "
            . "FROM " . db()->table('chat_messages') . " m "
            . "LEFT JOIN " . db()->table('users') . " u ON u.id = m.user_id "
            . "WHERE m.id = ?",
            [$messageId]
        );
        ok(['message' => $this->formatMessage($row)], '发送成功');
    }

    /** 格式化单条消息 (含用户信息与认证标识) */
    private function formatMessage($row)
    {
        if (!$row) {
            return null;
        }
        $avatar  = !empty($row['avatar']) ? asset($row['avatar']) : '';
        $replyTo = $row['reply_to'] ?? null;
        $replyTo = ($replyTo === null || $replyTo === '') ? null : (int)$replyTo;

        return [
            'id'              => (int)$row['id'],
            'user_id'         => (int)$row['user_id'],
            'username'        => $row['username'] ?? '',
            'avatar'          => $avatar,
            'content'         => (string)($row['content'] ?? ''),
            'msg_type'        => $row['msg_type'] ?? 'text',
            'reply_to'        => $replyTo,
            'is_recalled'     => (int)($row['is_recalled'] ?? 0),
            'created_at'      => $row['created_at'] ?? null,
            'created_at_text' => !empty($row['created_at']) ? date('Y-m-d H:i:s', strtotime($row['created_at'])) : '',
            'certs'           => user_certifications((int)$row['user_id']),
        ];
    }
}

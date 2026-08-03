<?php
/**
 * 认证控制器 (登录/注册)
 */
class AuthController extends Controller
{
    public function login()
    {
        if (is_logged_in()) redirect(site_url('user'));
        $this->view('auth/login', [
            'pageTitle' => '用户登录 - ' . site_config('site_name'),
            'active' => 'login',
        ]);
    }

    public function doLogin()
    {
        $username = trim(input('username', ''));
        $password = input('password', '');
        $captcha = (int)input('captcha_verified', 0);

        if (!$username || !$password) fail('请输入账号和密码');
        if (!$captcha) fail('请先完成人机验证');

        try {
            $user = db()->queryOne("SELECT * FROM " . db()->table('users') . " WHERE username = ? OR email = ? LIMIT 1", [$username, $username]);
            if (!$user || !verify_password($password, $user['password'])) {
                log_login("登录失败: 用户 {$username} 密码错误");
                fail('账号或密码错误');
            }
            if ($user['status'] != 1) {
                fail('账号已被禁用，请联系管理员');
            }
            // 更新登录信息
            db()->update('users', [
                'last_login' => date('Y-m-d H:i:s'),
                'last_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            ], 'id = :id', ['id' => $user['id']]);
            unset($user['password']);
            $_SESSION['gb_user'] = $user;
            log_login("用户 {$username} 登录成功", $user['id'], 'user');
            ok(['redirect' => site_url('user')], '登录成功');
        } catch (Throwable $e) {
            fail('系统异常: ' . $e->getMessage());
        }
    }

    public function register()
    {
        if (is_logged_in()) redirect(site_url('user'));
        $this->view('auth/register', [
            'pageTitle' => '用户注册 - ' . site_config('site_name'),
            'active' => 'login',
        ]);
    }

    public function doRegister()
    {
        $username = trim(input('username', ''));
        $password = input('password', '');
        $confirm = input('confirm_password', '');
        $email = trim(input('email', ''));
        $phone = trim(input('phone', ''));
        $agree = (int)input('agree', 0);
        $captcha = (int)input('captcha_verified', 0);

        if (!$captcha) fail('请先完成人机验证');
        if (!$agree) fail('请阅读并同意用户协议');
        // 校验
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]{2,19}$/', $username)) fail('用户名以字母开头，3-20位字母数字下划线');
        if (strlen($password) < 6 || strlen($password) > 20) fail('密码长度6-20位');
        if ($password !== $confirm) fail('两次密码不一致');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail('邮箱格式不正确');
        if (!preg_match('/^1[3-9]\d{9}$/', $phone)) fail('手机号格式不正确');

        try {
            // 检查重复
            $exists = db()->queryOne("SELECT id FROM " . db()->table('users') . " WHERE username = ?", [$username]);
            if ($exists) fail('用户名已存在');
            $exists = db()->queryOne("SELECT id FROM " . db()->table('users') . " WHERE email = ?", [$email]);
            if ($exists) fail('邮箱已被注册');

            $id = db()->insert('users', [
                'username' => $username,
                'password' => hash_password($password),
                'email'    => $email,
                'phone'    => $phone,
                'status'   => 1,
                'role'     => 'user',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            log_action('operation', "新用户注册: {$username}", $id, 'user');
            ok(['redirect' => site_url('login')], '注册成功，请登录');
        } catch (Throwable $e) {
            fail('注册失败: ' . $e->getMessage());
        }
    }

    public function logout()
    {
        $u = current_user();
        if ($u) log_action('login', "用户 {$u['username']} 退出登录", $u['id'], 'user');
        unset($_SESSION['gb_user']);
        redirect(site_url());
    }
}

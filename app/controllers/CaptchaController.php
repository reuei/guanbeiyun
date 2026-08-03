<?php
/** 人机验证控制器 (服务端校验备用) */
class CaptchaController extends Controller
{
    public function verify()
    {
        // 滑块为客户端完成,服务端记录验证状态
        $ok = (int)input('verified', 0);
        if ($ok) {
            $_SESSION['gb_captcha_ok'] = time();
            ok(['token' => session_id()], '验证成功');
        }
        fail('验证失败');
    }
}

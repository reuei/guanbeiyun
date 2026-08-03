<?php
/**
 * 反馈与举报控制器
 */
class FeedbackController extends Controller
{
    public function index()
    {
        $this->view('feedback/index', [
            'pageTitle' => '意见反馈 - ' . site_config('site_name'),
            'active' => 'feedback',
        ]);
    }

    public function report()
    {
        $this->view('feedback/report', [
            'pageTitle' => '违法举报 - ' . site_config('site_name'),
            'active' => 'report',
        ]);
    }

    public function submit()
    {
        $this->doSubmit('feedback');
    }

    public function submitReport()
    {
        $this->doSubmit('report');
    }

    private function doSubmit($type)
    {
        $captcha = (int)input('captcha_verified', 0);
        if (!$captcha) fail('请先完成人机验证');

        $name = trim(input('name', ''));
        $contact = trim(input('contact', ''));
        $title = trim(input('title', ''));
        $content = trim(input('content', ''));
        $targetUrl = trim(input('target_url', ''));

        if (!$content) fail('请输入内容');
        if (mb_strlen($content) < 5) fail('内容至少5个字符');
        if ($type === 'report') {
            if (!$targetUrl) fail('请填写举报目标网址');
        }

        try {
            $uid = current_user()['id'] ?? 0;
            db()->insert('feedbacks', [
                'user_id' => $uid ?: null,
                'name' => $name,
                'contact' => $contact,
                'type' => $type,
                'title' => $title,
                'content' => $content,
                'target_url' => $targetUrl,
                'status' => 0,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            log_action('operation', ($type === 'report' ? '提交举报' : '提交反馈') . ": {$title}");
            ok([], $type === 'report' ? '举报提交成功，我们将尽快处理' : '反馈提交成功，感谢您的支持');
        } catch (Throwable $e) {
            fail('提交失败: ' . $e->getMessage());
        }
    }
}

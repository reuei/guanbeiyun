<?php
/**
 * 控制器基类
 */
class Controller
{
    /** 渲染视图 (使用布局) */
    public function view($tpl, $data = [], $layout = 'default')
    {
        $data['site'] = site_config();
        extract($data);
        $viewFile = __DIR__ . '/../app/views/' . $tpl . '.php';
        if (!is_file($viewFile)) {
            throw new RuntimeException("视图不存在: $tpl");
        }
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout) {
            $layoutFile = __DIR__ . '/../app/views/layouts/' . $layout . '.php';
            if (is_file($layoutFile)) {
                require $layoutFile;
                return;
            }
        }
        echo $content;
    }

    /** 渲染无布局视图 */
    public function raw($tpl, $data = [])
    {
        extract($data);
        $viewFile = __DIR__ . '/../app/views/' . $tpl . '.php';
        require $viewFile;
    }

    /** JSON 成功 */
    public function ok($data = null, $msg = '操作成功')
    {
        ok($data, $msg);
    }

    /** JSON 失败 */
    public function fail($msg = '操作失败', $code = 1)
    {
        fail($msg, $code);
    }
}

# 管备云备案系统

专业 ICP 备案管理系统源码 — 蓝色主题、自适应昼夜模式、自带安装程序、完整后台与用户中心。

## 项目简介

管备云备案系统是一套面向 ICP 备案场景的轻量级 Web 应用，参考工信部 ICP 主题风格，采用蓝色主色调 + 白色基底，提供备案查询、备案申请、反馈举报、工单、认证申请、首页公示等完整业务闭环，并配备功能完善的后台管理端与用户中心。

## 技术栈

- **后端**：PHP 7.4+（原生 MVC，无第三方框架依赖）
- **数据库**：MySQL 5.7+（表前缀 `gb_`）
- **前端**：原生 HTML5 + CSS3 + JavaScript（CSS 变量驱动主题）
- **Web 服务器**：Apache（推荐，含 `.htaccess`）/ Nginx / PHP 内置服务器

## 核心特性

1. **主题与 UI**
   - 蓝色主题为主、白色紧退，参考工信部 ICP 风格
   - 适度方形 + 半圆角组件，简洁人工建站风格
   - 蓝色带缺口圆圈旋转加载动画
   - 主题跟随系统设置（日间白 / 夜间黑），亦可手动切换
   - 美化汉堡菜单动画、全局 Toast 提示

2. **人机验证滑块**
   - 登录 / 注册 / 备案查询 / 提交反馈均接入滑块验证
   - 从左向右拖动，左侧圆形图标随拖动旋转，进度逐渐变绿
   - 验证前显示「请拖动滑块完成验证」文字（白色透明 + 循环流体动画）
   - 验证图片后台可配置

3. **实时表单校验**
   - 登录 / 注册输入框实时检测
   - 不符合要求时输入框呈红色透明背景，并在下方显示原因

4. **首页**
   - 头部：左侧 Logo + 文字，右侧昼夜切换、中/英文切换、汉堡菜单
   - Hero 区、功能卡片区、办理流程区、首页公示区、CTA 行动区
   - 底部：Logo、备案信息、网站介绍、快捷方式、QQ / 微信 / 快手图标（点击弹二维码）、「本站由森企动力提供网站建设与技术支持」跳转 sqdl.uiyoi.icu

5. **安装程序**
   - 安装协议 → 条件检测 → 数据库配置 → 管理员配置 → 安装完成
   - 自动创建 `gb_` 前缀数据表与默认配置
   - 安装完成后生成 `install.lock` 防止重复安装

6. **后台管理**（菜单结构）
   - 工作台：总概览、数据大屏
   - 用户管理：用户管理、备案管理、申请管理、反馈管理、举报管理、工单管理
   - 系统配置：网站配置（Logo/缩略图/标题/页脚/ICP/版权）、公告配置、文章管理、邮箱配置、聚合登录配置
   - 认证管理：申请管理、合作方申请管理、首页公示管理
   - 日志管理：系统日志、登录日志、操作日志

7. **用户中心**（菜单结构）
   - 工作台：总概览
   - 备案管理：备案申请管理、反馈与举报管理
   - 工单管理：工单发送
   - 用户配置：信息配置、认证管理、合作伙伴申请
   - 日志管理：系统 / 登录 / 操作日志

8. **响应式**
   - 桌面端：固定侧边栏后台、多列布局
   - 移动端：抽屉式侧边栏、单列布局，UI 与桌面端差异化

## 目录结构

```
guanbeiyun/
├── app/
│   ├── controllers/      # 控制器 (Home/Auth/Filing/Feedback/Admin/User/Api/...)
│   ├── views/            # 视图模板 (admin/auth/home/user/layouts/...)
│   └── routes.php        # 路由定义
├── config/
│   ├── config.example.php# 配置示例 (随源码分发)
│   ├── config.php        # 实际配置 (安装生成, 已 gitignore)
│   └── install.lock      # 安装锁 (安装生成, 已 gitignore)
├── core/
│   ├── Controller.php    # 基础控制器
│   ├── Database.php      # 数据库封装
│   ├── Router.php        # 路由器
│   └── helpers.php       # 全局辅助函数
├── public/               # Web 根目录 (Apache/Nginx 指向此处)
│   ├── assets/
│   │   ├── css/          # theme.css + site.css
│   │   ├── js/           # app.js + slider-captcha.js
│   │   └── img/
│   ├── install/          # 安装程序
│   │   ├── index.php
│   │   └── database.sql
│   ├── uploads/          # 用户上传 (已 gitignore)
│   ├── .htaccess         # Apache 重写规则
│   └── index.php         # 应用入口
└── router.php            # PHP 内置服务器路由 (开发用)
```

## 安装部署

### 方式一：Apache / Nginx（生产）

1. 将源码上传至服务器
2. 将 Web 根目录指向 `public/`
3. 浏览器访问 `http://你的域名/install/` 进入安装向导
4. 依次完成：安装协议 → 条件检测 → 数据库配置 → 管理员配置
5. 安装完成后访问 `http://你的域名/admin/login` 进入后台

### 方式二：PHP 内置服务器（开发）

```bash
cd guanbeiyun
php -S 127.0.0.1:8080 -t public router.php
```

浏览器访问 `http://127.0.0.1:8080/install/` 完成安装。

## 环境要求

| 项目 | 要求 |
|------|------|
| PHP | ≥ 7.4（推荐 8.0+） |
| MySQL | ≥ 5.7 |
| PHP 扩展 | pdo_mysql, mbstring, gd（头像/图片处理）, fileinfo |
| Apache | 启用 mod_rewrite |

## 默认账号

管理员账号在安装时由用户自行设置。首次安装请牢记所填的管理员用户名与密码。

## 二次开发

- 控制器位于 `app/controllers/`，继承 `Controller` 基类
- 路由在 `app/routes.php` 注册：`$router->get('/path', 'Controller@method')`
- 视图使用原生 PHP，通过 `$this->view('view/name', $data, $layout)` 渲染
- 数据库操作：`db()->query()`, `db()->queryOne()`, `db()->insert()`, `db()->update()`, `db()->table()`
- 全局函数：`site_url()`, `asset()`, `e()`, `input()`, `ok()`, `fail()`, `current_user()`, `admin_user()`

## 许可

本项目源码由森企动力提供网站建设与技术支持。技术支持：https://sqdl.uiyoi.icu

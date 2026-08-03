<?php
/**
 * 路由定义
 */

$router = $GLOBALS['router'] ?? $router ?? null;
if (!$router) return;

// ====== 首页 ======
$router->get('', 'HomeController@index');
$router->get('/', 'HomeController@index');
$router->get('/home', 'HomeController@index');

// ====== 认证 ======
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@doLogin');
$router->get('/register', 'AuthController@register');
$router->post('/register', 'AuthController@doRegister');
$router->get('/logout', 'AuthController@logout');
$router->post('/captcha/verify', 'CaptchaController@verify');

// ====== 公共功能 ======
$router->get('/query', 'FilingController@query');
$router->post('/query', 'FilingController@doQuery');
$router->get('/feedback', 'FeedbackController@index');
$router->post('/feedback', 'FeedbackController@submit');
$router->get('/report', 'FeedbackController@report');
$router->post('/report', 'FeedbackController@submitReport');
$router->get('/article/{id}', 'ArticleController@show');

// ====== 用户中心 ======
$router->get('/user', 'UserController@index');
$router->get('/user/dashboard', 'UserController@dashboard');
$router->get('/user/filings', 'UserController@filings');
$router->post('/user/filing/apply', 'UserController@applyFiling');
$router->get('/user/feedback', 'UserController@feedbackList');
$router->get('/user/tickets', 'UserController@tickets');
$router->post('/user/ticket/create', 'UserController@createTicket');
$router->post('/user/ticket/reply', 'UserController@replyTicket');
$router->get('/user/profile', 'UserController@profile');
$router->post('/user/profile/update', 'UserController@updateProfile');
$router->post('/user/avatar/upload', 'UserController@uploadAvatar');
$router->get('/user/certification', 'UserController@certification');
$router->post('/user/certification/apply', 'UserController@applyCert');
$router->get('/user/partner', 'UserController@partner');
$router->post('/user/partner/apply', 'UserController@applyPartner');
$router->get('/user/logs', 'UserController@logs');

// ====== 后台管理 ======
$router->get('/admin', 'AdminController@index');
$router->get('/admin/login', 'AdminController@login');
$router->post('/admin/login', 'AdminController@doLogin');
$router->get('/admin/logout', 'AdminController@logout');
$router->get('/admin/dashboard', 'AdminController@dashboard');
$router->get('/admin/bigscreen', 'AdminController@bigscreen');
$router->get('/admin/users', 'AdminController@users');
$router->post('/admin/user/toggle', 'AdminController@toggleUser');
$router->get('/admin/ticket/detail', 'AdminController@ticketDetail');
$router->post('/admin/feedback/reply', 'AdminController@replyFeedback');
$router->get('/admin/filings', 'AdminController@filings');
$router->post('/admin/filing/audit', 'AdminController@auditFiling');
$router->get('/admin/applications', 'AdminController@applications');
$router->post('/admin/application/audit', 'AdminController@auditApp');
$router->get('/admin/feedbacks', 'AdminController@feedbacks');
$router->get('/admin/reports', 'AdminController@reports');
$router->get('/admin/tickets', 'AdminController@tickets');
$router->post('/admin/ticket/reply', 'AdminController@replyTicket');
$router->get('/admin/siteconfig', 'AdminController@siteConfig');
$router->post('/admin/siteconfig/save', 'AdminController@saveSiteConfig');
$router->post('/admin/upload', 'AdminController@upload');
$router->post('/admin/upload/delete', 'AdminController@deleteUpload');
$router->get('/admin/announcement', 'AdminController@announcement');
$router->post('/admin/announcement/save', 'AdminController@saveAnnouncement');
$router->get('/admin/notify', 'AdminController@notify');
$router->post('/admin/notify/send', 'AdminController@sendNotify');
$router->get('/admin/articles', 'AdminController@articles');
$router->get('/admin/article/edit', 'AdminController@articleEdit');
$router->post('/admin/article/save', 'AdminController@articleSave');
$router->post('/admin/article/delete', 'AdminController@articleDelete');
$router->get('/admin/mail', 'AdminController@mailConfig');
$router->post('/admin/mail/save', 'AdminController@saveMail');
$router->post('/admin/mail/test', 'AdminController@testMail');
$router->get('/admin/oauth', 'AdminController@oauth');
$router->post('/admin/oauth/save', 'AdminController@saveOauth');
$router->get('/admin/cert-apply', 'AdminController@certApply');
$router->get('/admin/partner-apply', 'AdminController@partnerApply');
$router->post('/admin/apply/audit', 'AdminController@auditCertApp');
$router->get('/admin/publicity', 'AdminController@publicity');
$router->post('/admin/publicity/save', 'AdminController@savePublicity');
$router->post('/admin/publicity/delete', 'AdminController@deletePublicity');
$router->get('/admin/logs/system', 'AdminController@systemLogs');
$router->get('/admin/logs/login', 'AdminController@loginLogs');
$router->get('/admin/logs/operation', 'AdminController@operationLogs');
$router->get('/admin/logs/clear', 'AdminController@clearLogs');

// ====== API ======
$router->get('/api/stats', 'ApiController@stats');
$router->get('/api/notifications', 'ApiController@notifications');

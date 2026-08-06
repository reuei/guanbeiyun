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
// 公示独立页面
$router->get('/publicity/filing', 'PublicityController@filing');
$router->get('/publicity/invalid', 'PublicityController@invalid');
// 聊天室
$router->get('/chat', 'ChatController@index');
$router->get('/chat/rooms', 'ChatController@rooms');
$router->get('/chat/online', 'ChatController@online');
$router->post('/chat/messages', 'ChatController@messages');
$router->post('/chat/send', 'ChatController@send');
$router->post('/chat/history', 'ChatController@history');
$router->post('/chat/recall', 'ChatController@recall');
$router->post('/chat/heartbeat', 'ChatController@heartbeat');
$router->get('/chat/online/count', 'ChatController@onlineCount');
$router->post('/chat/online/list', 'ChatController@onlineList');
$router->get('/chat/announcements', 'ChatController@announcements');
$router->get('/chat/quick_phrases', 'ChatController@quickPhrases');
$router->post('/chat/quick_phrase/save', 'ChatController@saveQuickPhrase');
$router->post('/chat/quick_phrase/delete', 'ChatController@deleteQuickPhrase');

// ====== 聊天室管理后台 (路径 /admins) ======
$router->get('/admins', 'ChatAdminController@index');
$router->get('/admins/login', 'ChatAdminController@login');
$router->post('/admins/doLogin', 'ChatAdminController@doLogin');
$router->get('/admins/logout', 'ChatAdminController@logout');
$router->get('/admins/dashboard', 'ChatAdminController@dashboard');
// 版块管理
$router->get('/admins/rooms', 'ChatAdminController@rooms');
$router->post('/admins/room/save', 'ChatAdminController@saveRoom');
$router->post('/admins/room/delete', 'ChatAdminController@deleteRoom');
// 公告管理
$router->get('/admins/announcements', 'ChatAdminController@announcements');
$router->post('/admins/announcement/save', 'ChatAdminController@saveAnnouncement');
$router->post('/admins/announcement/delete', 'ChatAdminController@deleteAnnouncement');
// 禁言管理
$router->get('/admins/banned', 'ChatAdminController@banned');
$router->post('/admins/ban', 'ChatAdminController@banUser');
$router->post('/admins/unban', 'ChatAdminController@unbanUser');
// 用户头衔管理
$router->get('/admins/titles', 'ChatAdminController@titles');
$router->post('/admins/title/save', 'ChatAdminController@saveTitle');
$router->post('/admins/role/set', 'ChatAdminController@setRole');
// 消息管理 (撤回)
$router->get('/admins/messages', 'ChatAdminController@messages');
$router->post('/admins/message/recall', 'ChatAdminController@recallMessage');
$router->post('/admins/message/delete', 'ChatAdminController@deleteMessage');
// 全局禁言 / 封禁用户 (超管)
$router->post('/admins/toggleGlobalMute', 'ChatAdminController@toggleGlobalMute');
$router->post('/admins/account/ban', 'ChatAdminController@banAccount');
// 在线用户
$router->get('/admins/online', 'ChatAdminController@online');
// 个人中心 (公开)
$router->get('/u/{id}', 'UserController@profileView');
// 备案详情页
$router->get('/filing/info/{icp_no}', 'FilingController@infoPage');
// 验证码路由
$router->get('/captcha/verify_code', 'CaptchaController@verifyCode');
$router->post('/captcha/verify_code_check', 'CaptchaController@verifyCodeCheck');
$router->get('/captcha/click_text', 'CaptchaController@clickText');
$router->post('/captcha/click_text_check', 'CaptchaController@clickTextCheck');
$router->get('/captcha/drag_image', 'CaptchaController@dragImage');
$router->post('/captcha/drag_image_check', 'CaptchaController@dragImageCheck');

// ====== 用户中心 ======
$router->get('/user', 'UserController@index');
$router->get('/user/dashboard', 'UserController@dashboard');
$router->get('/user/filings', 'UserController@filings');
$router->post('/user/filing/apply', 'UserController@applyFiling');
$router->get('/user/filing/detail', 'UserController@filingDetail');
$router->get('/user/feedback', 'UserController@feedbackList');
$router->get('/user/feedback/detail', 'UserController@feedbackDetail');
$router->get('/user/tickets', 'UserController@tickets');
$router->post('/user/ticket/create', 'UserController@createTicket');
$router->post('/user/ticket/reply', 'UserController@replyTicket');
$router->get('/user/ticket/detail', 'UserController@ticketDetail');
$router->get('/user/profile', 'UserController@profile');
$router->post('/user/profile/update', 'UserController@updateProfile');
$router->post('/user/avatar/upload', 'UserController@uploadAvatar');
$router->post('/user/bg/upload', 'UserController@uploadBg');
$router->post('/user/license/upload', 'UserController@uploadLicense');
$router->get('/user/certification', 'UserController@certification');
$router->post('/user/certification/apply', 'UserController@applyCert');
$router->get('/user/partner', 'UserController@partner');
$router->post('/user/partner/apply', 'UserController@applyPartner');
$router->get('/user/logs', 'UserController@logs');
$router->get('/user/notifications', 'UserController@notifications');
$router->post('/user/notification/read', 'UserController@readNotification');
$router->post('/user/notification/read_all', 'UserController@readAllNotifications');
$router->get('/user/notifications/unread_count', 'UserController@unreadCount');
$router->post('/user/deletion/apply', 'UserController@applyDeletion');
// v4: 用户社交与私聊
$router->post('/user/follow', 'UserController@follow');
$router->post('/user/block', 'UserController@block');
$router->post('/user/report', 'UserController@report');
$router->post('/user/like', 'UserController@like');
$router->get('/user/messages', 'UserController@messages');
$router->post('/user/messages/chat', 'UserController@messageChat');
$router->post('/user/messages/send', 'UserController@sendMessage');
$router->get('/user/blacklist', 'UserController@blacklist');
$router->post('/user/unblock', 'UserController@unblock');

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
$router->get('/admin/filing/detail', 'AdminController@filingDetail');
$router->post('/admin/filing/audit', 'AdminController@auditFiling');
$router->get('/admin/applications', 'AdminController@applications');
$router->post('/admin/application/audit', 'AdminController@auditApp');
$router->get('/admin/feedbacks', 'AdminController@feedbacks');
$router->get('/admin/reports', 'AdminController@reports');
$router->get('/admin/feedback/detail', 'AdminController@feedbackDetail');
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
$router->post('/admin/oauth/test', 'AdminController@testOauth');
$router->get('/admin/maintenance', 'AdminController@maintenance');
$router->post('/admin/maintenance/save', 'AdminController@saveMaintenance');
$router->get('/admin/captcha', 'AdminController@captcha');
$router->post('/admin/captcha/save', 'AdminController@saveCaptcha');
$router->get('/admin/cert-apply', 'AdminController@certApply');
$router->get('/admin/partner-apply', 'AdminController@partnerApply');
$router->post('/admin/apply/audit', 'AdminController@auditCertApp');
// 公示管理 (分开)
$router->get('/admin/publicity', 'AdminController@publicity');
$router->get('/admin/publicity/filing', 'AdminController@publicityFiling');
$router->get('/admin/publicity/invalid', 'AdminController@publicityInvalid');
$router->post('/admin/publicity/save', 'AdminController@savePublicity');
$router->post('/admin/publicity/delete', 'AdminController@deletePublicity');
// 认证图片配置
$router->get('/admin/certifications', 'AdminController@certifications');
$router->post('/admin/certification/save', 'AdminController@saveCertification');
$router->post('/admin/certification/delete', 'AdminController@deleteCertification');
// 注销申请管理
$router->get('/admin/deletions', 'AdminController@deletions');
$router->post('/admin/deletion/audit', 'AdminController@auditDeletion');
// v4: 后台消息通知 (管理员自身收到的通知)
$router->get('/admin/notifications', 'AdminController@adminNotifications');
$router->post('/admin/notification/read', 'AdminController@readAdminNotification');
$router->post('/admin/notification/read_all', 'AdminController@readAllAdminNotifications');
$router->post('/admin/notification/delete', 'AdminController@deleteAdminNotification');
// v4: ICP 备案号前图片管理
$router->get('/admin/icp-images', 'AdminController@icpImages');
$router->post('/admin/icp-image/save', 'AdminController@saveIcpImage');
$router->post('/admin/icp-image/delete', 'AdminController@deleteIcpImage');
// v4: 后台私信查看
$router->get('/admin/private-messages', 'AdminController@privateMessages');
$router->post('/admin/private-message/delete', 'AdminController@deletePrivateMessage');
// v4: 用户举报管理 (用户间举报)
$router->get('/admin/user-reports', 'AdminController@userReports');
$router->post('/admin/user-report/audit', 'AdminController@auditUserReport');
// 聊天室管理
$router->get('/admin/chat', 'AdminController@chat');
$router->post('/admin/chat/config', 'AdminController@saveChatConfig');
$router->post('/admin/chat/delete', 'AdminController@deleteChatMessage');
$router->get('/admin/chat/banned', 'AdminController@chatBanned');
$router->post('/admin/chat/ban', 'AdminController@banUser');
$router->post('/admin/chat/unban', 'AdminController@unbanUser');
$router->get('/admin/chat/words', 'AdminController@chatWords');
$router->post('/admin/chat/word/save', 'AdminController@saveChatWord');
$router->post('/admin/chat/word/delete', 'AdminController@deleteChatWord');
$router->get('/admin/logs/system', 'AdminController@systemLogs');
$router->get('/admin/logs/login', 'AdminController@loginLogs');
$router->get('/admin/logs/operation', 'AdminController@operationLogs');
$router->get('/admin/logs/clear', 'AdminController@clearLogs');

// ====== API ======
$router->get('/api/stats', 'ApiController@stats');
$router->get('/api/notifications', 'ApiController@notifications');
$router->get('/api/admin/notifications', 'ApiController@adminNotifications');
$router->get('/api/admin/unread_count', 'ApiController@adminUnreadCount');

<?php
/** 人机验证控制器 */
class CaptchaController extends Controller
{
    private function gdAvailable()
    {
        return extension_loaded('gd') && function_exists('imagecreatetruecolor');
    }

    private function setVerified()
    {
        $_SESSION['gb_captcha_verified'] = true;
        $_SESSION['gb_captcha_verified_at'] = time();
    }

    public function verify()
    {
        $ok = (int)input('verified', 0);
        if ($ok) {
            $_SESSION['gb_captcha_ok'] = time();
            $this->setVerified();
            ok(['token' => session_id()], '验证成功');
        }
        fail('验证失败');
    }

    public function verifyCode()
    {
        $length = max(4, min(6, (int)input('length', 4)));
        if (!$this->gdAvailable()) {
            $_SESSION['gb_captcha_code'] = 'OK';
            $this->setVerified();
            ok(['degraded' => true], 'GD不可用，降级模式');
        }
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        $_SESSION['gb_captcha_code'] = $code;

        $w = 120 + $length * 20;
        $h = 45;
        $img = @imagecreatetruecolor($w, $h);
        if (!$img) {
            $this->setVerified();
            header('Content-Type: image/jpeg');
            echo base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD3+iiigAooooAKKKKACiiigD//Z');
            exit;
        }

        $bg = imagecolorallocate($img, mt_rand(230, 255), mt_rand(230, 255), mt_rand(230, 255));
        imagefill($img, 0, 0, $bg);

        for ($i = 0; $i < 6; $i++) {
            $lineColor = imagecolorallocate($img, mt_rand(150, 220), mt_rand(150, 220), mt_rand(150, 220));
            imageline($img, mt_rand(0, $w), mt_rand(0, $h), mt_rand(0, $w), mt_rand(0, $h), $lineColor);
        }
        for ($i = 0; $i < 50; $i++) {
            $pixColor = imagecolorallocate($img, mt_rand(180, 240), mt_rand(180, 240), mt_rand(180, 240));
            imagesetpixel($img, mt_rand(0, $w), mt_rand(0, $h), $pixColor);
        }

        $font = 5;
        $xStart = 10;
        for ($i = 0; $i < $length; $i++) {
            $textColor = imagecolorallocate($img, mt_rand(20, 120), mt_rand(20, 120), mt_rand(20, 120));
            $x = $xStart + $i * (($w - 20) / $length) + mt_rand(-4, 4);
            $y = mt_rand(5, 15);
            $angle = mt_rand(-15, 15);
            if (function_exists('imagettftext') && function_exists('imagettfbbox')) {
                $fontFile = ini_get('gd.jpeg_ignore_warning') ? '' : '';
                @imagettftext($img, 20, $angle, (int)$x, (int)($y + 25), $textColor, '', $code[$i]);
            }
            imagestring($img, $font, (int)$x, (int)$y, $code[$i], $textColor);
        }

        header('Content-Type: image/jpeg');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        @imagejpeg($img, null, 80);
        imagedestroy($img);
        exit;
    }

    public function verifyCodeCheck()
    {
        $input = strtoupper(trim(input('code', '')));
        $stored = strtoupper($_SESSION['gb_captcha_code'] ?? '');
        if ($stored === 'OK') {
            $this->setVerified();
            ok([], '验证成功');
        }
        if ($input && $stored && $input === $stored) {
            unset($_SESSION['gb_captcha_code']);
            $this->setVerified();
            ok(['token' => random_str(16)], '验证成功');
        }
        fail('验证码错误');
    }

    public function clickText()
    {
        $targetCount = max(2, min(5, (int)site_config('captcha_click_count', 3)));
        $charset = ['的','一','是','在','不','了','有','和','人','这','中','大','为','上','个','国','我','以','要','他','时','来','用','们','生','到','作','地','于','出','就','分','对','成','会','可','主','发','年','动','同','工','也','能','下','过','子','说','产','种','面','而','方','后','多','定','行','学','法','所','民','得','经','十','三','之','进','着','等','部','度','家','电','力','里','如','水','化','高','自','二','理','起','小','物','现','实','加','量','都','两','体','制','机','当','使','点','从','业','本','去','把','性','好','应','开','它','合','还','因','由','其','些','然','前','外','天','政','四','日','那','社','义','事','平','形','相','全','反','此','意','料','将','她','矛','究','此','较','论','或','质','气','第','向','道','命','此','变','条','只','没','结','解','问','意','建','月','公','无','系','军','很','情','最','何','理','眼','志','设','论','运','及','则','或','圆','解','统','飞'];
        shuffle($charset);
        $totalCount = 8;
        $displayChars = array_slice($charset, 0, $totalCount);
        $targetKeys = array_rand($displayChars, $targetCount);
        if (!is_array($targetKeys)) $targetKeys = [$targetKeys];
        $targets = [];
        foreach ($targetKeys as $k) $targets[] = $displayChars[$k];
        $_SESSION['gb_captcha_click_targets'] = $targets;
        $_SESSION['gb_captcha_click_token'] = $token = random_str(16);
        $_SESSION['gb_captcha_click_count'] = 0;

        if (!$this->gdAvailable()) {
            ok([
                'image' => '',
                'characters' => $displayChars,
                'targets' => $targets,
                'positions' => [],
                'token' => $token,
                'degraded' => true,
            ], 'GD不可用');
        }

        $w = 320;
        $h = 160;
        $img = @imagecreatetruecolor($w, $h);
        if (!$img) {
            ok([
                'image' => '',
                'characters' => $displayChars,
                'targets' => $targets,
                'positions' => [],
                'token' => $token,
                'degraded' => true,
            ], 'GD不可用');
        }

        $bg = imagecolorallocate($img, mt_rand(240, 255), mt_rand(240, 255), mt_rand(235, 250));
        imagefill($img, 0, 0, $bg);

        for ($i = 0; $i < 8; $i++) {
            $lc = imagecolorallocate($img, mt_rand(200, 240), mt_rand(200, 240), mt_rand(200, 240));
            imageline($img, mt_rand(0, $w), mt_rand(0, $h), mt_rand(0, $w), mt_rand(0, $h), $lc);
        }

        $positions = [];
        $used = [];
        $charW = 32;
        $charH = 32;
        for ($i = 0; $i < $totalCount; $i++) {
            $ch = $displayChars[$i];
            $tries = 0;
            do {
                $x = mt_rand(10, $w - $charW - 10);
                $y = mt_rand(10, $h - $charH - 10);
                $ok = true;
                foreach ($used as $u) {
                    if (abs($u['x'] - $x) < $charW && abs($u['y'] - $y) < $charH) {
                        $ok = false;
                        break;
                    }
                }
                $tries++;
            } while (!$ok && $tries < 30);
            $used[] = ['x' => $x, 'y' => $y];
            $angle = mt_rand(-30, 30);
            $isTarget = in_array($ch, $targets, true);
            $colorR = mt_rand(30, 100);
            $colorG = mt_rand(30, 100);
            $colorB = mt_rand(80, 160);
            if ($isTarget) {
                $colorR = mt_rand(160, 220);
                $colorG = mt_rand(30, 80);
                $colorB = mt_rand(30, 80);
            }
            $textColor = imagecolorallocate($img, $colorR, $colorG, $colorB);
            $positions[] = [
                'char' => $ch,
                'x' => $x,
                'y' => $y,
                'w' => $charW,
                'h' => $charH,
                'angle' => $angle,
            ];
            $fontSize = 20;
            if (function_exists('imagettftext')) {
                @imagettftext($img, $fontSize, $angle, $x + 4, $y + 24, $textColor, '', $ch);
            } else {
                imagestring($img, 5, $x + 4, $y + 8, $ch, $textColor);
            }
        }

        ob_start();
        @imagepng($img);
        $imgData = ob_get_clean();
        imagedestroy($img);
        $dataUrl = 'data:image/png;base64,' . base64_encode($imgData);

        ok([
            'image' => $dataUrl,
            'characters' => $displayChars,
            'targets' => $targets,
            'positions' => $positions,
            'token' => $token,
        ]);
    }

    public function clickTextCheck()
    {
        $token = input('token', '');
        $storedToken = $_SESSION['gb_captcha_click_token'] ?? '';
        $targets = $_SESSION['gb_captcha_click_targets'] ?? [];

        if (empty($targets)) {
            fail('验证过期，请刷新');
        }
        if ($token && $storedToken && $token !== $storedToken) {
            fail('令牌无效');
        }

        $clicks = input('clicks', '');
        $clickedChars = [];
        if (is_array($clicks)) {
            $clickedChars = $clicks;
        } elseif (is_string($clicks)) {
            $clickedChars = json_decode($clicks, true) ?: [];
        }
        $clickedCount = count($clickedChars);
        $targetCount = count($targets);

        $degraded = empty($_SESSION['gb_captcha_click_token']) && isset($_SESSION['gb_captcha_verified']);
        if ($degraded || (isset($_SESSION['gb_captcha_click_degraded']) && $_SESSION['gb_captcha_click_degraded'])) {
            $this->setVerified();
            ok([], '验证成功');
        }

        $hit = 0;
        foreach ($targets as $t) {
            foreach ($clickedChars as $c) {
                if (is_array($c)) $c = $c['char'] ?? '';
                if ((string)$c === (string)$t) { $hit++; break; }
            }
        }
        if ($hit >= $targetCount && $clickedCount >= $targetCount) {
            unset($_SESSION['gb_captcha_click_targets'], $_SESSION['gb_captcha_click_token'], $_SESSION['gb_captcha_click_count']);
            $this->setVerified();
            ok(['token' => random_str(16)], '验证成功');
        }
        fail('点击验证错误，请重试');
    }

    public function dragImage()
    {
        $difficulty = site_config('captcha_difficulty', 'medium');
        $sizeMap = ['easy' => 60, 'medium' => 50, 'hard' => 40];
        $pieceSize = $sizeMap[$difficulty] ?? 50;

        $w = 340;
        $h = 180;
        $pieceY = mt_rand(10, $h - $pieceSize - 10);
        $targetX = mt_rand($pieceSize + 10, $w - $pieceSize - 10);

        $_SESSION['gb_captcha_drag_target_x'] = $targetX;
        $_SESSION['gb_captcha_drag_token'] = $token = random_str(16);
        $_SESSION['gb_captcha_drag_size'] = $pieceSize;
        $_SESSION['gb_captcha_drag_y'] = $pieceY;

        if (!$this->gdAvailable()) {
            ok([
                'bg_image' => '',
                'piece_image' => '',
                'piece_y' => $pieceY,
                'piece_size' => $pieceSize,
                'token' => $token,
                'degraded' => true,
            ], 'GD不可用');
        }

        $bg = @imagecreatetruecolor($w, $h);
        if (!$bg) {
            ok([
                'bg_image' => '',
                'piece_image' => '',
                'piece_y' => $pieceY,
                'piece_size' => $pieceSize,
                'token' => $token,
                'degraded' => true,
            ], 'GD不可用');
        }

        $c1 = imagecolorallocate($bg, mt_rand(80, 160), mt_rand(120, 200), mt_rand(180, 240));
        $c2 = imagecolorallocate($bg, mt_rand(140, 220), mt_rand(180, 240), mt_rand(100, 180));
        for ($x = 0; $x < $w; $x++) {
            $ratio = $x / $w;
            $r = (int)($c1 * (1 - $ratio) + ($c2 >> 16 & 0xFF) * $ratio);
            $g = (int)(($c1 >> 8 & 0xFF) * (1 - $ratio) + ($c2 >> 8 & 0xFF) * $ratio);
            $b = (int)(($c1 & 0xFF) * (1 - $ratio) + ($c2 & 0xFF) * $ratio);
            $col = imagecolorallocate($bg, $r % 256, $g % 256, $b % 256);
            imageline($bg, $x, 0, $x, $h, $col);
        }

        for ($i = 0; $i < 15; $i++) {
            $shapeColor = imagecolorallocatealpha($bg, mt_rand(255), mt_rand(255), mt_rand(255), 80);
            $type = mt_rand(0, 2);
            $sx = mt_rand(0, $w);
            $sy = mt_rand(0, $h);
            $sw = mt_rand(20, 80);
            $sh = mt_rand(20, 60);
            if ($type === 0) {
                imagefilledellipse($bg, $sx, $sy, $sw, $sh, $shapeColor);
            } elseif ($type === 1) {
                imagefilledrectangle($bg, $sx, $sy, $sx + $sw, $sy + $sh, $shapeColor);
            } else {
                $pts = [$sx, $sy, $sx + $sw, $sy + $sh, $sx - $sw / 2, $sy + $sh];
                imagefilledpolygon($bg, $pts, 3, $shapeColor);
            }
        }

        $holeBorder = imagecolorallocate($bg, 255, 255, 255);
        $innerColor = imagecolorallocatealpha($bg, 0, 0, 0, 45);
        $this->drawPiece($bg, $targetX, $pieceY, $pieceSize, $innerColor, true);
        $this->drawPiece($bg, $targetX, $pieceY, $pieceSize, $holeBorder, false, 2);

        $pieceImg = @imagecreatetruecolor($pieceSize, $pieceSize);
        if (!$pieceImg) {
            imagedestroy($bg);
            ok(['bg_image' => '', 'piece_image' => '', 'piece_y' => $pieceY, 'piece_size' => $pieceSize, 'token' => $token, 'degraded' => true], 'GD不可用');
        }
        imagealphablending($pieceImg, false);
        imagesavealpha($pieceImg, true);
        $transparent = imagecolorallocatealpha($pieceImg, 0, 0, 0, 127);
        imagefill($pieceImg, 0, 0, $transparent);

        imagecopy($pieceImg, $bg, 0, 0, $targetX, $pieceY, $pieceSize, $pieceSize);
        $maskPiece = @imagecreatetruecolor($pieceSize, $pieceSize);
        imagealphablending($maskPiece, false);
        imagesavealpha($maskPiece, true);
        $maskTransparent = imagecolorallocatealpha($maskPiece, 0, 0, 0, 127);
        imagefill($maskPiece, 0, 0, $maskTransparent);
        $maskColor = imagecolorallocatealpha($maskPiece, 255, 255, 255, 0);
        $this->drawPiece($maskPiece, 0, 0, $pieceSize, $maskColor, true);

        for ($px = 0; $px < $pieceSize; $px++) {
            for ($py = 0; $py < $pieceSize; $py++) {
                $alpha = imagecolorat($maskPiece, $px, $py);
                if (($alpha >> 24 & 0x7F) === 127) {
                    imagesetpixel($pieceImg, $px, $py, $transparent);
                }
            }
        }
        imagedestroy($maskPiece);

        $pieceBorder = imagecolorallocate($pieceImg, 255, 255, 255);
        $this->drawPiece($pieceImg, 0, 0, $pieceSize, $pieceBorder, false, 2);

        ob_start();
        @imagepng($bg);
        $bgData = ob_get_clean();
        imagedestroy($bg);

        ob_start();
        @imagepng($pieceImg);
        $pieceData = ob_get_clean();
        imagedestroy($pieceImg);

        ok([
            'bg_image' => 'data:image/png;base64,' . base64_encode($bgData),
            'piece_image' => 'data:image/png;base64,' . base64_encode($pieceData),
            'piece_y' => $pieceY,
            'piece_size' => $pieceSize,
            'token' => $token,
        ]);
    }

    private function drawPiece($img, $x, $y, $size, $color, $fill = true, $border = 0)
    {
        $r = (int)($size * 0.2);
        $cx = $x + (int)($size / 2);
        $cy = $y + (int)($size / 2);
        $half = (int)($size / 2);

        if ($fill) {
            $points = [];
            for ($a = 0; $a < 360; $a += 5) {
                $rad = deg2rad($a);
                $px = $cx + $half * cos($rad);
                $py = $cy + $half * sin($rad);
                if ($a >= 315 || $a <= 45) {
                    $nX = $cx + ($half - $r) + $r * cos(deg2rad(180 + ($a - 0) * 4));
                    $nY = $cy - $half - $r + $r * sin(deg2rad(180 + ($a - 0) * 4));
                    if ($a < 90) { $px = $nX; $py = $cy - $half + $r + ($size * 0.1) * sin(deg2rad($a * 4)); }
                }
                $points[] = (int)$px;
                $points[] = (int)$py;
            }
            $simplePoints = [
                $x, $y + $r,
                $x + $r, $y + $r,
                $x + $r, $y,
                $x + $size - $r, $y,
                $x + $size - $r, $y + $r,
                $x + $size, $y + $r,
                $x + $size, $y + $size - $r,
                $x + $size - $r, $y + $size - $r,
                $x + $size - $r, $y + $size,
                $x + $r, $y + $size,
                $x + $r, $y + $size - $r,
                $x, $y + $size - $r,
            ];
            imagefilledpolygon($img, $simplePoints, 12, $color);
            imagefilledellipse($img, $x + (int)($size / 2), $y, (int)($size * 0.45), (int)($size * 0.45), $color);
        } else {
            imagesetthickness($img, max(1, $border));
            imagerectangle($img, $x + 1, $y + (int)($size * 0.18), $x + $size - 1, $y + $size - 1, $color);
            imageellipse($img, $x + (int)($size / 2), $y + (int)($size * 0.18), (int)($size * 0.55), (int)($size * 0.55), $color);
        }
    }

    public function dragImageCheck()
    {
        $targetX = $_SESSION['gb_captcha_drag_target_x'] ?? null;
        $token = input('token', '');
        $storedToken = $_SESSION['gb_captcha_drag_token'] ?? '';
        $size = $_SESSION['gb_captcha_drag_size'] ?? 50;

        if ($targetX === null) fail('验证过期，请刷新');
        if ($token && $storedToken && $token !== $storedToken) fail('令牌无效');

        $userX = (float)input('x', -1);
        $degraded = isset($_SESSION['gb_captcha_drag_degraded']) && $_SESSION['gb_captcha_drag_degraded'];
        if ($degraded) {
            unset($_SESSION['gb_captcha_drag_target_x'], $_SESSION['gb_captcha_drag_token'], $_SESSION['gb_captcha_drag_size'], $_SESSION['gb_captcha_drag_y'], $_SESSION['gb_captcha_drag_degraded']);
            $this->setVerified();
            ok([], '验证成功');
        }

        $tolerance = 5;
        if ($userX >= 0 && abs($userX - $targetX) <= $tolerance) {
            unset($_SESSION['gb_captcha_drag_target_x'], $_SESSION['gb_captcha_drag_token'], $_SESSION['gb_captcha_drag_size'], $_SESSION['gb_captcha_drag_y']);
            $this->setVerified();
            ok(['token' => random_str(16)], '验证成功');
        }
        fail('拖动位置不准确，请重试');
    }
}

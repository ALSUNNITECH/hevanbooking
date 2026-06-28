<?php
/**
 * Hevan Booking - إعدادات النظام
 */

// --- إعداد الاتصال بقاعدة البيانات ---
// القيم الافتراضية مناسبة لـ XAMPP
// ويمكن override عبر environment variables عند التشغيل داخل Docker
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS');
$DB_PASS = $DB_PASS === false ? '' : $DB_PASS;
$DB_NAME = getenv('DB_NAME') ?: 'hevan_booking';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    die('<div style="direction:rtl;text-align:center;padding:50px;font-family:Tahoma">
         <h2>⚠️ فشل الاتصال بقاعدة البيانات</h2>
         <p>تأكد من:
            <br>1. تشغيل MySQL في XAMPP
            <br>2. استيراد ملف <b>database.sql</b> عبر phpMyAdmin
         </p>
         <p style="color:#888">الخطأ: ' . htmlspecialchars($e->getMessage()) . '</p>
         </div>');
}

// --- دوال مساعدة ---

/**
 * هروب النص لعرضه آمن في HTML
 */
function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * توليد URL نسبي
 */
function base_url() {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $parts = array_values(array_filter(explode('/', trim($script, '/'))));
    if (!$parts) return '';

    $adminIndex = array_search('admin', $parts, true);
    if ($adminIndex !== false) {
        return $adminIndex > 0 ? '/' . implode('/', array_slice($parts, 0, $adminIndex)) : '';
    }
    if (count($parts) > 1) return '/' . implode('/', array_slice($parts, 0, -1));
    return '';
}

function url($path = '') {
    $base = base_url();
    return $base . '/' . ltrim($path, '/');
}

function redirect($path) {
    header('Location: ' . url($path));
    exit;
}

function status_label($status) {
    return match ($status) {
        'accepted' => 'مقبول',
        'rejected' => 'مرفوض',
        default    => 'قيد الانتظار',
    };
}

function status_class($status) {
    return match ($status) {
        'accepted' => 'success',
        'rejected' => 'danger',
        default    => 'warning',
    };
}

function text_excerpt($text, $length = 100) {
    $text = trim((string)$text);
    if ($text === '') return '';

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($text) > $length ? mb_substr($text, 0, $length) . '...' : $text;
    }

    return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
}

/**
 * رابط آمن لملف داخل مجلد assets حتى لو كان اسم الصورة يحتوي مسافات أو رموز.
 */
function asset_url($file) {
    $file = trim((string)$file);
    if ($file === '') return '';

    if (preg_match('/^https?:\/\//i', $file) || str_starts_with($file, '/')) {
        return $file;
    }

    $file = preg_replace('#^assets/#', '', $file);
    $segments = array_map('rawurlencode', explode('/', $file));
    return url('assets/' . implode('/', $segments));
}

function asset_href($file) {
    $url = asset_url($file);
    $relative = preg_replace('#^assets/#', '', trim((string)$file));
    $local = __DIR__ . '/assets/' . ltrim($relative, '/');

    if (is_file($local)) {
        return $url . '?v=' . filemtime($local);
    }

    return $url;
}

/**
 * صور افتراضية ذكية للوجهات الموجودة في قاعدة البيانات.
 * هذا يجعل الصور الجديدة في assets تظهر بدون الحاجة لإعادة استيراد قاعدة البيانات.
 */
function place_image_files($place) {
    $name = is_array($place) ? ($place['name'] ?? '') : (string)$place;

    if (str_contains($name, 'مروي') || str_contains($name, 'أهرام')) {
        return ['Meroë-Pyramids1.png', 'Meroë-Pyramids2.png', 'Meroë-Pyramids3.png', 'Meroë-Pyramids4.png'];
    }
    if (str_contains($name, 'توتي')) {
        return ['Tuti-Island1.png', 'Tuti-Island2.png', 'Tuti-Island3.png', 'Tuti-Island4.png'];
    }
    if (str_contains($name, 'سواكن')) {
        return ['Suakin1.png', 'Suakin2.png', 'Suakin3.png', 'Suakin4.png', 'Suakin5.png'];
    }
    if (str_contains($name, 'جبل مرة') || str_contains($name, 'مرة')) {
        return ['Jabal-Marra1.png', 'Jabal-Marra2.png', 'Jabal-Marra3.png', 'Jabal-Marra4.png'];
    }
    if (str_contains($name, 'كسلا') || str_contains($name, 'شلال')) {
        return ['Totel-Mountain-Waterfalls1.png'];
    }

    return ['hram.png'];
}

function place_image_urls($place) {
    $urls = [];
    if (is_array($place) && !empty($place['image_url'])) {
        $urls[] = asset_url($place['image_url']);
    }

    foreach (place_image_files($place) as $file) {
        $url = asset_url($file);
        if (!in_array($url, $urls, true)) {
            $urls[] = $url;
        }
    }

    return $urls;
}

function place_cover_url($place) {
    $images = place_image_urls($place);
    return $images[0] ?? asset_url('hram.png');
}

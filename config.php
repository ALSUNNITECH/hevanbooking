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
         <h2>فشل الاتصال بقاعدة البيانات</h2>
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
 * أيقونات SVG داخلية بديلة للرموز التعبيرية حتى تظهر بشكل احترافي وثابت.
 */
function svg_icon($name, $class = '') {
    $icons = [
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3.6 9h16.8M3.6 15h16.8M12 3c2.3 2.4 3.5 5.4 3.5 9s-1.2 6.6-3.5 9c-2.3-2.4-3.5-5.4-3.5-9S9.7 5.4 12 3Z"/>',
        'booking' => '<path d="M8 4h8a2 2 0 0 1 2 2v14l-6-3-6 3V6a2 2 0 0 1 2-2Z"/><path d="M9 9h6M9 13h4"/>',
        'landscape' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 15l3-3 3 3 2-2 3 3"/><circle cx="16" cy="9" r="1.4"/>',
        'lock' => '<rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/>',
        'building' => '<path d="M4 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"/><path d="M3 21h18M8 7h1M13 7h1M8 11h1M13 11h1M8 15h1M13 15h1"/>',
        'pin' => '<path d="M12 21s7-4.6 7-11a7 7 0 1 0-14 0c0 6.4 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/>',
        'mobile' => '<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.4 19.4 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6.4 6.4l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2Z"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/>',
        'note' => '<path d="M6 3h9l3 3v15H6V3Z"/><path d="M14 3v4h4M9 11h6M9 15h6"/>',
        'save' => '<path d="M5 3h12l2 2v16H5V3Z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/>',
        'back' => '<path d="M19 12H5M12 19l-7-7 7-7"/>',
        'key' => '<circle cx="7" cy="15" r="4"/><path d="M10 12l8-8 3 3-2 2 2 2-2 2-2-2-4 4"/>',
        'bulb' => '<path d="M9 18h6M10 22h4M8 14a6 6 0 1 1 8 0c-.7.7-1 1.5-1 2H9c0-.5-.3-1.3-1-2Z"/>',
        'chart' => '<path d="M4 19V5M4 19h16"/><rect x="7" y="11" width="3" height="5"/><rect x="12" y="7" width="3" height="9"/><rect x="17" y="3" width="3" height="13"/>',
        'trash' => '<path d="M3 6h18M8 6V4h8v2M6 6l1 15h10l1-15M10 11v6M14 11v6"/>',
        'edit' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'check' => '<path d="M20 6L9 17l-5-5"/>',
        'x' => '<path d="M18 6L6 18M6 6l12 12"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'moon' => '<path d="M21 12.8A8.5 8.5 0 1 1 11.2 3 6.5 6.5 0 0 0 21 12.8Z"/>',
    ];

    $path = $icons[$name] ?? $icons['globe'];
    $class = trim('icon ' . $class);
    return '<svg class="' . h($class) . '" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
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

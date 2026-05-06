<?php
/**
 * Render product image safely — no 404 errors
 * @param string|null $imagen  filename from DB
 * @param string      $class   optional CSS class
 * @param string      $style   optional inline style
 */
function img_producto($imagen, $style='width:100%;height:100%;object-fit:cover', $alt='') {
    if (empty($imagen)) {
        echo '<span style="font-size:2rem;display:flex;align-items:center;justify-content:center;height:100%;">📦</span>';
        return;
    }
    // Resolve the uploads directory relative to this helpers.php file
    $uploads_dir = __DIR__ . '/../public/uploads/';
    $clean = basename($imagen); // prevent path traversal
    if (file_exists($uploads_dir . $clean)) {
        $url = '../uploads/' . htmlspecialchars($clean, ENT_QUOTES);
        echo '<img src="' . $url . '" alt="' . htmlspecialchars($alt, ENT_QUOTES) . '" style="' . $style . '" loading="lazy">';
    } else {
        echo '<span style="font-size:2rem;display:flex;align-items:center;justify-content:center;height:100%;">📦</span>';
    }
}

/**
 * Professional toast alert system
 */
function flash_alert($msg, $type='ok') {
    if (empty($msg)) return;
    $icon  = $type === 'ok' ? '✓' : ($type === 'warn' ? '⚠' : '✕');
    $color = $type === 'ok' ? 'var(--success)' : ($type === 'warn' ? 'var(--warning)' : 'var(--danger)');
    $bg    = $type === 'ok' ? 'rgba(6,214,160,.12)' : ($type === 'warn' ? 'rgba(255,190,11,.12)' : 'rgba(255,77,109,.12)');
    echo '<div class="rm-alert rm-alert-' . $type . '" style="'
       . 'display:flex;align-items:center;gap:.85rem;'
       . 'background:' . $bg . ';border:1px solid ' . $color . ';'
       . 'border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem;'
       . 'animation:slideIn .3s ease;">'
       . '<span style="width:32px;height:32px;border-radius:50%;background:' . $color . ';color:#05080f;'
       . 'display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.9rem;flex-shrink:0;">'
       . $icon . '</span>'
       . '<span style="font-size:.92rem;font-weight:500;color:var(--text-primary);">' . htmlspecialchars($msg) . '</span>'
       . '<button onclick="this.parentElement.remove()" style="margin-left:auto;background:none;border:none;color:' . $color . ';cursor:pointer;font-size:1.1rem;padding:.2rem .4rem;border-radius:6px;opacity:.7" title="Cerrar">×</button>'
       . '</div>';
}

<?php
declare(strict_types=1);
function canComplete(array $session, int $requestedEventId, int $now, int $ttl): bool {
    // Phải có bản nháp
    if (!isset($session['draft'])) { return false; }
    // Đúng event
    if (($session['draft']['data']['event_id'] ?? null) !== $requestedEventId) { return false; }
    // TTL phải dương
    if ($ttl <= 0) { return false; }
    // created_at không ở tương lai
    $createdAt = $session['draft']['created_at'];
    if ($createdAt > $now) { return false; }
    // Tuổi bản nháp < TTL (chưa hết hạn)
    if ($now - $createdAt >= $ttl) { return false; }
    return true;
}

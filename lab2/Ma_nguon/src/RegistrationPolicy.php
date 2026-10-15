<?php
declare(strict_types=1);
final class RegistrationPolicy {
    public function decide(int $capacity, int $registered,
        bool $open, bool $waitingEnabled): string {
        // Dữ liệu không hợp lệ: sức chứa <= 0, số đăng ký < 0 hoặc vượt sức chứa
        if ($capacity <= 0 || $registered < 0 || $registered > $capacity) {
            return 'rejected';
        }
        // Sự kiện đã đóng
        if (!$open) {
            return 'rejected';
        }
        // Còn chỗ: confirmed
        if ($registered < $capacity) {
            return 'confirmed';
        }
        // Đầy chỗ: có danh sách chờ -> waiting, không có -> rejected
        if ($waitingEnabled) {
            return 'waiting';
        }
        return 'rejected';
    }
}

<?php
$file = 'e:\Projects\Done Projects\360 Laundry\resources\views\staff\show.blade.php';
$lines = file($file);

function extract_lines($start, $end, $lines) {
    return implode("", array_slice($lines, $start - 1, $end - $start + 1));
}

$dir = 'e:\Projects\Done Projects\360 Laundry\resources\views\staff\partials';

file_put_contents("$dir/profile_card.blade.php", extract_lines(33, 129, $lines));
file_put_contents("$dir/attendance_grid.blade.php", extract_lines(136, 242, $lines));
file_put_contents("$dir/leave_history.blade.php", extract_lines(244, 290, $lines));
file_put_contents("$dir/payroll_summary.blade.php", extract_lines(295, 437, $lines));
file_put_contents("$dir/modals.blade.php", extract_lines(443, 617, $lines));
file_put_contents("$dir/scripts.blade.php", extract_lines(622, 744, $lines));

echo "Partials extracted successfully!";

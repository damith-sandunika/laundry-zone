<?php
$file = 'resources/views/delivery/index.blade.php';
$content = file_get_contents($file);

// 1. Remove the existing view buttons that I added
$pattern1 = '/<button type="button" @click="openPreview[^>]+><i class="fa-solid fa-eye"><\/i><\/button>\s*/';
$content = preg_replace($pattern1, '', $content);

// 2. Add the view button immediately inside the action containers for BOTH tables
$search = '<div style="display:inline-flex;gap:6px;align-items:center;">';
$replace = '<div style="display:inline-flex;gap:6px;align-items:center;">
                                        <button type="button" @click="openPreview($event, {{ $appt->id }}, {{ json_encode([\'customer\' => $appt->customer, \'order\' => $appt->order, \'branch\' => $appt->branch, \'pickup_lat\' => $appt->latitude, \'pickup_lng\' => $appt->longitude, \'delivery_lat\' => $appt->delivery_lat, \'delivery_lng\' => $appt->delivery_lng, \'pickup_address\' => $appt->pickup_address, \'delivery_address\' => $appt->delivery_address, \'notes\' => $appt->special_notes ?? \'\']) }})" style="padding:6px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;color:#3b82f6;cursor:pointer;" title="Preview Order"><i class="fa-solid fa-eye"></i></button>';

$content = str_replace($search, $replace, $content);

file_put_contents($file, $content);
echo "Done replacing view buttons";
?>

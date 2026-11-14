@php
    $cfg = $layout->custom_fields ?? [];
    $showLogo = $layout->show_logo;
    $logoSize = $cfg['logo_size'] ?? '45';
    $paperWidth = $cfg['paper_width'] ?? '300px';
    $orderNoSize = $cfg['order_no_size'] ?? '20px';
    $showCustomerPhone = $cfg['show_customer_phone'] ?? '1';
    $showDueDate = $cfg['show_due_date'] ?? '1';
    $showTotals = $cfg['show_totals'] ?? '1';
    $showSpecialNotes = $cfg['show_special_notes'] ?? '1';
    $notesLabel = $cfg['notes_label'] ?? 'Special Note :';
    $notesLineCount = (int)($cfg['notes_line_count'] ?? 2);
    $showProcessList = $cfg['show_process_list'] ?? '1';

    $savedProcesses = $cfg['process_items'] ?? null;
    if (!is_array($savedProcesses) || empty($savedProcesses)) {
        $savedProcesses = [
            ['name' => 'Collected', 'suffix' => 'Kamal'],
            ['name' => 'Wash', 'suffix' => '________'],
            ['name' => 'Iron', 'suffix' => '________'],
            ['name' => 'Quality check', 'suffix' => '________'],
            ['name' => 'Final inspection', 'suffix' => '________'],
        ];
    }

    $headerTitle = $layout->header_title ?: ($tenant->name ?? 'Laundry Zone');
    $headerSubtitle = $layout->header_subtitle ?: 'Workshop Job Card';
@endphp


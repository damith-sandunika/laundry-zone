<?php
use Illuminate\Support\Facades\Schema;
$columns = collect(Schema::getColumns('orders'));
$statusCol = $columns->firstWhere('name', 'status');
echo json_encode($statusCol);

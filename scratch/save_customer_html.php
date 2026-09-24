<?php
$ch = curl_init('http://127.0.0.1:8000/customer/book');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$html = curl_exec($ch);
curl_close($ch);
file_put_contents(__DIR__ . '/test_customer_book.html', $html);
echo "Saved HTML\n";

# 365laundry.lk System - Technical Audit Report
**දිනකට Orders 1000 ක් (වසරකට ලක්ෂ 3.6 ක්) හැසිරවීම සඳහා වන පද්ධති විගණන වාර්තාව**

පහත දැක්වෙන්නේ පද්ධතියේ ක්‍රියාකාරීත්වය, වේගය සහ ආරක්ෂාව තහවුරු කිරීම සඳහා වෙනස් කළ යුතු ගොනු (Files) සහ සිදු කළ යුතු නිශ්චිත වෙනස්කම් ලැයිස්තුවයි.

---

### 1. N+1 Query ගැටලුව (Speed Optimization)
Loop එකක් ඇතුළත Database queries ධාවනය වීම නිසා පද්ධතිය අතිශයින් මන්දගාමී වේ.

* **වෙනස් විය යුතු ගොනුව:** `app/Http/Controllers/OrderController.php`
* **සිදු කළ යුතු වෙනස:** 
  - මෙහි `index` method එක තුළ `$orders->getCollection()->transform(...)` මගින් map කරන විට, ඒ ඇතුළත `WorkshopDispatch::where('order_id', $order->id)...` ලෙස ලියා ඇති Query එක ඉවත් කළ යුතුය. 
  - ඒ වෙනුවට Controllers වෙතින් දත්ත යැවීමට පෙර Eager Loading (`with`) හෝ Subqueries භාවිතා කර සියලුම අදාළ WorkshopDispatch දත්ත එකවර load කරගත යුතුය.

---

### 2. Memory Crash අවදානම (Chunking භාවිතා නොකිරීම)
විශාල දත්ත ප්‍රමාණයක් එකවර Server RAM එකට load කිරීම නිසා "Memory Exhausted" වී Server එක Crash වීමේ අවදානමක් ඇත.

* **වෙනස් විය යුතු ගොනුව:** `app/Http/Controllers/ReportController.php`
* **සිදු කළ යුතු වෙනස:**
  - `exportPdf`, `salesReportPdf`, `serviceReportPdf`, `closeShiftReport` යන methods වල Database එකෙන් දත්ත ලබා ගැනීමට යොදා ඇති `get()` වෙනුවට `chunk(500)` හෝ `cursor()` භාවිතා කළ යුතුය.
  - Delivery Fees සහ Branch Profits ගණනය කිරීම සඳහා ලියා ඇති විශාල `foreach ($payments as $payment)` PHP loop එක ඉවත් කර, ඒ වෙනුවට කෙළින්ම SQL `SUM(CASE WHEN...)` query එකක් Database මට්ටමෙන් ධාවනය කළ යුතුය.

---

### 3. Database Indexes (Reports වේගවත් කිරීම)
දැනට තනි columns වලට Indexes දමා තිබුණත්, Reports load කිරීමේදී Queries භාවිතා කරන්නේ columns දෙකක එකතුවකි (Branch සහ Date).

* **වෙනස් විය යුතු ගොනු:** `database/migrations/` හි නව Migration ගොනුවක් සෑදිය යුතුය.
* **සිදු කළ යුතු වෙනස:**
  - `php artisan make:migration add_composite_indexes_to_orders` යන command එක run කර අලුත් migration එකක් සෑදිය යුතුය.
  - එම ගොනුව තුළ `orders` table එක සඳහා `branch_id` සහ `created_at` එකට එකතු වූ Composite Index එකක් යෙදිය යුතුය: `$table->index(['branch_id', 'created_at']);`

---

### 4. Security සහ Queue Management (පෝලිම් කළමනාකරණය)
1000 orders/day එන විට, SMS සහ WhatsApp යැවීම් Database එක හරහා සිදුකිරීමෙන් Database Lock විය හැක. එසේම Security ගැටලු පවතී.

* **වෙනස් විය යුතු ගොනුව:** `.env` (Project Root එකෙහි ඇති)
* **සිදු කළ යුතු වෙනස:**
  - Production (Live Server) එකෙහි `APP_DEBUG=true` යන්න `APP_DEBUG=false` ලෙස අනිවාර්යයෙන් වෙනස් කළ යුතුය.
  - `QUEUE_CONNECTION=database` යන්න `QUEUE_CONNECTION=redis` ලෙස වෙනස් කළ යුතුය (මෙය සිදුකිරීමට පෙර Server එකේ Redis install කර තිබිය යුතුය).

---

### 5. Data Archiving (පැරණි දත්ත කළමනාකරණය)
කාලයත් සමඟ ප්‍රධාන `orders` table එකේ දත්ත මිලියන ගණනක් පිරී පද්ධතිය මන්දගාමී වේ. ඔබ දැනටමත් `orders_archive` table එක සාදා ඇති බැවින් මෙය ක්‍රියාත්මක කළ යුතුය.

* **වෙනස් විය යුතු ගොනු:** 
  1. `app/Console/Commands/ArchiveOldOrders.php` (මෙම නමින් අලුත් Command ගොනුවක් සෑදිය යුතුය).
  2. `routes/console.php` (හෝ Laravel 10 නම් `app/Console/Kernel.php`).
* **සිදු කළ යුතු වෙනස:**
  - නව Command ගොනුව තුළ: මාස 6 කට වඩා පරණ වූ සහ Status එක "Delivered" හෝ "Completed" වූ Orders සොයා, ඒවා `orders_archive` table එකට copy කර ප්‍රධාන table එකෙන් මකා දැමීමේ (Delete) logic එක ලිවිය යුතුය.
  - `console.php` හෝ `Kernel.php` ගොනුව තුළ: මෙම Command එක දිනපතා අලුයම 2:00 ට ස්වයංක්‍රීයව ක්‍රියාත්මක වීමට CRON Job (Task Scheduling) එකක් සකස් කළ යුතුය (`$schedule->command('orders:archive')->dailyAt('02:00');`).

---

**අවසන් නිගමනය:**
මෙම ගොනු (Files) ටිකේ ඉහත වෙනස්කම් සිදු කිරීමෙන් පසු, කිසිදු බාධාවකින් තොරව (Crash නොවී), ඉතාමත් වේගවත්ව, ආරක්ෂිතව ඔබේ පද්ධතිය දිනකට Orders 1000 කට වඩා වුවද හැසිරවීමට සමත් වනු ඇත.

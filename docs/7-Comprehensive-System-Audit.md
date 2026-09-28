# 7. Comprehensive System Audit Report

මෙම වාර්තාවෙන් 360 Laundry පද්ධතියේ ගෘහ නිර්මාණ ශිල්පය (Architecture), ක්‍රියාකාරීත්වය (Performance), ආරක්ෂාව (Security), සහ දත්ත සමුදාය (Database) පිළිබඳව පූර්ණ විශ්ලේෂණයක් (Full Audit) ඉදිරිපත් කෙරේ. දිනකට Orders 1000 කට වඩා ලැබෙන, අවුරුද්දේ දින 365 ම ක්‍රියාත්මක වන (High Scale) පද්ධතියක් ලෙස මෙහි අඩුපාඩු සහ දියුණු කළ හැකි ස්ථාන මෙහි දක්වා ඇත.

---

## 1. Code Quality & Action-Based Architecture (කේතයේ ගුණාත්මකභාවය සහ ආකෘතිය)
**තත්වය:** Excellent (ඉතා හොඳයි)
පද්ධතියේ ප්‍රධාන ව්‍යාපාරික තර්ක (Business Logic) Controller වලින් වෙන් කරලා `App\Actions` කියන වෙනම ගොනුවකට දාලා තියෙනවා (උදා: `CreatePosOrderAction`, `ApprovePaymentSlipAction`). 
* **වාසිය:** මේ නිසා අනාගතයේදී API එකක් හැදුවොත් හරි, Mobile App එකක් හැදුවොත් හරි මේ Action classes කිසිම වෙනසක් නැතුව පාවිච්චි කරන්න පුළුවන්. 
* **යෝජනාව:** `OrderCorrectionController@approve` එකේ තියෙන ගණන් හදන code කෑලි ටිකත් (Turnaround, Extras අලුතින් ගණනය කිරීම) වෙනම `RecalculateOrderTotalsAction` එකකට ගෙනාවොත් Code Quality එක තවත් වැඩි වෙනවා.

## 2. Database Design Audit (දත්ත සමුදායේ සැලැස්ම)
**තත්වය:** Good (හොඳයි)
* **වාසි:** `orders`, `payment_transactions`, `inventory_items` වගේ ප්‍රධාන Tables වලට අදාලව `foreignId` සහ `cascade` delete පාවිච්චි කරලා තියෙනවා.
* **Performance Indexes:** මෑතකදී `2026_09_05_162600_add_performance_indexes_to_core_tables` හරහා `status`, `created_at`, `due_date` වගේ නිතර Search වෙන තැන් වලට Indexes දාලා තියෙනවා. මේක දිනකට Orders 1000 ක් එන System එකකට අත්‍යවශ්‍ය දෙයක්.
* **යෝජනාව:** `orders` table එකේ `order_number` එක String එකක්. මේක Index කරලා තිබ්බත් `LIKE %...%` search කරද්දි (Global Search එකේ) Full Table Scan එකක් යන්න පුළුවන්. ඒ නිසා Elasticsearch වගේ සෙවුම් යන්ත්‍රයක් (Search Engine) හෝ Full Text Index එකක් පාවිච්චි කිරීම වඩාත් සුදුසුයි.

## 3. Performance & Memory Management (කාර්යක්ෂමතාව සහ මතකය කළමනාකරණය)
**තත්වය:** Needs Improvement (වැඩිදියුණු කළ යුතුයි)
මෙතන තමයි දිනකට Orders 1000ක් එන System එකක ලොකුම ප්‍රශ්න මතු වෙන්නේ.
* **Reports වල Memory Issue:** `SalesReportController` සහ `MainReportController` වල යම් දින පරාසයක දත්ත ගන්න `->get()` පාවිච්චි කරලා තියෙනවා. 
  - *උදාහරණයක්:* අවුරුද්දක Sales Report එකක් PDF කරන්න හැදුවොත්, 365,000 ක Orders ප්‍රමාණයක් එකපාර PHP Memory එකට Load වෙන්න හදනවා. මේකෙන් "PHP Fatal Error: Allowed memory size exhausted" කියන Error එක එනවා අනිවාර්යයි.
  - **විසඳුම:** Reports හදද්දි අනිවාර්යයෙන්ම **Chunking** (`->chunk(1000, function(...)`) පාවිච්චි කරන්න ඕනේ. එහෙමත් නැත්නම් Database මට්ටමෙන්ම (SQL SUM, COUNT) ගණන් හදලා ගන්න ඕනේ.

## 4. Pagination & Chunking (දත්ත කොටස් වශයෙන් ගැනීම)
**තත්වය:** Moderate (මධ්‍යම)
* **වාසි:** `OrderController@index` වගේ ප්‍රධාන තැන් වල `->paginate(15)` පාවිච්චි කරලා තියෙනවා. ඒ වගේම N+1 Query ප්‍රශ්න එන්නේ නැති වෙන්න `with(['customer', 'items...'])` Eager Loading පාවිච්චි කරලා තියෙනවා.
* **අඩුපාඩු:** PDF Exports වලදී Pagination එකක් Manual හදලා තියෙනවා (`slice(...)`). මේකෙන් මුලින්ම මුළු දත්ත තොගයම Memory එකට එන එක නවතින්නේ නෑ. PDF Generate කරද්දි Chunking පාවිච්චි කරන්නම වෙනවා.

## 5. Security Audit (ආරක්ෂාව පිළිබඳ විමර්ශනය)
**තත්වය:** Good (හොඳයි)
* **Access Control:** Roles පදනම් කරගෙන (super_admin, branch_admin, cashier) Gate/Middleware හරහා ආරක්ෂාව තහවුරු කරලා තියෙනවා.
* **Validation:** Form requests වලට Laravel Validation rules හරියටම පාවිච්චි කරලා තියෙනවා.
* **අඩුපාඩුවක්:** `OrderController@reverseOrder` එකේදි Cashier ගේ මුරපදය (Password) අහනවා. හැබැයි මේකෙදි Rate Limiting (Throttle) එකක් දාලා නෑ වගේ. කවුරුහරි දිගටම වැරදි Password එක ගැහුවොත් Brute-force attack එකක් යන්න පුළුවන්. ඒකට Middleware එකට `throttle:5,1` වගේ දැම්මොත් හොඳයි.

## 6. Error Handling & Logging Audit (දෝෂ කළමනාකරණය)
**තත්වය:** Good (හොඳයි)
* **වාසි:** `try-catch` blocks පාවිච්චි කරලා තියෙනවා (උදා: `OrderController@updateStatus`). ඒ වගේම `Log::error()` හරහා Errors file එකක ලියවෙන්න සලස්වලා තියෙනවා. Database Transactions (`DB::transaction`) ගොඩක් තැන් වල පාවිච්චි කරලා තියෙන නිසා භාගෙට Save වෙන අවුල් එන්නේ නෑ.
* **යෝජනාව:** `config/logging.php` එකේ `daily` channel එක පාවිච්චි කරන එක හොඳයි. මොකද අවුරුද්දක් එක දිගට දුවද්දි `laravel.log` file එක GB ගාණක් ලොකු වෙන්න පුළුවන්. 

## 7. Summary & Next Steps (සාරාංශය සහ ඊළඟ පියවර)
ඔබගේ පද්ධතිය දිනකට Orders 1000ක් (දින 365ම) දරාගැනීමට අවශ්‍ය මූලික අඩිතාලම (Action based architecture, Database indexes, DB transactions) ඉතා හොඳින් සකසා ඇත. 

**වහාම සිදුකළ යුතු වෙනස්කම්:**
1. Reports වල (විශේෂයෙන් PDF Export වල) තියෙන `->get()` වෙනුවට SQL මට්ටමින් aggregate කිරීම හෝ `chunk()` යෙදීම.
2. `OrderCorrectionController` එකේ ඇති Code duplication (ගණන් හදන කොටස) වෙනම Action Class එකකට ගැනීම.
3. Brute-force attacks වළක්වා ගැනීමට මුරපද අසන ස්ථාන වලට (Reverse Order) Rate Limiting යෙදීම.

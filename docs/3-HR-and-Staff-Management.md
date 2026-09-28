# 3. HR & Staff Management (සේවක කළමනාකරණය) - Developer Guide

මේ Document එකෙන් පැහැදිලි කරන්නේ System එකේ HR වැඩ කටයුතු, Attendance (පැමිණීම), Leaves (නිවාඩු), සහ Salary Advances/Adjustments (පඩි අත්තිකාරම් හා වෙනස්කම්) manage කරන විදිහයි.

## 1. Attendance Management (සේවක පැමිණීම සටහන් කිරීම)
**Controller:** `App\Http\Controllers\HRController`
**Models:** `User` (Staff), `Attendance`

* **Clock In / Clock Out**: 
  - සේවකයෙක් උදේට වැඩට ආවාම `storeAttendance` හරහා `clock_in` time එකත් එක්ක record එකක් හැදෙනවා.
  - වැඩ ඉවරවෙලා යද්දි `updateAttendance` හරහා `clock_out` time එක දානවා.
* **OT (Overtime) ගණනය කිරීම**:
  - සේවකයෙක් ගෙදර යද්දි (Clock out කරද්දි), System එකෙන් check කරනවා එයාගේ `standard_working_hours` (උදා: පැය 8). 
  - වැඩ කරපු මුළු පැය ගාන `standard_working_hours` වලට වඩා වැඩි නම්, ඒ වැඩිපුර තියෙන කාලය `ot_minutes` විදිහට Database එකේ සටහන් වෙනවා.
* **Bulk Update**: 
  - මාසේ අන්තිමට Admin කෙනෙක්ට එක පාරටම සේවකයෙක්ගේ මාසෙම Attendance එක වෙනස් කරන්න පුළුවන් (`bulkUpdateAttendance` හරහා). මේකෙදි JSON Data එකක් යවලා අදාල දවස් ටික update කරනවා.

## 2. Leaves Management (නිවාඩු කළමනාකරණය)
* **Requesting Leave**: `storeLeave` හරහා සේවකයෙක්ට නිවාඩුවක් දාන්න පුළුවන් (casual හෝ sick).
* **Auto Approval**: Admin කෙනෙක් නිවාඩුව දැම්මොත් ඒක කෙලින්ම `approved` වෙනවා. නැත්තම් `pending` විදිහට වැටෙනවා.
* **Auto Absent Marking**: නිවාඩුවක් `approved` උනාම, System එකෙන් ඉබේම එදා දවසට අදාලව `Attendance` record එකක් හදලා ඒකේ status එක `absent` විදිහට update කරනවා. (එතකොට එදා දවසේ Attendance list එකේ එයාගේ නම පෙන්නන්නේ නෑ).

## 3. Salary Advances & Adjustments (පඩි අත්තිකාරම් සහ දීමනා)
* **Salary Advances (අත්තිකාරම් මුදල්)**: 
  - සේවකයෙක් අත්තිකාරම් මුදලක් ඉල්ලද්දි `storeAdvance` හරහා System එක `App\Services\PayrollCalculator` එක පාවිච්චි කරලා එයා එදා වෙනකන් වැඩ කරලා කීයක් හොයලා තියෙනවද (payable balance / net salary) කියලා බලනවා.
  - ඉල්ලන ගාණ ඒ හොයපු ගාණට වඩා වැඩි නම් Error එකක් දෙනවා. (ඒ කියන්නේ වැඩ කරපු ගාණට වඩා Advances ගන්න බෑ).
* **Adjustments (Allowances / Deductions)**:
  - මාසේ පඩියට එකතු වෙන්න ඕනේ දීමනා (Allowances) හෝ කැපෙන්න ඕනේ දඩ (Deductions) `storeAdjustment` හරහා දාන්න පුළුවන්. මේවා කෙලින්ම Payroll ගණන් හදද්දි එකතු/අඩු වෙනවා.

## 4. Payroll Calculation (පඩි හැදෙන විදිහ)
* පඩිය ගණන් හදන්නේ `App\Services\PayrollCalculator` කියන Service class එකෙන්. ඒකෙන් Basic Salary එක දවස් ගාණට බෙදලා, OT ගාස්තු එකතු කරලා, Advances / Deductions අඩු කරලා තමයි අතට එන ගාණ (Net Salary) හදන්නේ.

## 5. Code එකේ හදාගන්න පුළුවන් දේවල් (Bugs / Enhancements)
* **OT Calculation Bug**: `clock_in` සහ `clock_out` අතර වෙනස (diffInMinutes) බලද්දි, දවල් කෑම පැය (Lunch Hour) අඩු කරලා නෑ. මේ නිසා පැය 9ක් හිටියොත් (පැය 8 වැඩ + 1 කෑම) ඒකත් OT විදිහට ගණන් හැදෙන්න පුළුවන්. ඒක `standard_working_hours` වලට පැය 9ක් දීලා විසඳන්න පුළුවන් උනත්, වඩා හොඳ ක්‍රමය තමයි Lunch break එක අඩු කරන logic එකක් ලියන එක.

# 5. Reports & Accounting (වාර්තා සහ ගිණුම්කරණය) - Developer Guide

මේ Document එකෙන් පැහැදිලි කරන්නේ System එකේ Sales Reports, Shift Reports, සහ ගිණුම්කරණයට අදාල දේවල් වැඩ කරන විදිහයි.

## 1. Sales Report (අලෙවි වාර්තාව)
**Controller:** `App\Http\Controllers\Reports\SalesReportController`

මේකෙන් දවසක හෝ මාසයක Sales එක Cash, Card, Bank, සහ Credit විදිහට වෙන් කරලා පෙන්නනවා.
* **ගණන් හදන විදිහ**:
  - Cash, Card, Bank Sales: මේවා ගන්නේ `PaymentTransaction` table එකෙන්. ඒ කියන්නේ Customer සල්ලි දුන්න දවසට අදාලවයි. 
  - Credit Sales (ණයට දුන්න දේවල්): මේවා ගන්නේ කෙලින්ම `Order` table එකෙන්. ඒ කියන්නේ Order එක දාපු දවසට අදාලවයි.
* **Settlements (ණය බේරීම)**:
  - කලින් Credit එකට දාපු Order එකකට පස්සේ දවසක සල්ලි දුන්නොත්, අලුත් `PaymentTransaction` එකක් හැදෙනවා. එතකොට ඒක Sales report එකේ `is_settlement` කියලා වෙනම පෙන්නනවා (මොකද එදා දවසේ අලුතින් කරපු Sale එකක් නෙමෙයි, පරණ ණය එකතු වීමක් නිසා).

## 2. Shift Report (Cashier කෙනෙක්ගේ Shift එක ඉවර කිරීම)
**Controller:** `App\Http\Controllers\Reports\ShiftReportController`
**Model:** `RegisterSession`

* Cashier කෙනෙක් Shift එකක් Open කරලා Orders දාලා, දවස අන්තිමට Shift එක Close කරද්දි System එකෙන් එයාගේ Cash Till එකේ තියෙන්න ඕනේ ගාණ ගණන් හදනවා (System Expected Cash).
* **Cash Balance Service**: `App\Services\CashBalanceService` එක හරහා Petty cash වියදම්, Refund කරපු සල්ලි ඔක්කොම අඩු කරලා තමයි අතේ ඉතුරු වෙන්න ඕනේ ගාණ හදන්නේ.
* එතනදි Cashier අතේ තියෙන ඇත්ත ගාණ ගැහුවාම (Actual Cash), System එකෙන් හිඟයක් (Shortage) තියෙනවද වැඩිපුර තියෙනවද (Overage) කියලා පෙන්නනවා.

## 3. Main Report / Packages (ප්‍රධාන වාර්තා සහ පැකේජ)
**Controller:** `App\Http\Controllers\Reports\MainReportController`

* මේකෙදි මාසෙට අදාල මුළු ආදායම, වියදම සහ ලාභය Dashboard එකක් විදිහට පෙන්නනවා.
* **Packages**: Customer කෙනෙක් Monthly Package එකක් ගත්තොත් ඒකේ ගෙවීම් Approve කරන එකත් මේ හරහා වෙනවා.

## 4. Code එකේ තියෙන බරපතල ප්‍රශ්න (Critical Bugs in Accounting)
1. **Credit Order එකක් ගෙව්වාම Sale එක මැකීම**: 
   - `CollectCreditPaymentAction` එකේදි, ණයට ගත්ත Order එකක සල්ලි පස්සේ දවසක ගෙව්වාම System එකෙන් Order එකේ `payment_method` එක 'credit' ඉඳන් 'cash' (හෝ අදාල විදිහට) මාරු කරනවා (`$order->payment_method = $data['payment_method'];`).
   - මේක නිසා Sales Report එකේ ලොකු අවුලක් යනවා! මොකද Sales Report එකෙන් Credit sales හොයන්නේ `payment_method = 'credit'` කියලා තියෙන Orders වලින් විතරයි.
   - උදාහරණයකට ජනවාරි 1 දාපු Credit Order එකකට, ජනවාරි 5 Cash දුන්නොත්, ජනවාරි 1 Sales Report එක බැලුවොත් එදා කරපු Sale එක පේන්න නෑ (මොකද දැන් payment_method එක Cash වෙලා). එතකොට ජනවාරි 1 ගිණුම් වැරදියි. 
   - **විසඳුම**: ණයට දෙන Orders වල මූලික payment_method එක හැමතිස්සෙම 'credit' විදිහටම තියන්න ඕනේ. ගෙවන සල්ලි ටික `PaymentTransaction` වල විතරක් තියෙන්න අරින්න ඕනේ. නැත්තම් Orders table එකට `initial_payment_method` කියලා අලුත් column එකක් දාන්න වෙනවා.

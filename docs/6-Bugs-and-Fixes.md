# 6. Bugs & Enhancements Report (හමුවූ දෝෂ සහ යෝජනා)

System එක සම්පූර්ණයෙන්ම Analyze කරද්දි මට හම්බුන Bugs සහ System එක තවත් හොඳ කරන්න පුළුවන් තැන් ටිකක් පල්ලෙහායින් දාලා තියෙනවා.

## 1. Credit Orders වල අලෙවි වාර්තා මැකී යාමේ දෝෂය (Critical Bug)
**ස්ථානය:** `App\Actions\Order\CollectCreditPaymentAction` සහ `SalesReportController`
**ප්‍රශ්නය:** 
කෙනෙක් ණයට (Credit) Order එකක් දාලා, දවස් කීපයකට පස්සේ ඒ සල්ලි Cash වලින් ගෙව්වොත් System එකෙන් Order එකේ `payment_method` එක 'credit' ඉඳන් 'cash' වලට මාරු කරනවා. හැබැයි Sales Report එකේ Credit Sales ගණන් හදන්නේ `payment_method = 'credit'` කියලා තියෙන Orders වලින් විතරයි. මේ නිසා සල්ලි ගෙවපු ගමන් පරණ දවසේ Sales Report එකෙන් ඒ Credit Sale එක සම්පූර්ණයෙන්ම මැකිලා යනවා.
**විසඳුම:** `CollectCreditPaymentAction` එකේදි මුළු මුදල ගෙව්වාම `$order->payment_method` එක overwrite කරන එක නවත්තන්න ඕනේ. (ඒක මම Fix කරන්නද කියලා ඔයා කියන්න).

## 2. Gate Facade එක Import කරලා නැති වීම (Fixed)
**ස්ථානය:** `App\Http\Controllers\InventoryController@destroy`
**ප්‍රශ්නය:** `Gate::authorize('manage-users')` පාවිච්චි කරලා තිබ්බට උඩින් class එක import කරලා තිබ්බේ නෑ. ඒ නිසා Item එකක් delete කරන්න හැදුවොත් System එක Crash වෙනවා.
**තත්වය:** මම මේක Fix කරා (Code එකට `use Illuminate\Support\Facades\Gate;` එකතු කරා).

## 3. Bulk Stock Out වලදි ඍණ අගයන් (Negative Stock) යන්න පුළුවන් වීම
**ස්ථානය:** `InventoryController@bulkStockOut`
**ප්‍රශ්නය:** සාමාන්‍ය විදිහට එක Item එකක් Stock Out කරද්දි තියෙන ගාණට වඩා අඩුවෙන් දාන්න බෑ කියලා Validation එකක් තියෙනවා (`$item->current_stock < $request->quantity`). හැබැයි Bulk Stock Out (එක පාර ගොඩක් දාද්දි) මේ Validation එක නෑ. ඒ නිසා කවුරුහරි වැරදිලා ලොකු ගාණක් දැම්මොත් Stock එක සෘණ (Negative) වෙනවා.

## 4. පඩි හැදීමේදී (Payroll) Lunch Break එක අදාල නොවීම
**ස්ථානය:** `HRController@updateAttendance`
**ප්‍රශ්නය:** OT (Overtime) ගණන් හදන්නේ Clock In සහ Clock Out අතර කාලය බලලා. හැබැයි මේකෙදි කෑමට ගන්න පැය (Lunch Break) අඩු වෙන්නේ නෑ. සේවකයෙක් පැය 8 ක් වැඩ කරලා පැයක් කෑමට ගත්තොත්, System එකට පේන්නේ පැය 9 ක් වැඩ කරලා වගේ නිසා අමතර පැයටත් OT හැදෙන්න පුළුවන්.

## 5. UI ප්‍රශ්නය - Request Button එකේ උස මදි වීම (Pending)
**ස්ථානය:** Order Edit Page එකේ දකුණු පැත්තේ මෙනුව (`order-panel-right.blade.php`).
**ප්‍රශ්නය:** ඔයා කලින් කියපු විදිහට Edit page එකේ Request button එකේ උස (Height) සහ පෙනුම තව ටිකක් හදන්න ඕනේ. (ඒකත් මම මේ ගමන්ම හදලා දෙන්නම්).

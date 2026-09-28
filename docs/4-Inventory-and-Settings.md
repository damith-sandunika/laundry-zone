# 4. Inventory & Settings (බඩු තොග සහ සැකසුම්) - Developer Guide

මේ Document එකෙන් පැහැදිලි කරන්නේ System එකේ බඩු තොග (Inventory) manage කරන විදිහ සහ Admin settings වල සිදුවෙන දේවල් ගැනයි.

## 1. Inventory Management (බඩු තොග කළමනාකරණය)
**Controller:** `App\Http\Controllers\InventoryController` සහ `InventorySettingsController`
**Models:** `InventoryItem`, `InventoryTransaction`

Workshop එකේ සබන්, ලික්විඩ් වගේ දේවල් වල තොග (Stock) බලන්න සහ Update කරන්න මේක පාවිච්චි කරනවා. 

* **අලුතෙන් බඩු දැමීම (Stock In)**:
  - Admin Settings පැත්තෙන් (`InventorySettingsController@storeInventory`) අලුත් Item එකක් හදන්න පුළුවන් (උදා: Washing Liquid).
  - Workshop එකේදි එක පාරටම බඩු ගොඩක් stock කරද්දි `bulkStockIn` කියන method එකෙන් අදාල Items වල `current_stock` එක වැඩි කරනවා. ඒ වගේම ඒකට අදාලව `InventoryTransaction` එකක් 'stock_in' විදිහට හැදෙනවා.
* **බඩු අඩු කිරීම (Stock Out)**:
  - Workshop එකෙන් සබන් පාවිච්චි කරාම, දවස අන්තිමට `bulkStockOut` හරි `stockOut` හරි පාවිච්චි කරලා current stock එකෙන් ඒ ගාණ අඩු කරන්න (decrement) පුළුවන්. 
  - මෙතනදි `quantity` එක 0.01 ට වඩා වැඩි අගයක් වෙන්න ඕනේ කියලා validate කරලා තියෙනවා. මොකද බර (කිලෝ/ග්‍රෑම්) විදිහට දාන දේවල් තියෙන්න පුළුවන් නිසා.

## 2. Admin Settings (පද්ධතියේ සැකසුම්)
**Controllers:** `GeneralSettingsController`, `ServiceSettingsController`, `LogisticsSettingsController`, `PackageSettingsController`

System එකේ හැමතැනම පාවිච්චි වෙන Settings හදන්නේ මේ Controllers වලින්.
* **Services & Categories**: 
  - Laundry එකේ දෙන සේවා (උදා: Wash & Fold, Dry Cleaning) හදන්නෙයි, ඒවගේ ගණන් දාන්නෙයි `ServiceSettingsController` එකෙන්. 
  - මේවා හැදුවාම කෙලින්ම POS එකේ සහ Order Edit page එකේ පෙන්නනවා.
  - ඒවා order වෙන පිළිවෙල (Drag & Drop) කරන්නත් `reorderServices` කියන එකෙන් පුළුවන්.
* **Delivery & Pickup Settings**:
  - `LogisticsSettingsController` එකෙන් තමයි Minimum Delivery Cost එක සහ Delivery Ranges/Zones හදන්නේ.
* **Cubbies (රාක්ක පේලි)**: 
  - රෙදි හෝදලා ආවම අසුරලා තියෙන Cubbies / Racks හදන්නේ `storeCubby` හරහා. මේවා Order Edit page එකේ දකුණු පැත්තේ පල්ලෙහායින් assign කරන්න පුළුවන්.

## 3. Code එකේ හදාගන්න තියෙන දේවල් (Bugs & Fixes)
1. **Gate Facade Import Error**: `InventoryController@destroy` එකේ `Gate::authorize('manage-users');` කියලා පාවිච්චි කරලා තියෙනවා. හැබැයි උඩින් `use Illuminate\Support\Facades\Gate;` කියලා import කරලා නෑ. මේ නිසා item එකක් delete කරන්න ගියොත් System එක Error එකක් දෙනවා ("Class 'Gate' not found"). මේක හදන්න ඕනේ.
2. **Current Stock Validation**: `stockOut` කරද්දි Item එකේ තියෙන තොගයට වඩා අඩුවෙන් දාන්න බැරි වෙන්න (`$item->current_stock < $request->quantity`) කියලා බලලා තියෙනවා. හැබැයි `bulkStockOut` එකේදි ඒ Check එක නෑ. ඒ නිසා ඍණ (Negative) stock යන්න පුළුවන් Bulk Stock Out එකේදි. ඒකට Validation එකක් දාන්න ඕනේ.

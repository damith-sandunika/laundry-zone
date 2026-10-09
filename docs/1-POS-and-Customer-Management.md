# 1. POS සහ Customer Management (පාරිභෝගික කළමනාකරණය) - Developer Guide

මේ Document එකෙන් පැහැදිලි කරන්නේ අපේ Laundry Zone System එකේ Point of Sale (POS) එකයි, Customers ලව manage කරන විදිහයි.

## 1. Point of Sale (POS) Module එක
**Path:** `resources/views/pos/index.blade.php`
**Controller:** `App\Http\Controllers\POSController`

Branch එකක Cashier කෙනෙක්ට Order එකක් දාන්න තියෙන ප්‍රධානම තැන තමයි POS එක.

### ප්‍රධාන දේවල් (Key Features)
* **Shift එක Open කරලා තියෙන්න ඕනේ:** Cashier කෙනෙක්ට POS එක පාවිච්චි කරන්න නම් අනිවාර්යයෙන්ම Shift එකක් open කරලා (RegisterSession) තියෙන්න ඕනේ. හැබැයි Workshop Admin ලට මේ නීතිය අදාල නෑ, මොකද එයාලා සල්ලිත් එක්ක වැඩ කරන්නේ නැති නිසා.
* **Centralized vs Decentralized:** System එකේ Settings වල `centralized_management` on කරලා නම් හැම branch එකකම services ඔක්කොම පෙන්නනවා. නැත්තම් ඒ ඒ branch එකට අදාල services විතරයි POS එකේ පෙන්නන්නේ.
* **AlpineJS State Management:** POS එකේ Cart එක (තෝරන Items, ගණන් හැදෙන විදිහ, Discount අඩු වෙන විදිහ) සම්පූර්ණයෙන්ම වැඩ කරන්නේ Client-side (Alpine.js වල `x-data="posCart()"` හරහා). මේක නිසා page එක reload වෙන්නේ නැතුව real-time ගණන් හැදෙනවා.
* **Order එකක් හැදෙන විදිහ:** 
  - සාමාන්‍ය Orders: `App\Actions\POS\CreatePosOrderAction` එකෙන්.
  - Delivery Orders (දුරකථන මාර්ගයෙන් එන): `App\Actions\POS\CreatePosDeliveryOrderAction` එකෙන්.

### POS එකෙන් Order එකක් දාන පියවර:
1. Cashier මුලින්ම Customer ව search කරනවා (AJAX හරහා `CustomerController@search` වලට call වෙනවා).
2. Category එකක් select කරලා ඒකෙන් Items තෝරනවා.
3. ඒ තෝරපු ඒවා Alpine JS cart array එකට එකතු වෙනවා.
4. Turnaround time (දාන දවස/වෙලාව), Extras, සහ Discounts එතනදිම adjust කරන්න පුළුවන්.
5. "Place Order" click කරාම JSON විදිහට backend (`POSController@store`) එකට යනවා.
6. Action class එකෙන් `Order`, `OrderItem` tables වලට data දාලා, OrderStatusHistory එක `received` විදිහට මුලින්ම හදනවා.

## 2. Customer Management (පාරිභෝගිකයින් කළමනාකරණය)
**Controller:** `App\Http\Controllers\CustomerController`

අපේ System එකේ Customers ලා හැම branch එකටම පොදුයි. (Branch A එකෙන් හදන Customer කෙනෙක් Branch B එකෙන් ආවත් සේවය දෙන්න පුළුවන්).

### ප්‍රධාන දේවල්
* **Customer Search (`/customers/search`):** නමින් හරි Phone නම්බර් එකෙන් හරි search කරන්න පුළුවන් AJAX endpoint එකක්. මේකෙදි `withoutGlobalScopes()` පාවිච්චි කරලා තියෙනවා හැම Branch එකකම ඉන්න Customers ලව පෙන්නන්න.
* **POS එකේ ඉඳන් ඉක්මනින් Customer ව Add කිරීම:** POS එක අස්සෙ තියෙන Modal එකෙන් අලුත් Customer කෙනෙක්ව එකතු කරන්න පුළුවන්.
  - **වැදගත්:** මේ Modal එකේ UI එක හදද්දි `x-teleport="body"` පාවිච්චි කරලා තියෙනවා Modal එක පිටිපස්සට යන්නේ නැතුව ඉස්සරහින්ම (z-index) පෙන්නන්න.
* **Customer Mark (`customer_mark`):** Workshop එකේදි රෙදි බෑග් ලේසියෙන් අඳුරගන්න Customer ට අදාලව විශේෂ සලකුණක් (උදා: "VIP", "M1") දෙන්න පුළුවන්. මේක Print වෙන බිල් එකේ පෙන්නනවා.
* **Sales Report / Profile:** Customer ගේ Profile එකට ගියාම මාසිකව එයාලා කොච්චර වියදම් කරලා තියෙනවද කියලා Carbon grouping පාවිච්චි කරලා පෙන්නනවා. ඒක PDF එකක් විදිහට ගන්නත් පුළුවන්.

## 3. Code එකේ හදාගන්න පුළුවන් දේවල් (Bugs / Enhancements)
* **Phone Validation:** `CustomerController@store` එකේ Phone Number එක අලුතෙන් add කරද්දි manually database එකෙන් check කරලා තියෙනවා (Customer::where('phone')). ඊට වඩා ලේසියි Laravel වල තියෙන `unique:customers,phone` කියන validation rule එක පාවිච්චි කරා නම්.

# 2. Order Management (ඕඩර් කළමනාකරණය) - Developer Guide

මේ Document එකෙන් පැහැදිලි කරන්නේ System එකේ Orders Manage කරන විදිහ, Orders Edit කරන හැටි සහ Workflows (Status මාරු වෙන හැටි) ගැනයි.

## 1. Order Edit Page (ඕඩර් එකක් වෙනස් කිරීමේ පිටුව)
**Path:** `resources/views/orders/edit.blade.php`
**Controller:** `App\Http\Controllers\OrderController@edit`

මේ Page එක සම්පූර්ණයෙන්ම වගේ වැඩ කරන්නේ AlpineJS වලින්. Cashier කෙනෙක්ට Order එකේ තියෙන Items වෙනස් කරන්න, Delivery/Pickup මාරු කරන්න සහ Payments වෙනස් කරන්න මේකෙන් පුළුවන්.

### ප්‍රධාන දේවල්
* **AlpineJS State (`orderCart()`)**: POS එකේ වගේම මේකෙත් Cart එක, මුළු මුදල, Delivery ගාස්තු හැදෙන්නේ Client-side එකේ. හැබැයි මේකෙදි අලුත් Order එකක් හදනවට වඩා තියෙන Order object එකෙන් data අරගෙන තමයි Cart එක මුලින් පුරවාගන්නේ.
* **Correction Requests (වෙනස්කම් ඉල්ලීම්)**: Cashier කෙනෙක්ට එක පාරටම Order එකක් වෙනස් කරන්න බෑ. මොකද එතකොට සල්ලි වල ප්‍රශ්න එන්න පුළුවන්. ඒ නිසා Cashier කෙනෙක් Order එකක් වෙනස් කරද්දි ඒක "Correction Request" එකක් විදිහට Admin ට යනවා.
* **Admin Auto-approval**: හැබැයි Admin කෙනෙක් (branch_admin, workshop_admin, super_admin) Order එක Edit කරොත්, ඒකෙන් හදන Correction Request එක එවලෙම Auto Approve වෙලා Database එක අප්ඩේට් වෙනවා.
* **Order Panel UI Buttons (දකුණු පැත්තේ මෙනුව)**:
  - **Edit Order Items**: මේක click කරාම Cart එක වෙනස් කරන්න පුළුවන්.
  - **Refund Overpayment**: Customer ට ඕනෙවට වඩා සල්ලි ගෙවිලා නම් (paid_amount > total), ඒ ඉතුරු සල්ලි Customer ට ආපහු දෙන්න (Refund) කරන්න මේක පාවිච්චි කරනවා. ඉස්සර මේකේ "Keep as Store Credit" කියලත් තිබ්බා, දැන් ඒක UI එකෙන් අයින් කරලා තියෙන්නේ.
  - **Convert to Delivery / Pickup**: සාමාන්‍ය Order එකක් Delivery Order එකක් කරන්නත්, Delivery එකක් සාමාන්‍ය එකක් කරන්නත් මේකෙන් පුළුවන්.

## 2. Order Corrections & Refunds (වෙනස්කම් සහ සල්ලි ආපසු දීම)
**Controller:** `App\Http\Controllers\OrderCorrectionController`

Order එකක් place කරාට පස්සේ කරන ඕනෑම වෙනස්කමක් යන්නේ මේ Controller එක හරහා.

### Correction වර්ග:
1. `payment_change`: ගෙවන විදිහ මාරු කිරීම (උදා: Cash ඉඳන් Card එකට).
2. `items_change` / `order_change`: Cart එකේ තියෙන Items මාරු කිරීම. (මෙතනදි ආයෙත් අලුතෙන් ගණන් හැදිලා, Turnaround charges, Extras ඔක්කොම update වෙනවා).
3. `convert_to_delivery`: අලුතෙන් Delivery Appointment එකක් හැදිලා Delivery Fee එකතුවෙනවා.
4. `convert_to_pickup`: Delivery Appointment එක අයින් වෙලා Delivery Fee එක අඩු වෙනවා.
5. `overpayment_handling`: වැඩිපුර ගෙවපු සල්ලි Customer ට ආපහු දෙන එක (මේකෙදි `CashBalanceService` හරහා Branch එකේ Cash Balance එක අඩු වෙනවා).

### Approval Workflow එක යන විදිහ:
1. Cashier Request එකක් දැම්මාම -> `status = pending` වෙනවා. Admins ලට Notification එකක් යනවා.
2. Admin ඇවිත් Order එකේ "Approve" එබුවාම -> `OrderCorrectionController@approve` එක call වෙනවා.
3. ඒ `approve()` method එක ඇතුලෙදි තමයි අලුත් ගණන් හදලා, `PaymentTransaction` එක update කරලා `OrderStatusHistory` එකට Note එකක් වැටෙන්නේ.

## 3. Order Workflows & Statuses (ඕඩර් එකක් ගමන් කරන පියවර)
**Action:** `App\Actions\Order\UpdateOrderStatusAction`

Order එකක් මේ පිළිවෙලට තමයි status මාරු වෙන්නේ:
`order_created` -> `received` (Branch එකේ තියෙනවා) -> `dispatched_to_workshop` (Workshop එකට යැව්වා) -> `processing` (හෝදනවා/අයන් කරනවා) -> `completed_at_workshop` (Workshop එකෙන් වැඩ ඉවරයි) -> `returned_from_workshop` (ආපහු Branch එකට එව්වා) -> `done` (Branch එකට ලැබුනා) -> `delivered` (Customer අරන් ගියා).

* **Bulk Status Updates**: එකපාර Orders ගොඩක status මාරු කරන්නේ `OrderController@bulkStatus` එකෙන්.
* **Workshop එකේ වැඩ**: Workshop admins ලා ගොඩක් වෙලාවට handle කරන්නේ `dispatched_to_workshop` එකේ ඉඳන් `returned_from_workshop` වෙනකන් කෑල්ල විතරයි.

## 4. Code එකේ හදාගන්න පුළුවන් දේවල් (Bugs / Enhancements)
* **Store Credit Code එක**: UI එකෙන් Store Credit කියන button එක අයින් කරාට, `OrderCorrectionController@approve` එක ඇතුලේ තාමත් `if ($action === 'refund') ... else { kept as store credit }` කියන backend code එක තියෙනවා.
* **Code Duplicate වීම**: POS එකේ Order එකක් දාද්දි ගණන් හදන code කෑල්ලම (Turnaround charges / extras හදන විදිහ) `OrderCorrectionController@approve` එකේ (`items_change` වලට) ආයෙත් ලියලා තියෙනවා. මේක වෙනම Service Class එකකට දැම්මා නම් අනාගතේදි වෙනස්කම් කරද්දි ගණන් වරදින්නේ නෑ.

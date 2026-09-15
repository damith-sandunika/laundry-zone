@extends('layouts.site')

@section('content')
<div class="bg-blue-50 py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl lg:text-4xl font-black text-brand mb-4">Terms and Conditions</h1>
        <p class="text-slate-600">Last updated: {{ date('F d, Y') }}</p>
    </div>
</div>

<div class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="prose prose-slate prose-blue max-w-none">
            <p class="text-slate-600 mb-8 leading-relaxed text-lg">
                To ensure a smooth, transparent, and professional laundry experience, customers are kindly requested to read and understand the following terms and conditions before handing over garments or other items to 365 Laundry Service.
            </p>

            <h2 class="text-xl font-bold text-slate-800 mb-3 mt-8">1. Garment Count & Verification</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                Customers are requested to carefully count and verify all garments/items at the time of handing over and collection, and cross-check them against the relevant invoice or receipt. Once the customer accepts and collects the items, the customer is considered to have confirmed the quantity.
            </p>

            <h2 class="text-xl font-bold text-slate-800 mb-3 mt-8">2. Garment Condition & Natural Changes</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                365 Laundry Service takes every reasonable precaution to maintain the quality and appearance of garments. However, we cannot accept responsibility for natural or pre-existing issues such as colour bleeding, discoloration, shrinkage, fading, loss of buttons, belts, buckles, ornaments, elastic slackness, or alterations to curtain rings and similar accessories that may occur during cleaning.
            </p>

            <h2 class="text-xl font-bold text-slate-800 mb-3 mt-8">3. Stain & Spot Treatment</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                Our professional team will make every reasonable effort to remove stains and spots using suitable cleaning methods. However, complete stain removal cannot always be guaranteed, particularly for old, permanent, chemical, oil-based, or deeply embedded stains. Stains may be removed fully or partially. Any additional stain treatment process requested or required will be carried out at the customer's risk.
            </p>

            <h2 class="text-xl font-bold text-slate-800 mb-3 mt-8">4. Special-Care Requirements</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                If any garment requires special handling, delicate treatment, specific cleaning methods, or has known sensitivities, customers must clearly inform our staff at the time of handing over. Where special treatment is requested, the cleaning process will be undertaken at the customer's risk.
            </p>

            <h2 class="text-xl font-bold text-slate-800 mb-3 mt-8">5. Manufacturer Care Instructions</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                Due to operational requirements and processing procedures, 365 Laundry Service does not guarantee compliance with individual manufacturer's washing, drying, ironing, or care-label instructions. The laundry will use professional cleaning methods appropriate to the nature and condition of the item, and the customer accepts responsibility for any manufacturer-specific limitations.
            </p>

            <h2 class="text-xl font-bold text-slate-800 mb-3 mt-8">6. Personal Belongings</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                Customers are responsible for checking all pockets and removing money, jewellery, documents, electronic devices, keys, accessories, and other personal belongings before handing over garments. 365 Laundry Service shall not be responsible for any personal items left inside garments or laundry items.
            </p>

            <h2 class="text-xl font-bold text-slate-800 mb-3 mt-8">7. Payment Requirements</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                A 50% advance payment or full payment is required at the time garments/items are handed over, as applicable. 365 Laundry Service reserves the right to delay, suspend, or refuse processing where the required payment has not been received within the applicable timeframe.
            </p>

            <h2 class="text-xl font-bold text-slate-800 mb-3 mt-8">8. Claims & Complaints</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                Any claim relating to missing, damaged, or incorrectly processed garments/items must be reported to 365 Laundry Service within 24 hours of collection. Claims submitted after this period may not be considered.
            </p>

            <h2 class="text-xl font-bold text-slate-800 mb-3 mt-8">9. Limitation of Liability</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                In the event that a valid claim is accepted following an appropriate assessment, the maximum liability of 365 Laundry Service shall be limited to the value of the cleaning service charged for the affected item, unless otherwise agreed in writing.
            </p>

            <h2 class="text-xl font-bold text-slate-800 mb-3 mt-8">10. Assessment & Reimbursement</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                Where reimbursement is determined to be applicable following an investigation and assessment by 365 Laundry Service, the approved amount shall be settled within 30 days from the date the claim is formally accepted and confirmed.
            </p>

            <h2 class="text-xl font-bold text-slate-800 mb-3 mt-8">11. Collection of Completed Items</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                Customers must collect all completed garments/items within 15 days from the date of completion. Customers will be notified once their items are ready for collection. Items remaining uncollected after 15 days will be subject to a 25% additional surcharge.
            </p>

            <h2 class="text-xl font-bold text-slate-800 mb-3 mt-8">12. Items Uncollected for Extended Periods</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                Any garment or item remaining uncollected for more than three (03) months from the date of completion may be considered for donation or other appropriate disposal. Once this period has elapsed, 365 Laundry Service shall have no liability for any loss, damage, deterioration, or claim relating to such items.
            </p>

            <div class="mt-12 p-6 bg-blue-50 border border-blue-100 rounded-xl">
                <h2 class="text-lg font-black text-blue-900 mb-2 uppercase tracking-wide">Customer Acknowledgement</h2>
                <p class="text-blue-800 leading-relaxed font-medium">
                    By handing over garments or other items to 365 Laundry Service, the customer acknowledges that they have read, understood, and agreed to the above Terms & Conditions of Service.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection



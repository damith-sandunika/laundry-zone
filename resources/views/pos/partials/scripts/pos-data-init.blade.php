<script>
window.posSystemData = {
            timeSlots: @json($timeSlots),
            extras: @json($extras),
            turnaroundTypes: @json($turnaroundTypes),
            defaultPickupDays: {{ (int)\App\Models\Setting::get('default_pickup_days', '3') }},
            defaultPickupTime: '{{ \App\Models\Setting::get('default_pickup_time', '17:00') }}'
        };
        
        window.posSystem = function() {
            return {
                allServices: @json($services),
                allCategories: @json($categories),
                allCustomers: @json($customers),
                allCustomerPackages: @json($customerPackages),
                allExtras: window.posSystemData.extras,
                allTurnaroundTypes: window.posSystemData.turnaroundTypes,
                activeCategory: @json(count($categories) > 0 ? $categories[0]->id : null),
                activeSubcategory: null,
                searchQuery: '',
                customerSearch: '',
                filteredCustomers: [],
                showCustDropdown: false,
                selectedCustomer: null,
                cart: [],
                fastSearchQuery: '',
                fastSearchQty: 1,
                fastSearchSuggestions: [],
                fastSearchHighlightIndex: 0,
                selectedFastService: null,
                fastDrillCategory: null,
                fastDrillSubcategory: null,

                activePackage: null,
                showPackageModal: false,
                packageCart: [],

                showCheckoutModal: false,
                showNoteModal: false,
                currentNoteIndex: null,
                currentNoteText: '',
                isPlacingOrder: false,
                paymentMode: 'pay_on_pickup',
                paymentMethod: 'cash',
                dueDate: '',
                discountType: 'percentage', // 'percentage' or 'fixed'
                discountValue: 0,
                discountPercentage: 0,
                advanceAmount: '',
                specialName: '',
                specialPrice: 0,
                
                init() {
                    window.posAddCustomer = (customer) => {
                        this.allCustomers.push(customer);
                        this.selectCustomer(customer);
                    };

                    // Auto-calculate default due date using settings
                    let today = new Date();
                    today.setDate(today.getDate() + window.posSystemData.defaultPickupDays);
                    // Apply fixed pickup time from settings (e.g. "17:00")
                    const timeParts = (window.posSystemData.defaultPickupTime || '17:00').split(':');
                    today.setHours(parseInt(timeParts[0] || 17), parseInt(timeParts[1] || 0), 0, 0);
                    // Format as YYYY-MM-DDTHH:MM for datetime-local input
                    const pad = n => String(n).padStart(2, '0');
                    this.dueDate = today.getFullYear() + '-' + pad(today.getMonth()+1) + '-' + pad(today.getDate()) + 'T' + pad(today.getHours()) + ':' + pad(today.getMinutes());

                    // Auto-select first parent category and first sub-category on load
                    this.$nextTick(() => {
                        this.focusFastSearch();
                        const parentCats = this.allCategories.filter(c => !c.parent_id);
                        if (parentCats.length > 0) {
                            this.activeCategory = parentCats[0].id;
                            if (parentCats[0].children && parentCats[0].children.length > 0) {
                                this.activeSubcategory = parentCats[0].children[0].id;
                            }
                        }
                    });
                },

                @include('pos.partials.scripts.pos_fast_search_js')

                get filteredCategories() {
                    let filtered = this.allCategories;
                    // Only show parent categories in the main bar
                    filtered = filtered.filter(c => !c.parent_id);
                    return filtered;
                },

                get activeCategoryData() {
                    if (this.activeCategory && this.activeCategory !== 'uncategorized') {
                        return this.allCategories.find(c => c.id === this.activeCategory);
                    }
                    return null;
                },

                get filteredServices() {
                    let filtered = this.allServices;
                    
                    if (this.activeCategory === 'uncategorized') {
                        filtered = filtered.filter(s => s.category_id === null);
                    } else if (this.activeCategory !== null) {
                        if (this.activeSubcategory) {
                            // Show services in the selected sub-category AND services directly under the parent
                            filtered = filtered.filter(s => 
                                s.category_id === this.activeSubcategory || 
                                s.category_id === this.activeCategory
                            );
                        } else {
                            const cat = this.activeCategoryData;
                            if (cat) {
                                const validIds = [cat.id];
                                if (cat.children) {
                                    cat.children.forEach(child => validIds.push(child.id));
                                }
                                filtered = filtered.filter(s => validIds.includes(s.category_id));
                            } else {
                                filtered = filtered.filter(s => s.category_id === this.activeCategory);
                            }
                        }
                    }
                    
                    if (this.searchQuery) {
                        const q = this.searchQuery.toLowerCase();
                        filtered = filtered.filter(s => s.name.toLowerCase().includes(q));
                    }
                    
                    return filtered;
                },

                getColorStyle(colorName) {
                    const styles = {
                        'blue': { bg: 'bg-blue-50', border: 'border-blue-200', text: 'text-blue-600' },
                        'green': { bg: 'bg-green-50', border: 'border-green-200', text: 'text-green-600' },
                        'purple': { bg: 'bg-purple-50', border: 'border-purple-200', text: 'text-purple-600' },
                        'orange': { bg: 'bg-orange-50', border: 'border-orange-200', text: 'text-orange-600' },
                        'teal': { bg: 'bg-teal-50', border: 'border-teal-200', text: 'text-teal-600' },
                        'red': { bg: 'bg-red-50', border: 'border-red-200', text: 'text-red-600' }
                    };
                    return styles[colorName] || styles['blue'];
                },

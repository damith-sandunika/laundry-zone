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

                focusFastSearch() {
                    let el = document.getElementById('fast-search-input');
                    if (el) el.focus();
                },

                focusFastQty() {
                    this.$nextTick(() => {
                        let el = document.getElementById('fast-qty-input');
                        if (el) { el.focus(); el.select(); }
                    });
                },

                onFastSearchInput() {
                    // If in drill-down mode, don't re-run text search
                    if (this.fastDrillCategory !== null || this.fastDrillSubcategory !== null) return;
                    let q = this.fastSearchQuery.toLowerCase().trim();
                    if (!q) { this.fastSearchSuggestions = []; return; }
                    let suggestions = [];
                    // Exact item code match first
                    let exactCode = this.allServices.find(s => s.item_code && s.item_code.toLowerCase() === q);
                    if (exactCode) {
                        suggestions.push({ type: 'service', item: exactCode, label: exactCode.name });
                    }
                    // Partial match on name or item_code
                    this.allServices.forEach(s => {
                        if (s === exactCode) return;
                        if (s.name.toLowerCase().includes(q) || (s.item_code && s.item_code.toLowerCase().includes(q))) {
                            suggestions.push({ type: 'service', item: s, label: s.name });
                        }
                    });
                    // Main categories
                    this.allCategories.filter(c => !c.parent_id).forEach(c => {
                        if (c.name.toLowerCase().includes(q)) {
                            suggestions.push({ type: 'category', item: c, parent_id: null, label: '📁 ' + c.name });
                        }
                        // Sub categories
                        if (c.children) {
                            c.children.forEach(sub => {
                                if (sub.name.toLowerCase().includes(q)) {
                                    suggestions.push({ type: 'subcategory', item: sub, parent_id: c.id, label: '📂 ' + sub.name });
                                }
                            });
                        }
                    });
                    this.fastSearchSuggestions = suggestions.slice(0, 12);
                    this.fastSearchHighlightIndex = 0;
                },

                // Drill into a category - show its subcategories or services in dropdown
                fastDrillIntoCategory(cat) {
                    this.fastDrillCategory = cat;
                    this.fastDrillSubcategory = null;
                    let subs = cat.children || [];
                    let directServices = this.allServices.filter(s => s.category_id === cat.id);
                    let suggestions = [];
                    // Back item
                    suggestions.push({ type: 'back', item: null, label: '← Back to Search' });
                    // If has subcategories, show them first
                    subs.forEach(sub => {
                        suggestions.push({ type: 'subcategory', item: sub, parent_id: cat.id, label: '📂 ' + sub.name });
                    });
                    // Direct services in this category
                    directServices.forEach(s => {
                        suggestions.push({ type: 'service', item: s, label: s.name });
                    });
                    this.fastSearchSuggestions = suggestions;
                    this.fastSearchHighlightIndex = 1; // skip 'back'
                },

                // Drill into a subcategory - show its services
                fastDrillIntoSubcategory(sub, parentCat) {
                    this.fastDrillSubcategory = sub;
                    let services = this.allServices.filter(s => s.category_id === sub.id);
                    let suggestions = [];
                    suggestions.push({ type: 'back_to_cat', item: parentCat, label: '← ' + (parentCat ? parentCat.name : 'Back') });
                    services.forEach(s => {
                        suggestions.push({ type: 'service', item: s, label: s.name });
                    });
                    this.fastSearchSuggestions = suggestions;
                    this.fastSearchHighlightIndex = suggestions.length > 1 ? 1 : 0;
                },

                onFastSearchEnter() {
                    if (this.fastSearchSuggestions.length === 0) return;
                    let selected = this.fastSearchSuggestions[this.fastSearchHighlightIndex];
                    if (!selected) return;

                    if (selected.type === 'back') {
                        // Go back to text search
                        this.fastDrillCategory = null;
                        this.fastDrillSubcategory = null;
                        this.fastSearchSuggestions = [];
                        this.fastSearchQuery = '';
                        this.focusFastSearch();
                    } else if (selected.type === 'back_to_cat') {
                        // Go back to parent category drill
                        this.fastDrillSubcategory = null;
                        if (selected.item) {
                            this.fastDrillIntoCategory(selected.item);
                        } else {
                            this.fastDrillCategory = null;
                            this.fastSearchSuggestions = [];
                            this.fastSearchQuery = '';
                        }
                        this.focusFastSearch();
                    } else if (selected.type === 'service') {
                        this.selectedFastService = selected.item;
                        this.fastSearchQuery = selected.item.name;
                        this.fastSearchSuggestions = [];
                        this.fastDrillCategory = null;
                        this.fastDrillSubcategory = null;
                        this.focusFastQty();
                    } else if (selected.type === 'category') {
                        // Drill into category - show subcategories/services in dropdown
                        this.fastDrillIntoCategory(selected.item);
                        this.focusFastSearch();
                    } else if (selected.type === 'subcategory') {
                        // Find parent category for back navigation
                        let parentCat = this.allCategories.find(c => c.id === selected.parent_id) || null;
                        this.fastDrillIntoSubcategory(selected.item, parentCat);
                        this.focusFastSearch();
                    }
                },

                onFastSearchKeyDown(e) {
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (this.fastSearchHighlightIndex < this.fastSearchSuggestions.length - 1) this.fastSearchHighlightIndex++;
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (this.fastSearchHighlightIndex > 0) this.fastSearchHighlightIndex--;
                    } else if (e.key === 'Escape' || (e.key === 'Backspace' && this.fastSearchQuery === '' && (this.fastDrillCategory || this.fastDrillSubcategory))) {
                        // Escape drill-down
                        if (this.fastDrillSubcategory && this.fastDrillCategory) {
                            this.fastDrillIntoCategory(this.fastDrillCategory);
                        } else {
                            this.fastDrillCategory = null;
                            this.fastDrillSubcategory = null;
                            this.fastSearchSuggestions = [];
                            this.fastSearchQuery = '';
                        }
                    }
                },

                onFastQtyEnter() {
                    if (!this.selectedFastService) return;
                    let qty = parseFloat(this.fastSearchQty);
                    if (!qty || qty <= 0) return;
                    this.addToCartWithQty(this.selectedFastService, qty);
                    this.selectedFastService = null;
                    this.fastSearchQuery = '';
                    this.fastSearchQty = 1;
                    this.focusFastSearch();
                },

                addToCartWithQty(service, qty) {
                    let defaultPrice = parseFloat(service.base_price);
                    let existingIdx = this.cart.findIndex(i => i.id === service.id && !i.is_special && parseFloat(i.base_price) === defaultPrice);
                    if (existingIdx !== -1) {
                        let existing = this.cart.splice(existingIdx, 1)[0];
                        existing.qty = Math.round((parseFloat(existing.qty) + qty) * 100) / 100;
                        this.cart.unshift(existing);
                    } else {
                        let defaultTt = this.allTurnaroundTypes.find(tt => tt.is_default);
                        this.cart.unshift({
                            ...service,
                            qty: qty,
                            base_price: defaultPrice,
                            turnaround_type_id: defaultTt ? defaultTt.id : null,
                            custom_turnaround_percentage: null,
                            turnaround_multiplier: defaultTt ? defaultTt.multiplier : 1,
                            is_special: false,
                            selected_extras: [],
                            note: null
                        });
                    }
                },

                openNoteModal(index) {
                    this.currentNoteIndex = index;
                    this.currentNoteText = this.cart[index].note || '';
                    this.showNoteModal = true;
                    this.$nextTick(() => {
                        let el = document.getElementById('note-modal-input');
                        if (el) el.focus();
                    });
                },

                saveNote() {
                    if (this.currentNoteIndex !== null) {
                        this.cart[this.currentNoteIndex].note = this.currentNoteText;
                    }
                    this.showNoteModal = false;
                },

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

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
                    if (this.fastDrillCategory !== null || this.fastDrillSubcategory !== null) return;
                    let q = this.fastSearchQuery.toLowerCase().trim();
                    if (!q) { this.fastSearchSuggestions = []; return; }
                    let suggestions = [];
                    let exactCode = this.allServices.find(s => s.item_code && s.item_code.toLowerCase() === q);
                    if (exactCode) {
                        suggestions.push({ type: 'service', item: exactCode, label: exactCode.name });
                    }
                    this.allServices.forEach(s => {
                        if (s === exactCode) return;
                        if (s.name.toLowerCase().includes(q) || (s.item_code && s.item_code.toLowerCase().includes(q))) {
                            suggestions.push({ type: 'service', item: s, label: s.name });
                        }
                    });
                    this.allCategories.filter(c => !c.parent_id).forEach(c => {
                        if (c.name.toLowerCase().includes(q)) {
                            suggestions.push({ type: 'category', item: c, parent_id: null, label: '📁 ' + c.name });
                        }
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

                fastDrillIntoCategory(cat) {
                    this.fastDrillCategory = cat;
                    this.fastDrillSubcategory = null;
                    let subs = cat.children || [];
                    let directServices = this.allServices.filter(s => s.category_id === cat.id);
                    let suggestions = [];
                    suggestions.push({ type: 'back', item: null, label: '← Back to Search' });
                    subs.forEach(sub => {
                        suggestions.push({ type: 'subcategory', item: sub, parent_id: cat.id, label: '📂 ' + sub.name });
                    });
                    directServices.forEach(s => {
                        suggestions.push({ type: 'service', item: s, label: s.name });
                    });
                    this.fastSearchSuggestions = suggestions;
                    this.fastSearchHighlightIndex = 1;
                },

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
                        this.fastDrillCategory = null;
                        this.fastDrillSubcategory = null;
                        this.fastSearchSuggestions = [];
                        this.fastSearchQuery = '';
                        this.focusFastSearch();
                    } else if (selected.type === 'back_to_cat') {
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
                        this.fastDrillIntoCategory(selected.item);
                        this.focusFastSearch();
                    } else if (selected.type === 'subcategory') {
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


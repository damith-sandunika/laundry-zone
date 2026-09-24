    <script>
        let editCategorySelect = null;
        let editCatParentSelect = null;

        function openEditServiceModal(id, name, price, priceType, isActive, color, categoryId, isVisible, itemCode) {
            document.getElementById('edit-service-form').action = `/settings/services/${id}`;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-price').value = price;
            document.getElementById('edit-price-type').value = priceType;
            document.getElementById('edit-active').value = isActive;
            
            if (editCategorySelect) {
                editCategorySelect.setValue(categoryId || '');
            } else {
                document.getElementById('edit-category-id').value = categoryId || '';
            }
            
            document.getElementById('edit-website-visible').checked = isVisible == 1;
            document.getElementById('edit-item-code').value = itemCode || '';
            document.getElementById('edit-service-modal').style.display = 'flex';
        }

        function openEditCategoryModal(id, name, isActive, parentId, isVisible) {
            document.getElementById('edit-category-form').action = `/settings/service-categories/${id}`;
            document.getElementById('edit-cat-name').value = name;
            document.getElementById('edit-cat-active').value = isActive;
            
            if (editCatParentSelect) {
                editCatParentSelect.setValue(parentId || '');
            } else {
                document.getElementById('edit-cat-parent-id').value = parentId || '';
            }
            
            document.getElementById('edit-cat-website-visible').checked = isVisible == 1;
            document.getElementById('edit-category-modal').style.display = 'flex';
        }

        // Close modals on outside click
        document.getElementById('add-service-modal').addEventListener('click', function(e) {
            if (e.target === this) this.style.display = 'none';
        });
        document.getElementById('edit-service-modal').addEventListener('click', function(e) {
            if (e.target === this) this.style.display = 'none';
        });
        document.getElementById('add-category-modal').addEventListener('click', function(e) {
            if (e.target === this) this.style.display = 'none';
        });
        document.getElementById('edit-category-modal').addEventListener('click', function(e) {
            if (e.target === this) this.style.display = 'none';
        });
    </script>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <style>
        .ts-control { border-radius: 10px; padding: 10px 14px; border: 1.5px solid #e2e8f0; font-size: 13px; font-weight: 600; box-shadow: none !important; outline: none !important; }
        .ts-control.focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1) !important; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Tom Select for Searchable Dropdowns
            const tsConfig = { create: false, placeholder: 'Select Category...' };
            if(document.getElementById('add-category-id')) new TomSelect('#add-category-id', tsConfig);
            if(document.getElementById('add-cat-parent-id')) new TomSelect('#add-cat-parent-id', { create: false, placeholder: 'None (Main Category)' });
            
            if(document.getElementById('edit-category-id')) {
                editCategorySelect = new TomSelect('#edit-category-id', tsConfig);
            }
            if(document.getElementById('edit-cat-parent-id')) {
                editCatParentSelect = new TomSelect('#edit-cat-parent-id', { create: false, placeholder: 'None (Main Category)' });
            }

            // Sort Main Categories
            const categoriesSortable = document.getElementById('categories-sortable');
            if (categoriesSortable) {
                new Sortable(categoriesSortable, {
                    handle: '.category-handle',
                    animation: 150,
                    ghostClass: 'bg-slate-100',
                    onEnd: function (evt) {
                        const order = Array.from(categoriesSortable.children).map(item => item.dataset.id);
                        fetch('{{ route("settings.reorderCategories") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ ordered_ids: order })
                        });
                    }
                });
            }

            // Sort Services within Categories
            document.querySelectorAll('.services-sortable').forEach(function(el) {
                new Sortable(el, {
                    handle: '.service-handle',
                    animation: 150,
                    ghostClass: 'bg-slate-100',
                    group: 'services', // allows dragging services between categories
                    onEnd: function (evt) {
                        const itemEl = evt.item;
                        const newCategoryEl = evt.to;
                        const newCategoryId = newCategoryEl.dataset.categoryId || null;
                        
                        const order = Array.from(newCategoryEl.children)
                            .filter(item => item.classList.contains('service-item'))
                            .map(item => item.dataset.id);
                        const serviceId = itemEl.dataset.id;

                        fetch('{{ route("settings.reorderServices") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ 
                                ordered_ids: order,
                                category_id: newCategoryId
                            })
                        });
                    }
                });
            });

            // Sort Subcategories within each Main Category
            document.querySelectorAll('.subcategory-sortable').forEach(function(el) {
                new Sortable(el, {
                    handle: '.category-handle',
                    animation: 150,
                    ghostClass: 'bg-blue-50',
                    onEnd: function (evt) {
                        const order = Array.from(el.children).map(item => item.dataset.id).filter(Boolean);
                        fetch('{{ route("settings.reorderCategories") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ ordered_ids: order })
                        });
                    }
                });
            });
        });
        function showServiceMoveToast() {
            let toast = document.getElementById('service-move-toast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'service-move-toast';
                toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;background:#1d4ed8;color:white;padding:12px 20px;border-radius:12px;font-size:13px;font-weight:700;box-shadow:0 8px 24px rgba(29,78,216,0.4);display:flex;align-items:center;gap:8px;transform:translateY(80px);transition:transform 0.3s ease;';
                toast.innerHTML = '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Service moved & saved';
                document.body.appendChild(toast);
            }
            setTimeout(function() { toast.style.transform = 'translateY(0)'; }, 10);
            clearTimeout(toast._timeout);
            toast._timeout = setTimeout(function() { toast.style.transform = 'translateY(80px)'; }, 2500);
        }
    </script>

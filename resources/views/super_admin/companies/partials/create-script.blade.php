<script>
function provisionWizard() {
    return {
        subdomain: '{{ old('subdomain', '') }}',
        subdomainManuallyEdited: false,
        basePrice: {{ old('plan_price', $defaultBasePrice) }},
        showModal: false,
        isCompleted: false,
        hasError: false,
        errorMessage: '',
        progressPercent: 10,
        redirectUrl: '{{ route('super_admin.companies.index') }}',
        tenantResult: null,

        steps: [
            { id: 1, title: 'Step 1: Landlord Registry & Validation', desc: 'Validating tenant parameters, reserving unique subdomain, and creating Landlord record.', status: 'pending' },
            { id: 2, title: 'Step 2: Dedicated MySQL Database Creation', desc: 'Creating isolated physical database schema with utf8mb4 collation.', status: 'pending' },
            { id: 3, title: 'Step 3: Database Tables Migration & Indexing', desc: 'Migrating all 28+ operational tables and high-throughput composite indexes.', status: 'pending' },
            { id: 4, title: 'Step 4: Company Owner & Master Account Setup', desc: 'Registering Company Owner user, hashing password and binding global permissions.', status: 'pending' },
            { id: 5, title: 'Step 5: Main Branch & System Presets Initialization', desc: 'Setting up Main Branch, currency symbols, and activating tenant.', status: 'pending' }
        ],

        formatLkr(val) {
            return new Intl.NumberFormat('en-LK', { style: 'currency', currency: 'LKR', maximumFractionDigits: 0 }).format(val);
        },

        async submitProvisioning(e) {
            const form = e.target;
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            this.showModal = true;
            this.isCompleted = false;
            this.hasError = false;
            this.errorMessage = '';
            this.progressPercent = 15;
            this.tenantResult = null;
            this.steps.forEach(s => s.status = 'pending');
            this.steps[0].status = 'active';

            const formData = new FormData(form);

            const timer1 = setTimeout(() => {
                if (!this.hasError && !this.isCompleted) {
                    this.steps[0].status = 'completed';
                    this.steps[1].status = 'active';
                    this.progressPercent = 35;
                }
            }, 600);

            const timer2 = setTimeout(() => {
                if (!this.hasError && !this.isCompleted) {
                    this.steps[1].status = 'completed';
                    this.steps[2].status = 'active';
                    this.progressPercent = 65;
                }
            }, 1400);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });

                clearTimeout(timer1);
                clearTimeout(timer2);

                const data = await response.json();

                if (!response.ok || !data.success) {
                    this.hasError = true;
                    this.errorMessage = data.message || 'Provisioning failed. Please check inputs.';
                    const currentActive = this.steps.find(s => s.status === 'active') || this.steps[0];
                    currentActive.status = 'failed';
                    return;
                }

                this.steps.forEach(s => s.status = 'completed');
                this.progressPercent = 100;
                this.tenantResult = data.tenant;
                if (data.redirect_url) this.redirectUrl = data.redirect_url;
                setTimeout(() => { this.isCompleted = true; }, 400);

            } catch (err) {
                clearTimeout(timer1);
                clearTimeout(timer2);
                this.hasError = true;
                this.errorMessage = err.message || 'Network error occurred.';
                const currentActive = this.steps.find(s => s.status === 'active') || this.steps[0];
                currentActive.status = 'failed';
            }
        }
    }
}
</script>

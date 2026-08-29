import re

with open('resources/views/customer/booking/index.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update tabs in progress bar
content = content.replace(
    "['Services', 'Account', 'Pickup', 'Delivery & Pay'][i-1]",
    "['Account', 'Pickup', 'Services', 'Delivery & Pay'][i-1]"
)

# Make tabs clickable
content = content.replace(
    '<div class="w-12 h-12 rounded-full flex items-center justify-center font-black text-base border-4 transition-all duration-300"',
    '<div @click="goToStep(i)" class="w-12 h-12 rounded-full flex items-center justify-center font-black text-base border-4 transition-all duration-300 cursor-pointer"'
)

# 2. Swap the step values in the div attributes.

# Step 1 -> 3
content = content.replace('<!-- STEP 1: Services Selection -->', '<!-- STEP 3: Services Selection -->')
content = content.replace('x-show="step === 1" x-transition.opacity.duration.300ms', 'x-show="step === 3" x-transition.opacity.duration.300ms')
# Back button for new Step 3:
content = content.replace('@click="goToStep(2)" :disabled="totalPrice < minLimit || cart.length === 0"', '@click="goToStep(4)" :disabled="totalPrice < minLimit || cart.length === 0"')
content = content.replace(
    '<button type="button" @click="goToStep(4)" :disabled="totalPrice < minLimit || cart.length === 0" class="bg-slate-900',
    '<button type="button" @click="goToStep(2)" class="text-slate-500 hover:text-slate-800 font-bold flex items-center gap-2 px-4 py-2"><i class="fa-solid fa-arrow-left"></i> Back</button>\\n                    <button type="button" @click="goToStep(4)" :disabled="totalPrice < minLimit || cart.length === 0" class="bg-slate-900'
)

# Step 2 -> 1
content = content.replace('<!-- STEP 2: Authentication -->', '<!-- STEP 1: Authentication -->')
content = content.replace('x-show="step === 2" style="display:none;" x-transition.opacity.duration.300ms', 'x-show="step === 1" x-transition.opacity.duration.300ms')
# Remove back button from new Step 1
content = re.sub(r'<button type="button" @click="step = 1" class="text-slate-500 hover:text-slate-800 font-bold flex items-center gap-2 px-4 py-2">\s*<i class="fa-solid fa-arrow-left"></i> Back\s*</button>', '', content)
content = content.replace('@click="goToStep(3)" class="bg-slate-900', '@click="goToStep(2)" class="bg-slate-900')


# Step 3 -> 2
content = content.replace('<!-- STEP 3: Pickup Details -->', '<!-- STEP 2: Pickup Details -->')
content = content.replace('x-show="step === 3" style="display:none;" x-transition.opacity.duration.300ms', 'x-show="step === 2" style="display:none;" x-transition.opacity.duration.300ms')
# Back button for new step 2
content = content.replace('@click="step = 2" class="text-slate-500', '@click="goToStep(1)" class="text-slate-500')
# Next button for new step 2 -> 3
content = content.replace('@click="goToStep(4)" :disabled="isOutOfRange"', '@click="goToStep(3)" :disabled="isOutOfRange"')

# Step 4 Back button -> 3
content = content.replace('@click="step = 3" class="text-slate-500', '@click="goToStep(3)" class="text-slate-500')


# 3. Update bookingWizard JS state and methods
content = content.replace('step: 1,', 'step: 1, highestStepReached: 1,')

# Map initialization watches
content = content.replace('if (value === 3) {', 'if (value === 2) {')

# goToStep logic
old_gotostep = """                    goToStep(n) {
                        // Validation logic before moving to next step
                        if (n === 2) {
                            if (this.cart.length === 0) {
                                alert("Please select at least one service before proceeding.");
                                return;
                            }
                            if (this.totalPrice < this.minLimit) {
                                alert("Minimum delivery limit is LKR " + this.minLimit.toFixed(2) + ". Please add more items to proceed.");
                                return;
                            }
                        }
                        
                        if (n === 4) {
                            const date = document.getElementById('pickup_date_input').value;
                            const time = document.getElementById('pickup_time_input').value;
                            const addr = document.getElementById('pickup_address').value;
                            
                            if (!date || !time || !addr) {
                                alert("Please fill in all pickup details (Date, Time, Address).");
                                return;
                            }
                        }

                        this.step = n;
                        
                        // If going to step 2 but already authenticated, skip to step 3
                        if (n === 2 && this.isAuthenticated) {
                            this.step = 3;
                        }
                    },"""

new_gotostep = """                    async goToStep(n) {
                        if (n > this.highestStepReached + 1) return; // Must go in order initially

                        // Validation logic before moving to next step
                        if (n === 3) { // Going to Services
                            const date = document.getElementById('pickup_date_input').value;
                            const time = document.getElementById('pickup_time_input').value;
                            const addr = document.getElementById('pickup_address').value;
                            
                            if (!date || !time || !addr) {
                                alert("Please fill in all pickup details (Date, Time, Address).");
                                return;
                            }
                            // Calculate closest branch and fetch services
                            if (this.nearestWorkshop && this.nearestWorkshop.id) {
                                try {
                                    const res = await axios.get('/customer/branch-services/' + this.nearestWorkshop.id);
                                    if (res.data && res.data.categories) {
                                        this.categories = res.data.categories;
                                    }
                                } catch (e) {
                                    console.error("Error fetching branch services", e);
                                }
                            }
                        }

                        if (n === 4) { // Going to Delivery
                            if (this.cart.length === 0) {
                                alert("Please select at least one service before proceeding.");
                                return;
                            }
                            if (this.totalPrice < this.minLimit) {
                                alert("Minimum delivery limit is LKR " + this.minLimit.toFixed(2) + ". Please add more items to proceed.");
                                return;
                            }
                        }

                        this.step = n;
                        if (n > this.highestStepReached) {
                            this.highestStepReached = n;
                        }
                        
                        // If going to step 1 but already authenticated, skip to step 2
                        if (n === 1 && this.isAuthenticated) {
                            this.step = 2;
                            this.highestStepReached = Math.max(this.highestStepReached, 2);
                        }
                    },"""

content = content.replace(old_gotostep, new_gotostep)

content = content.replace('this.goToStep(3);', 'this.goToStep(2);')

with open('resources/views/customer/booking/index.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)

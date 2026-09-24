            <!-- STEP: Authentication -->
            <div x-show="currentView === 'account'" style="display:none;" x-transition.opacity.duration.300ms>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8">
                    
                    <!-- If already logged in, show account info instead of form -->
                    <template x-if="isAuthenticated">
                        <div class="text-center py-8">
                            <div class="w-20 h-20 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-4xl mx-auto mb-4 border-4 border-blue-100">
                                <i class="fa-solid fa-user-check"></i>
                            </div>
                            <div class="mb-8">
                                <h3 class="text-xl md:text-2xl font-bold text-slate-800 mb-2">Welcome back, <span x-text="authCustomer.name"></span>!</h3>
                                <p class="text-sm text-slate-500 font-medium">You are currently logged in with <span x-text="authCustomer.phone"></span>.</p>
                            </div>
                        </div>
                    </template>

                    <!-- If not logged in, show auth tabs -->
                    <template x-if="!isAuthenticated">
                        <div>
                            <div class="flex justify-center mb-8">
                                <div class="bg-slate-100 p-1 rounded-xl flex gap-1">
                                    <button type="button" @click="authTab = 'login'" :class="authTab === 'login' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500 hover:text-slate-700'" class="px-6 py-2 rounded-lg font-bold text-sm transition-all">Login</button>
                                    <button type="button" @click="authTab = 'register'" :class="authTab === 'register' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500 hover:text-slate-700'" class="px-6 py-2 rounded-lg font-bold text-sm transition-all">Register</button>
                                </div>
                            </div>
                            
                            <div x-show="authError" class="p-4 bg-red-50 border border-red-200 text-red-600 text-sm font-bold rounded-xl mb-6 text-center" x-text="authError"></div>

                            <!-- Login Form -->
                            <div x-show="authTab === 'login'">
                                <div class="space-y-4 max-w-md mx-auto">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Phone Number</label>
                                        <input type="text" x-model="loginData.phone" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500" placeholder="07XXXXXXXX">
                                    </div>
                                    <div x-data="{ showPass: false }">
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                                        <div class="relative">
                                            <input :type="showPass ? 'text' : 'password'" x-model="loginData.password" class="w-full px-4 py-3 pr-10 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500" placeholder="Password">
                                            <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none">
                                                <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <button type="button" @click="submitLogin()" :disabled="authLoading" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3.5 rounded-xl font-bold mt-2 transition-all flex justify-center items-center gap-2">
                                        <span x-show="!authLoading">Login to Continue</span>
                                        <i x-show="authLoading" class="fa-solid fa-spinner fa-spin"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Register Form -->
                            <div x-show="authTab === 'register'">
                                <div class="space-y-4 max-w-md mx-auto">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Full Name</label>
                                        <input type="text" x-model="registerData.name" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500" placeholder="John Doe">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Phone Number</label>
                                        <input type="text" x-model="registerData.phone" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500" placeholder="07XXXXXXXX">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Email (Optional)</label>
                                        <input type="email" x-model="registerData.email" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500" placeholder="john@example.com">
                                    </div>
                                    <div x-data="{ showPass: false }">
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                                        <div class="relative">
                                            <input :type="showPass ? 'text' : 'password'" x-model="registerData.password" class="w-full px-4 py-3 pr-10 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500" placeholder="Password">
                                            <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none">
                                                <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <button type="button" @click="submitRegister()" :disabled="authLoading" class="w-full bg-slate-900 hover:bg-slate-800 text-white py-3.5 rounded-xl font-bold mt-2 transition-all flex justify-center items-center gap-2">
                                        <span x-show="!authLoading">Create Account & Continue</span>
                                        <i x-show="authLoading" class="fa-solid fa-spinner fa-spin"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                
                <div class="flex justify-between items-center mt-6">
                    <button type="button" x-show="step > 1" @click="goToStep(step - 1)" class="text-slate-500 hover:text-slate-800 font-bold flex items-center gap-2 px-4 py-2 text-sm sm:text-base">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </button>
                    <!-- Only show next if authenticated -->
                    <button type="button" x-show="isAuthenticated" @click="goToStep(step + 1)" class="bg-slate-900 hover:bg-slate-800 text-white px-8 py-3.5 rounded-xl font-bold transition-all shadow-md flex items-center gap-2 mt-4 ml-auto">
                        Next Step <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
            <!-- NOTIFICATIONS TAB -->
            @if(auth()->user()->role === 'super_admin')
                <div style="display: block;">
                    <form action="{{ route('settings.notifications.update') }}" method="POST">
                        @csrf
                        
                        @php
                            $isSmsEnabled = \App\Models\Setting::get('is_sms_enabled', 'false') === 'true';
                            $isEmailEnabled = \App\Models\Setting::get('is_email_enabled', 'false') === 'true';
                        @endphp

                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                                <h3 class="text-lg font-black text-slate-800"><i class="fa-solid fa-bell text-yellow-500 mr-2"></i>Master Notification Settings</h3>
                                <p class="text-sm text-slate-500 font-medium">Globally enable or disable notifications across the system.</p>
                            </div>

                            <div class="divide-y divide-slate-100">
                                <!-- SMS Toggle -->
                                <div class="p-6 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 bg-blue-50 text-blue-600">
                                            <i class="fa-solid fa-comment-sms text-xl"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-black text-slate-800">SMS Notifications</h4>
                                            <p class="text-sm font-medium text-slate-500">Send text messages to customers (Requires SMS gateway configured).</p>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="is_sms_enabled" value="true" class="sr-only peer" {{ $isSmsEnabled ? 'checked' : '' }}>
                                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Email Toggle -->
                                <div class="p-6 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 bg-orange-50 text-orange-600">
                                            <i class="fa-solid fa-envelope text-xl"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-black text-slate-800">Email Notifications</h4>
                                            <p class="text-sm font-medium text-slate-500">Send email updates to customers.</p>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="is_email_enabled" value="true" class="sr-only peer" {{ $isEmailEnabled ? 'checked' : '' }}>
                                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end mt-4 mb-8">
                            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-bold transition-colors shadow-sm flex items-center gap-2">
                                <i class="fa-solid fa-floppy-disk"></i> Save Notification Settings
                            </button>
                        </div>
                    </form>
                </div>
            @endif

    <script>
        function attendanceGrid() {
            const year = {{ $payroll->year }};
            const month = {{ $payroll->month }};
            const standardWorkingHours = {{ $staff->profile->standard_working_hours ?? 8 }};
            const branchPlan = @json(optional($branchPlan)->plan_data ?? []);
            const defaultInTime = "{{ optional($branchPlan)->default_in_time ? \Carbon\Carbon::parse($branchPlan->default_in_time)->format('H:i') : '08:30' }}";
            const defaultOutTime = "{{ optional($branchPlan)->default_out_time ? \Carbon\Carbon::parse($branchPlan->default_out_time)->format('H:i') : '16:30' }}";
            const currentAttendance = @json($attendancesThisMonth->keyBy('date'));
            
            const daysInMonth = new Date(year, month, 0).getDate();
            const days = {};
            
            for (let i = 1; i <= daysInMonth; i++) {
                const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                
                let rule = branchPlan[dateStr] || { type: 'normal' };
                let att = currentAttendance[dateStr] || {};
                
                // Format times for input
                let clockIn = att.clock_in ? att.clock_in.substring(0, 5) : '';
                let clockOut = att.clock_out ? att.clock_out.substring(0, 5) : '';

                days[dateStr] = {
                    rule: rule,
                    status: att.status || (rule.type === 'holiday' ? 'holiday' : ''),
                    clock_in: clockIn,
                    clock_out: clockOut,
                    normal_minutes: att.normal_minutes || 0,
                    ot_minutes: att.ot_minutes || 0,
                };
            }

            return {
                days: days,
                submitData: '',

                formatDate(dateStr) {
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                },
                formatDay(dateStr) {
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('en-US', { weekday: 'short' });
                },
                formatHours(mins) {
                    if(!mins || mins <= 0) return '-';
                    const h = Math.floor(mins / 60);
                    const m = mins % 60;
                    if (h > 0 && m > 0) return `${h}h ${m}m`;
                    if (h > 0) return `${h}h`;
                    return `${m}m`;
                },
                recalculate(dateStr) {
                    const day = this.days[dateStr];
                    
                    if (day.status !== 'present') {
                        day.normal_minutes = 0;
                        day.ot_minutes = 0;
                        return;
                    }

                    // Auto-fill default times if empty
                    if (day.status === 'present') {
                        if (!day.clock_in) day.clock_in = defaultInTime;
                        if (!day.clock_out) day.clock_out = defaultOutTime;
                    }

                    if (!day.clock_in || !day.clock_out) {
                        day.normal_minutes = 0;
                        day.ot_minutes = 0;
                        return;
                    }

                    const inParts = day.clock_in.split(':');
                    const outParts = day.clock_out.split(':');
                    let inMins = parseInt(inParts[0]) * 60 + parseInt(inParts[1]);
                    let outMins = parseInt(outParts[0]) * 60 + parseInt(outParts[1]);
                    
                    // Handle crossing midnight
                    if (outMins < inMins) outMins += 24 * 60;
                    
                    let totalWorkedMins = outMins - inMins;
                    let normalMins = 0;
                    let otMins = 0;
                    
                    if (day.rule.type === 'normal') {
                        let stdMins = standardWorkingHours * 60;
                        if (totalWorkedMins > stdMins) {
                            normalMins = stdMins;
                            otMins = totalWorkedMins - stdMins;
                        } else {
                            normalMins = totalWorkedMins;
                            otMins = 0;
                        }
                    } else if (day.rule.type === 'holiday') {
                        normalMins = 0;
                        otMins = totalWorkedMins; // Fully OT
                    } else if (day.rule.type === 'special') {
                        if (day.rule.special_type === 'fully_ot') {
                            normalMins = 0;
                            otMins = totalWorkedMins;
                        } else if (day.rule.special_type === 'custom') {
                            let stdMins = (day.rule.normal_hours || 0) * 60;
                            if (totalWorkedMins > stdMins) {
                                normalMins = stdMins;
                                otMins = totalWorkedMins - stdMins;
                            } else {
                                normalMins = totalWorkedMins;
                                otMins = 0;
                            }
                        }
                    }
                    
                    day.normal_minutes = normalMins;
                    day.ot_minutes = otMins;
                },
                prepareSubmit() {
                    this.submitData = JSON.stringify(this.days);
                }
            }
        }
    </script>

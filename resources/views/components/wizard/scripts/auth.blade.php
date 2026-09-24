                    async submitLogin() {
                        this.authLoading = true;
                        this.authError = null;
                        try {
                            const response = await axios.post('{{ route("customer.ajax.login") }}', this.loginData);
                            if (response.data.success) {
                                this.isAuthenticated = true;
                                this.authCustomer = response.data.customer;
                                if (response.data.csrf_token) {
                                    document.querySelectorAll('input[name="_token"]').forEach(el => el.value = response.data.csrf_token);
                                    document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', response.data.csrf_token);
                                    axios.defaults.headers.common['X-CSRF-TOKEN'] = response.data.csrf_token;
                                }
                                this.goToStep(this.viewOrder.indexOf('pickup') + 1);
                            }
                        } catch (error) {
                            this.authError = error.response?.data?.message || 'Login failed. Please check your credentials.';
                        } finally {
                            this.authLoading = false;
                        }
                    },

                    async submitRegister() {
                        this.authLoading = true;
                        this.authError = null;
                        try {
                            const response = await axios.post('{{ route("customer.ajax.register") }}', this.registerData);
                            if (response.data.success) {
                                this.isAuthenticated = true;
                                this.authCustomer = response.data.customer;
                                if (response.data.csrf_token) {
                                    document.querySelectorAll('input[name="_token"]').forEach(el => el.value = response.data.csrf_token);
                                    document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', response.data.csrf_token);
                                    axios.defaults.headers.common['X-CSRF-TOKEN'] = response.data.csrf_token;
                                }
                                this.goToStep(this.viewOrder.indexOf('pickup') + 1);
                            }
                        } catch (error) {
                            this.authError = error.response?.data?.message || 'Registration failed. Please check the form.';
                        } finally {
                            this.authLoading = false;
                        }
                    },


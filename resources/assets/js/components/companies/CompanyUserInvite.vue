
<template>
    <div>
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group mr-2">
                    <router-link :to="{name: 'companyUsers', params: {id: user.company_id}}" class="btn back_btn"> {{ trans('custom.back') }}</router-link>
                </div>
            </div>
        </div>

        <div class="card my-3">
            <div class="card-body">
                <div class="container">
                    <form v-on:submit="saveForm()">
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.email') }}</label>
                                <input name="email" type="text" v-model="user.email" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.role') }}</label>
                                <select v-model="user.role" class="form-control" required>
                                    <option value="">------</option>
                                    <option v-for="role, name in roles" :value="name">{{ role.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group text-right">
                                <button class="btn btn-success">{{ trans('custom.create') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                user: {
                    email: null,
                    company_id: this.$route.params.id,
                    role: null
                },
                roles: []
            }
        },
        mounted(){
            var app = this;
            axios.get('/api/v1/user/roles?limit=invite').then((resp) => {
                this.roles = resp.data;
            }).catch((resp) => {
                if (resp.response.status === 401) {
                    window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                } else {
                    app.$dialog.alert("Could not load roles")
                }
            });
        },
        methods: {
            saveForm() {
                event.preventDefault();
                var app = this;
                axios.post('/api/v1/companies/' + this.user.company_id + '/invite', this.user)
                    .then(() => {
                        this.$router.push({name: 'companyUsers', params: {id: this.user.company_id}});
                    })
                    .catch(error => {
                        if (error.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                        } else {
                            app.$dialog.alert(window.parseError(error.response.data.errors, 'Could not invite user:'), {'html': true});
                        }
                    });
            }
        }
    }
</script>

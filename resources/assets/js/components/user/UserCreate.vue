
<template>
    <div>
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group mr-2">
                    <router-link to="/users" class="btn back_btn"> {{ trans('custom.back') }}</router-link>
                </div>
            </div>
        </div>

        <div class="card my-3">
            <div class="card-body">
                <div class="container">
                    <form v-on:submit="saveForm()">
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.company') }}</label>
                                <v-select
                                        v-model="user.company_id"
                                        :options="companies"
                                        label="title"
                                        index="id"
                                        :clearable="false"
                                ></v-select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.name') }}</label>
                                <input name="name" type="text" v-model="user.name" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.email') }}</label>
                                <input name="email" type="text" v-model="user.email" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.phone') }}</label>
                                <input name="phone" type="text" v-model="user.phone" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.password') }}</label>
                                <input name="password" type="password" v-model="user.password" class="form-control" min="5" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.role') }}</label>
                                <select v-model="user.role" class="form-control">
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
        data: function () {
            return {
                user: {
                    name: null,
                    email: null,
                    role: '',
                    password: null,
                    company_id: null,
                    phone: null,
                },
                roles: [],
                companies: []
            }
        },
        mounted() {
            let app = this,
                company_id = app.$route.params.company_id;

                if (company_id) {
                    app.user.company_id = company_id;
                }

            axios.get('/api/v1/user/roles?limit=edit')
                .then(function (resp) {
                    app.roles = resp.data;
                })
                .catch(function () {
                    alert("Could not load roles")
                });

            axios.get('/api/v1/companies')
                .then(function (resp) {
                    app.companies = resp.data;
                })
                .catch(function () {
                    alert("Could not load companies")
                });
        },
        methods: {
            saveForm() {
                event.preventDefault();
                var app = this;
                var newUser = app.user;

                axios.post('/api/v1/user', newUser)
                    .then(function (resp) {
                        app.$router.push({path: '/users'});
                    })
                    .catch(error => {
                        alert("Could not create user:\n\n" + JSON.stringify(error.response.data.errors));
                    });
            }
        }
    }
</script>


<template>
    <div>
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group mr-2">
                    <router-link :to="{name: 'projectItems', params: {id: projectId, company_id: companyId}}" class="btn back_btn"> {{ trans('custom.back') }}</router-link>
                </div>
            </div>
        </div>

        <div class="card my-3">
            <div class="card-body">
                <div class="container">
                    <form v-on:submit="saveForm()">
                        <input type="hidden" name="project_id" v-model="project.id">
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.email') }}</label>
                                <input name="email" type="text" v-model="contact.email" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.full_name') }}</label>
                                <input name="fullname" type="text" v-model="contact.fullname" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.position') }}</label>
                                <input name="position" type="text" v-model="contact.position" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.phone') }}</label>
                                <input name="phone" type="text" v-model="contact.phone" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group text-right">
                                <button class="btn btn-success">{{ trans('custom.save') }}</button>
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
        mounted() {
            this.companyId = this.$route.params.company_id;
            this.projectId = this.$route.params.project_id;
            this.contactId = this.$route.params.id;

            let app = this;

            axios.get('/api/v1/projects/' + app.projectId)
                .then(function (resp) {
                    app.project = resp.data;
                })
                .catch(function () {
                    app.$dialog.alert("Could not load your project")
                });

            axios.get('/api/v1/projects/' + app.projectId + '/contact/' + app.contactId)
                .then(function (resp) {
                    app.contact = resp.data;
                })
                .catch(function () {
                    app.$dialog.alert("Could not load your contact")
                });
        },
        data: function () {
            return {
                projectId: null,
                companyId: null,
                project: {
                    title: '',
                    description: '',
                    status: 1,
                    image: ''
                },
                contactId: null,
                contact: {
                    fullname: '',
                    email: '',
                    phone: '',
                    project_id: null,
                    position: ''
                }
            }
        },
        methods: {
            saveForm() {
                event.preventDefault();
                let app = this,
                    formData = app.contact;

                axios.put('/api/v1/projects/' + app.projectId + '/contact/' + app.contactId, formData)
                        .then(function (resp) {
                            app.$router.push({path: '/' + app.companyId + '/projects/' + app.projectId + '/items'});
                        }).catch(error => {
                            app.$dialog.alert("Could not save contact");
                        });
            },
        }
    }
</script>


<template>
    <div class="company_profile">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group mr-2">
                    <router-link :to="{name: 'companyIndex'}" class="btn back_btn"> {{ trans('custom.back') }}</router-link>
                </div>
            </div>
        </div>

        <div class="card my-3 company_card">
            <div class="card-body">
                <div class="container pr-0">
                    <div class="row">
                            <div class="col-md-3 col-sm-12 text-md-left text-center card-img-wrapper">
                                <img :src="company.logo" class="img-fluid img-thumbnail" />
                            </div>
                            <div class="col-sm-12 col-md-6 text-md-left text-center pl-4">
                               <span class="h3 text-dark"> {{ company.title }}</span>
                                <div class="row mt-3">
                                    <div class="col-md-5 col-md-5 mb-3"><div class="industry_icon cont-icon"></div><span class="text-dark"><b>{{trans('custom.industry')}}:</b> {{ company.industry !== null ? company.industry.title:'' }}</span></div>
                                    <div class="col-md-5 col-md-5 mb-3"><div class="employee_icon cont-icon"></div><span class="text-dark"> <b>{{trans('custom.employees')}}: </b> {{company.employees_number_value !== null ? company.employees_number_value:''}}</span></div>

                                </div>
                                <div class="row">

                                    <div class="col-md-5 col-md-5"><div class="projects_icon cont-icon"></div><span class="text-dark"><b>{{trans('custom.projects')}}: </b>{{ company.projects.length }}</span></div>
                                    <div class="col-md-5 col-md-5">
                                        <template v-if="company.company_address != null">
                                            <div class="address_icon cont-icon"></div><span class="text-dark"><b>{{ company.company_address }}</b></span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        <template v-if="allowedDisplay('all') || allowedDisplay('company_edit', 'company', company.id)">
                            <div class="col-sm-12 col-md-3 action_buttons pr-0">
                                <div class="company_actions_holder">
                                    <router-link :to="{name: 'companyUsers', params: {id: company.id}}" class="btn btn-xs btn-success">
                                        <i class="plus_icon"></i> {{ trans('custom.add_user') }}
                                    </router-link>
                                    <div class="dropdown">
                                        <button class="btn dropdown-toggle" type="button" data-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="false">
                                            {{ trans('custom.actions') }}
                                        </button>
                                        <div class="dropdown-menu">


<!--                                            <router-link :to="{name: 'companyEdit', params: {id: company.id}}"-->
<!--                                                         class=""><i class="edit_icon"></i>  {{ trans('custom.edit') }}-->
<!--                                            </router-link>-->

                                            <a v-if="company.can_delete"
                                               v-on:click.prevent="deleteEntry(company.id)" class="" href="#"><i class="delete_icon"></i>
                                                {{ trans('custom.delete') }}
                                            </a>

                                        </div>
                                    </div>
                                </div>
                                <!--
                                                                <router-link :to="{name: 'companyUsers', params: {id: company.id}}" class="btn btn-xs btn-default btn-success">
                                                                    <i class="fa fa-user-plus"></i>
                                                                </router-link>



                                                                <span class="btn btn-danger" v-if="company.can_delete" v-on:click="deleteEntry(company.id)">
                                                                        <i class="fa fa-trash-o" style="font-size:18px"></i>
                                                                    </span>-->
                            </div>
                        </template>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-md-12 mt-3">
                            <button v-for="specialization, index in company.specializations" type="button" class="btn mr-1 btn-outline-dark disabled spec-item">{{specialization.title}}</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-md-12 mt-3">
                         <p>{{company.description}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card my-3 info-block">
            <div class="card-body">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-12 col-md-12">
                            <div class="legal_info_icon cont-icon"></div>
                            <span class="h3 text-dark"> {{ trans('custom.company_legal_information') }}</span>
                        </div>
                    </div>
                    <div class='col-sm-12 col-md-12 mt-3'>
                        <div class="pt-1 pb-1"><span class="text-dark col-md-3 col-md-3 "> {{trans('custom.company_address')}}:</span><b><span>{{ company.company_address }}</span></b></div>
                        <div class="pt-1 pb-1"><span class="text-dark col-md-3 col-md-3"> {{trans('custom.company_number')}}:</span><b><span>{{ company.company_number }}</span></b></div>
                        <div class="pt-1 pb-1"><span class="text-dark col-md-3 col-md-3"> {{trans('custom.vat_number')}}:</span><b><span>{{ company.vat_number }}</span></b></div>
                      <!--  <div class="pt-1 pb-1"><span class="text-dark col-md-3 col-md-3"> {{trans('custom.company_bank')}}:</span><b><span>{{ company.company_bank }}</span></b></div>
                        <div class="pt-1 pb-1"><span class="text-dark col-md-3 col-md-3"> {{trans('custom.company_account_number')}}:</span><b><span>{{ company.company_account_number }}</span></b></div> -->
                    </div>
                </div>
            </div>
        </div>
        <div class="card my-3 info-block">
            <div class="card-body">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-12 col-md-12">
                            <div class="contacts_icon cont-icon"></div>
                            <span class="h3 text-dark"> {{ trans('custom.company_form_contacts') }}</span>
                        </div>
                    </div>
                    <div class='col-sm-12 col-md-12 mt-3'>
                        <div class="pt-1 pb-1"><span class="text-dark col-md-3 col-md-3"> {{trans('custom.office_address')}}:</span><b><span>{{ company.office_address }}</span></b>
                        </div>
                        <div class="pt-1 pb-1"><span
                                class="text-dark col-md-3 col-md-3"> {{trans('custom.phone')}}:</span><b><span>{{ company.phone }}</span></b>
                        </div>
                        <div class="pt-1 pb-1"><span class="text-dark col-md-3 col-md-3"> {{trans('custom.website_url')}}:</span><b><a v-if="company.website !=null"
                                :href="company.website.substring(0, 4) == 'http'?company.website:'http://'+company.website" target="_blank">{{ company.website }}</a></b></div>
                        <div class="social_media pt-1 pb-1">
                            <span class="text-dark col-md-3 col-md-3"> {{trans('custom.social_media')}}:</span>
                            <a v-if="company.twitter !=null" :href="company.twitter.substring(0, 4) == 'http'?company.twitter:'https://'+company.twitter" target="_blank">
                                <div class="cont-icon twitter_link_icon"></div>
                            </a>
                            <a v-if="company.linkedin !=null" :href="company.linkedin.substring(0, 4) == 'http'?company.linkedin:'https://'+company.linkedin" target="_blank">
                                <div class="cont-icon linkedin_link_icon"></div>
                            </a>
                            <a v-if="company.facebook !=null" :href="company.facebook.substring(0, 4) == 'http'?company.facebook:'https://'+company.facebook" target="_blank">
                                <div class="cont-icon facebook_link_icon"></div>
                            </a>
                        </div>
                        <div class="pt-1 pb-1"><span class="text-dark col-md-3 col-md-3"> {{trans('custom.founder_or_seo')}}:</span><b><span>{{ company.founder }}</span></b>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card my-3 info-block projects_block">
            <div class="card-body">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-12 col-md-12">
                            <div class="project_icon cont-icon"></div>
                          <span class="h3 text-dark"> {{ trans('custom.projects') }}</span>
                        </div>
                    </div>
                    <div class='col-sm-12 col-md-12 mt-4'>
                        <div class="d-inline-block mr-2 mb-3" v-for="project, index in company.projects">
                        <router-link  v-if="allowedDisplay('project_files_view', 'project', project.id )"  :to="{name: 'projectItems', params: {id: project.id, company_id: companyId}}">
                        <div class="card" style="width: 14rem;">
                            <div class="image_holder">
                            <img :src="project.image" class="card-img-top" />
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">{{project.title}}</h5>
                            </div>
                        </div></router-link></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data: function () {
            return {
                company: {
                    title: '',
                    description: '',
                    status: 1,
                    logo: '',
                    industry_id :'',
                    employees_number :'',
                    company_name: '',
                    company_number: '',
                    company_address: '',
                    vat_number:'',
                    linkedin:'',
                    office_address:'',
                    industry:{
                        'title':'',
                    },
                    projects:[],
                    founder: '',
                    twitter:'',
                    website:'',
                    specialization:[],
                    company_account_number: '',
                    company_bank: '',
                    membership_id: 0
                },
                industries:[],
                company_specialization : '',
                specializations: [],
                specializations_data: [],
                employee_numbers: [],
                user:{
                    role:'',
                },

                memberships: [],
                allowedDisplay: function(action, model=false, id=false) {
                    return window.allowedDisplay(action, model, id)
                }
            }
        },

        mounted() {
            this.companyId = this.$route.params.id;
            var app = this;
            axios.get('/api/v1/companies/' + this.companyId + '/profile').then((response) => {
                for (let index in response.data.specializations) {
                    this.company.specialization.push(response.data.specializations[index].id);
                    let $item = '<span class="selected-tag" data-id="' + response.data.specializations[index].id + '">' + response.data.specializations[index].title
                        + '<button type="button" aria-label="Remove option" class="close"><span aria-hidden="true">×</span></button></span>';
                    $('.selected_values').append($($item));
                }
                let company = response.data;
                company.specialization = this.company.specialization;

                this.company = company;

            }).catch((resp) => {
                if (resp.response.status === 401) {
                    window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                } else {
                    app.$dialog.alert(window.trans('custom.error_load_company'))
                }
            });

            axios.put('/api/v1/usersettings', {
                'settings_key': 'pre_project_page',
                'settings_value': '/'+decodeURI(window.location.hash),
            }).then(function (resp) {
                console.log('View settings updated');
            }).catch(function (resp) {
                console.log('Failed to update View settings');
            });


        },

        methods: {
            deleteEntry(id) {
                var app = this;
                this.$dialog.confirm(window.trans('custom.delete_confirm'))
                    .then(function () {
                        axios.delete('/api/v1/companies/' + id)
                            .then(function (resp) {
                                this.$router.push({path: '/companies'});
                            })
                            .catch(function (resp) {
                                if (resp.response.status === 401) {
                                    window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                                } else {
                                    app.$dialog.alert(window.trans('custom.error_delete_company'));
                                }
                            });
                    })
                    .catch(function () {
                        console.log('Clicked on cancel')
                    });
            }
        }
    }
</script>
<template>
    <div class="user-settings">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3">
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group mr-2"><a href="#/"
                                               class="btn router-link-active back_btn custom_selected">Back</a></div>
            </div>
        </div>

        <tabs :options="{ useUrlFragment: false }" ref="itemsTabs" @clicked="tabClicked" @changed="tabChanged">
            <!-- Files tab -->
            <tab :name="trans('custom.profile')" id="profile">

                <div class="my-3">
                    <div class="card-body">
                        <div class="container">
                            <form v-on:submit="saveForm()" autocomplete="off">
                                <div class="upload_image">
                                <simple-file-upload :setImage="user.picture"
                                                    @uploaded="imageUploaded">{{ trans('custom.update') }}</simple-file-upload>
                                    <span class="btn btn-default update_avatar">{{ trans('custom.update') }}</span>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 form-group">
                                        <label class="control-label">{{ trans('custom.name') }}</label>
                                        <input name="name" type="text" v-model="user.name" class="form-control" required
                                               autocomplete="off">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 form-group">
                                        <label class="control-label">{{ trans('custom.email') }}</label>
                                        <input type="text" v-model="user.email" class="form-control" required
                                               autocomplete="off">
                                        <span class="btn btn-danger mt-2" style="float: right;"
                                              v-if="this.user.isset_change_email_request"
                                              @click="deleteNewEmailRequest()">Delete Change Email Request</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 form-group">
                                        <label class="control-label">{{ trans('custom.phone') }}</label>
                                        <input type="text" v-model="user.phone" class="form-control">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 form-group">
                                        <label class="control-label">{{ trans('custom.job_title') }}</label>
                                        <input type="text" v-model="user.job_title" class="form-control">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-12 form-group">
                                        <label class="control-label">{{ trans('custom.languages') }}</label>
                                        <v-select
                                                :options="lang_options"
                                                v-model="lang"
                                                label="name"
                                                index="locale"
                                                :clearable="false"
                                                @input="changeLocale($event)" taggable push-tags>

                                        </v-select>
                                    </div>
                                </div>




                                <div class="row">
                                    <div class="col-sm-12 form-group mt-2">
                                        <button class="btn btn-primary">{{ trans('custom.save') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </tab>
            <tab :name="trans('custom.billing')" id="billing" v-if="this.user.id == this.user.company.owner_id">

                    <div class="billing-block">
                        <div v-if="this.activeMembership.id" class="mb-4">
                            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center title_cust  mb-3">
                                <h1 class="h4">{{ trans('custom.your_plan') }}</h1>
                            </div>
                            <div class="row">
                                <div class="col-md-3 col-sm-12 text-md-left">
                                    <h2> {{ this.activeMembership['title'] }}</h2>
                                </div>
                                <div class="col-md-7 col-sm-12 text-md-left mt-3">
                                    <div class="row">
                                        <div class='col-md-7 col-sm-7'>
                                            <div class="progress my-1 h-25">
                                                <div class="progress-bar" role="progressbar"
                                                     :style="{width: percentage + '%'}"
                                                     :aria-valuenow="percentage" aria-valuemin="0"
                                                     aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-4">
                                            {{ trans('custom.used') }}: {{ this.used }}GB / {{ this.total }}GB
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-12 text-md-left">
                                    <a href="" data-toggle="modal" data-target="#upgradeModal"
                                       class="btn btn-outline-primary">{{ trans('custom.upgrade')}}</a>
                                </div>
                            </div>
                            <template v-if="this.subscriptions.length == 0 && this.user.company.active_until !== null">
                                <div class="row ml-4 mt-3">
                                    <button class="btn btn-outline-success">{{ trans('custom.next_payment')}}
                                        {{this.user.formatted_until}}
                                    </button>
                                </div>
                            </template>

                            <template v-else v-for="subscription, index in this.subscriptions">
                                <div class="row ml-4 mt-3">
                                    <button class="btn btn-outline-success">{{ trans('custom.next_payment')}}
                                        {{subscription.ends_at}}
                                    </button>
                                    <button @click="unsubscribe(subscription.id)"
                                            class="btn btn btn-outline-danger ml-2">{{ trans('custom.unsubscribe')}}
                                    </button>
                                </div>
                            </template>

                        </div>
                        <template v-if="user.company.card_last_four !== null && user.company.card_brand !== null">
                            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mt-5 mb-3 title_cust">
                                <h1 class="h4">{{ trans('custom.payment_method') }}</h1>
                            </div>
                        <div class="row">
                            <div class="mb-2"><i class="mr-2">{{ trans('custom.card_type') }}:</i><b
                                    class="text-uppercase">{{user.company.card_brand}}</b></div>
                            <div class="mb-2"><span class="">**** ***** **** {{user.company.card_last_four}}</span>
                            </div>
                        </div>

                        </template>


                        <template v-if="invoices.data.length > 0">
                            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mt-5 title_cust">
                                <h1 class="h4">{{ trans('custom.billing_history') }}</h1>
                            </div>

                            <div>

                                    <div class="card-body">
                                        <table class="table table-striped table-responsive-md">
                                            <thead>
                                            <th>{{ trans('custom.invoice') }}</th>
                                            <th>{{ trans('custom.total') }}</th>
                                            <th>{{ trans('custom.date') }}</th>
                                            <th>{{ trans('custom.status') }}</th>
                                            <th>{{ trans('custom.url') }}</th>
                                            </thead>
                                            <tbody>
                                            <tr v-for="invoice, index in invoices.data">
                                                <td>
                                                    {{ invoice.title }}
                                                </td>
                                                <td>
                                                    {{ invoice.price }}&euro;
                                                </td>
                                                <td>
                                                    {{ invoice.created_at }}
                                                </td>
                                                <td>
                                                    {{ trans(invoice.payment_status) }}
                                                </td>
                                                <td>
                                                    <a v-if="invoice.stripe_url !== null" :href="invoice.stripe_url"
                                                       target="_blank"
                                                       class="mr-1"> Receipt
                                                    </a>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>


                                <div class="row">
                                    <div class="col-sm-12">
                                        <vue-pagination :pagination="invoices"
                                                        @paginate="loadInvoices()"
                                                        :offset="5"
                                        ></vue-pagination>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

            </tab>
            <tab :name="trans('custom.password')" id="password">
                <div class="my-3">
                    <div class="card-body">
                        <div class="container">
                            <form v-on:submit="savePass()" autocomplete="off">

                                <div class="row">
                                    <div class="col-sm-12 form-group">
                                        <label class="control-label">{{ trans('custom.current_password') }}</label>
                                        <input v-model="user.current_password" type="password" class="form-control"
                                               autocomplete="new-password">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 form-group">
                                        <label class="control-label">{{ trans('custom.new_password') }}</label>
                                        <input v-model="user.new_password" type="password" class="form-control"
                                               autocomplete="new-password">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-12 form-group">
                                        <label class="control-label">{{ trans('custom.confirm_password') }}</label>
                                        <input id="confirm_password" type="password" class="form-control">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-12 form-group mt-2">
                                        <button class="btn btn-primary">{{ trans('custom.save') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>


            </tab>

        </tabs>


        <!-- Upgrade Modal -->
        <div class="modal styled_modal fade" id="upgradeModal" role="dialog" ref="vuemodal">
            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('custom.choose_subscription') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="pricing card-deck flex-column flex-md-row">
                            <div :class="{'active': item.active}" class="card card-pricing text-center px-3" v-for="item in memberships">
                                <div class="bg-transparent card-header pt-4 border-0 pl-1 pr-0">
                                    <h3 class="font-weight-bold mb-3 mt-1">
                                        {{ item.title }}
                                    </h3>
                                    <template v-if="item.month_price > 0">
                                        <h4 v-if="period == 'month'" class="h5 font-weight-normal text-primary font-weight-bold text-left mb-0"
                                            data-pricing-value="30">&euro;<span class="price">{{ item.month_price }}</span><span
                                                class="h6 text-muted ml-3">/ {{ trans('custom.month') }}</span></h4>
                                        <h4 v-if="period == 'year'" class="h5 font-weight-normal text-primary font-weight-bold text-left mb-0 mt-1"
                                            data-pricing-value="30">&euro;<span class="price">{{ item.year_price }}</span><span
                                                class="h6 text-muted ml-3">/ {{ trans('custom.year') }}</span></h4>
                                        <ul class="mt-3 mb-5 pb-5">
                                            <li class="mb-1">{{item.size/1000}} GB</li>
                                            <li class="mb-1">{{item.managers_limit}} {{ trans('custom.managers') }}</li>
                                            <li class="mb-1">{{item.visitors_limit}} {{ trans('custom.visitors') }}</li>
                                            <li class="mb-1">{{item.projects_limit}} {{ trans('custom.projects') }}</li>
                                            <li class="mb-1">{{ trans('custom.unlimited_share_projects') }}</li>
                                            <li class="mb-1">{{ trans('custom.unlimited_conversions') }}</li>
                                            <li class="mb-1">{{item.overlimit_gb_price/100}} {{ trans('custom.euro_for_each_iverlimit_gb') }}</li>
                                            <li class="mb-1">{{ trans('custom.'+ item.support_type) }}</li>
                                        </ul>
                                    </template>
                                    <template v-else>
                                        <h4 v-if="period == 'month'" class="h5 font-weight-normal font-weight-bold text-primary text-left mb-0"
                                            data-pricing-value="30">&euro;<span class="price">0</span><span
                                                class="h6 text-muted ml-3">/ {{ trans('custom.month') }}</span></h4>
                                        <h4 v-if="period == 'year'" class="font-weight-normal font-weight-bold text-primary text-left mb-0 mt-1"
                                            data-pricing-value="30">&euro;<span class="price">0</span><span
                                                class="h6 text-muted ml-3">/ {{ trans('custom.year') }}</span></h4>

                                        <ul class="mt-3 mb-5 pb-5">
                                            <li class="mb-1">{{item.size/1000}} GB</li>
                                            <li class="mb-1">{{item.managers_limit}} {{ trans('custom.managers') }}</li>
                                            <li class="mb-1">{{item.visitors_limit}} {{ trans('custom.visitors') }}</li>
                                            <li class="mb-1">{{item.projects_limit}} {{ trans('custom.projects') }}</li>
                                            <li class="mb-1">{{ trans('custom.unlimited_share_projects') }}</li>
                                            <li class="mb-1">{{item.projects_limit}} {{ trans('custom.conversions') }}</li>
                                            <li class="mb-1">{{ trans('custom.'+ item.support_type) }}</li>
                                            <li class="mb-1">{{ trans('custom.30_days_trial') }}</li>
                                        </ul>
                                    </template>

                                </div>
                                <div class="absolute_bottom card-body pt-3 mb-1" :id="item.id +'_item'" :data-id="item.id">
                                    <template v-if="item.active && (subscriptions.length > 0 || activeId === 1)">
                                        <span class="margin_bottom-29  btn btn-success disabled">Active</span>
                                    </template>

                                    <template v-else-if="item.id < activeId">
                                        <span class="margin_bottom-29 btn btn-secondary disabled">N/A</span>
                                    </template>

                                    <template v-else>
                                        <div>
                                            <button class="btn btn-primary"
                                                    @click="checkout_price(item.id+'_item')">{{trans('custom.select_plan')}}
                                            </button>
                                        </div>
                                        <div class="mt-2">
                                            <input type="checkbox" checked="checked" class="mr-1 to_subscribe"> <label class="to_checkbox">{{trans('custom.subscribe')}}</label>
                                        </div>
                                    </template>

                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="select_period mb-3 font-weight-bold">
                                {{trans('custom.monthly')}}
                                <div :class="period" class="period_selector ml-3 mr-3" @click="changePeriod()">
                                 <div class="select_point"></div>
                                </div>
                               {{trans('custom.yearly')}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import VuePagination from '../Pagination';

    export default {
        data: function () {
            return {
                user: {
                    name: '',
                    email: '',
                    current_password: null,
                    new_password: null,
                    picture: '',
                    job_title: '',
                    phone: '',
                    isset_change_email_request: false,
                    company: {
                        owner_id: null
                    },
                },
                memberships: {},
                invoices: {
                    total: 0,
                    from: 1,
                    to: 0,
                    current_page: 1,
                    data: [],
                },
                activeId: 0,
                showPaid: 0,
                activeMembership: [],
                tabs_loaded: 0,
                percentage:0,
                used:0,
                lang:'en',
                lang_options:[],
                total:0,
                subscriptions:[],
                period: 'month',
            }
        },
        components: {
            VuePagination
        },
        mounted() {
            var app = this;

            for(let lang in window.availableLanguages){
                app.lang_options.push({
                    'locale' : lang,
                    'name' : window.availableLanguages[lang],
                })
            }

            axios.get('/api/v1/user')
                .then(function (resp) {
                    app.user = resp.data;
                    app.user.company = resp.data.company;
                })
                .catch(function (resp) {
                    if (resp.response.status === 401) {
                        window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                    } else {
                        app.$dialog.alert("Could not load user");
                    }
                });

            $('body').on('click', '.update_avatar', function () {
                $('.upload_image input[type="file"]').click();
            });

            let tabs = this.$refs.itemsTabs;
            let url_tab = decodeURI(window.location.hash).split('#')[2];
            if (typeof url_tab != 'undefined') {
                if (url_tab == 'package_discount') {
                    setTimeout(() => {
                        let price = app.memberships[3]['year_stripe_key'];
                        if (app.activeId > 1) {
                            app.$dialog.confirm(trans('custom.this_will_stop_your_membership_and_start_new_membership'))
                                .then(function () {
                                    axios.get('/api/v1/memberships/create_session/' + price + '/' + app.user.company.id + '/discounted').then(resp => {
                                        window.Stripe.redirectToCheckout({
                                            sessionId: resp.data.id,
                                        })
                                            .then(function (result) {
                                                console.log(result);
                                                app.$dialog.alert(result.error.message);
                                            });
                                    }).catch((error) => {
                                        if (error.response.status !== 401) {
                                            app.$dialog.alert("Could not process request");
                                        }
                                    });
                                })
                                .catch(function () {
                                    console.log('Clicked on cancel')
                                });
                        } else {

                            axios.get('/api/v1/memberships/create_session/' + price + '/' + app.user.company.id + '/discounted').then(resp => {
                                window.Stripe.redirectToCheckout({
                                    sessionId: resp.data.id,
                                })
                                    .then(function (result) {
                                        console.log(result);
                                        app.$dialog.alert(result.error.message);
                                    });
                            }).catch((error) => {
                                if (error.response.status !== 401) {
                                    app.$dialog.alert("Could not process request");
                                }
                            });
                        }
                    }, 1000);


                } else {
                    tabs.selectTab("#" + url_tab);
                }
            } else {
                axios.get('/api/v1/usersettings').then(resp => {
                    let hash = (typeof resp.data !== 'undefined' && typeof resp.data['settings_view'] !== 'undefined') ? resp.data['settings_view'] : decodeURI(window.location.hash).split('#')[2];
                    if (typeof hash === "undefined") {
                        hash = 'profile';
                    }
                    if(typeof resp.data.locale !== 'undefined'){
                        app.lang = resp.data.locale;
                    }

                    tabs.selectTab("#" + hash);
                }).catch(() => {
                    let hash = decodeURI(window.location.hash).split('#')[2];
                    if (typeof hash === "undefined") {
                        hash = 'profile';
                    }

                    tabs.selectTab("#" + hash);
                    console.log("Could not load settings")
                });
            }

            axios.get('/api/v1/storage')
                .then((response) => {
                    this.used = response.data.used;
                    this.total = response.data.total;

                    this.percentage = this.used/this.total*100;
                });

            this.loadInvoices();

            setTimeout(function () {
                $('.vue-warning').hide(500)
            }, 3000)

            axios.get('/api/v1/subscription')
                .then((response) => {
                    this.subscriptions = response.data;
                }).catch((error) => {
               console.log(error);
            });

            axios.get('/api/v1/memberships')
                .then((response) => {

                    for (let index in response.data) {
                        let membership = response.data[index];
                        this.memberships[membership.id] = membership;
                        if (membership.active) {
                            this.activeId = membership.id;
                            this.activeMembership = membership;
                        }
                    }
                });

            $('body').on('click', '.to_checkbox', function(){
                var $checkbox = $(this).parents('div:first').find('input:first');
                if ($checkbox.is(':checked')){
                    $checkbox.removeAttr('checked');
                } else {
                    $checkbox.attr('checked', 'checked');
                }
            });
        },
        methods: {
            tabClicked(selectedTab) {
                console.log('Current tab re-clicked:' + selectedTab.tab.name);
            },
            tabChanged(selectedTab) {
                if (this.tabs_loaded > 0) {
                    let tempUrl = decodeURI(window.location.hash).split('#')[1];
                    window.location.hash = tempUrl + '#' + selectedTab.tab.id.toLowerCase();
                    this.updateUserSettingProjectTab(selectedTab.tab.id.toLowerCase());
                } else {
                    this.tabs_loaded = 1
                }
            },
            updateUserSettingProjectTab($tab) {
                axios.put('/api/v1/usersettings', {
                    'settings_key': 'settings_view',
                    'settings_value': $tab,
                }).then(function (resp) {
                    console.log('View settings updated');
                }).catch(function (resp) {
                    console.log('Failed to update View settings');
                });
            },
            showInvoice(status) {
                if (status) {
                    status = status.toLowerCase();
                    return status == 'custom.payment_paid';
                }
                return false
            },
            loadInvoices() {
                var app = this;
                axios.get('/api/v1/memberships/invoices?page=' + this.invoices.current_page).then(resp => {
                    app.invoices = resp.data
                }).catch((resp) => {
                    if (resp.response.status === 401) {
                        window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                    } else {
                        app.$dialog.alert("Could not load invoices");
                    }
                });
            },
            imageUploaded(file) {
                this.user.picture = file;
            },
            saveForm() {
                event.preventDefault();
                var app = this;
                var update = app.user;

                axios.put('/api/v1/user', update)
                    .then(function (resp) {
                        app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>' + resp.data.notification, {'html':true, okText:'ok'}).then((dialog) =>
                        {
                            location.reload();
                        });
                    })
                    .catch(error => {
                        if (error.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                        } else {
                            app.$dialog.alert(window.parseError(error.response.data.errors, 'Could not edit user:'), {'html': true});
                        }
                    });
            },
            savePass(){
                event.preventDefault();
                var app = this;
                var update = app.user;

                if ($('#confirm_password').val() != update.new_password){
                    app.$dialog.alert(trans('custom.not_equal_password'));
                    return false;
                }

                axios.put('/api/v1/user/passwords', update)
                    .then(function (resp) {
                        app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>' + resp.data.notification, {'html':true, okText:'ok'}).then((dialog) =>
                        {
                            location.reload();
                        })
                    })
                    .catch(error => {
                        if (error.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                        } else {
                            app.$dialog.alert(window.parseError(error.response.data.errors, 'Could not edit user:'), {'html': true});
                        }
                    });
            },
            deleteNewEmailRequest() {
                var app = this;
                axios.get('/api/v1/user/delete_change_email_request').then(resp => {
                    app.$dialog.alert(resp.data.notification);
                    location.reload();
                }).catch((error) => {
                    if (error.response.status === 401) {
                        window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                    } else {
                        app.$dialog.alert("Could not process request");
                    }
                });
            },
            unsubscribe(id){
                var app = this;
                axios.put('/api/v1/subscription/unsubscribe', {
                    'id' : id,
                }).then(resp => {
                    app.$dialog.alert(trans('custom.unsubscribed_successfully_watch_your_payments'));
                    location.reload();
                }).catch((error) => {
                    if (error.response.status === 401) {
                        window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                    } else {
                        app.$dialog.alert("Could not process request");
                    }
                });
            },
            checkout_price(id_attr) {
                var app = this;
                let is_regular = $('#' + id_attr).find('.to_subscribe:first').prop("checked") == true ? 1 : 0;
                let item_id = $('#' + id_attr).data('id');
                let price = is_regular ? app.memberships[item_id][app.period + '_stripe_sub_key'] : app.memberships[item_id][app.period + '_stripe_key'];
                if (app.activeId > 1 && app.activeMembership.status == 'custom.active') {
                    var $confirm_message = '';

                    if (item_id > app.activeId) {
                        $confirm_message = trans('custom.this_will_stop_your_membership_and_start_new_membership');
                    }
                    if (item_id == app.activeId) {
                        if (is_regular) {
                            $confirm_message = trans('custom.this_will_add_new_subscription_to_your_current_membership');
                        } else {
                            $confirm_message = trans('custom.this_will_add_new_period_to_your_current_membership');
                        }
                    }

                    app.$dialog.confirm($confirm_message)
                        .then(function () {
                            axios.get('/api/v1/memberships/create_session/' + price + '/' + app.user.company.id + '/' + is_regular).then(resp => {
                                window.Stripe.redirectToCheckout({
                                    sessionId: resp.data.id,
                                }).then(function (result) {
                                    console.log(result);
                                    app.$dialog.alert(result.error.message);
                                });
                            }).catch((error) => {
                                if (error.response.status === 401) {
                                    window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                                } else {
                                    app.$dialog.alert("Could not process request");
                                }
                            });
                        })
                        .catch(function () {
                                console.log('Clicked on cancel')
                        });

                } else {
                    axios.get('/api/v1/memberships/create_session/' + price + '/' + app.user.company.id + '/' + is_regular).then(resp => {
                        window.Stripe.redirectToCheckout({
                            sessionId: resp.data.id,
                        })
                            .then(function (result) {
                                console.log(result);
                                app.$dialog.alert(result.error.message);
                            });
                    }).catch((error) => {
                        if (error.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                        } else {
                            app.$dialog.alert("Could not process request");
                        }
                    });
                }

            },
            changeLocale(lang){
                axios.get('lang/' + lang).then(response => (window.location.reload()))
            },
            changePeriod(){
                if (this.period == 'month'){
                    this.period = 'year';
                } else {
                    this.period = 'month';
                }
            }
        }
    }

</script>

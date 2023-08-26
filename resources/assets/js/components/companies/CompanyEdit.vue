
<template>
    <div>
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group mr-2">
                    <router-link :to="{name: 'companyIndex'}" class="btn back_btn">{{ trans('custom.back') }}</router-link>
                </div>
            </div>
        </div>

        <div class="card my-3">
            <div class="card-body">
                <div class="container">
                    <form v-on:submit="saveForm()">
                        <div class="row">
                            <label class="control-label breadcrumb h4 bg-light w-100">{{ trans('custom.general_information') }}</label>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label class="control-label">{{ trans('custom.company_name') }}*</label>
                                <input v-validate="'required'" name="title" type="text" v-model="company.title" class="form-control" required>
                            </div>
                            <div class="col-sm-6 form-group">
                                <label class="control-label">{{ trans('custom.industry') }}*</label>
                                <v-select
                                        v-model="company.industry_id"
                                        :options="industries"
                                        label="title"
                                        index="id"
                                        :clearable="false"
                                ></v-select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group specialization_wrapp">
                                <label class="control-label">{{ trans('custom.specialization') }}*</label>
                                <v-select
                                        v-model="company_specialization"
                                        :options="specializations"
                                        label="title"
                                        index="id"
                                        @input="addSpecializationItem"
                                        :clearable="true"
                                >
                                 <option value="undefined" disabled>Placeholder text</option>
                                </v-select>
                                <div class="selected_values"></div>
                            </div>
                            <div class="col-sm-6 form-group">
                                <label class="control-label">{{ trans('custom.number_of_employees') }}*</label>
                                <v-select
                                        v-model="company.employees_number"
                                        :options="employee_numbers"
                                        label="title"
                                        index="id"
                                        :clearable="false"
                                ></v-select>
                            </div>
                        </div>
                        <div class="upload_styled">
                            <simple-file-upload @uploaded="imageUploaded"></simple-file-upload>
                            <span class="custom_upload_btn btn">{{ trans('custom.choose_logo') }}</span>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.description') }}</label>
                                <textarea v-model="company.description" class="form-control"></textarea>
                            </div>
                        </div>
                        <div v-if="allowedDisplay('all') && owner_id_saved !== null" class="row">
                            <div class="col-sm-8 form-group">
                                <label class="control-label">{{ trans('custom.company_verification.message_to_admins') }}</label>
                                <textarea id="message_to_admins" class="form-control"></textarea>
                                <small class="control-label d-block font-italic">{{ trans('custom.company_verification.admins_uses_locale') }} "{{company.owner_locale}}"</small>
                                <div class="text-right mt-1"><span @click="message_owner(company.id)"  class="btn btn-success width-200">{{ trans('custom.send') }}</span></div>
                            </div>

                        </div>
                        <div v-if="allowedDisplay('all')" class="row">
                            <div class="col-sm-4 form-group">
                                        <label class="control-label">{{ trans('custom.membership') }}</label>
                                        <v-select
                                            v-model="company.membership_id"
                                            :options="memberships"
                                            label="title"
                                            index="id"
                                            :clearable="false"
                                ></v-select>
                            </div>

                            <div class="col-sm-3 form-group mb-2 date_picker_styled">
                                <label class="control-label">{{ trans('custom.active_to') }}</label>
                                <v-date-picker v-model="company.active_until"></v-date-picker>
                            </div>

                            <div class="col-sm-6 form-group">
                                <label class="control-label">{{ trans('custom.owner') }}</label>
                                <v-select

                                        :placeholder="trans('custom.owner')"
                                        :options="company.users"
                                        :clearable="false"
                                        label="name"
                                        index="id"
                                        v-model="company.owner_id"
                                >
                                    <template v-slot:option="option">
                                        <span>{{ option.name }}</span>
                                    </template>
                                </v-select>
                            </div>
                        </div>
                        <!--
                        <div class="row">
                            <div class="col-sm-2 form-group">
                                <label class="control-label">{{ trans('custom.status') }}</label>
                                <select v-model="company.status" class="form-control">
                                    <option value="1">{{ trans('custom.active') }}</option>
                                    <option value="0">{{ trans('custom.disabled') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="row" v-if="allowedDisplay('customers_manage_storage')">
                            <div class="col-sm-2 form-group">
                                <label class="control-label">{{ trans('custom.storage') }}</label>
                                <select v-model="company.membership_id" class="form-control">
                                    <option v-for="membership in memberships" :value="membership.id">{{ membership.title }}</option>
                                </select>
                            </div>
                        </div> -->

                        <div class="row">
                            <label class="control-label breadcrumb h4 bg-light w-100">{{ trans('custom.company_legal_information') }} <span class="h6 ml-2 mt-2">*({{ trans('custom.required_for_verification') }})</span></label>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 col-md-6 form-group">
                                <label class="control-label">{{ trans('custom.company_address') }}*</label>
                                <input name="company_address" type="text" v-model="company.company_address" class="form-control">
                            </div>
                            <div class="col-sm-12 col-md-6 form-group">
                                <label class="control-label">{{ trans('custom.company_number') }}*</label>
                                <input name="registration_number" type="number" v-model="company.company_number" class="form-control">
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-sm-12 col-md-6 form-group">
                                <label class="control-label">{{ trans('custom.vat_number') }}*</label>
                                <input name="vat_number" type="text" v-model="company.vat_number" class="form-control">
                            </div>
                            <!--  <div class="col-sm-12 col-md-6 form-group">
                                  <label class="control-label">{{ trans('custom.company_bank') }}</label>
                                  <input name="company_bank" type="number" v-model="company.company_bank" class="form-control">
                              </div>-->
                          </div>
                          <!--<div class="row">
                              <div class="col-sm-12 col-md-6 form-group">
                                  <label class="control-label">{{ trans('custom.company_account_number') }}</label>
                                  <input v-on:change="isValidIBANNumber()" name="company_account_number" type="text"
                                         v-model="company.company_account_number" class="form-control">
                              </div>
                          </div>-->
                        <div class="row">
                            <label class="control-label breadcrumb h4 bg-light w-100">{{ trans('custom.company_form_contacts') }}</label>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-6 form-group">
                                <label class="control-label">{{ trans('custom.office_address') }}*</label>
                                <input name="office_address" type="text" v-model="company.office_address" class="form-control">
                            </div>
                            <div class="col-sm-12 col-md-6 form-group">
                                <label class="control-label">{{ trans('custom.phone') }}*</label>
                                <input name="phone" type="text" v-model="company.phone" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-6 form-group">
                                <label class="control-label">{{ trans('custom.website_url') }}*</label>
                                <input v-on:change="isValidUrl('website')" name="website" type="text"
                                       v-model="company.website" class="form-control">
                            </div>
                            <div class="col-sm-12 col-md-6 form-group">
                                <label class="control-label">{{ trans('custom.email') }}*</label>
                                <input v-on:change="isValidEmail()" name="email" type="text" v-model="company.email"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-6 form-group">
                                <label class="control-label">{{ trans('custom.linkedin') }}</label>
                                <input v-on:change="isValidUrl('linkedin')" name="linkedin" type="text" v-model="company.linkedin" class="form-control">
                            </div>
                            <div class="col-sm-12 col-md-6 form-group">
                                <label class="control-label">{{ trans('custom.facebook') }}</label>
                                <input v-on:change="isValidUrl('facebook')" name="facebook" type="text" v-model="company.facebook" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-6 form-group">
                                <label class="control-label">{{ trans('custom.twitter') }}</label>
                                <input v-on:change="isValidUrl('twitter')" name="twitter" type="text" v-model="company.twitter" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-6 form-group">
                                <label class="control-label">{{ trans('custom.founder_or_seo') }}</label>
                                <input name="founder" type="text" v-model="company.founder" class="form-control">
                            </div>
                        </div>
                        <div v-if="allowedDisplay('all') && company.owner_id > 0" class="row">
                            <div v-if="company.verified"  class="col-sm-12 form-group text-left">
                                <span @click="cancel_verify(company.id)" class="btn btn-danger">{{ trans('custom.company_verification.cancel_verify') }}</span>
                            </div>
                            <div v-if="!company.verified" class="col-sm-12 form-group text-left">
                                <span @click="verify(company.id)" class="btn btn-success">{{ trans('custom.company_verification.verify') }}</span>
                            </div>
                        </div>
                        <div v-if="allowedDisplay('company_edit', 'company', company.id) && !allowedDisplay('all') && company.owner_id > 0" class="row">
                            <div v-if="!company.verified" class="col-sm-12 form-group text-left">
                                <span @click="request_verify(company.id)" class="btn btn-success">{{ trans('custom.company_verification.send_verify_request') }}</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group text-right">
                                <button class="btn btn-primary width-200">{{ trans('custom.save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <template v-if="invoices.data.length > 0">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-5 border-bottom">
                <h1 class="h2">{{ trans('custom.billing_history') }}</h1>
            </div>

            <div>
                <div class="card my-3">
                    <div class="card-body">
                        <table class="table table-striped table-responsive-md">
                            <thead>
                            <th>{{ trans('custom.invoice') }}</th>
                            <th>{{ trans('custom.total') }}</th>
                            <th>{{ trans('custom.date') }}</th>
                            <th>{{ trans('custom.status') }}</th>
                            <th>PDF</th>
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
                                    <a :href="'/memberships/' + invoice.id + '/invoice'" target="_blank" v-if="showInvoice(invoice.payment_status)" class="mr-1">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <vue-pagination  :pagination="invoices"
                                         @paginate="loadInvoices()"
                                         :offset="5"
                        ></vue-pagination>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
    import VuePagination from "../Pagination";

    export default {
        data() {
            return {
                companyId: null,
                company: {
                    title: '',
                    active_until:null,
                    description: '',
                    status: 1,
                    logo: '',
                    industry_id :'',
                    employees_number :'',
                    company_name: '',
                    company_number: '',
                    company_address: '',
                    vat_number:'',
                    office_address:'',
                    founder: '',
                    twitter:'',
                    website:'',
                    specialization:[],
                    company_account_number: '',
                    company_bank: '',
                    membership_id: 0,
                    owner_id:null,
                    verified:0,
                    users:[],
                    owner_locale : 'EN'
                },
                industries:[],
                industries_filtered:[],
                company_base_verify_data: {},
                owner_id_saved:null,
                company_specialization : '',
                specializations: [],
                specializations_data: [],
                employee_numbers: [],
                verification_fields_list:[],
                memberships: [],
                invoices: {
                    total: 0,
                    from: 1,
                    to: 0,
                    current_page: 1,
                    data: []
                },
                allowedDisplay: function(action, model = false, id = false) {
                    return window.allowedDisplay(action, model, id)
                }
            }
        },
        components: {
            VuePagination
        },
        mounted() {

            $('.verify_btn').hide();


            this.companyId = this.$route.params.id;

            this.loadInvoices();
            var app = this;
            axios.get('/api/v1/companies/' + this.companyId + '/edit').then((response) => {
                for (let index in response.data.specializations) {
                    this.company.specialization.push(response.data.specializations[index].id);
                    let $item = '<span class="selected-tag" data-id="' + response.data.specializations[index].id + '">' + response.data.specializations[index].title
                        + '<button type="button" aria-label="Remove option" class="close"><span aria-hidden="true">×</span></button></span>';
                    $('.selected_values').append($($item));
                }
                let company = response.data;
                company.specialization = this.company.specialization;

                company.active_until = response.data.active_until ? new Date(response.data.active_until) : null;

                this.company = company;
                this.company_base_verify_data = response.data;
                this.owner_id_saved = company.owner_id > 0?company.owner_id:null;
                this.verification_fields_list = response.data.verification_fields_list;
                this.verification_fields_list.forEach(field_name => {
                    this.company_base_verify_data['old_' + field_name] = response.data[field_name];
                });

                if (!window.readCookie('dont_show_verificate_ivite') && typeof company.show_verify_popup != 'undefined' && company.show_verify_popup == 1){
                    window.eraseCookie('dont_show_verificate_ivite');
                    app.$dialog.alert('<div class="mt-4"><img src="/images/email/verify_request_email.png"></div><div class="text-center mb-4 mt-4" style="font-size:28px;font-weight:400;line-height: 40px;">' + trans('custom.company_verification.verify_your_company') + '</div>' +
                        '<div style="font-size: 16px;font-weight: 400;line-height: 24px;text-align: justify;">'+ trans('custom.please_fill_in_all_required_information_about_your_company') + '</div>', {
                        'html': true,
                        okText: 'Ok',
                    });
                }

            }).catch((resp) => {
                if (resp.response.status === 401) {
                    window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                } else {
                    app.$dialog.alert(window.trans('custom.error_load_company'))
                }
            });


            axios.get('/api/v1/memberships?company_id=' + this.companyId).then((response) => {
                this.memberships = response.data;
            });

            axios.get('/api/v1/companies/list_values')
                .then((response) => {
                    this.industries = response.data.data.industries;
                    this.employee_numbers = response.data.data.employee_number;
                    this.specializations_data = response.data.data.specializations;
                    this.specializations = [];
                    setTimeout(function(){
                        $('.specialization_wrapp input').attr('placeholder', 'Type for search...');
                    }, 400);
                });

            setTimeout(function(){
                $('.specialization_wrapp .vs__actions .clear').click();
            }, 100);



            $('body').on('keyup', '.specialization_wrapp input[type="search"]', function (event) {
                let ignore_keycodes = [37,38,39,40,33,34,35,36];
                if (ignore_keycodes.indexOf(event.keyCode) !== -1){
                    return;
                }
                let val = $(this).val().toLowerCase();
                if (val != ''){
                    let new_data = app.specializations_data.filter(function (specialization) {
                        return specialization.title.toLowerCase().indexOf(val) !== -1
                    });
                    app.specializations = new_data.slice(0, 10);
                } else {
                    app.specializations = [];
                }

            })

            $('body').on('click', '.selected_values .close', function (event) {
                let $this = $(this);
                let value = $this.parents('.selected-tag:first').data('id');
                $this.parents('.selected-tag:first').remove();
                const index = app.company.specialization.indexOf(value);
                if (index > -1) {
                    app.company.specialization.splice(index, 1);
                }
                app.company.specializations.forEach(specialization => {
                    if (specialization.id == value) {
                        const obj_index = app.company.specialization.indexOf(specialization);
                        if (obj_index > -1) {
                            app.company.specializations.splice(obj_index, 1);
                        }
                    }
                });
            });

            $('body').on('click', '.custom_upload_btn', function(){
                $(this).parents('.upload_styled:first').find('input[type="file"]:first').click();
            });

            $('body').on('click', '.btn.btn-danger.mt-2', function(){
                $(this).parents('.upload_styled:first').find('input[type="file"]:first').val('');
            });



        },

        methods: {
            verify(company_id){
                var app = this;
                let required_fields_filled = 1;
                this.verification_fields_list.forEach(field_name => {
                    if ($('input[name="'+field_name+'"]').val() == ''){
                        required_fields_filled = 0;
                    }
                });

                if (!required_fields_filled) {
                    this.$dialog.confirm(window.trans('custom.company_verification.not_all_fields_filled_for_verification_continue'))
                        .then(function () {
                            axios.put('/api/v1/companies/' + app.companyId, {verified: 1}).then(() => {
                                app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>' + window.trans('custom.company_verification.company_verified'), {'html':true, okText:'ok'}).then((dialog) =>
                                {
                                    window.createCookie('dont_show_verificate_ivite', 1, 1);
                                    window.location.reload();
                                });
                            }).catch((resp) => {
                                if (resp.response.status === 401) {
                                    window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                                } else {
                                    app.$dialog.alert(window.trans('custom.error_update_company'));
                                }
                            });
                        })
                        .catch(function () {
                            console.log('Clicked on cancel')
                        });
                } else {
                    axios.put('/api/v1/companies/' + app.companyId, {verified: 1} ).then(() => {
                        app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>' + window.trans('custom.company_verification.company_verified'), {'html':true, okText:'ok'}).then((dialog) =>
                        {
                            window.createCookie('dont_show_verificate_ivite', 1, 1);
                            window.location.reload();
                        });
                    }).catch((resp) => {
                        if (resp.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                        } else {
                            app.$dialog.alert(window.trans('custom.error_update_company'));
                        }
                    });
                }
            },
            addSpecializationItem(value) {
                const index = this.company.specialization.indexOf(value);
                if (index > -1 || value === null) {
                    $('.specialization_wrapp .vs__actions .clear').click();
                    return;
                }
                this.company.specialization.push(value);
                let result_item = this.specializations_data.filter(function (specialization) {
                    return specialization.id == value;
                });
                let $item = '<span class="selected-tag" data-id="' + value + '">' + result_item[0].title
                    + '<button type="button" aria-label="Remove option" class="close"><span aria-hidden="true">×</span></button></span>';
                $('.selected_values').append($($item));

                setTimeout(function(){
                    $('.specialization_wrapp .vs__actions .clear').click();
                }, 100);

            },
            isValidIBANNumber() {
                var input = this.company.company_account_number;
                var CODE_LENGTHS = {
                    AD: 24, AE: 23, AT: 20, AZ: 28, BA: 20, BE: 16, BG: 22, BH: 22, BR: 29,
                    CH: 21, CR: 21, CY: 28, CZ: 24, DE: 22, DK: 18, DO: 28, EE: 20, ES: 24,
                    FI: 18, FO: 18, FR: 27, GB: 22, GI: 23, GL: 18, GR: 27, GT: 28, HR: 21,
                    HU: 28, IE: 22, IL: 23, IS: 26, IT: 27, JO: 30, KW: 30, KZ: 20, LB: 28,
                    LI: 21, LT: 20, LU: 20, LV: 21, MC: 27, MD: 24, ME: 22, MK: 19, MR: 27,
                    MT: 31, MU: 30, NL: 18, NO: 15, PK: 24, PL: 28, PS: 29, PT: 25, QA: 29,
                    RO: 24, RS: 22, SA: 24, SE: 24, SI: 19, SK: 24, SM: 27, TN: 24, TR: 26,
                    AL: 28, BY: 28, CR: 22, EG: 29, GE: 22, IQ: 23, LC: 32, SC: 31, ST: 25,
                    SV: 28, TL: 23, UA: 29, VA: 22, VG: 24, XK: 20
                };
                var iban = String(input).toUpperCase().replace(/[^A-Z0-9]/g, ''), // keep only alphanumeric characters
                    code = iban.match(/^([A-Z]{2})(\d{2})([A-Z\d]+)$/), // match and capture (1) the country code, (2) the check digits, and (3) the rest
                    digits;
                // check syntax and length
                if (!code || iban.length !== CODE_LENGTHS[code[1]]) {
                    this.errorFieldData('company_account_number', window.trans('custom.iban_not_correct'));
                    return;
                }
                // rearrange country code and check digits, and convert chars to ints
                digits = (code[3] + code[1] + code[2]).replace(/[A-Z]/g, function (letter) {
                    return letter.charCodeAt(0) - 55;
                });
                // final check
                let  checksum =  this.mod97(digits);
                if(checksum !== 1){
                    this.errorFieldData('company_account_number', window.trans('custom.iban_not_correct'));
                } else {
                    this.clearError('company_account_number');
                }
            },

            mod97(string) {
                var checksum = string.slice(0, 2), fragment;
                for (var offset = 2; offset < string.length; offset += 7) {
                    fragment = String(checksum) + string.substring(offset, offset + 7);
                    checksum = parseInt(fragment, 10) % 97;
                }
                return checksum;
            },
            isValidUrl(field_name){
                this.clearError(field_name);
                let input = this.company[field_name];
                if(!(/(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})/.test(input))){
                    this.errorFieldData(field_name, window.trans('custom.url_not_correct'));
                } else {
                    this.clearError(field_name);
                }
            },
            isValidEmail(){
                this.clearError(email);
                let input = this.company.email;
                if(!(/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/i.test(input))){
                    this.errorFieldData('email', window.trans('custom.email_not_correct'));
                } else {
                    this.clearError(email);
                }
            },
            errorFieldData(field_name, error){
                $('[name="'+field_name+'"]').addClass('text-danger border-danger');
                let $small_error = $('<small/>').addClass('text-danger border-danger').html(error);
                $('[name="'+field_name+'"]').after($small_error);
            },
            clearError(field_name){
                $('[name="'+field_name+'"]').removeClass('text-danger border-danger');
                $('[name="'+field_name+'"]').siblings('small').remove();
            },
            cancel_verify(company_id) {
                var app = this;
                axios.put('/api/v1/companies/' + app.companyId, {verified: 0} ).then(() => {
                    app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>' + window.trans('custom.company_verification.company_unverified'), {'html':true, okText:'ok'}).then((dialog) =>
                    {
                        window.createCookie('dont_show_verificate_ivite', 1, 1);
                        window.location.reload();
                    });
                }).catch(() => {
                    app.$dialog.alert(window.trans('custom.error_update_company'));
                });
            },
            request_verify(company_id) {
                let required_fields_filled = 1;
                this.verification_fields_list.forEach(field_name => {
                    if ($('input[name="' + field_name + '"]').val() == '') {
                        required_fields_filled = 0;
                    }
                });
                if (!required_fields_filled) {
                    this.$dialog.alert(window.trans('custom.company_verification.not_all_fields_filled_for_verification'));
                } else {
                    var app = this;
                    axios.post('/api/v1/companies/' + this.companyId + '/verify_request').then(() => {
                        app.$dialog.alert('<div class="mt-4"><img style="margin-left: -55px;" src="/images/email/verefication_request_sent.png"></div><div class="text-center mb-4 mt-4" style="font-size:28px;font-weight:400;line-height: 40px;">' + trans('custom.company_verification.company_verify_request_sent') + '</div>' +
                            '<div style="font-size: 16px;font-weight: 400;line-height: 24px;text-align: justify;">'+ trans('custom.company_verification.company_verify_request_sent_message') + '</div>', {
                            'html': true,
                            okText: 'Ok',
                        });
                    }).catch((resp) => {
                        if (resp.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                        } else {
                            app.$dialog.alert(window.trans('custom.company_verification.company_verify_request_failed'));
                        }
                    });
                }
            },
            message_owner(company_id){
                var app = this;
                if($('#message_to_admins').val() != ''){
                    axios.post('/api/v1/companies/' + this.companyId + '/message', {
                        message:$('#message_to_admins').val(),
                    }).then(() => {
                        app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>' + window.trans('custom.company_verification.message_sent_successfully'), {'html':true, okText:'ok'});
                        $('#message_to_admins').val('');
                    }).catch((resp) => {
                        if (resp.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                        } else {
                            app.$dialog.alert(window.trans('custom.company_verification.failed_sent_message'));
                        }
                    });
                }
            },
            imageUploaded(file) {
                this.company.logo = file;
            },
            saveForm() {
                event.preventDefault();

                let verified_fields_been_changes = 0;
                var app = this;
                this.verification_fields_list.forEach(field_name => {
                    if (this.company[field_name] !== this.company_base_verify_data['old_' + field_name]) {
                        verified_fields_been_changes = 1;
                    }
                });

                var saving_company = this.company;
                if(saving_company.active_until == 'Invalid Date' || saving_company.active_until == null){
                    delete saving_company.active_until;
                } else{
                    saving_company.active_until = new Date(saving_company.active_until);
                }
                
                if (verified_fields_been_changes && app.company.verified > 0){
                    this.$dialog.confirm(window.trans('custom.company_verification.update_data_will_broke_verification'))
                        .then(function () {
                            saving_company.verified = 0;
                            axios.put('/api/v1/companies/' + app.companyId, saving_company).then(() => {
                                app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>' + window.trans('custom.data_saved_successfully'), {'html':true, okText:'ok'}).then((dialog) =>
                                {
                                    window.createCookie('dont_show_verificate_ivite', 1, 1);
                                    window.location.reload();
                                });
                            }).catch((resp) => {
                                if (resp.response.status === 401) {
                                    window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                                } else {
                                    app.$dialog.alert(window.trans('custom.error_update_company'));
                                }
                            });
                        })
                        .catch(function () {
                            console.log('Clicked on cancel')
                        });

                } else {
                    axios.put('/api/v1/companies/' + this.companyId, saving_company).then(() => {
                        app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>' + window.trans('custom.data_saved_successfully'), {'html':true, okText:'ok'}).then((dialog) =>
                        {
                            window.createCookie('dont_show_verificate_ivite', 1, 1);
                            window.location.reload();
                        });
                    }).catch((resp) => {
                        if (resp.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                        } else {
                            app.$dialog.alert(window.trans('custom.error_update_company'));
                        }
                    });
                }
            },
            approve(id, index) {
                axios.put('/api/v1/memberships/approve', {invoice_id: id})
                    .then(() => {
                        this.invoices[index].payment_status = 'Paid';
                    })
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
                axios.get('/api/v1/memberships/invoices?page=' + this.invoices.current_page + '&company_id=' + this.companyId).then(resp => {
                    this.invoices = resp.data
                }).catch((resp) => {
                    if (resp.response.status === 401) {
                        window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                    } else {
                        app.$dialog.alert(window.trans('custom.error_update_company'));
                    }
                });
            }
        }
    }
</script>
<style>
    .selected_values .selected-tag{
        display: inline-block;
        align-items: center;
        width: fit-content;
        background-color: #f0f0f0;
        border: 1px solid #ccc;
        border-radius: 4px;
        color: #333;
        line-height: 1.42857143;
        margin: 4px 2px 0;
        padding: 7px 10px 7px;
        transition: opacity .25s;
    }
    .selected_values .selected-tag .close {
        margin-left: 2px;
        font-size: 1.25em;
        appearance: none;
        padding: 0;
        cursor: pointer;
        background: 0 0;
        border: 0;
        font-weight: 700;
        line-height: 1;
        color: #000;
        text-shadow: 0 1px 0 #fff;
        filter: alpha(opacity=20);
        opacity: .2;
    }

</style>

<template>
    <div>
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group mr-2">
                    <router-link :to="{name: 'companyIndex'}" class="btn back_btn"> {{ trans('custom.back') }}</router-link>
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
                                        :placeholder="isListLoading ? 'Loading...' : isListError ? 'Cannot get any industry...' : 'Type for search...'"
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
                                        :clearable="false"
                                        :placeholder="isListLoading ? 'Loading...' : isListError ? 'Cannot get any specialization...' : 'Type for search...'"
                                ></v-select>
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
                                        :placeholder="isListLoading ? 'Loading...' : isListError ? 'Cannot get any employee...' : 'Type for search...'"
                                ></v-select>
                            </div>
                        </div>
                        <div class="upload_styled">
                            <simple-file-upload @uploaded="imageUploaded"></simple-file-upload>
                            <!-- <span class="custom_upload_btn btn">{{ trans('custom.choose_logo') }}</span> -->
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.description') }}</label>
                                <textarea v-model="company.description" class="form-control"></textarea>
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
                         <!--   <div class="col-sm-12 col-md-6 form-group">
                                <label class="control-label">{{ trans('custom.company_bank') }}</label>
                                <input name="company_bank" type="number" v-model="company.company_bank" class="form-control">
                            </div>-->
                        </div>
                        <!--<div class="row">
                            <div class="col-sm-12 col-md-6 form-group">
                                <label class="control-label">{{ trans('custom.company_account_number') }}</label>
                                <input  v-on:change="isValidIBANNumber()" name="company_account_number" type="text" v-model="company.company_account_number" class="form-control">
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
                                <input v-on:change="isValidUrl('website')" name="website" type="text" v-model="company.website" class="form-control">
                            </div>
                            <div class="col-sm-12 col-md-6 form-group">
                                <label class="control-label">{{ trans('custom.email') }}*</label>
                                <input v-on:change="isValidEmail()" name="email" type="text" v-model="company.email" class="form-control">
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
                        <div class="row">
                            <div class="col-sm-12 form-group text-left">
                                <button class="btn btn-primary width-150">{{ trans('custom.create') }}</button>
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
                isListLoading: true,
                isListError: false,
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
                    office_address:'',
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
            // $('.specialization_wrapp input').attr('placeholder', 'Loading...');
            var app = this;
            axios.get('/api/v1/user')
                .then((resp) => {
                    this.user.role = resp.data.role;
                })
                .catch(function (resp) {
                    if (resp.response.status === 401) {
                        window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                    } else {
                        app.$dialog.alert("Could not load user")
                    }
                });

            axios.get('/api/v1/memberships')
                .then((response) => {
                    this.memberships = response.data;
                    this.company.membership_id = response.data[0].id;
                });

            axios.get('/api/v1/companies/list_values')
                .then((response) => {
                    this.industries = response.data.data.industries;
                    this.employee_numbers = response.data.data.employee_number;
                    this.specializations_data = response.data.data.specializations;
                    this.specializations = response.data.data.specializations.slice(0, 10);
                    this.isListLoading = false;
                })
                .catch(() => this.isListError = false);

            setTimeout(function(){
                $('.specialization_wrapp .vs__actions .clear').click();
            }, 100);


            $('body').on('keyup', '.specialization_wrapp input[type="search"]', function (event) {
                var ignore_keycodes = [37,38,39,40,33,34,35,36];
                if (ignore_keycodes.indexOf(event.keyCode) !== -1){
                    return;
                }
                var val = $(this).val();
                let new_data = app.specializations_data.filter(function (specialization) {
                    return specialization.title.toLowerCase().indexOf(val.toLowerCase()) !== -1
                });
                if (new_data.length > 0){
                    app.specializations = new_data.slice(0, 10);
                } else {
                    app.specializations = [];
                }
            })

            $('body').on('click', '.selected_values .close', function (event) {
                var $this = $(this);
                let value = $this.parents('.selected-tag:first').data('id');
                $this.parents('.selected-tag:first').remove();
                const index = app.company.specialization.indexOf(value);
                if (index > -1) {
                    app.company.specialization.splice(index, 1);
                }
            });

            $('body').on('click', '.custom_upload_btn', function(){
                $(this).parents('.upload_styled:first').find('input[type="file"]:first').click();
            });

            $('body').on('click', '.btn.btn-danger.mt-2', function(){
                $(this).parents('.upload_styled:first').find('input[type="file"]:first').val('');
            });


        },

        methods: {
            imageUploaded(file) {
                this.company.logo = file;
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
                let input = this.company[field_name];
                if(!(/^https?:\/\/(?:www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)$/gm.test(input))){
                    this.errorFieldData(field_name, window.trans('custom.url_not_correct'));
                } else {
                    this.clearError(field_name);
                }
            },
            isValidEmail(){
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
            saveForm() {
                event.preventDefault();
                var app = this;
                axios.post('/api/v1/companies', this.company)
                    .then(() => {
                        window.updatePersonalPermissions();
                        this.$router.push({path: '/companies'});
                    })
                    .catch((resp) => {
                        if (resp.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                        } else {
                            app.$dialog.alert(window.trans('custom.error_create_company'));
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
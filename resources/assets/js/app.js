
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
import VueRouter from 'vue-router'
import VeeValidate from 'vee-validate'
import { VTooltip, VPopover, VClosePopover } from 'v-tooltip'
import {Tabs, Tab} from 'vue-tabs-component'
import _ from 'lodash'
import VCalendar from 'v-calendar'
import CKEditor from '@ckeditor/ckeditor5-vue'
import vSelect from 'vue-select'
import BackToTop from 'vue-backtotop'
import VueSpinners from 'vue-spinners'
import VuejsDialog from "vuejs-dialog"

import 'vuejs-dialog/dist/vuejs-dialog.min.css';

Vue.directive('tooltip', VTooltip);
Vue.directive('close-popover', VClosePopover);
Vue.component('v-popover', VPopover);
Vue.component('tabs', Tabs);
Vue.component('tab', Tab);
Vue.component('mapbox', require('mapbox-gl-vue/src/components/Mapbox'));
Vue.component('v-select', vSelect)
window.mapboxgl = require('mapbox-gl');
Vue.use(VueSpinners)

window.Vue.use(VeeValidate);
window.Vue.use(VueRouter);
Vue.component('simple-file-upload',require('./components/FileUpload'));
Vue.component('storage-status', require('./components/StorageStatus'));
Vue.component('modal-file-upload', require('./components/files/ModalUploadFile'));
Vue.use(VCalendar);
Vue.use(CKEditor);
Vue.use(BackToTop);
Vue.use(VuejsDialog)

Vue.prototype.trans = string => _.get(window.i18n, string);
window.trans = string => _.get(window.i18n, string);

import Index from './components/Index';

import ProjectPublic from './components/projects/ProjectPublic';
import ProjectIndex from './components/projects/ProjectIndex';
import ProjectCreate from './components/projects/ProjectCreate';
import ProjectEdit from './components/projects/ProjectEdit';
import ProjectAddItem from './components/projects/ProjectAddItem';
import ProjectVisibility from './components/projects/ProjectVisibility';
import ProjectItems from './components/projects/ProjectItems';
import ProjectEditItems from './components/projects/ProjectEditItem';
import ProjectAddContact from './components/projects/ProjectAddContact';
import ProjectEditContact from './components/projects/ProjectEditContact';

import UserSettings from './components/user/UserSettings';
import UserList from './components/user/UserList';
import UserEdit from './components/user/UserEdit';
import UserCreate from './components/user/UserCreate';
import UserStats from './components/user/UserStats';

import CompanyIndex from './components/companies/CompanyIndex';
import CompanyEdit from './components/companies/CompanyEdit';
import CompanyProfile from './components/companies/CompanyProfile';
import CompanyCreate from './components/companies/CompanyCreate';
import CompanyUsers from './components/companies/CompanyUsers';
import CompanyUserInvite from './components/companies/CompanyUserInvite';

import NotificationIndex from './components/notifications/NotificationIndex';
import NotificationCreate from './components/notifications/NotificationCreate';
import NotificationEdit from './components/notifications/NotificationEdit';

import Memberships from './components/Memberships';
import FilesList from './components/files/FilesList';
import Transfers from "./components/Transfers";

Vue.component('public', ProjectPublic);

const routes = [
    {path: '/', component: Index, name: 'index'},
    {path: 'logouted/:message', component: Index, name: 'unauthorized'},

    {path: '/:id/projects', component: ProjectIndex, name: 'projectsIndex'},
    {path: '/:id/projects/create', component: ProjectCreate, name: 'createProject'},
    {path: '/:company_id/projects/:id/edit', component: ProjectEdit, name: 'editProject'},
    {path: '/:company_id/projects/:id/visibility', component: ProjectVisibility, name: 'editProjectVisibility'},
    {path: '/:company_id/projects/:id/item/add', component: ProjectAddItem, name: 'addProjectItem'},
    {path: '/:company_id/projects/:id/items', component: ProjectItems, name: 'projectItems'},
    {path: '/:company_id/projects/:project_id/items/:id', component: ProjectEditItems, name: 'editProjectItem'},
    {path: '/:company_id/projects/:id/contact/add', component: ProjectAddContact, name: 'addProjectContact'},
    {path: '/:company_id/projects/:project_id/contact/:id', component: ProjectEditContact, name: 'editProjectContact'},

    {path: '/settings', component: UserSettings, name: 'userSettings'},
    {path: '/users', component: UserList, name: 'userList'},
    {path: '/users/:id/edit', component: UserEdit, name: 'userEdit'},
    {path: '/users/create', component: UserCreate, name: 'userCreate'},
    {path: '/users/stats', component: UserStats, name: 'userStats'},

    {path: '/companies', component: CompanyIndex, name: 'companyIndex'},
    {path: '/companies/:id/edit', component: CompanyEdit, name: 'companyEdit'},
    {path: '/companies/:id/profile', component: CompanyProfile, name: 'companyProfile'},
    {path: '/companies/:id/users', component: CompanyUsers, name: 'companyUsers'},
    {path: '/companies/create', component: CompanyCreate, name: 'companyCreate'},
    {path: '/companies/:id/invite', component: CompanyUserInvite, name: 'companyUserInvite'},

    {path: '/notifications', component: NotificationIndex, name: 'notificationIndex'},
    {path: '/notifications/:id/edit', component: NotificationEdit, name: 'notificationEdit'},
    {path: '/notifications/create', component: NotificationCreate, name: 'notificationCreate'},

    {path: '/memberships', component: Memberships, name: 'memberships'},
    {path: '/files', component: FilesList, name: 'filesList'},
    {path: '/transfers', component: Transfers, name: 'transfers'},
]

const router = new VueRouter({routes})

const app = new Vue({
    router,
    el: '#app',

    // Notifications loader
    data() {
        return {
            notifications: 0,
            reg_phone: '',
        }
    },
    mounted() {
        if (window.authorised) {
            this.loadData();
            this.markMenu(router);

            window.initiatePageLoad();

            setInterval(() => {
                window.updatePersonalPermissions();
            }, 30000);


            var app = this;

            this.windowSizeRedirect(router.history.current);

            router.afterEach((to, from) => {
                this.windowSizeRedirect(to, from);
            })

            router.afterEach((to, from) => {
                this.markMenu(to);
            })

            $('body').on('hidden.bs.modal', function(){
                window.clearmodalHash();
                window.rebuildPageLoad();
            });


            if (window.env != 'local') {
                setInterval(() => {
                    this.loadData()
                }, 5000);
            }

            setTimeout(() => {
                $('.router-link-exact-active').length > 0 ? $('.router-link-exact-active').addClass('custom_selected') : $('.router-link-active').length > 1?$('.router-link-active:not(.project_nav)').addClass('custom_selected'):$('.router-link-active').addClass('custom_selected');
                if ($('.custom_selected:first').hasClass('project_nav')){
                    $('.project_nav').addClass('custom_selected');
                }
                if ($('.custom_selected:first').hasClass('user_nav')){
                    $('.user_nav').addClass('custom_selected');
                }
                if ($('.custom_selected:first').hasClass('file_nav')){
                    $('.file_nav').addClass('custom_selected');
                }
                if ($('.custom_selected:first').hasClass('company_nav')){
                    $('.company_nav').addClass('custom_selected');
                }
            }, 150);

            var  last_sent_activity = Date.now();
            var  last_active_stamp = Date.now();

            var oldOpen = XMLHttpRequest.prototype.open;
            window.openHTTPs = 0;
            XMLHttpRequest.prototype.open = function(method, url, async, user, pass) {
                window.openHTTPs++;
                if (['/api/v1/statistics/online',
                    '/api/v1/notifications/unseen',
                    '/api/v1/storage',
                    '/api/v1/user/personal_permissions'].indexOf(url) == -1){
                    last_active_stamp = Date.now();
                }
                oldOpen.call(this, method, url, async, user, pass);
            }

            axios.post('/api/v1/statistics/online');

            setInterval(() => {
                if (last_sent_activity != last_active_stamp){
                    axios.post('/api/v1/statistics/online').then(() => {
                        last_sent_activity = last_active_stamp;
                    })
                }

            }, 30000);

            $('body').on('click', '#navbarSupportedContent .mobile-view a', function(){
                $('.navbar-toggler').click();
            });
            var app = this;

            let blocked_approve_data = window.readCookie('blocked_admin_approvement');
            if (typeof blocked_approve_data != 'undefined'){
                $('body').trigger('overlimit.membership',blocked_approve_data);
                window.eraseCookie('blocked_admin_approvement');
            }

            $('body').on('overlimit.membership', function(e, o){
                app.$dialog.alert('<div class="mt-4"><img style="margin-left: -25px;width: 55%;" src="/images/popup/operation_breaks_the_linits.svg"></div><div class="text-center mb-4 mt-4" style="font-size:28px;font-weight:400;line-height: 40px;">' + trans('custom.dear_user') + ',</div>' +
                    '<div style="font-size: 16px;font-weight: 400;line-height: 24px;text-align: justify;">' + trans('custom.the_requested_action_cannot_be_completed_because_of_limits') + '</div>'+
                    '<div style="text-align: left; padding-left: 2rem;font-size: 1rem;line-height: 1.5rem; margin-top: 0.2rem; margin-bottom: 0.2rem;"><li class="mb-1">' + o.space_limit + ' GB</li>' +
                    '<li class="mb-1">' + o.managers_limit + ' ' + trans('custom.managers') +'</li>'+
                    '<li class="mb-1">' + o.visitors_limit + ' ' +  trans('custom.visitors') +'</li>'+
                    '<li class="mb-1">' + o.projects_limit + ' ' +  trans('custom.projects') +'</li></div>'+
                    '<div style="font-size: 16px;font-weight: 400;line-height: 24px;text-align: justify;">' + o.error_message + '</div>', {
                    'html': true,
                    okText: 'Ok',
                });
            });


            $('.nav_search_input input').on('change', function(){
                $(this).trigger('input');
            });

            $('body').on('functional.coming.soon', function(){
                app.$dialog.alert('<div class="mt-4"><img src="/images/popup/feature_coming_soon.png" style="width: 55%;"></div><div class="text-center mb-4 mt-4" style="font-size:28px;font-weight:400;line-height: 40px;">' + trans('custom.functional_coming_soon') + '</div>'
                    , {
                        'html': true,
                        okText: 'Ok',
                    });
            });


            $('body').on('change.project.category', function(e, obj){
                if (typeof obj != 'undefined' && typeof obj.companyId != 'undefined' ) {
                    axios.put('/api/v1/usersettings', {
                        'settings_key': 'projects_page_settings',
                        'settings_value': {
                            'companyId': obj.companyId,
                        }
                    }).then(function (resp) {
                        if (router.history.current.name != 'index' && router.history.current.name != 'projectsIndex') {
                            router.push({name: 'projectsIndex', params: {id: obj.companyId}})
                        }
                    }).catch(function (resp) {
                        console.log('Failed to update View settings');
                    });
                }
            });

            $('body').on('click', '#navbarSupportedContent .nav-item .nav-link, .mobile_navigation .nav-item .nav-link', function(){
                $('.custom_selected').removeClass('custom_selected');
                $(this).addClass('custom_selected');
            });

            $(window).on('scroll', function () {
                if (window.innerWidth < 681) {
                    if ($(this).scrollTop() > 0) {
                        $('.bottom-companies').fadeOut();
                    } else {
                        $('.bottom-companies').fadeIn();
                    }
                }
            });
        }


    },

    methods: {
        windowSizeRedirect(router, from){
            switch (router.name) {
                case 'companyEdit':
                    if (window.innerWidth > 680 && !window.perosnal_permissions.is_super_user) {
                        this.$router.push({
                            name: from.name,
                            params: {doc_hash: router.path, company_edit: router.params.id}
                        });
                    }
                    break;
                case 'userSettings':
                    setTimeout(() => {
                        if (typeof router.params.company_edit != 'undefined') {
                            $('body').trigger('modal_params', {name: 'company_edit', val: router.params.company_edit});
                        }
                    }, 250);

                    break;
                case 'companyProfile':
                    setTimeout(() => {
                        if (typeof router.params.company_edit != 'undefined') {
                            $('body').trigger('modal_params', {name: 'company_edit', val: router.params.company_edit});
                        }
                        if (typeof router.params.doc_hash != 'undefined') {

                            window.addHash('/#mr' + router.params.doc_hash);
                            window.rebuildPageLoad();

                        } else {
                            window.rebuildPageLoad();
                        }
                    }, 250);
                    break
                case 'companyList':
                    setTimeout(() => {
                        if (typeof router.params.company_edit != 'undefined') {
                            $('body').trigger('modal_params', {name: 'company_edit', val: router.params.company_edit});
                        }
                        if (typeof router.params.company_create != 'undefined') {
                            $('body').trigger('modal_params', {name: 'company_create', val: 1});
                        }

                        if (typeof router.params.doc_hash != 'undefined') {

                            window.addHash('/#mr' + router.params.doc_hash);
                            window.rebuildPageLoad();

                        } else {
                            window.rebuildPageLoad();
                        }
                    }, 250);

                    break;
                case 'companyCreate':
                    if (window.innerWidth > 680) {
                        this.$router.push({name: 'companyList', params: {doc_hash: router.path, company_create: 1}});
                    }
                    break;
                case 'editProject':
                    if (window.innerWidth > 680) {
                        let params = from.params;
                        params.doc_hash = router.path;
                        params.company_id = from.params.company_id;
                        params.project_edit = router.params.id;
                        this.$router.push({ name: from.name,params: params});
                    }
                    break;
                case 'createProject':
                    if (window.innerWidth > 680) {
                        this.$router.push({name: 'projectsIndex', params: {doc_hash: router.path, project_create: 1}});
                    }
                    break;
                case 'projectsIndex':
                    setTimeout(() => {
                        if (typeof router.params.project_edit != 'undefined') {
                            $('body').trigger('modal_params', {name: 'project_edit', val: router.params.project_edit});
                        }
                        if (typeof router.params.project_create != 'undefined') {
                            $('body').trigger('modal_params', {name: 'project_create', val: 1});
                        }
                        if (typeof router.params.item_create != 'undefined') {
                            $('body').trigger('modal_params', {name: 'item_create', val: router.params.id});
                        }
                        if (typeof router.params.doc_hash != 'undefined') {
                            window.addHash('/#mr' + router.params.doc_hash);
                            window.rebuildPageLoad();
                        } else {
                            window.rebuildPageLoad();
                        }
                    }, 250);
                    break;
                case 'editProjectItem':
                    var params = from.params;
                    let route_forvard = from.name;
                    let company_id = from.params.company_id;
                    params.doc_hash = router.path;
                    params.type = router.params.type;
                    params.item_edit = router.params.id;
                    params.company_id = router.params.company_id;
                    params.project_id = router.params.project_id;
                    params.id = router.params.project_id;

                    if (typeof router.params.from_search != 'undefined') {
                        route_forvard = 'projectItems';
                        company_id = router.params.company_id;
                    }
                    this.$router.push({
                        name: route_forvard,
                        params: params
                    });
                    break;
                case 'addProjectItem':
                    //   if (window.innerWidth > 680) {
                    let params = from.params;
                    params.doc_hash = router.path;
                    params.type = router.params.type;
                    params.item_create = 1;
                    params.company_id = router.params.company_id;
                    params.id = router.params.id;
                    this.$router.push({
                        name: from.name,
                        params: params
                    });
                    //   }
                    break;
                case 'filesList':
                    setTimeout(() => {
                        if (typeof router.params.item_create != 'undefined') {
                            $('body').trigger('modal_params', {name: 'item_create', company_id:router.params.company_id, val: 1, project_id: router.params.id, type: router.params.type});
                        }
                        if (typeof router.params.item_edit != 'undefined') {
                            $('body').trigger('modal_params', {
                                name: 'item_edit', val: router.params.item_edit,type: router.params.type,project_id: router.params.project_id});
                        }
                        if (typeof router.params.doc_hash != 'undefined') {
                            window.addHash('/#mr' + router.params.doc_hash);
                            window.rebuildPageLoad();
                        } else {
                            window.rebuildPageLoad();
                        }
                    }, 250);
                    break;
                case 'projectItems':
                    setTimeout(() => {
                        if (typeof router.params.project_edit != 'undefined') {
                            $('body').trigger('modal_params', {name: 'project_edit', val: router.params.project_edit});
                        }
                        if (typeof router.params.item_edit != 'undefined') {
                            $('body').trigger('modal_params', {
                                name: 'item_edit', val: router.params.item_edit,type: router.params.type});
                        }
                        if (typeof router.params.item_create != 'undefined') {
                            $('body').trigger('modal_params', {name: 'item_create', val: 1, project_id: router.params.id, type: router.params.type});
                        }
                        if (typeof router.params.doc_hash != 'undefined') {
                            window.addHash('/#mr' + router.params.doc_hash);
                            window.rebuildPageLoad();
                        } else {
                            window.rebuildPageLoad();
                        }
                    }, 250);
                    break;
                case 'userCreate':
                    if (window.innerWidth > 680) {
                        this.$router.push({name: 'userList', params: {doc_hash:router.path, user_create:1}});
                    }
                    break;
                case 'userEdit':
                    if (window.innerWidth > 680){
                        let params = from.params;
                        params.doc_hash = router.path;
                        params.user_edit=router.params.id;
                        this.$router.push({name: from.name, params: params});
                    }
                    break;
                case 'userProfile':
                    if (typeof router.params.from_search != 'undefined') {
                        this.$router.push({name: 'userList', params: {user_profile:1, user_id:router.params.id}});
                    }
                    break;
                case 'userList':
                    setTimeout(() => {
                        if (typeof router.params.user_create != 'undefined') {
                            $('body').trigger('modal_params', {name: 'user_create', val: 1});
                        }
                        if (typeof router.params.user_edit != 'undefined') {
                            $('body').trigger('modal_params', {name: 'user_edit', val: router.params.user_edit});
                        }
                        if (typeof router.params.user_profile != 'undefined') {
                            $('body').trigger('modal_params', {name: 'user_profile', val: router.params.user_id});
                        }
                        if (typeof router.params.doc_hash != 'undefined') {
                            document.location.hash += '/#mr' + router.params.doc_hash;
                            window.rebuildPageLoad();
                        } else {
                            window.rebuildPageLoad();
                        }
                    }, 250);
                    break;
                case 'companyUserInvite':
                    if (window.innerWidth > 680) {
                        this.$router.push({name: 'companyUsers', params: {doc_hash:router.path, id:router.params.id, user_invite: router.params.id}});
                    }
                    break;
                case 'searchPage':
                    setTimeout(() => {
                        if (typeof router.params.user_edit != 'undefined') {
                            $('body').trigger('modal_params', {name: 'user_edit', val: router.params.user_edit});
                        }
                        if (typeof router.params.project_edit != 'undefined') {
                            $('body').trigger('modal_params', {name: 'project_edit', val: router.params.project_edit});
                        }
                        if (typeof router.params.company_edit != 'undefined') {
                            $('body').trigger('modal_params', {name: 'company_edit', val: router.params.company_edit});
                        }
                        if (typeof router.params.item_create != 'undefined') {
                            $('body').trigger('modal_params', {name: 'item_create', val: 1, project_id: router.params.id, type: router.params.type});
                        }
                        if (typeof router.params.item_edit != 'undefined') {
                            $('body').trigger('modal_params', {
                                name: 'item_edit', val: router.params.item_edit,type: router.params.type,project_id: router.params.project_id});
                        }
                        if (typeof router.params.doc_hash != 'undefined') {
                            window.addHash('/#mr' + router.params.doc_hash);
                            window.rebuildPageLoad();
                        } else {
                            window.rebuildPageLoad();
                        }

                    }, 250);
                    break;
                case 'companyUsers':
                    setTimeout(() => {
                        if (typeof router.params.user_edit != 'undefined') {
                            $('body').trigger('modal_params', {name: 'user_edit', val: router.params.user_edit});
                        }
                        if (typeof router.params.user_invite != 'undefined') {
                            $('body').trigger('modal_params', {name: 'user_invite', val: router.params.user_invite});
                        }
                        if (typeof router.params.doc_hash != 'undefined') {
                            window.addHash('/#mr' + router.params.doc_hash);
                            window.rebuildPageLoad();
                        } else {
                            window.rebuildPageLoad();
                        }
                    }, 250);
                    break;
                case 'addProjectVisibility':
                    if (window.innerWidth > 680){
                        this.$router.push({name: 'editProjectVisibility', params: {doc_hash:router.path, project_visibility: router.params.id}});
                    }
                    break;
                case 'editProjectVisibility':
                    setTimeout(() => {
                        if (typeof router.params.project_visibility != 'undefined') {
                            $('body').trigger('modal_params', {
                                name: 'project_visibility',
                                val: router.params.project_visibility
                            });
                        }
                        if (typeof router.params.doc_hash != 'undefined') {
                            window.addHash('/#mr' + router.params.doc_hash);
                            window.rebuildPageLoad();
                        } else {
                            window.rebuildPageLoad();
                        }
                    }, 250);
                    break;
                default:
                    setTimeout(() => {
                        window.clearmodalHash();
                        if (typeof router.params.doc_hash != 'undefined') {
                            window.addHash('/#mr' + router.params.doc_hash);
                            window.rebuildPageLoad();
                        } else {
                            window.rebuildPageLoad();
                        }
                    }, 250);
                    break;

            }
        },
        markMenu(router){
            $('.navbar-laravel .favorites_icon.active, .navbar-laravel .garbage_icon.active').removeClass('active');
            console.log(router.name);
            switch (router.name) {
                case 'companyList':
                    setTimeout(() => {
                        $('.custom_selected').removeClass('custom_selected');
                        $('#app .company_nav').addClass('custom_selected');
                    }, 250);
                    break;
                case 'userList':
                    setTimeout(() => {
                        $('.custom_selected').removeClass('custom_selected');
                        $('#app .user_nav').addClass('custom_selected');
                    }, 250);
                    break;
                case 'filesList':
                    setTimeout(() => {
                        $('.custom_selected').removeClass('custom_selected');
                        $('#app .file_nav').addClass('custom_selected');
                    }, 250);
                    break;
                case 'index':
                case 'projectsIndex':
                    setTimeout(() => {
                        $('.custom_selected').removeClass('custom_selected');
                        $('#app .project_nav').addClass('custom_selected');
                    }, 250);
                    break;
                default:
                    $('.custom_selected').removeClass('custom_selected');
                    break;
            }
        },
        updateRegPhone(event){
            $('#reg_phone').val(event.formattedNumber);
            $('body').trigger('reg.phone.updated', {phone:event});
        },
        loadData() {
            axios.get('/api/v1/notifications/unseen').then(response => function(){this.notifications = response.data.length; $('.notifications_point > span, .notification-informer').show()});
        }
    }
})

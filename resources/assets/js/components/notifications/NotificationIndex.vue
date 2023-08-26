<template>
    <div  class="notification_page">
        <div class="row mt-4">
            <div class="col-sm-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                    <span class="special_title">{{ trans('custom.notifications') }}</span>
                </div>
            </div>
            <div class="col-sm-6"></div>
            <div class="col-sm-2" v-if="allowedDisplay('create_notification')">
                <router-link :to="{name: 'notificationCreate'}" class="btn btn-primary w-100">{{
                    trans('custom.create_new') }}
                </router-link>
            </div>
        </div>



        <tabs :options="{ useUrlFragment: false }"   ref="itemsTabs" @clicked="tabClicked" @changed="tabChanged">
            <tab :name="trans('custom.all')" id="all">
                <div v-if="!notifications" class="text-center">{{ trans('custom.no_notifications_found') }}</div>
                <table v-else class="table table-striped table-responsive-md">
                    <tbody>
                        <tr v-for="notification, index in notifications">
                            <td :class="{ 'unread' : notification.unread}">
                                <h4 class="title">{{ notification['title_' + language] }}</h4>
                                <p v-html="notification['content_' + language]"></p>
                            </td>
                            <td class="align-middle">
                                {{ notification.date }}
                            </td>
                            <td class="mt-3 float-right" v-if="allowedDisplay('all')">

                                <router-link :to="{name: 'notificationEdit', params: {id: notification.id}}" class="btn_edit_user">
                                </router-link>

                                <a v-on:click.prevent="deleteEntry(notification.id, index)" class="btn_delete_user" href="#"></a>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </tab>

            <tab :name="trans('custom.unread')" id="unread">
                <div v-if="!notifications_unread" class="text-center">{{ trans('custom.no_notifications_found') }}</div>
                <table v-else class="table table-striped table-responsive-md">
                    <tbody>
                    <tr v-for="notification, index in notifications_unread">
                        <td :class="{ 'unread' : notification.unread}">
                            <h4 class="title">{{ notification['title_' + language] }}</h4>
                            <p v-html="notification['content_' + language]"></p>
                        </td>
                        <td class="align-middle">
                            {{ notification.date }}
                        </td>
                        <td class="mt-3 float-right" v-if="allowedDisplay('all')">

                            <router-link :to="{name: 'notificationEdit', params: {id: notification.id}}" class="btn_edit_user">
                            </router-link>

                            <a v-on:click.prevent="deleteEntry(notification.id, index)" class="btn_delete_user" href="#"></a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </tab>
        </tabs>


    </div>
</template>

<script>
    export default {
        data() {
            return {
                notifications: [],
                notifications_unread:[],
                allowedDisplay: function(action) {
                    return window.allowedDisplay(action)
                },
                language: window.activeLanguage
            }
        },
        mounted() {
            axios.get('/api/v1/notifications').then((response) => {
                this.notifications = response.data;
                for(let notif_key in response.data){
                    if(response.data[notif_key].unread){
                        this.notifications_unread.push(response.data[notif_key]);
                    }
                }
            });

            axios.post('/api/v1/notifications/set_seen');
            window.notifications = 0;
        },
        methods: {
            deleteEntry(id, index) {
                var app = this;
                app.$dialog.confirm(window.trans('custom.delete_confirm'))
                    .then(function () {
                        axios.delete('/api/v1/notifications/' + id).then(() => {
                            app.notifications.splice(index, 1);
                        }).catch((error) => {
                            if (error.response.status === 401) {
                                window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                            } else {
                                app.$dialog.alert(window.trans('custom.error_delete_company'));
                            }
                        });
                    })
                    .catch(function () {
                        console.log('Clicked on cancel')
                    });
            },
            tabClicked (selectedTab) {
                console.log('Current tab re-clicked:' + selectedTab.tab.name);
            },
            tabChanged (selectedTab) {
                let tempUrl = decodeURI(window.location.hash).split('#')[1];
                window.location.hash = tempUrl + '#' + selectedTab.tab.id.toLowerCase();
            },
        }
    }
</script>

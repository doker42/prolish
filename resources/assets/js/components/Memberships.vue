<template>


    <div class="container">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-5 border-bottom">
            <h1 class="h2">{{ trans('custom.active_subscription') }}</h1>
        </div>

        <div class="card my-3" v-if="this.activeSubscription.id">
            <div class="card-body">
                <table class="table table-striped table-responsive-md">
                    <thead>
                        <th>{{ trans('custom.plan') }}</th>
                        <th>{{ trans('custom.next_payment') }}</th>
                        <th>{{ trans('custom.status') }}</th>
                        <th>{{ trans('custom.price') }}</th>
                        <th>{{ trans('custom.actions') }}</th>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ this.activeSubscription['title'] }}</td>
                            <td>{{ this.activeSubscription['price'] == 0 ? '-' : this.activeSubscription['next_payment'] }}</td>
                            <td>
                                <template v-if="this.activeSubscription['status'] == 'custom.active'">
                                    <span class="badge badge-success">{{ trans(this.activeSubscription['status']) }}</span>
                                </template>
                                <template v-else>
                                    <span class="badge badge-danger">{{ trans(this.activeSubscription['status']) }}</span>
                                </template>
                            </td>
                            <td>{{ this.activeSubscription['price'] }}&euro;</td>
                            <td>
                                <a href="" data-toggle="modal" data-target="#upgradeModal" class="text-primary pr-3">{{ trans('custom.upgrade') }}</a>

                                <a href="mailto:info@3dskenesana.lv" v-if="this.activeSubscription['status'] != 'custom.active'" class="text-danger">
                                    {{ trans('custom.retry') }}
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
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


        <!-- Upgrade Modal -->
        <div class="modal fade" id="upgradeModal" role="dialog" ref="vuemodal">
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
                            <div class="card card-pricing text-center px-3" v-for="item in memberships">
                                <div class="bg-transparent card-header pt-5 border-0">
                                    <h1 class="font-weight-bold">
                                        {{ item.title }}
                                    </h1>
                                    <h4 class="h4 font-weight-normal text-primary text-center mb-0" data-pricing-value="30"><span class="price">{{ item.price }}</span>&euro;<span class="h6 text-muted ml-2">/ {{ trans('custom.month') }}</span></h4>
                                </div>
                                <div class="card-body pt-4 mb-4">
                                    <template v-if="item.active">
                                        <span class="btn btn-success disabled">Active</span>
                                    </template>

                                    <template v-else-if="item.id < activeId">
                                        <span class="btn btn-secondary disabled">N/A</span>
                                    </template>

                                    <template v-else>
                                        <a :href="'/memberships/' + item.id + '/payment/card'" class="btn btn-primary">Pay</a>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import VuePagination from './Pagination';

    export default {
        data() {
            return {
                memberships: [],
                invoices: {
                    total: 0,
                    from: 1,
                    to: 0,
                    current_page: 1,
                    data: []
                },
                activeId: 0,
                showPaid: 0,
                activeSubscription: []
            }
        },
        components: {
            VuePagination
        },
        mounted() {
            this.loadInvoices();

            axios.get('/api/v1/memberships')
                .then((response) => {
                    this.memberships = response.data;

                    for (let index in this.memberships) {
                        if (this.memberships[index].active) {
                            this.activeId = this.memberships[index].id;
                            this.activeSubscription = this.memberships[index]
                        }
                    }
                });
        },

        methods: {
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
                }).catch(() => {
                    app.$dialog.alert("Could not load invoices");
                });
            }
        }
    }
</script>

<style scoped>
    .card-pricing .list-unstyled li {
        padding: .5rem 0;
        color: #6c757d;
    }
</style>

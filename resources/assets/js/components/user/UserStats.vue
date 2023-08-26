
<template>
    <div>
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <span class="special_title">{{ trans('custom.statistics') }}</span>
        </div>

        <div class="row">
            <div class="col-xl-3 col-lg-6 col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="align-self-center">
                                    <h1 class="ml-3"><i class="fa fa-user text-warning float-left"></i></h1>
                                </div>
                                <div class="media-body text-right">
                                    <h3>{{ users.total }}</h3>
                                    <span>{{ trans('custom.total_users') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="align-self-center">
                                    <h1 class="ml-3"><i class="fa fa-building text-danger float-left"></i></h1>
                                </div>
                                <div class="media-body text-right">
                                    <h3>{{ totalCompanies }}</h3>
                                    <span>{{ trans('custom.total_companies') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="align-self-center">
                                    <h1 class="ml-3"><i class="fa fa-map text-primary float-left"></i></h1>
                                </div>
                                <div class="media-body text-right">
                                    <h3>{{ totalProjects }}</h3>
                                    <span>{{ trans('custom.total_projects') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="align-self-center">
                                    <h1 class="ml-3"><i class="fa fa-money text-success float-left"></i></h1>
                                </div>
                                <div class="media-body text-right">
                                    <h3>{{ totalIncome }}&euro;</h3>
                                    <span>{{ trans('custom.total_income') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card my-3">
            <div class="card-body">
                <div class="container">
                    <div class="row my-3">
                        <div class="col-sm-12 col-md-4">
                            <label class="font-weight-bold">{{ trans('custom.date') }}</label>
                            <div class="input-group">
                                <v-date-picker
                                        ref="datepicker"
                                        mode="range"
                                        v-model="selectedDates"
                                        @input="load"
                                        :max-date="new Date()"
                                        style="width: 80%">
                                    <template slot-scope="props">
                                        <input
                                                type="text"
                                                class="form-control"
                                                :value="props.inputValue"
                                                @change.native="props.updateValue($event.target.value)">
                                    </template>
                                </v-date-picker>
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <GChart
                            type="LineChart"
                            :data="chartData"
                            :options="chartOptions"
                    />

                    <table class="table table-striped mt-5">
                        <thead>
                        <tr>
                            <th scope="col">{{ trans('custom.full_name') }}</th>
                            <th scope="col">Device</th>
                            <th scope="col">IP</th>
                            <th scope="col">{{ trans('custom.date') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="statistic in statistics">
                            <th scope="row">{{ statistic.user }}</th>
                            <td>{{ statistic.device }}</td>
                            <td>{{ statistic.ip }}</td>
                            <td>{{ statistic.created_at }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { GChart } from 'vue-google-charts'

    export default {
        components: {
            GChart
        },
        data () {
            return {
                selectedDates: {
                    start: new Date(new Date().getTime() - (6 * 24 * 60 * 60 * 1000)),
                    end: new Date()
                },
                dataCollection: {
                    labels: ['default', 'default'],
                    datasets: [
                        {
                            label: 'Data One',
                            backgroundColor: '#f87979',
                            data: [2, 22]
                        }
                    ]
                },
                chartData: null,
                chartOptions: {
                    width: '100%',
                    legend: {position: 'none'}
                },
                users: [],
                selectedUser: 0,
                statistics: [],
                totalProjects: 0,
                totalCompanies: 0,
                totalIncome: 0.00
            }
        },
        mounted() {
            var app = this;
            axios.get('/api/v1/user/list').then(response => (this.users = response.data))
                .catch((error) => {
                    if (error.response.status === 401) {
                        window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                    } else {
                        app.$dialog.alert("Could not process request");
                    }
                });
            axios.get('/api/v1/statistics/numbers').then(response => {
                this.totalIncome = response.data.income;
                this.totalCompanies = response.data.companies;
                this.totalProjects = response.data.projects;
            });

            this.load()
        },
        methods: {
            load() {
                axios.get('/api/v1/statistics', {
                    params: {
                        'from': this.selectedDates.start.toDateString(),
                        'to': this.selectedDates.end.toDateString(),
                        'user': this.selectedUser
                    }
                }).then(response => {
                    this.statistics = response.data.log;
                    this.chartData = response.data.chart;
                })
            }
        }
    }
</script>

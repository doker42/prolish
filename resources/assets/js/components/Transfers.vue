<template>


    <div class="container">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-5 border-bottom">
            <h1 class="h2">{{ trans('custom.pending_transfers') }}</h1>
        </div>

        <div class="card my-3" v-for="project, index in projects">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-12 text-md-left text-center card-img-wrapper">
                        <img :src="project.image" class="img-fluid img-thumbnail" />
                    </div>

                    <div class="col-sm-12 col-md-6 text-md-left text-center">
                        <h3 class="text-dark">{{ project.title }}</h3>
                        <p class="mt-2 mb-1">{{ project.address }}</p>
                        <hr class="mt-1 mb-2" />
                        <p>{{ project.description }}</p>
                    </div>

                    <div class="col-sm-12 col-md-3 text-md-right text-center">
                        <a href="#" class="btn btn-xs btn-default btn-info">
                            <i class="fa fa-edit"></i>
                        </a>
                        <div class="mt-2">
                            <img :title="project.company.title" :src="project.company.logo" style="max-height: 50px; max-width:150px; vertical-align: text-bottom;" />
                        </div>
                    </div>
                </div>

                <div class="row pt-3">
                    <div class="col-sm-12 col-md-4 text-md-left text-center">
                        <a href="#" class="btn btn-success">
                            {{ trans('custom.open') }}
                        </a>
                    </div>

                    <div class="col-sm-12 col-md-8  text-md-right text-center">
                        <h3 class="d-inline align-text-top" v-for="type, files in project.summary">
                            <i :class="'fa text-dark '+ files"></i> {{ type.length }}
                        </h3>

                        <span class="btn btn-success" v-if="project.summary.length < 1 && project.gallery_items.length < 1">
                            {{ trans('custom.add_files') }}
                        </span>

                        <a href="#">
                            <h3 class="d-inline align-text-top" v-if="project.gallery_items > 0">
                                <i :class="'fa text-dark fa-image'"></i> {{ project.gallery_items }}
                            </h3>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                projects: [],
            }
        },
        mounted() {
            axios.get(`/api/v1/projects?per_page=2&belong_to=2&sort=id`).then(resp => {
                this.projects = resp.data.data;
            });
        },
        methods: {
        }
    }
</script>

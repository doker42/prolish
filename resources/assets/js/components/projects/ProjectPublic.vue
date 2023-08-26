
<template>
    <div>
        <div class="row pt-3 pb-2 mb-3 border-bottom">
            <div class="col-sm-12 col-md-10">
                <h1 class="h2 mb-0 d-inline align-middle">{{ companyName }}</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 col-md-8 mt-3">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search" v-model="search">
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                    </div>
                </div>
            </div>

            <div class="col-sm-12 col-md-4 mt-3 text-right">
                <div class="btn-group btn-group-toggle" data-toggle="buttons" @click="mapSwitch()">
                    <label class="btn btn-light btn-outline-secondary active">
                        <input type="checkbox" autocomplete="off"> <i class="fa fa-globe text-dark"></i>
                    </label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 mt-3">
                <mapbox
                        access-token="pk.eyJ1Ijoia2lrdW5jaGlrIiwiYSI6ImNqczBkZjJ1MjFkZmwzeW0xa2J6am92ZmgifQ.dtB6550NNaHP-ovRadTWsQ"
                        :map-options="{
                            style: 'mapbox://styles/kikunchik/cjs0dfrwe06pn1fpacsegdype',
                            center: [21.426,56.300],
                            zoom: 4.5,
                            scrollZoom: false
                        }"
                        :nav-control="{
                              show: false,
                              showZoom: true,
                              showCompass: false
                        }"
                        @map-load="onMapReady"
                        @map-click="mapClicked"
                        @mousemove="mapMouseMoved"
                        v-if="showMap && search.length < 1"
                ></mapbox>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 col-md-5 mt-3">
                <div class="input-group">
                    <label class="col-form-label">{{ trans('custom.sort_by') }}:</label>
                    <select class="form-control ml-1" v-model="sort">
                        <option value="0">{{ trans('custom.sort_by_title_asc') }}</option>
                        <option value="1">{{ trans('custom.sort_by_title_desc') }}</option>
                        <option value="2">{{ trans('custom.sort_by_created_asc') }}</option>
                        <option value="3">{{ trans('custom.sort_by_created_desc') }}</option>
                    </select>
                </div>
            </div>


            <div class="col-sm-12 col-md-7 mt-3 text-right">
                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                    <label class="btn btn-info active" @click="changeView('list')">
                        <input type="radio" name="options" autocomplete="off"> <i class="fa fa-list-ul"></i>
                    </label>
                    <label class="btn btn-info" @click="changeView('column')">
                        <input type="radio" name="options" autocomplete="off"> <i class="fa fa-th"></i>
                    </label>
                </div>
            </div>
        </div>

        <template v-if="!projects[0]">
            <div class="row">
                <div class="col-sm-12 text-center mt-4">{{ trans('custom.no_project_found') }}</div>
            </div>
        </template>

        <template v-else>
            <!-- List view -->

            <div v-if="view_type == 'list'" style="min-height: 60vh">
                <div class="card my-3" v-for="project, index in filteredProjects">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-12 text-md-left text-center card-img-wrapper">
                                <a href="/login"><img :src="project.image" class="img-fluid img-thumbnail" /></a>
                            </div>

                            <div class="col-sm-12 col-md-6 text-md-left text-center">
                                <a href="/login" class="h3 text-dark">{{ project.title }}</a>
                                <p class="mt-2 mb-1">{{ project.address }}</p>
                                <hr class="mt-1 mb-2" />
                                <p>{{ project.description }}</p>
                            </div>

                            <div class="col-sm-12 col-md-3 text-md-right text-center">
                                <div class="mt-2">
                                    <img :title="project.company.title" :src="project.company.logo" style="max-height: 50px; max-width:150px; vertical-align: text-bottom;" />
                                </div>
                            </div>
                        </div>

                        <div class="row pt-3">
                            <div class="col-sm-12 col-md-4 text-md-left text-center">
                                <a href="/login" class="btn btn-success">
                                    {{ trans('custom.open') }}
                                </a>
                            </div>

                            <div class="col-sm-12 col-md-8  text-md-right text-center">
                                <a href="/login">
                                    <h3 class="d-inline align-text-top" v-for="type, files in project.summary">
                                        <i :class="'fa text-dark '+ files"></i> {{ type.length }}
                                    </h3>

                                    <span class="btn btn-success" v-if="project.summary.length < 1">
                                        {{ trans('custom.add_files') }}
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column view -->

            <div class="row" v-if="view_type == 'column'" style="min-height: 60vh">
                <div class="col-md-4 col-sm-12 my-3 project-columns" v-for="project, index in filteredProjects">
                    <div class="card">
                        <div class="project-thumbnail card-img-top">
                            <a href="/login"><div :style="{'background-image': 'url(' + project.image + ')'}"></div></a>
                        </div>
                        <div class="card-body">
                            <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" class="h5" :title="project.title">
                                {{ project.title }}
                            </div>

                            <hr class="mt-1 mb-2" />

                            <div class="row pt-3">
                                <div class="col-sm-9">
                                    <img :title="project.company.title" :src="project.company.logo" style="max-height: 25px;max-width:55%" />
                                </div>

                                <div class="col-sm-3 text-right">
                                    <a href="/login" style="margin-left: -20px;">
                                        <span class="btn btn-success">
                                            {{ trans('custom.open') }}
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

    </div>
</template>

<script>
    export default {
        name: "public",

        data: function () {
            return {
                companyName: trans('custom.public'),
                search: '',
                projects: [],
                view_type: 'list',
                sort: 0,
                showMap: true
            }
        },
        mounted() {
            this.load();
        },
        methods: {
            load() {
                axios.get('/public_projects').then(resp => {
                    this.projects = resp.data;
                }).catch(() => {
                    alert(window.trans('custom.error_load_project'));
                });
            },
            filterChange(type) {
                this.load();
            },
            changeView(type) {
                this.view_type = type;
            },
            onMapReady(map) {
                map.loadImage("/images/marker.png", function(error, image) {
                    if (error) throw error;
                    map.addImage("custom-marker", image);
                });

                const nav = new mapboxgl.NavigationControl({
                    showCompass: false
                });
                map.addControl(nav, 'top-right');

                map.addSource("projects", {
                    type: "geojson",
                    data: "/public_projects?json=1",
                    cluster: true,
                    clusterMaxZoom: 14, // Max zoom to cluster points on
                    clusterRadius: 50 // Radius of each cluster when clustering points (defaults to 50)
                });

                map.addLayer({
                    id: "clusters",
                    type: "circle",
                    source: "projects",
                    filter: ["has", "point_count"],
                    paint: {
                        "circle-color": "rgba(110,204,57,.6)",
                        "circle-radius": 20,
                        "circle-stroke-width": 5,
                        "circle-stroke-color": 'rgba(181,226,140,6)'
                    }
                });

                map.addLayer({
                    id: "cluster-count",
                    type: "symbol",
                    source: "projects",
                    filter: ["has", "point_count"],
                    layout: {
                        "text-field": "{point_count_abbreviated}",
                        "text-font": ["DIN Offc Pro Medium", "Arial Unicode MS Bold"],
                        "text-size": 12
                    }
                });

                map.addLayer({
                    id: "points",
                    type: "symbol",
                    source: "projects",
                    filter: ["!", ["has", "point_count"]],
                    layout: {
                        "icon-image": "custom-marker",
                        "icon-size": 0.15
                    }
                });
            },
            mapClicked(map, e) {
                this.addPopUp(map, e);

                const features = map.queryRenderedFeatures(e.point, { layers: ['clusters'] });
                if (!features.length) {
                    return;
                }

                const clusterId = features[0].properties.cluster_id;
                map.getSource('projects').getClusterExpansionZoom(clusterId, function (err, zoom) {
                    if (err)
                        return;

                    map.easeTo({
                        center: features[0].geometry.coordinates,
                        zoom: zoom
                    });
                });
            },
            mapMouseMoved(map, e) {
                const features = map.queryRenderedFeatures(e.point, {
                    layers: ['points']
                });
                map.getCanvas().style.cursor = (features.length) ? 'pointer' : '';
            },
            addPopUp(map, e) {
                const features = map.queryRenderedFeatures(e.point, {
                    layers: ['points']
                });
                if (!features.length) {
                    return;
                }

                const feature = features[0];

                map.easeTo({
                    center: features[0].geometry.coordinates
                });

                let router = this.$router;

                const popupContent = Vue.extend({
                    props: ['feature', 'router'],
                    data: function() {
                        return {
                            item: feature.properties
                        }
                    },
                    template: '<div @click="popupClicked" v-html="item.html" class="cursor-pointer"></div>',
                    methods: {
                        popupClicked() {
                            router.replace("/login")
                        },
                    }
                });

                // Populate the popup and set its coordinates
                // based on the feature found.
                const popup = new mapboxgl.Popup()
                    .setLngLat(feature.geometry.coordinates)
                    .setHTML('<div id="vue-popup-content"></div>')
                    .addTo(map);

                new popupContent().$mount('#vue-popup-content');
            },
            mapSwitch() {
                this.showMap = !this.showMap
            }
        },

        computed: {
            filteredProjects() {
                const sort_by = this.sort;

                function compare(a, b) {
                    if (sort_by == 0) {
                        if (a.title > b.title)
                            return -1;
                        if (a.title < b.title)
                            return 1;
                        return 0;
                    } else if (sort_by == 1) {
                        if (a.title < b.title)
                            return -1;
                        if (a.title > b.title)
                            return 1;
                        return 0;
                    } else if (sort_by == 2) {
                        if (a.created_at > b.created_at)
                            return -1;
                        if (a.created_at < b.created_at)
                            return 1;
                        return 0;
                    } else {
                        if (a.created_at < b.created_at)
                            return -1;
                        if (a.created_at > b.created_at)
                            return 1;
                        return 0;
                    }
                }

                return this.projects.filter(project => {
                    return project.title.toLowerCase().indexOf(this.search.toLowerCase()) > -1
                }).sort(compare)
            }
        }
    }
</script>

<template>
    <div class="row">
        <div class="col-sm-4 form-group">
            <label class="control-label">{{ trans('custom.image') }}*</label>
            <input type="file" v-on:change="onFileChange" name="image" class="form-control">

            <span class="btn btn-danger mt-2" v-on:click="reset">{{ trans('custom.reset') }}</span>
        </div>
        <div class="col-sm-2">
            <img :src="image" class="img-responsive">
        </div>
    </div>
</template>

<style scoped>
    img{
        max-height: 150px;
        max-width: 350px;
    }

    input {
        height: auto;
    }
</style>
<script>
    export default{
        props: ['setImage'],
        data(){
            return {
                image: '',
                default: '/images/450x450.png'
            }
        },
        mounted:function(){
            this.changeImage(this.default);
        },
        watch:{
            setImage(newVal){
                this.changeImage(newVal);
            }
        },
        methods: {
            onFileChange(e) {
                let files = e.target.files || e.dataTransfer.files;
                if (!files.length)
                    return;
                this.createImage(files[0]);
            },
            createImage(file) {
                let reader = new FileReader();
                let vm = this;
                reader.onload = (e) => {
                    vm.upload(e.target.result);
                };
                reader.readAsDataURL(file);
            },
            upload(file){
                var app = this;
                axios.post('/api/v1/upload',{image: file}).then(response => {
                    if (response.data.errors) {
                        app.$dialog.alert(window.parseError(error.response.data.errors), {'html':true});
                    } else {
                        app.changeImage(response.data.image);
                    }
                });
            },
            reset() {
                this.changeImage(this.default);
            },
            changeImage(image) {
                this.$emit('uploaded', image);
                this.image = image;
            }
        }
    }
</script>
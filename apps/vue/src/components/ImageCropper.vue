<template>
  <div class="card text-center" :style="{width: width + 'px'}">
    <img v-if="imgDataUrl" class="card-img-top" :src="imgDataUrl">
    <div class="card-body">
      <div class="btn-group-vertical" role="group">
        <a class="btn btn-primary" @click="toggleShow">{{upload_button_title}}</a>
        <a v-if="imgDataUrl" class="btn btn-danger" @click="deleteImage">{{image_delete_title}}</a>
      </div>
      <v-dialog />
      <my-upload field="img"
                 @crop-success="cropSuccess"
                 @crop-upload-success="cropUploadSuccess"
                 @crop-upload-fail="cropUploadFail"
                 v-model="show"
                 :langType="language"
                 :width="width"
                 :height="height"
                 :url="api_url + '?action=profile&do=update_avatar'"
                 :params="params"
                 :headers="headers"
                 img-format="png"></my-upload>
    </div>
  </div>
</template>

<script>
import Vue from 'vue'
import 'babel-polyfill'; // es6 shim
import myUpload from 'vue-image-crop-upload/upload-2.vue';
import Notifications from 'vue-notification'
import VModal from 'vue-js-modal'

Vue.use(Notifications)
Vue.use(VModal, { dialog: true })
Vue.component('my-upload', myUpload)

export default {
  name: 'ImageCropper',
  props: {
    api_url: {
      type: String,
      default: '/apps/api/rest.php'
    },
    upload_button_title: {
      type: String,
      default: 'Загрузить новое фото'
    },
    image_delete_title: {
      type: String,
      default: 'Удалить фото'
    },
    language: {
      type: String,
      default: 'ru'
    },
    image_url: {
      type: String,
      default: ''
    },
    update_user_id: {
      type: String,
      default: 'new'
    },
    width: {
      type: Number,
      default: 270
    },
    height: {
      type: Number,
      default: 270
    },
  },
  components: {
    myUpload,
    Notifications
  },
  data() {
    return {
      show: false,
      params: {
        token: '123456798',
        name: 'avatar',
        update_user_id: this.update_user_id
      },
      headers: {
        smail: '*_~'
      },
      imgDataUrl: this.image_url // the datebase64 url of created image
    };
  },
  methods: {
    toggleShow() {
      this.show = !this.show;
    },
    deleteImage() {
      this.$modal.show('dialog', {
        title: 'Действительно хотите удалить фото?',
        buttons: [
          {
            title: 'Удалить',
            handler: () => {
              axios.post(this.api_url, {
                action: 'profile',
                do: 'delete_avatar',
                update_user_id: this.update_user_id
              })
                  .then((response) => {
                    if ( response.data.status != 'ok' ) {
                      this.$notify({ type: 'error', text: 'Ошибка при удалении: ' + response.data.message });
                    } else {
                      this.imgDataUrl = '';
                      this.$notify({ type: 'success', text: 'Фото удалено' });
                      this.$modal.hide('dialog');
                    }
                  }, (error) => {
                    this.$notify({ type: 'error', text: error });
                  });
            }
          },
          {
            title: 'Отмена',
            handler: () => {
              this.$modal.hide('dialog')
            }
          },
        ]
      })
    },
    /**
     * crop success
     *
     * [param] imgDataUrl
     * [param] field
     */
    cropSuccess(imgDataUrl, field){
      //console.log('-------- crop success --------');
      this.$notify({ type: 'success', text: 'Фото обновлено' });

      this.imgDataUrl = imgDataUrl;
    },
    /**
     * upload success
     *
     * [param] jsonData  server api return data, already json encode
     * [param] field
     */
    cropUploadSuccess(jsonData, field){
      if (jsonData.status == 'error') {

      }
      //console.log('-------- upload success --------');
      //console.log(jsonData);
      //console.log('field: ' + field);
    },
    /**
     * upload fail
     *
     * [param] status    server api return error status, like 500
     * [param] field
     */
    cropUploadFail(status, field){
      //console.log('-------- upload fail --------');
      //console.log(status);
      //console.log('field: ' + field);
    }
  }
}
</script>
<style>
#photo_element_deprecated, #photo_element_image_list_deprecated {
  display: none;
}
.vue-image-crop-upload img {
  max-width: none;
}
</style>

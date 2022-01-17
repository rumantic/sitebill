<template>
  <div>
  <input type="hidden" :name="column_name" :value="hidden_value">
  <v-select
      class="vue_select_by_query"
      :options="options"
      label="value"
      :pushTags="true"
      :taggable="true"
      :value="selected_value"
      :placeholder="placeholder"
      @search="fetchOptions"
      @input="setSelected"
  >
    <template v-slot:option="option">
      <span :class="option.icon"></span>
      {{ option.value }}
    </template>

  </v-select>
  </div>
</template>

<script>
import VueSelect from 'vue-select'
import 'vue-select/dist/vue-select.css';
import { assert } from '@/core'
import { BaseService } from '@/services/base.service'
import { PostsService } from '@/services/posts.service'
import { FormService } from '@/services/form.service'

import { ErrorWrapper, ResponseWrapper } from '@/services/util'
import Vue from "vue";
import Notifications from "vue-notification";
Vue.use(Notifications)

Vue.component('v-select', VueSelect)

export default {
  name: "SelectByQuery",
  data() {
    return {
      selected_value : false,
      hidden_value: 0,
    }
  },
  props: {
    api_url: {
      type: String,
      default: '/apps/api/rest.php'
    },
    column_name: {
      type: String,
      default: ''
    },
    model_name: {
      type: String,
      default: ''
    },
    switch_off_ai_mode: {
      type: Boolean,
      default: false
    },
    options: {
      type: Array,
      default: []
    },
    placeholder: {
      type: String,
      default: ''
    },
    value: {
      type: String,
      default: ''
    },
    form_id: {
      type: String,
      default: 'form_id'
    },
  },
  components: {
    VueSelect,
    Notifications
  },
  mounted() {
    this.load_dictionary();
  },
  methods: {
    fetchOptions (search, loading) {
    },
    async load_dictionary () {

      try {
        let params = {};
        const { data } = await FormService.loadDictionary(this.model_name, this.column_name, params);
        console.log('data')
        console.log(data);
        this.options = data.content;
        this.set_default_selected_value();
      } catch (e) {
        // this.$store.commit('toast/NEW', { type: 'error', message: e.message, e })
        this.$notify({ type: 'error', text: e.message });
        this.error = e.message
        console.log(e)
      } finally {
        this.loading = false
      }

      /*
      axios.post(this.api_url, {
        action: 'model',
        do: 'load_dictionary_with_params',
        columnName: this.column_name,
        model_name: this.model_name,
        params: params,
        switch_off_ai_mode: this.switch_off_ai_mode,
      })
        .then((response) => {
          if ( response.data.status != 'ok' ) {
          } else {
          }
        }, (error) => {
          this.$notify({ type: 'error', text: error });
        });
       */
    },
    async test_request () {
      console.log(FormService.item_list);
      try {
        let params = {
          action: 'model',
          do: 'load_dictionary_with_params',
          columnName: 'district_id',
          model_name: 'data',
        };
        const { data } = await PostsService.getListPublic(params)
        console.log(data);
      } catch (e) {
        // this.$store.commit('toast/NEW', { type: 'error', message: e.message, e })
        this.$notify({ type: 'error', text: e.message });
        this.error = e.message
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    set_default_selected_value () {
      if ( this.value ) {
        this.hidden_value = this.value;
        this.selected_value = this.options.filter(item => item.id == this.value);
        FormService.setItem(this.form_id, this.column_name, this.value);
      }
    },
    setSelected(value) {
      this.test_request();
      if ( value ) {
        this.hidden_value = value.id;
        this.selected_value = this.options.filter(item => item.id == value.id);
        FormService.setItem(this.form_id, this.column_name, value.id);
      } else {
        this.hidden_value = 0;
        this.selected_value = null;
        FormService.setItem(this.form_id, this.column_name, 0);
      }
    }
  },


}

</script>

<style>
 .vue_select_by_query input {
   border: 1px solid transparent !important;
 }

</style>

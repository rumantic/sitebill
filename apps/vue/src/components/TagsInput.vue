<template>
  <div>
    <vue-tags-input
      v-model="tag"
      :tags="tags"
      :autocomplete-items="autocompleteItems"
      :add-only-from-autocomplete="true"
      :autocomplete-always-open="true"
      :autocomplete-min-length="0"
      @tags-changed="update"
      :placeholder="placeholder_default"

    />
  </div>
</template>

<script>
import VueTagsInput from '@johmun/vue-tags-input';
import axios from 'axios';

export default {
  name: 'TagsInput',
  props: {
    model_name: String,
    column_name: String,
    input_tags_array: String,
  },
  data() {
    return {
      tag: '',
      tags: [],
      autocompleteItems: [],
      debounce: null,
      placeholder_default: '...',
    };
  },
  watch: {
    'tag': 'initItems',
  },
  mounted() {
    this.loadAutocompleteItems('-1')
    if (this.input_tags_array !== '') {
      this.tags = JSON.parse(this.input_tags_array).map(item => ({"text": item, "tiClasses": ["ti-valid"]}));
    }
  },
  methods: {
    update(newTags) {
      this.autocompleteItems = [];
      this.tags = newTags;
      const tags_normal = {};
      tags_normal[this.column_name] = newTags.map(item => {
        return item.text;
      });
      const url = estate_folder + '/js/ajax.php';

      const request = {
        action: 'get_tags',
        do: 'set',
        version: 'api',
        column_name: this.column_name,
        model_name: this.model_name,
        tags_array: tags_normal
      };
      axios.post(url, request).then(response => {
        location.reload();
      }).catch(() => console.warn('Oh. Something went wrong'));

    },
    loadAutocompleteItems (term) {
      const url = estate_folder + '/js/ajax.php?action=get_tags&version=api&column_name=' +
        this.column_name + '&model_name=' + this.model_name + '&term=' + term + '';

      axios.get(url).then(response => {
        this.autocompleteItems = response.data.map(a => {
          return {
            text: a
          };
          /*
          if (term === '-1' || a.includes(term)) {
          }
           */
        });
      }).catch(() => console.warn('Oh. Something went wrong'));
    },
    initItems() {
      let request_tag = this.tag;
      if (this.tag.length < 1) {
        request_tag = '-1';
      }
      clearTimeout(this.debounce);
      this.debounce = setTimeout(() => {
        this.loadAutocompleteItems(request_tag)
      }, 100);
    },
  },
};
</script>

<style lang="css">
.vue-tags-input {
  width: 201px;
  overflow: hidden;
}

.vue-tags-input .ti-input {
}

.vue-tags-input .ti-autocomplete {
  max-height: 300px !important;
  overflow-y: auto !important;
}

.vue-tags-input.ti-focus {
  overflow: visible;
  width: auto;
  max-width: 200px;
}

.vue-tags-input >>> .ti-new-tag-input {
  min-width: auto;
}
</style>

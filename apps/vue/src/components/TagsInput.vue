<template>
  <div>
    <vue-tags-input
        v-model="tag"
        :tags="tags"
        :autocomplete-items="autocompleteItems"
        :add-only-from-autocomplete="true"
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
    if ( this.input_tags_array !== '' ) {
      this.tags = JSON.parse(this.input_tags_array).map(item => ({ "text": item,"tiClasses":["ti-valid"] }));
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
      const url = estate_folder+'/js/ajax.php?action=get_tags&do=set'
          +'&model_name='+ this.model_name +'&tags_array='+JSON.stringify(tags_normal)+'';

      this.debounce = setTimeout(() => {
        axios.get(url).then(response => {
          location.reload();
        }).catch(() => console.warn('Oh. Something went wrong'));
      }, 600);

    },
    initItems() {
      if (this.tag.length < 1) return;
      const url = estate_folder+'/js/ajax.php?action=get_tags&column_name='
          +this.column_name+'&model_name='+ this.model_name +'&term='+this.tag+'';

      clearTimeout(this.debounce);
      this.debounce = setTimeout(() => {
        axios.get(url).then(response => {
          this.autocompleteItems = response.data.map(a => {
            return { text: a };
          });
        }).catch(() => console.warn('Oh. Something went wrong'));
      }, 600);
    },
  },
};
</script>

<style scoped>
  .vue-tags-input {
    width: 50px;
    overflow: hidden;
  }
  .vue-tags-input .ti-input {
  }
  .vue-tags-input.ti-focus  {
    overflow: visible;
    width: auto;
    max-width: 200px;
  }
  .vue-tags-input >>> .ti-new-tag-input {
    min-width: auto;
  }
</style>

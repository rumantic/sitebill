<template>
  <div>
    Search = {{ searchstring }}
    <autocomplete name="q" :search="search" :get-result-value="getResultValue">
      <template #result="{ result, props }">
        <li
            v-bind="props"
            class="autocomplete-result wiki-result"
        >
          <div class="wiki-title">
            {{ result.title }}
          </div>
          <div class="wiki-snippet" v-html="result.description + ' теги: (' + result.tags + ')'" />
        </li>
      </template>
    </autocomplete>
  </div>

</template>

<script>
import Vue from 'vue'
import Autocomplete from '@trevoreyre/autocomplete-vue'

Vue.component('autocomplete', Autocomplete)
// const params = 'action=query&list=search&format=json&origin=*'

export default {
    name: "SearchBarComponent",
    props: ['searchstring'],
    components: {
        Autocomplete
    },
    methods: {
        search(input) {
            const url = `/ajax/search/json?&q=${encodeURI(input)}`

            return new Promise(resolve => {
                if (input.length < 3) {
                    return resolve([])
                }

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        console.log(data);
                        resolve(data.result.images.data)
                    })
            })
        },
        getResultValue(result) {
            return result.title
        },
    }
}
</script>

<style scoped>
.wiki-result {
    border-top: 1px solid #eee;
    padding: 16px;
    background: transparent;
}

.wiki-title {
    font-size: 20px;
    margin-bottom: 8px;
}

.wiki-snippet {
    font-size: 14px;
    color: rgba(0, 0, 0, 0.54);
}

</style>
<style src="@trevoreyre/autocomplete-vue/dist/style.css"></style>

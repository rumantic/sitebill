require('./bootstrap');

window.Vue = require('vue');


Vue.component('searchbar-component', require('./components/SearchBarComponent').default);
Vue.component('hello-world', require('./components/HelloWorld').default);
Vue.component('tags-input', require('./components/TagsInput').default);
Vue.component('image-cropper', require('./components/ImageCropper').default);
Vue.component('select-by-query', require('./components/SelectByQuery').default);

new Vue({
    el: '#vueapp',
});

import Vue from 'vue'
import VueCodemirror from 'vue-codemirror'
import { quillEditor } from 'vue-quill-editor'
// import VueTrix from 'vue-trix'
// import VueTrix from '@dymantic/vue-trix-editor'
import 'quill/dist/quill.core.css'
import 'quill/dist/quill.snow.css'
import 'quill/dist/quill.bubble.css'

import 'codemirror/mode/css/css.js'
import 'codemirror/mode/xml/xml.js'
import './lib/Filters'
import components from './components'
import { get } from 'lodash'

Vue.component('vue-trix', quillEditor)

Vue.use(VueCodemirror, {
    options: {
        tabSize: 4,
        // theme: 'base16-dark',
        lineNumber: true,
        line: true,
    }
})

Vue.use(components)

Vue.prototype.$t = (key, extra_data = {}) => {
    let str = FLOW_LANG[key]

    if (typeof str === 'undefined') {
        return `['${key}' not found]`
    }

    if (extra_data) {
        str = str.replace(/:(\w+)/g, function (_, $1) {
            return extra_data[$1]
        })
    }

    return str
}

Vue.prototype.$s = (key) => {
    let str = get(FLOW, key)

    if (typeof str === 'undefined') {
        return `['${key}' not found]`
    }

    return str
}

import App from './App.vue'
import router from './router'
import http from './lib/Http'

http.interceptors.response.use(function (response) {
    // Do something with response data
    return response;
    }, function (err) {
        // Do something with response error
        if (err.response.status === 401) {
            components.LoadingBar.finish();
            window.location = '/?logout=true'
        }

        if(err.response.status === 403){
            components.LoadingBar.finish();
            components.Message.error(err.response.data.message, 0)
        }

        if (err.response.status === 500) {
            components.LoadingBar.finish();
            components.Message.error(err.response.data.message, 0)
        }

        if (err.response.status === 404) {
            components.LoadingBar.finish();
            components.Message.error(err.response.data.message, 0)
        }
        return Promise.reject(err);
    }
);

router.beforeEach((to, form, next) => {
    components.LoadingBar.start()
    next()
});

const app = new Vue({
    el: '#app',
    render: (h) => h(App),
    router
})
import { reactive } from 'vue'
import { defineStore } from 'pinia'

export const useConfigStore = defineStore('config', () => {
    const config = reactive({})

    function setConfig(data) {
        //遍历data，赋值给config
        Object.keys(data).forEach(key => {
            config[key] = data[key]
        })
    }

    return { config, setConfig }
})
import { reactive } from 'vue'
import { defineStore } from 'pinia'

export const useUserStore = defineStore('user', () => {
    const user = reactive({})

    function setUser(data) {
        data.avatar = data.avatar || './resources/images/avatar.jpg'; //头像
        //遍历data，赋值给user
        Object.keys(data).forEach(key => {
            user[key] = data[key]
        })
    }

    return { user, setUser }
})
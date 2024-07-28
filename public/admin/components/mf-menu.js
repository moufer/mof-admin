import { useRouteStore } from 'comm/routeStore.js';
export default {
    props: {
        name: {
            type: String,
            default: 'system'
        },
        menus: {
            type: Array,
            default: () => []
        },
        defaultPath: {
            type: String,
            default: ''
        },
        defaultCollapse: {
            type: Boolean,
            default: false
        }
    },
    emits: ['update:collapse', 'click-menu', 'change-path', 'change-collapse'],
    data() {
        return {
            pagePath: '',
            defaultActive: '0',
            isCollapse: true,
            urls: []
        }
    },
    computed: {
        storageKey() {
            return `${this.name}_menu_collapse`;
        },
        // urls() {
        //     //后台默认首页
        //     let result = [];
        //     //扁平化
        //     function flatten(node) {
        //         if (node.type === 'menu') {
        //             result.push({ id: node.id, url: node.url, title: node.title });
        //         }
        //         if (node.children && node.children.length > 0) {
        //             for (let i = 0; i < node.children.length; i++) {
        //                 flatten(node.children[i]);
        //             }
        //         }
        //     }
        //     for (let i = 0; i < this.menus.length; i++) {
        //         flatten(this.menus[i]);
        //     }
        //     return result;
        // }
    },
    watch: {
        pagePath(newValue) {
            this.$emit('change-path', newValue);
        },
        isCollapse(newValue) {
            this.$emit('change-collapse', newValue);
        }
    },
    mounted() {
        this.init();
    },
    methods: {
        init() {
            this.urls = useRouteStore().urls;
            this.isCollapse = localStorage.getItem(this.storageKey) === '1';
            this.defaultActive = this.getPermIdBySrc(this.$route.path).toString();
        },

        setPagePath(path) {
            this.defaultActive = this.getPermIdBySrc(path).toString();
            this.pagePath = this.getSrcByPermId(this.defaultActive);
            console.log('setPagePath', path, this.defaultActive, this.pagePath, this.urls);
        },

        handleClick(event) {
            //获取函数的所有参数
            //因event.index是单项绑定，所以event.index只能设置为ID，通过ID来查找url
            let path = this.getSrcByPermId(event.index);
            //console.log('handleClick', event.index, path);
            if (this.pagePath !== path) {
                this.pagePath = path;
            }
            this.defaultActive = event.index;
            //修改当前链接url的hash为this.path
            //window.location.hash = this.pagePath;
            this.$emit('click-menu', event, this.pagePath, event.index);
        },

        handelCollapseMenu() {
            this.isCollapse = !this.isCollapse;
            localStorage.setItem(this.storageKey, this.isCollapse ? 1 : 0);
        },

        //根据ID获取URL
        getSrcByPermId(id) {
            id = parseInt(id);
            //遍历urls变量
            for (let i = 0; i < this.urls.length; i++) {
                const perm = this.urls[i];
                //检查当前perm的id是否匹配
                if (perm.id === Number(id)) {
                    return perm.url;
                }
            }
            // 如果未找到匹配的 id，返回默认页面
            return '404';
        },

        //根据URL获取ID
        getPermIdBySrc(src) {
            //如果src的第一个字母是/时，去除
            if (src[0] === '/') src = src.substring(1);
            if (src.length > 0) {
                for (let i = 0; i < this.urls.length; i++) {
                    const perm = this.urls[i];
                    if (perm.url === src) {
                        return `${perm.id}`;
                    }
                }
            }
            return 0;
        },

        handleScroll(event) {

            event.preventDefault();
            console.log('xxx', this.$refs.menuBoxRef.scrollTop, event.deltaY);
            this.$refs.menuBoxRef.scrollTop += event.deltaY;
        },
    },
    template: /*html*/`
    <div class="menu-box" ref="menuBoxRef" v-on:wheel="handleScroll">
        <el-menu class="menu-component" :unique-opened="true" :default-active="defaultActive" 
            background-color="#393d4a" text-color="#fff" active-text-color="#ffd04b" :collapse="isCollapse">
            <template v-for="(menu, index) in menus">
            <el-sub-menu :index="menu.id.toString()" v-if="menu.type=='group'">
                <template #title>
                <el-icon>
                    <component :is="menu.icon ? menu.icon : 'Sugar'"></component>
                </el-icon>
                <span>{{menu.title}}</span>
                </template>
                <template v-for="(menuL2, indexL2) in menu.children">
                <el-menu-item :index="menuL2.id.toString()" @click="handleClick">
                    <template #title>
                    <el-icon>
                        <component :is="menuL2.icon ? menuL2.icon : 'Sugar'"></component>
                    </el-icon>
                    <span>{{menuL2.title}}</span>
                    </template>
                </el-menu-item>
                </template>
            </el-sub-menu>
            <el-menu-item @click="handleClick" :index="menu.id.toString()" v-else-if="menu.type=='menu'">
                <el-icon>
                    <component :is="menu.icon ? menu.icon : 'Sugar'"></component>
                </el-icon>
                <span>{{menu.title}}</span>
            </el-menu-item>
            </template>
        </el-menu>
    </div>
    <div class="menu-fold">
        <el-icon color="#fff" @click="handelCollapseMenu" style="cursor:pointer;">
            <component :is="isCollapse ? 'Expand' : 'Fold'"></component>
        </el-icon>
    </div>
    `
}
<?php

return [
    // 默认磁盘
    'default' => env('filesystem.driver', 'public'),
    // 磁盘列表
    'disks'   => [
        //local不对外访问
        'local'  => [
            'type' => 'local', // 本地存储
            'root' => app()->getRuntimePath() . 'storage', // 磁盘路径
        ],
        //public对外访问
        'public' => [
            // 磁盘类型
            'type'       => 'local',
            // 磁盘路径
            'root'       => app()->getRootPath() . 'public/storage',
            // 磁盘路径对应的外部URL路径
            'url'        => '/storage',
            // 可见性
            'visibility' => 'public',
        ],
        // 更多的磁盘配置信息
        // 阿里云oss
        'aliyun' => [
            'type'         => 'aliyun',
            'accessId'     => '******', // 阿里云的accessKey
            'accessSecret' => '******', // 阿里云的accessSecret
            'bucket'       => 'bucket', // 阿里云的bucket名称
            'endpoint'     => 'oss-cn-hongkong.aliyuncs.com', // OSS 外网节点或自定义外部域名
            'url'          => 'https://oss-cn-hongkong.aliyuncs.com',//URL地址域名,不要斜杠结尾
        ],
        // 七牛云
        'qiniu'  => [
            'type'      => 'qiniu',
            'accessKey' => '******',
            'secretKey' => '******',
            'bucket'    => 'bucket', // 七牛云的bucket名称
            'url'       => '', //URL地址域名,不要斜杠结尾
        ],
        // 腾讯云COS
        'qcloud' => [
            'type'            => 'qcloud',
            'region'          => 'ap-shanghai', //bucket 所属区域 英文
            'appId'           => '******', // 域名中数字部分
            'secretId'        => '******',
            'secretKey'       => '******',
            'bucket'          => '******', // 存储桶名称
            'timeout'         => 60, // 超时时间
            'connect_timeout' => 60, // 连接超时时间
            'cdn'             => 'https://******.cos.ap-shanghai.myqcloud.com', // CDN加速域名
            'scheme'          => 'https', //协议头部
            'read_from_cdn'   => false, //是否从CDN读取
        ]
    ],
];

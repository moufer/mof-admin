<?php

use think\migration\Seeder;

class Config extends Seeder
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run(): void
    {
        $data = [
            [
                "module" => "admin",
                "name"   => "site_name",
                "value"  => "磨锋开发系统",
                "type"   => "input"
            ],
            [
                "module" => "admin",
                "name"   => "storage_file_ext",
                "value"  => "pdf,doc,docx,json",
                "type"   => "input"
            ],
            [
                "module" => "admin",
                "name"   => "storage_file_size",
                "value"  => "3",
                "type"   => "input"
            ],
            [
                "module" => "admin",
                "name"   => "storage_image_ext",
                "value"  => "jpg,png,jpeg,gif",
                "type"   => "input"
            ],
            [
                "module" => "admin",
                "name"   => "storage_image_size",
                "value"  => "5",
                "type"   => "input"
            ],
            [
                "module" => "admin",
                "name"   => "storage_image_wh",
                "value"  => "1000x1000",
                "type"   => "input"
            ],
            [
                "module" => "admin",
                "name"   => "storage_media_ext",
                "value"  => "mp4,mp3",
                "type"   => "input"
            ],
            [
                "module" => "admin",
                "name"   => "storage_media_size",
                "value"  => "10",
                "type"   => "input"
            ],
            [
                "module" => "admin",
                "name"   => "storage_provider",
                "value"  => "public",
                "type"   => "select"
            ]
        ];

        foreach ($data as $index => $item) {
            $item['extra'] = '[]';
            $item['create_at'] = date('Y-m-d H:i:s');
            $item['update_at'] = date('Y-m-d H:i:s');
            $data[$index] = $item;
        }

        $posts = $this->table('system_config');
        $posts->insert($data)->saveData();
    }
}
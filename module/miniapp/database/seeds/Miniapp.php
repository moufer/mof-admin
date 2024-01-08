<?php
declare(strict_types=1);

use think\migration\Seeder;

class Miniapp extends Seeder
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
                'id'          => 2,
                'type'        => 'wechat',
                'title'       => '小图文小程序',
                'intro'       => '图图兔兔图图',
                'original_id' => '',
                'avatar_img'  => '',
                'qrcode_img'  => '',
                'appid'       => '12345678',
                'appsecret'   => '876543wertyujhgfd',
                'config'      => '',
                'module'      => 'article',
                'create_at'   => '2023-07-29 00:51:00',
                'update_at'   => '2023-07-29 00:51:00',
            ]
        ];

        $this->table('miniapp')->insert($data)->save();
    }
}
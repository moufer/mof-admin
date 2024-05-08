<?php

use think\migration\Migrator;

class MiniappMiniapp extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up()
    {
        $table = $this->table('miniapp', [
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '小程序平台',
            'id'          => 'id',
            'signed'      => false,
            'primary_key' => ['id']
        ]);
        $table->addColumn('type', 'enum', ['values' => ['wechat'], 'null' => false, 'default' => 'wechat', 'comment' => '小程序类型'])
            ->addColumn('module', 'string', ['limit' => 50, 'null' => false, 'default' => '', 'comment' => '关联模块'])
            ->addColumn('title', 'string', ['limit' => 60, 'null' => false, 'default' => '', 'comment' => '小程序名称'])
            ->addColumn('intro', 'string', ['limit' => 255, 'null' => false, 'default' => '', 'comment' => '小程序简介'])
            ->addColumn('original_id', 'string', ['limit' => 100, 'null' => false, 'default' => '', 'comment' => '原始ID'])
            ->addColumn('avatar_img', 'string', ['limit' => 255, 'null' => false, 'default' => '', 'comment' => '头像图片地址'])
            ->addColumn('qrcode_img', 'string', ['limit' => 255, 'null' => false, 'default' => '', 'comment' => '二维码图片地址'])
            ->addColumn('appid', 'string', ['limit' => 50, 'null' => false, 'comment' => '小程序AppID'])
            ->addColumn('app_secret', 'string', ['limit' => 100, 'null' => false, 'default' => '', 'comment' => '小程序AppSecret'])
            ->addColumn('app_token', 'string', ['limit' => 100, 'null' => false, 'default' => '', 'comment' => '小程序token'])
            ->addColumn('app_aes_key', 'string', ['limit' => 100, 'null' => false, 'default' => '', 'comment' => '小程序aes_key'])
            ->addColumn('pay', 'text', ['comment' => '支付配置'])
            ->addColumn('config', 'text', ['comment' => '小程序配置'])
            ->addColumn('create_at', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('update_at', 'datetime', ['null' => false, 'comment' => '更新时间'])
            ->addColumn('delete_at', 'datetime', ['null' => true, 'comment' => '软删除'])
            ->addIndex('appid', ['unique' => true])
            ->create();
    }

    public function down()
    {
        $table = $this->table('miniapp');
        $table->exists() && $table->drop()->save();
    }
}

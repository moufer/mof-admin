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
        $table = $this->table('miniapp', ['engine' => 'InnoDB']);
        $table->addColumn('type', 'enum', ['values' => ['wechat'], 'default' => 'wechat', 'comment' => '小程序类型'])
            ->addColumn('title', 'string', ['limit' => 60, 'default' => '', 'comment' => '小程序名称'])
            ->addColumn('intro', 'string', ['limit' => 255, 'default' => '', 'comment' => '小程序简介'])
            ->addColumn('original_id', 'string', ['limit' => 100, 'default' => '', 'comment' => '原始ID'])
            ->addColumn('avatar_img', 'string', ['limit' => 255, 'default' => '', 'comment' => '头像图片地址'])
            ->addColumn('qrcode_img', 'string', ['limit' => 255, 'default' => '', 'comment' => '二维码图片地址'])
            ->addColumn('appid', 'string', ['limit' => 50, 'default' => '', 'comment' => '小程序AppID'])
            ->addColumn('appsecret', 'string', ['limit' => 100, 'default' => '', 'comment' => '小程序AppSecret'])
            ->addColumn('module', 'string', ['limit' => 50, 'default' => '', 'comment' => '关联模块'])
            ->addColumn('config', 'string', ['limit' => 500, 'default' => '', 'comment' => '小程序配置'])
            ->addColumn('create_at', 'datetime', ['comment' => '创建时间'])
            ->addColumn('update_at', 'datetime', ['comment' => '更新时间'])
            ->addIndex('appid', ['unique' => true])
            ->create();
    }

    public function down()
    {
        $table = $this->table('miniapp');
        $table->exists() && $table->drop()->save();
    }
}

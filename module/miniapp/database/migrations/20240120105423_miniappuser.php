<?php

use think\migration\Migrator;

class Miniappuser extends Migrator
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
        $table = $this->table('miniapp_user', ['engine' => 'InnoDB']);
        $table->addColumn('miniapp_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '小程序ID'])
            ->addColumn('miniapp_type', 'enum', ['values' => ['wechat'], 'default' => 'wechat', 'comment' => '小程序类型'])
            ->addColumn('openid', 'string', ['limit' => 32, 'default' => '', 'comment' => 'Openid'])
            ->addColumn('unionid', 'string', ['limit' => 32, 'default' => '', 'comment' => 'Unionid'])
            ->addColumn('session_key', 'string', ['limit' => 32, 'default' => '', 'comment' => 'SessionKey'])
            ->addColumn('nickname', 'string', ['limit' => 50, 'default' => '', 'comment' => '昵称'])
            ->addColumn('avatar', 'string', ['limit' => 255, 'default' => '', 'comment' => '头像'])
            ->addColumn('login_ip', 'string', ['limit' => 15, 'default' => '', 'comment' => '登录IP'])
            ->addColumn('login_at', 'datetime', ['comment' => '登录时间'])
            ->addColumn('create_at', 'datetime', ['comment' => '创建时间'])
            ->addColumn('update_at', 'datetime', ['comment' => '更新时间'])
            ->addIndex(['miniapp_id', 'create_at'])
            ->addIndex(['miniapp_id', 'openid'])
            ->create();
    }

    public function down()
    {
        $table = $this->table('miniapp');
        $table->exists() && $table->drop()->save();
    }
}

<?php

use think\migration\Migrator;

class MiniappUser extends Migrator
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
        $table = $this->table('miniapp_user', [
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_unicode_ci',
            'comment'     => '小程序访客',
            'id'          => 'id',
            'primary_key' => ['id']
        ]);
        $table->addColumn('miniapp_id', 'integer', ['limit' => 11, 'null' => false, 'default' => 0, 'comment' => '小程序ID'])
            ->addColumn('miniapp_type', 'enum', ['values' => ['wechat'], 'null' => false, 'default' => 'wechat', 'comment' => '小程序类型'])
            ->addColumn('openid', 'string', ['limit' => 32, 'null' => false, 'default' => '', 'comment' => 'Openid'])
            ->addColumn('unionid', 'string', ['limit' => 32, 'null' => false, 'default' => '', 'comment' => 'Unionid'])
            ->addColumn('session_key', 'string', ['limit' => 32, 'null' => false, 'default' => '', 'comment' => 'SessionKey'])
            ->addColumn('nickname', 'string', ['limit' => 50, 'null' => false, 'default' => '', 'comment' => '昵称'])
            ->addColumn('avatar', 'string', ['limit' => 255, 'null' => false, 'default' => '', 'comment' => '头像'])
            ->addColumn('create_at', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('update_at', 'datetime', ['null' => false, 'comment' => '更新时间'])
            ->addIndex(['miniapp_id', 'create_at'])
            ->addIndex(['miniapp_id', 'openid'])
            ->create();

    }

    public function down()
    {
        $table = $this->table('miniapp_user');
        $table->exists() && $table->drop()->save();
    }
}

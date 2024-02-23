<?php

use think\migration\Migrator;

class MiniappAdmin extends Migrator
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
        $table = $this->table('miniapp_admin_relation', ['engine' => 'InnoDB']);
        $table->addColumn('admin_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '管理员ID'])
            ->addColumn('miniapp_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '小程序ID'])
            ->addColumn('module', 'string', ['limit' => 50, 'default' => '', 'comment' => '关联模块标识'])
            ->addColumn('create_at', 'datetime', ['comment' => '创建时间'])
            ->addIndex('admin_id')
            ->create();
    }

    public function down()
    {
        $table = $this->table('miniapp');
        $table->exists() && $table->drop()->save();
    }
}

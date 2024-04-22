<?php

use think\migration\Migrator;

class MiniappPackage extends Migrator
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
        $table = $this->table('miniapp_package', [
            'engine'      => 'InnoDB',
            'collation'   => 'utf8_general_ci',
            'comment'     => '',
            'id'          => 'id',
            'primary_key' => ['id']
        ]);
        $table->addColumn('miniapp_id', 'integer', ['limit' => 10, 'signed' => false, 'null' => false, 'comment' => '小程序平台'])
            ->addColumn('admin_id', 'integer', ['limit' => 10, 'signed' => false, 'null' => false, 'comment' => '打包用户'])
            ->addColumn('key', 'string', ['limit' => 60, 'null' => false, 'default' => '', 'comment' => '下载key'])
            ->addColumn('module', 'string', ['limit' => 100, 'null' => false, 'default' => '', 'comment' => '小程序模块'])
            ->addColumn('filename', 'string', ['limit' => 100, 'null' => false, 'comment' => '文件名'])
            ->addColumn('filesize', 'integer', ['limit' => 10, 'signed' => false, 'null' => false, 'default' => 0, 'comment' => '文件大小'])
            ->addColumn('downloaded', 'tinyinteger', ['limit' => 1, 'null' => false, 'default' => 0, 'comment' => '是否已下载'])
            ->addColumn('package_at', 'datetime', ['null' => false, 'comment' => '打包时间'])
            ->addColumn('download_at', 'datetime', ['null' => true, 'comment' => '下载时间'])
            ->addIndex(['key'], ['unique' => true])
            ->addIndex(['miniapp_id', 'admin_id'])
            ->create();
    }

    public function down()
    {
        $table = $this->table('miniapp_package');
        $table->exists() && $table->drop()->save();
    }
}

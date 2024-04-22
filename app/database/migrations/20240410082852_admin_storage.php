<?php

use think\migration\Migrator;

class AdminStorage extends Migrator
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
        $table = $this->table('system_storage', [
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '附件',
            'id'          => 'id',
            'signed'      => false,
            'primary_key' => ['id']
        ]);

        $table->addColumn('user_type', 'string', ['limit' => 20, 'default' => '', 'comment' => '用户类型', 'null' => false])
            ->addColumn('user_id', 'integer', ['limit' => 11, 'signed' => false, 'default' => 0, 'comment' => '上传用户', 'null' => false])
            ->addColumn('category', 'string', ['limit' => 50, 'default' => '', 'comment' => '附件分类', 'null' => false])
            ->addColumn('name', 'string', ['limit' => 100, 'default' => '', 'comment' => '上传名', 'null' => false])
            ->addColumn('title', 'string', ['limit' => 100, 'default' => '', 'comment' => '文件名', 'null' => false])
            ->addColumn('path', 'string', ['limit' => 500, 'default' => '', 'comment' => '文件路径', 'null' => false])
            ->addColumn('sha1', 'string', ['limit' => 60, 'default' => '', 'comment' => '文件MD5', 'null' => false])
            ->addColumn('mime', 'string', ['limit' => 120, 'default' => '', 'comment' => '文件Mime', 'null' => false])
            ->addColumn('size', 'integer', ['limit' => 10, 'signed' => false, 'default' => 0, 'comment' => '文件大小', 'null' => false])
            ->addColumn('width', 'integer', ['limit' => 10, 'signed' => false, 'null' => true, 'comment' => '图片宽度'])
            ->addColumn('height', 'integer', ['limit' => 10, 'signed' => false, 'null' => true, 'comment' => '图片高度'])
            ->addColumn('file_type', 'string', ['limit' => 50, 'comment' => '文件类型', 'null' => false])
            ->addColumn('file_ext', 'string', ['limit' => 15, 'default' => '', 'comment' => '文件后缀', 'null' => false])
            ->addColumn('provider', 'string', ['limit' => 50, 'default' => 'public', 'comment' => '存储提供商', 'null' => false])
            ->addColumn('create_at', 'datetime', ['comment' => '上传时间', 'null' => false])
            ->addColumn('update_at', 'datetime', ['comment' => '更新时间', 'null' => false])
            ->addIndex(['user_type', 'id'])
            ->addIndex(['sha1'], ['name' => 'md5'])
            ->create();
    }

    public function down()
    {
        $table = $this->table('system_storage');
        $table->exists() && $table->drop()->save();
    }
}

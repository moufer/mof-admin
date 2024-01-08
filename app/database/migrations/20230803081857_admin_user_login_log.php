<?php

use think\migration\Migrator;

class AdminUserLoginLog extends Migrator
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
        $table = $this->table('user_login_log', ['engine' => 'InnoDB']);
        $table->addColumn('username', 'string', ['limit' => 50, 'default' => '', 'comment' => '用户姓名'])
            ->addColumn('status', 'boolean', ['default' => false, 'comment' => '登录状态：0-失败，1-成功'])
            ->addColumn('ip', 'string', ['limit' => 50, 'default' => '', 'comment' => '登录IP地址'])
            ->addColumn('browser', 'string', ['limit' => 50, 'default' => '', 'comment' => '浏览器'])
            ->addColumn('os', 'string', ['limit' => 50, 'default' => '', 'comment' => '操作系统'])
            ->addColumn('user_agent', 'string', ['limit' => 255, 'default' => '', 'comment' => '用户代理'])
            ->addColumn('login_at', 'datetime', ['comment' => '登录时间'])
            ->setPrimaryKey('id')
            ->create();
    }

    public function down()
    {
        $table = $this->table('user_login_log');
        if ($table->exists()) $table->drop();
    }
}

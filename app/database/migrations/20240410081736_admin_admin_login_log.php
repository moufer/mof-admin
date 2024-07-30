<?php

use think\migration\Migrator;

class AdminAdminLoginLog extends Migrator
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
        // 创建表
        $table = $this->table('system_admin_login_log', [
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '',
            'id'          => 'id',
            'signed'      => false,
            'primary_key' => ['id']
        ]);

        if(!$table->exists())

        $table->addColumn('username', 'string', ['limit' => 50, 'default' => '', 'comment' => '用户姓名', 'null' => false])
            ->addColumn('status', 'boolean', ['default' => 0, 'comment' => '登录状态：0-失败，1-成功', 'null' => false])
            ->addColumn('ip', 'string', ['limit' => 50, 'default' => '', 'comment' => '登录IP地址', 'null' => false])
            ->addColumn('browser', 'string', ['limit' => 50, 'default' => '', 'comment' => '浏览器', 'null' => false])
            ->addColumn('os', 'string', ['limit' => 50, 'default' => '', 'comment' => '操作系统', 'null' => false])
            ->addColumn('user_agent', 'string', ['limit' => 500, 'comment' => '用户代理', 'null' => false])
            ->addColumn('login_at', 'datetime', ['comment' => '登录时间', 'null' => false])
            ->create();
    }

    public function down()
    {
        // 删除表
        $table = $this->table('system_admin_login_log');
        $table->exists() && $table->drop()->save();
    }
}

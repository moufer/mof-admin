<?php

use think\migration\Migrator;

class AdminAdmin extends Migrator
{
    public function up()
    {
        // 创建表
        $table = $this->table('system_admin', [
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '后台管理员',
            'id'          => 'id',
            'signed'      => false,
            'primary_key' => ['id']
        ]);

        if(!$table->exists())

        $table->addColumn('module', 'string', ['limit' => 30, 'default' => 'system', 'null' => false, 'comment' => '管理员分类'])
            ->addColumn('username', 'string', ['limit' => 30, 'default' => '', 'null' => false, 'comment' => '用户名'])
            ->addColumn('password', 'string', ['limit' => 60, 'default' => '', 'null' => false, 'comment' => '密码'])
            ->addColumn('name', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '姓名'])
            ->addColumn('avatar', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '头像'])
            ->addColumn('email', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '邮箱'])
            ->addColumn('role_id', 'smallinteger', ['limit' => 5, 'default' => 0, 'null' => false, 'comment' => '权限角色'])
            ->addColumn('status', 'boolean', ['default' => 1, 'null' => false, 'comment' => '状态:0=禁用,1=正常'])
            ->addColumn('login_ip', 'string', ['limit' => 50, 'comment' => '登录ip'])
            ->addColumn('login_at', 'datetime', ['comment' => '登录时间'])
            ->addColumn('last_login_ip', 'string', ['limit' => 50, 'comment' => '上次登录ip'])
            ->addColumn('last_login_at', 'datetime', ['comment' => '上次登录时间'])
            ->addColumn('create_at', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('update_at', 'datetime', ['null' => false, 'comment' => '更新时间'])
            ->addIndex(['username'], ['unique' => true])
            ->create();
    }

    public function down()
    {
        // 删除表
        $table = $this->table('system_admin');
        $table->exists() && $table->drop()->save();
    }
}

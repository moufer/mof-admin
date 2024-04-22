<?php

use think\migration\Migrator;

class AdminPerm extends Migrator
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
        $table = $this->table('system_perm', [
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '管理员菜单',
            'id'          => 'id',
            'signed'      => false,
            'primary_key' => ['id']
        ]);

        $table->addColumn('pid', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '上级Id', 'null' => false])
            ->addColumn('pid_path', 'string', ['limit' => 60, 'default' => '', 'comment' => '上级路径', 'null' => false])
            ->addColumn('type', 'enum', ['values' => ['group', 'menu', 'action'], 'default' => 'menu', 'comment' => '类型:group=组,link=链接', 'null' => false])
            ->addColumn('category', 'string', ['limit' => 50, 'comment' => '权限分类', 'null' => false])
            ->addColumn('module', 'string', ['limit' => 50, 'comment' => '模块名', 'null' => false])
            ->addColumn('perm', 'string', ['limit' => 255, 'default' => '', 'comment' => '权限路径', 'null' => false])
            ->addColumn('url', 'string', ['limit' => 255, 'default' => '', 'comment' => 'URL地址', 'null' => false])
            ->addColumn('title', 'string', ['limit' => 60, 'comment' => '标题', 'null' => false])
            ->addColumn('icon', 'string', ['limit' => 100, 'default' => '', 'comment' => '图标', 'null' => false])
            ->addColumn('sort', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '排序', 'null' => false])
            ->addColumn('status', 'boolean', ['default' => 1, 'comment' => '状态:0=隐藏,1=正常', 'null' => false])
            ->addColumn('create_at', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('update_at', 'datetime', ['null' => false, 'comment' => '更新时间'])
            ->addIndex(['module', 'perm'])
            ->create();
    }

    public function down()
    {
        // 删除表
        $table = $this->table('system_perm');
        $table->exists() && $table->drop()->save();
    }

}

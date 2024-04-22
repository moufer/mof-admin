<?php

use think\migration\Migrator;

class AdminModule extends Migrator
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
        $table = $this->table('system_module', [
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '',
            'id'          => 'id',
            'signed'      => false,
            'primary_key' => ['id']
        ]);

        $table->addColumn('name', 'string', ['limit' => 50, 'default' => '', 'comment' => '模块Id', 'null' => false])
            ->addColumn('title', 'string', ['limit' => 11, 'null' => true, 'comment' => '模块标题'])
            ->addColumn('version', 'string', ['limit' => 20, 'default' => '', 'comment' => '版本号', 'null' => false])
            ->addColumn('status', 'boolean', ['default' => 1, 'comment' => '状态:1=正常,0=禁用', 'null' => false])
            ->addColumn('order', 'smallinteger', ['limit' => 5, 'default' => 0, 'comment' => '加载顺序', 'null' => false])
            ->addColumn('install_at', 'datetime', ['null' => false, 'comment' => '安装时间'])
            ->addColumn('update_at', 'datetime', ['null' => true, 'comment' => '更新时间'])
            ->addColumn('sg_perm', 'boolean', ['default' => 0, 'comment' => '独立权限', 'null' => false])
            ->addIndex(['name'], ['unique' => true])
            ->create();

        $this->addData($table);
    }

    public function down()
    {
        $table = $this->table('system_module');
        $table->exists() && $table->drop()->save();
    }

    protected function addData($table): void
    {
        $data = [
            [
                'name'       => 'admin',
                'title'      => '后台管理',
                'version'    => '1.0.0',
                'status'     => 1,
                'order'      => 0,
                'install_at' => date('Y-m-d H:i:s'),
                'update_at'  => date('Y-m-d H:i:s'),
                'sg_perm'    => 1,
            ],
        ];
        $table->insert($data)->save();
    }
}

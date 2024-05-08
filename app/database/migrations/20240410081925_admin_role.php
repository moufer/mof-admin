<?php

use think\migration\Migrator;

class AdminRole extends Migrator
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
        $table = $this->table('system_role', [
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '管理员角色',
            'id'          => 'id',
            'signed'      => false,
            'primary_key' => ['id']
        ]);

        $table->addColumn('category', 'string', ['limit' => 60, 'default' => 'system', 'comment' => '分类', 'null' => false])
            ->addColumn('pid', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '上级Id', 'null' => false])
            ->addColumn('name', 'string', ['limit' => 60, 'default' => '', 'comment' => '名称', 'null' => false])
            ->addColumn('intro', 'string', ['limit' => 200, 'default' => '', 'comment' => '简介', 'null' => false])
            ->addColumn('status', 'boolean', ['default' => 1, 'comment' => '状态:0=停用,1=正常', 'null' => false])
            ->addColumn('create_at', 'datetime', ['null' => false, 'comment' => '添加时间'])
            ->addColumn('update_at', 'datetime', ['null' => false, 'comment' => '更新时间'])
            ->create();

        $this->addData($table);
    }

    public function down()
    {
        $table = $this->table('system_role');
        $table->exists() && $table->drop()->save();
    }

    protected function addData($table): void
    {
        $data = [
            [
                'category'  => 'system',
                'pid'       => 0,
                'name'      => '超级管理员',
                'intro'     => '拥有系统所有权利权限',
                'status'    => 1,
                'create_at' => date('Y-m-d H:i:s'),
                'update_at' => date('Y-m-d H:i:s'),
            ]
        ];
        $table->insert($data)->save();
    }

}

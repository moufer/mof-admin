<?php

use think\migration\Migrator;

class AdminConfig extends Migrator
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
        $table = $this->table('system_config', [
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '',
            'id'          => 'id',
            'signed'      => false,
            'primary_key' => ['id']
        ]);

        if(!$table->exists())

        $table->addColumn('module', 'string', ['limit' => 60, 'comment' => '所属模块', 'null' => false])
            ->addColumn('extend_type', 'string', ['limit' => 10, 'default' => '', 'comment' => '扩展类型', 'null' => false])
            ->addColumn('extend_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '扩展id', 'null' => false])
            ->addColumn('name', 'string', ['limit' => 60, 'default' => '', 'comment' => '参数名', 'null' => false])
            ->addColumn('value', 'text', ['comment' => '参数值', 'null' => false])
            ->addColumn('type', 'string', ['limit' => 30, 'default' => '', 'comment' => '参数类型', 'null' => false])
            ->addColumn('extra', 'string', ['limit' => 500, 'default' => '', 'comment' => '附加信息', 'null' => false])
            ->addColumn('create_at', 'datetime', ['null' => false, 'comment' => '新增时间'])
            ->addColumn('update_at', 'datetime', ['null' => false, 'comment' => '更新时间'])
            ->addIndex(['module', 'extend_type', 'extend_id', 'name'], ['unique' => true])
            ->create();
    }

    public function down()
    {
        // 删除表
        $table = $this->table('system_config');
        $table->exists() && $table->drop()->save();
    }
}

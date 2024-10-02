<?php

use think\migration\Migrator;

class SystemTotal extends Migrator
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
    protected string $name = 'system_total';

    public function up(): void
    {
        /*
         * CREATE TABLE `test_system_total` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
      `day` date NOT NULL COMMENT '统计日期',
      `module` varchar(30) NOT NULL DEFAULT '' COMMENT '模块',
      `name` varchar(30) NOT NULL DEFAULT '' COMMENT '名称',
      `count` int(11) NOT NULL DEFAULT '0' COMMENT '数量',
      `create_at` datetime NOT NULL COMMENT '添加时间',
      `update_at` datetime NOT NULL COMMENT '更新时间',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
         */
        $table = $this->table($this->name, [
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '数据统计',
            'id'          => 'id',
            'signed'      => false,
            'primary_key' => ['id']
        ]);
        $table->addColumn('day', 'date', [
            'comment' => '统计日期', 'null' => false
        ])->addColumn('module', 'string', [
            'limit' => 30, 'default' => '', 'comment' => '模块', 'null' => false
        ])->addColumn('name', 'string', [
            'limit' => 30, 'default' => '', 'comment' => '名称', 'null' => false
        ])->addColumn('count', 'integer', [
            'limit' => 11, 'default' => 0, 'comment' => '数量', 'null' => false
        ])->addColumn('create_at', 'datetime', [
            'comment' => '添加时间', 'null' => false
        ])->addColumn('update_at', 'datetime', [
            'comment' => '更新时间', 'null' => false
        ])->addIndex(['day'], ['name' => 'day'])
            ->create();
    }

    public function down(): void
    {
        $table = $this->table($this->name);
        $table->exists() && $table->drop()->save();
    }
}

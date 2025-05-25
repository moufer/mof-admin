<?php

use think\migration\Migrator;

class SystemStorageAddExtend extends Migrator
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
    public function up(): void
    {
        //增加extend_type字段
        $table = $this->table('system_storage');
        if (!$table->hasColumn('extend_type')) {
            $table->addColumn('extend_type', 'string', [
                'limit' => 20, 'default' => '', 'comment' => '扩展类型', 'after' => 'id'
            ])->save();
        }
        if (!$table->hasColumn('extend_id')) {
            $table->addColumn('extend_id', 'integer', [
                'limit' => 11, 'default' => 0, 'comment' => '扩展id', 'after' => 'extend_type'
            ])->save();
        }
        //检测索引 module 是否存在
        if ($table->hasIndexByName('extend')) {
            $table->addIndex(['extend_type', 'extend_id'], ['unique' => true])
                ->save();
        }
    }

    public function down(): void
    {
        //回滚
        $table = $this->table('system_storage');
        if ($table->hasColumn('extend_type')) {
            $table->removeColumn('extend_type')->save();
        }
        if ($table->hasColumn('extend_id')) {
            $table->removeColumn('extend_id')->save();
        }
        if ($table->hasIndexByName('extend')) {
            $table->removeIndexByName('extend')->save();
        }
    }
}

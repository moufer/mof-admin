<?php

use think\migration\Migrator;

class SystemPerm extends Migrator
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
    public function change()
    {
        //hash,name
        $table = $this->table('system_perm');
        //添加字段hash
        $table->addColumn('hash', 'string', ['limit' => 32, 'comment' => 'hash', 'null' => false, 'after' => 'pid_path'])
            ->addColumn('name', 'string', ['limit' => 30, 'comment' => '名称', 'null' => false, 'after' => 'module'])
            ->addIndex(['pid','hash'], ['unique' => true, 'name'=>'hash'])
            ->save();
    }
}

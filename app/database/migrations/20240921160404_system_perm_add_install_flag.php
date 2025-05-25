<?php

use think\migration\Migrator;

class SystemPermAddInstallFlag extends Migrator
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
    protected string $name = 'system_perm';

    public function up()
    {
        //增加一个 install_flag 字段，varchar(10), after status
        $table = $this->table($this->name);
        if (!$table->hasColumn('install_flag')) {
            $table->addColumn('install_flag', 'string', [
                'limit' => 10, 'default' => '', 'comment' => '安装标识', 'after' => 'status'
            ])->update();
        }
    }

    public function down()
    {
    }
}

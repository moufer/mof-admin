<?php

use think\migration\Migrator;

class SystemRolePerm extends Migrator
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
        $table = $this->table('system_role_perm');
        if (!$table->hasColumn('perm_hash')) {
            $table->addColumn('perm_hash', 'string', [
                'limit' => 32, 'comment' => '权限hash', 'null' => false, 'after' => 'perm_id'
            ])->save();
        }
    }

    public function down()
    {
    }
}

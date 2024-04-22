<?php

use think\migration\Migrator;

class AdminRolePerm extends Migrator
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
        $table = $this->table('system_role_perm', [
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '角色菜单',
            'id'          => 'id',
            'signed'      => false,
            'primary_key' => ['id']
        ]);

        $table->addColumn('role_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '角色Id', 'null' => false])
            ->addColumn('perm_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '菜单Id', 'null' => false])
            ->create();
    }

    public function down()
    {
        $table = $this->table('system_role_perm');
        $table->exists() && $table->drop()->save();
    }
}

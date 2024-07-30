<?php

use think\migration\Migrator;

class AdminToken extends Migrator
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
        $table = $this->table('system_token', [
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => 'Token',
            'id'          => false,
            'primary_key' => ['uuid']
        ]);

        if(!$table->exists())

        $table->addColumn('uuid', 'string', ['limit' => 60, 'default' => '', 'comment' => 'hash', 'null' => false])
            ->addColumn('uid', 'integer', ['limit' => 10, 'null' => false, 'comment' => '用户ID'])
            ->addColumn('expire_at', 'integer', ['limit' => 11, 'comment' => '到期时间', 'null' => false])
            ->create();
    }

    public function down()
    {
        $table = $this->table('system_token');
        $table->exists() && $table->drop()->save();
    }
}

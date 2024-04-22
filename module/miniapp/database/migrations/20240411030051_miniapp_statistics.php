<?php

use think\migration\Migrator;

class MiniappStatistics extends Migrator
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
        $table = $this->table('miniapp_statistics', [
            'engine'      => 'InnoDB',
            'collation'   => 'utf8_general_ci',
            'comment'     => '小程序访问统计',
            'id'          => 'id',
            'primary_key' => ['id']
        ]);
        $table->addColumn('miniapp_id', 'integer', ['limit' => 10, 'null' => false, 'default' => 0])
            ->addColumn('def_date', 'date', ['null' => false])
            ->addColumn('session_cnt', 'integer', ['limit' => 11, 'null' => false])
            ->addColumn('visit_pv', 'integer', ['limit' => 11, 'null' => false])
            ->addColumn('visit_uv', 'integer', ['limit' => 11, 'null' => false])
            ->addColumn('visit_uv_new', 'integer', ['limit' => 11, 'null' => false])
            ->addColumn('stay_time_uv', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false])
            ->addColumn('stay_time_session', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false])
            ->addColumn('visit_depth', 'decimal', ['precision' => 10, 'scale' => 4, 'null' => false])
            ->addIndex(['miniapp_id'])
            ->create();
    }

    public function down()
    {
        $table = $this->table('miniapp_statistics');
        $table->exists() && $table->drop()->save();
    }
}

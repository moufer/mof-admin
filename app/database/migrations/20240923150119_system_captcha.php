<?php

use think\migration\Migrator;

class SystemCaptcha extends Migrator
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
    protected string $name = 'system_captcha';

    public function up(): void
    {
        /*
CREATE TABLE `system_captcha_ems` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `event` varchar(30) DEFAULT '' COMMENT '事件',
  `account_type` varchar(30) DEFAULT '' COMMENT '账号类型',
  `account` varchar(100) DEFAULT '' COMMENT '账号',
  `code` varchar(10) DEFAULT '' COMMENT '验证码',
`times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '验证次数',
  `ip` varchar(30) DEFAULT '' COMMENT 'IP',
  `create_at` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB COMMENT='邮箱验证码';
         */
        $table = $this->table($this->name, [
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '验证码',
            'id'          => 'id',
            'signed'      => false,
            'primary_key' => ['id']
        ]);
        $table->addColumn('event', 'string', ['limit' => 30, 'default' => '', 'comment' => '事件'])
            ->addColumn('account_type', 'string', ['limit' => 30, 'default' => '', 'comment' => '账号类型'])
            ->addColumn('account', 'string', ['limit' => 100, 'default' => '', 'comment' => '账号'])
            ->addColumn('code', 'string', ['limit' => 10, 'default' => '', 'comment' => '验证码'])
            ->addColumn('times', 'integer', ['limit' => 10, 'default' => 0, 'comment' => '验证次数'])
            ->addColumn('ip', 'string', ['limit' => 30, 'default' => '', 'comment' => 'IP'])
            ->addColumn('create_at', 'datetime', ['comment' => '创建时间'])
            ->addIndex(['event', 'account_type', 'account'], ['name' => 'event_account'])
            ->create();
    }

    public function down(): void
    {
        $table = $this->table($this->name);
        $table->exists() && $table->drop()->save();
    }
}

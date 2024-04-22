<?php

namespace module\miniapp\model;

use mof\Model;

class Package extends Model
{
    protected $name = 'miniapp_package';

    protected $createTime         = 'package_at';
    protected $updateTime         = false;
}
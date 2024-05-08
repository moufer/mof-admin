<?php

namespace app\model;

use mof\Model;
use mof\Mof;
use mof\Util;
use think\model\relation\MorphTo;

/**
 * 文件模型
 * Class Storage
 * @package app\model
 * @property string $url 文件访问地址
 * @property Admin $user 文件所属用户
 */
class Storage extends Model
{
    protected $name = 'system_storage';

    protected $append = ['url'];

    protected static array $userMorph = [
        'user' => ['system' => Admin::class],
    ];

    protected array $searchFields = [
        'id'        => 'integer',
        'user_type' => 'string',
        'user_id'   => 'integer',
        'file_type' => 'string',
        'mime'      => 'string',
        'title'     => ['string', 'op' => 'like'],
        'create_at' => ['datetime', 'op' => 'between'],
    ];

    public static function extendMorph(string $morph, array $alias): void
    {
        $morphs = self::$userMorph[$morph] ?? [];
        self::$userMorph[$morph] = array_merge($morphs, $alias);
    }

    public function user(): MorphTo
    {
        return $this->morphTo('user', self::$userMorph['user'] ?? []);
    }

    public function getUrlAttr($value, $data): string
    {
        return Mof::storageUrl($data['path'], $data['provider']);
    }

    public function setMimeAttr($value, $data): string
    {
        // 保存文件类型
        $this->setAttr('file_type', $value ? Util::getCommonFileFormat($value) : '');
        return $data['mime_type'] ?? $value;
    }

    public function setPathAttr($value, $data): string
    {
        // 保存文件后缀
        $this->setAttr('file_ext', $value ? pathinfo($value, PATHINFO_EXTENSION) : '');
        return $value;
    }
}
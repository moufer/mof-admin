<?php

namespace mof;

use mof\concern\model\Form;
use mof\concern\model\Searcher;
use think\db\Raw;

class Model extends \think\Model
{
    use Searcher;

    protected $autoWriteTimestamp = 'datetime';
    protected $createTime         = 'create_at';
    protected $updateTime         = 'update_at';

    /**
     * 检测字段是否被修改
     * @param $field
     * @return bool
     */
    public function isDirty($field): bool
    {
        $data = $this->getChangedData();
        return isset($data[$field]);
    }

    /**
     * @inheritdoc
     */
    protected function writeTransform($value, string|array $type)
    {
        if (is_null($value)) {
            return;
        }

        if ($value instanceof Raw) {
            return $value;
        }

        if (is_array($type)) {
            [$type,] = $type;
        } elseif (strpos($type, ':')) {
            [$type,] = explode(':', $type, 2);
        }

        switch ($type) {
            case 'el-image':
                return is_array($value) && isset($value[0]['path']) ? $value[0]['path'] : '';
        }

        return parent::writeTransform($value, $type);
    }

    /**
     * @inheritdoc
     */
    protected function readTransform($value, $type)
    {
        if (is_null($value)) {
            return;
        }

        if (is_array($type)) {
            [$type, $param] = $type;
        } elseif (strpos($type, ':')) {
            [$type, $param] = explode(':', $type, 2);
        }

        switch ($type) {
            case 'el-image':
                return $value
                    ? [['name' => basename($value), 'url' => Mof::storageUrl($value), 'path' => $value]]
                    : [];
        }

        return parent::readTransform($value, $type);
    }
}
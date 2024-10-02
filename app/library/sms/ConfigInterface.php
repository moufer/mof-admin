<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/22 10:56
 */

namespace app\library\sms;

interface ConfigInterface
{
    public function getName(): string;

    public function getFlag(): string;

    public function getConfigForm(?array $values): array;

    public function getTemplatesForm(?array $values): array;

}
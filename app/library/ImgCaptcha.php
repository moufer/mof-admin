<?php
/**
 * Project: AIGC
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/24 16:20
 */

namespace app\library;

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;

class ImgCaptcha
{
    public static function create(PhraseBuilder $builder = null): array
    {
        $builder = new CaptchaBuilder(null, $builder);
        $builder->build();
        // 获取验证码文本
        $phrase = $builder->getPhrase();

        //写入到 session
        app()->session->set('captcha_phrase', $phrase);
        // 返回验证码图片的Base64编码和文本
        $imageData = $builder->inline();

        return [
            'value' => $phrase,
            'image' => $imageData
        ];
    }

    /**
     * @param string $value
     * @param bool $ignoreCase
     * @return bool
     */
    public static function verify(string $value, bool $ignoreCase = true): bool
    {
        if ($phrase = app()->session->get('captcha_phrase')) {
            if ($ignoreCase) {
                $phrase = strtolower($phrase);
                $value = strtolower($value);
            }
            $result = $phrase === $value;
            app()->session->delete('captcha_phrase');
            return $result;
        }
        return false;
    }
}
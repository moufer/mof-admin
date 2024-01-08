<?php

namespace mof;

class Util
{
    public static function getCommonFileFormat($mime): string
    {
        $mimeTypes = [
            'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'],
            'video' => ['video/mp4', 'video/mpeg', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv', 'video/x-flv', 'video/x-matroska'],
            'audio' => ['audio/mpeg', 'audio/wav', 'audio/midi', 'audio/x-m4a', 'audio/x-ms-wma', 'audio/x-ms-wmv', 'audio/x-flac', 'audio/x-matroska'],
            'pdf'   => ['application/pdf', 'application/x-pdf'],
            'doc'   => ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls'   => ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'ppt'   => ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            'zip'   => ['application/zip', 'application/x-zip-compressed', 'application/gzip', 'application/x-bzip2', 'application/x-7z-compressed', 'application/x-rar-compressed', 'application/x-tar'],
        ];
        foreach ($mimeTypes as $format => $types) {
            if (in_array($mime, $types)) {
                return $format;
            }
        }

        return 'other';
    }

}
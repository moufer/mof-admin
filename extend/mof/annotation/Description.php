<?php

namespace mof\annotation;

#[\Attribute]
class Description
{
    public function __construct(public string $title)
    {
    }
}

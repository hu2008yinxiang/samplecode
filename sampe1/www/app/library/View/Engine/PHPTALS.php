<?php
@include __DIR__ . '/PHPTALS-EXT.php';

function phptal_tales_url($src, $nothrow)
{
    return '$ctx->di->get(\'url\')->get( ' . $src . ' )';
}
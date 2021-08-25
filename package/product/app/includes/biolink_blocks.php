<?php
/*
 * @copyright Copyright (c) 2021 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

$pro_blocks = \Altum\Plugin::is_active('pro-blocks') && file_exists(\Altum\Plugin::get('pro-blocks')->path . 'pro_blocks.php') ? include \Altum\Plugin::get('pro-blocks')->path . 'pro_blocks.php' : [];

return array_merge(
    [
        'link' => ['type' => 'default'],
        'text' => ['type' => 'default'],
        'image' => ['type' => 'default'],
        'mail' => ['type' => 'default'],
        'soundcloud' => ['type' => 'default'],
        'spotify' => ['type' => 'default'],
        'youtube' => ['type' => 'default'],
        'twitch' => ['type' => 'default'],
        'vimeo' => ['type' => 'default'],
        'tiktok' => ['type' => 'default'],
    ],
    $pro_blocks
);


<?php
/**
 * Download module config
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Hossein Azizabadi <azizabadi@faragostaresh.com>
 * @since           3.0
 * @package         Module\Download
 * @version         $Id$
 */

return array(
    'category' => array(
        array(
            'title' => __('Admin'),
            'name' => 'admin',
        ),
        array(
            'title' => __('Show'),
            'name' => 'show',
        ),
        array(
            'title' => __('Feed'),
            'name' => 'feed',
        ),
        array(
            'title' => __('Image'),
            'name' => 'image',
        ),
        array(
            'title' => __('File'),
            'name' => 'file',
        ),
        array(
            'title' => __('Social'),
            'name' => 'social',
        ),
        array(
            'title' => __('Vote'),
            'name' => 'vote',
        ),
    ),
    'item' => array(
        // Generic
        'advertisement' => array(
            'title' => __('Advertisement'),
            'description' => '',
            'edit' => 'textarea',
            'filter' => 'string',
            'value' => ''
        ),
        // Admin
        'admin_perpage' => array(
            'category' => 'admin',
            'title' => __('Perpage'),
            'description' => '',
            'edit' => 'text',
            'filter' => 'number_int',
            'value' => 12
        ),
        // Feed 
        'feed_icon' => array(
            'category' => 'feed',
            'title' => __('Show feed icon'),
            'description' => '',
            'edit' => 'checkbox',
            'filter' => 'number_int',
            'value' => 1
        ),
        'feed_num' => array(
            'category' => 'feed',
            'title' => __('Feed number'),
            'description' => '',
            'edit' => 'text',
            'filter' => 'number_int',
            'value' => 10
        ),
        // File
        'file_size' => array(
            'category' => 'file',
            'title' => __('File Size'),
            'description' => '',
            'edit' => 'text',
            'filter' => 'number_int',
            'value' => 10000000
        ),
        'file_path' => array(
            'category' => 'file',
            'title' => __('File path'),
            'description' => '',
            'edit' => 'text',
            'filter' => 'string',
            'value' => 'download'
        ),
        'file_extension' => array(
            'category' => 'file',
            'title' => __('File Extension'),
            'description' => '',
            'edit' => 'textarea',
            'filter' => 'string',
            'value' => 'jpg,png,gif,avi,flv,mp3,mp4,pdf,docs,xdocs,zip,rar'
        ),
        // Image
        'image_size' => array(
            'category' => 'image',
            'title' => __('Image Size'),
            'description' => '',
            'edit' => 'text',
            'filter' => 'number_int',
            'value' => 10000000
        ),
        'image_path' => array(
            'category' => 'image',
            'title' => __('Image path'),
            'description' => '',
            'edit' => 'text',
            'filter' => 'string',
            'value' => 'download/image'
        ),
        'image_extension' => array(
            'category' => 'image',
            'title' => __('Image Extension'),
            'description' => '',
            'edit' => 'text',
            'filter' => 'string',
            'value' => 'jpg,png,gif'
        ),
        'image_mediumh' => array(
            'category' => 'image',
            'title' => __('Medium Image height'),
            'description' => '',
            'edit' => 'text',
            'filter' => 'number_int',
            'value' => 600
        ),
        'image_mediumw' => array(
            'category' => 'image',
            'title' => __('Medium Image width'),
            'description' => '',
            'edit' => 'text',
            'filter' => 'number_int',
            'value' => 600
        ),
        'image_thumbh' => array(
            'category' => 'image',
            'title' => __('Thumb Image height'),
            'description' => '',
            'edit' => 'text',
            'filter' => 'number_int',
            'value' => 200
        ),
        'image_thumbw' => array(
            'category' => 'image',
            'title' => __('Thumb Image width'),
            'description' => '',
            'edit' => 'text',
            'filter' => 'number_int',
            'value' => 200
        ),
        // Social
        'social_bookmark' => array(
            'category' => 'social',
            'title' => __('Show Bookmark'),
            'description' => '',
            'edit' => 'checkbox',
            'filter' => 'number_int',
            'value' => 1
        ),
        'social_gplus' => array(
            'category' => 'social',
            'title' => __('Show Google Plus'),
            'description' => '',
            'edit' => 'checkbox',
            'filter' => 'number_int',
            'value' => 1
        ),
        'social_facebook' => array(
            'category' => 'social',
            'title' => __('Show facebook'),
            'description' => '',
            'edit' => 'checkbox',
            'filter' => 'number_int',
            'value' => 1
        ),
        'social_twitter' => array(
            'category' => 'social',
            'title' => __('Show twitter'),
            'description' => '',
            'edit' => 'checkbox',
            'filter' => 'number_int',
            'value' => 1
        ),
        // Vote
        'vote_bar' => array(
            'category' => 'vote',
            'title' => __('Use vote system'),
            'description' => '',
            'edit' => 'checkbox',
            'filter' => 'number_int',
            'value' => 1
        ),
        'vote_type' => array(
            'category' => 'vote',
            'title' => __('VoteBar type'),
            'description' => '',
            'filter' => 'string',
            'value' => 'plus',
            'edit' => array(
                'type' => 'select',
                'options' => array(
                    'options' => array(
                        'plus' => __('Plus'),
                        'star' => __('Star'),
                    ),
                ),
            ),
        ),
    ),
);
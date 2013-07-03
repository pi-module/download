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
    'admin' => array(
        'category' => array(
            'label' => __('Category'),
            'route' => 'admin',
            'controller' => 'category',
            'action' => 'index',
        ),
        'file' => array(
            'label' => __('File'),
            'route' => 'admin',
            'controller' => 'file',
            'action' => 'index',
        ),
        'limit' => array(
            'label' => __('Limit'),
            'route' => 'admin',
            'controller' => 'limit',
            'action' => 'index',
        ),
        'tools' => array(
            'label' => __('Tools'),
            'route' => 'admin',
            'controller' => 'tools',
            'action' => 'index',
        ),
    ),
);
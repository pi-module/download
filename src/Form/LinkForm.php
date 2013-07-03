<?php
/**
 * Category form
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

namespace Module\Download\Form;

use Pi;
use Pi\Form\Form as BaseForm;

class LinkForm extends BaseForm
{

    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new LinkFilter;
        }
        return $this->filter;
    }

    public function init()
    {
        // file
        $this->add(array(
            'name' => 'file',
            'attributes' => array(
                'type' => 'hidden',
                'id' => 'link_file',
            ),
        ));
        // title
        $this->add(array(
            'name' => 'title',
            'options' => array(
                'label' => __('Title'),
            ),
            'attributes' => array(
                'type' => 'text',
                'id' => 'link_title',
                'description' => '',
            )
        ));
        // link
        $this->add(array(
            'name' => 'link',
            'options' => array(
                'label' => __('Link'),
            ),
            'attributes' => array(
                'type' => 'text',
                'id' => 'link_link',
                'description' => '',
            )
        ));
        // Save
        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'value' => __('Submit'),
            )
        ));
    }
}  	
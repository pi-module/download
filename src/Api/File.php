<?php
/**
 * Download module File class
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

namespace Module\Download\Api;

use Pi;
use Pi\Application\AbstractApi;

/*
 * Pi::service('api')->download(array('File', 'SourceCount'), $id);
 */

class File extends AbstractApi
{
	/**
     * Set number of attach source for selected file
     */
    public function SourceCount($id)
    {
        // Get attach count
        $where = array('file' => $id);
        $columns = array('count' => new \Zend\Db\Sql\Predicate\Expression('count(*)'));
        $select = Pi::model('source', $this->getModule())->select()->columns($columns)->where($where);
        $count = Pi::model('source', $this->getModule())->selectWith($select)->current()->count;
        // Set attach count
        Pi::model('file', $this->getModule())->update(array('source' => $count), array('id' => $id));
    }
}	
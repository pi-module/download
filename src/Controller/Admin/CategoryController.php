<?php
/**
 * Download admin category controller
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

namespace Module\Download\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\File\Transfer\Upload;
use Module\Download\Form\CategoryForm;
use Module\Download\Form\CategoryFilter;

class CategoryController extends ActionController
{
    protected $ImagePrefix = 'image_';
    protected $categoryColumns = array(
	     'id', 'pid`', 'title', 'alias', 'information', 'keywords', 
	     'description', 'status', 'create', 'image' ,'path', 'image', 'count', 'order'
    );
    
    public function indexAction()
    {
        // Get page
        $page = $this->params('p', 1);
        // Get info
        $columns = array('id', 'pid', 'title', 'alias', 'create', 'status');
        $select = $this->getModel('category')->select()->columns($columns)->order(array('create DESC', 'id DESC'));
        $rowset = $this->getModel('category')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $category[$row->id] = $row->toArray();
            $category[$row->id]['create'] = date('Y/m/d H:i:s', $category[$row->id]['create']);
        }
        // Set paginator
        $paginator = \Pi\Paginator\Paginator::factory($category);
        $paginator->setItemCountPerPage($this->config('admin_perpage'));
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            // Use router to build URL for each page
            'pageParam' => 'p',
            'totalParam' => 't',
            'router' => $this->getEvent()->getRouter(),
            'route' => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params' => array(
                'module' => $this->getModule(),
                'controller' => 'category',
                'action' => 'index',
            ),
        ));
        // Set view
        $this->view()->setTemplate('category_index');
        $this->view()->assign('categories', $paginator);
    }
    
    public function removeAction()
    {
        // Get id and status
        $id = $this->params('id');
        // set story
        $category = $this->getModel('category')->find($id);
        // Check
        if ($category && !empty($id)) {
            // remove file
            $files = array(
                Pi::path('upload/' . $this->config('image_path') . '/original/' . $category->path . '/' . $category->image),
                Pi::path('upload/' . $this->config('image_path') . '/medium/' . $category->path . '/' . $category->image),
                Pi::path('upload/' . $this->config('image_path') . '/thumb/' . $category->path . '/' . $category->image),
            );
            Pi::service('file')->remove($files);
            // clear DB
            $category->image = '';
            $category->path = '';
            // Save
            if ($category->save()) {
                $message = sprintf(__('Image of %s removed'), $category->title);
                $status = 1;
            } else {
                $message = __('Image not remove');
                $status = 0;
            }
        } else {
            $message = __('Please select category');
            $status = 0;
        }
        return array(
            'status' => $status,
            'message' => $message,
        );
    }
    
    public function updateAction()
    {
        // Get id
        $id = $this->params('id');
        // Set story image url
        $options['imageurl'] = null;
        $options['module'] = $this->getModule();
        // Get story
        if ($id) {
            $values = $this->getModel('category')->find($id)->toArray();
            // Set story image url
            if ($values['image']) {
                $options['imageurl'] = Pi::url('upload/' . $this->config('image_path') . '/thumb/' . $values['path'] . '/' . $values['image']);
                $options['removeurl'] = $this->url('', array('action' => 'remove', 'id' => $values['id']));
            }
        }
        // Set form
        $form = new CategoryForm('category', $options);
        $form->setAttribute('enctype', 'multipart/form-data');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $file = $this->request->getFiles();
            $form->setInputFilter(new CategoryFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                // upload image
                if (!empty($file['image']['name'])) {
                    // Set path
                    $values['path'] = date('Y') . '/' . date('m');
                    $original_path = $this->config('image_path') . '/original/' . $values['path'];
                    $medium_path = $this->config('image_path') . '/medium/' . $values['path'];
                    $thumb_path = $this->config('image_path') . '/thumb/' . $values['path'];
                    // Do upload
                    $uploader = new Upload(array('destination' => $original_path, 'rename' => $this->ImagePrefix . '%random%'));
                    $uploader->setExtension($this->config('image_extension'));
                    $uploader->setSize($this->config('image_size'));
                    if ($uploader->isValid()) {
                        $uploader->receive();
                        // Get image name
                        $values['image'] = $uploader->getUploaded('image');
                        // Resize
                        $this->resize($values['image'], $original_path, $medium_path, $this->config('image_mediumw'), $this->config('image_mediumh'));
                        $this->resize($values['image'], $original_path, $thumb_path, $this->config('image_thumbw'), $this->config('image_thumbh'));
                    } else {
                        $this->jump(array('action' => 'update'), __('Problem in upload image. please try again'));
                    }
                } elseif (!isset($values['image'])) {
	                $values['image'] = '';	
                }
                // Set just category fields
                foreach (array_keys($values) as $key) {
                    if (!in_array($key, $this->categoryColumns)) {
                        unset($values[$key]);
                    }
                }
                // Set keywords
                $keywords = ($values['keywords']) ? $values['keywords'] : $values['title'];
                $values['keywords'] = $this->meta()->keywords($keywords);
                // Set description
                $description = ($values['description']) ? $values['description'] : $values['title'];
                $values['description'] = $this->meta()->description($description);
                // Set alias
                $alias = ($values['alias']) ? $values['alias'] : $values['title'];
                $values['alias'] = $this->alias($alias, $values['id'], $this->getModel('category'));
                // Set if new
                if (empty($values['id'])) {
                    // Set time
                    $values['create'] = time();
                    // Set order
                    $select = $this->getModel('category')->select()->columns(array('order'))->order(array('order DESC'))->limit(1);
                    $lastrow = $this->getModel('category')->selectWith($select);
                    $values['order'] = $lastrow + 1;
                }
                // Save values
                if (!empty($values['id'])) {
                    $row = $this->getModel('category')->find($values['id']);
                } else {
                    $row = $this->getModel('category')->createRow();
                }
                $row->assign($values);
                $row->save();
                // Check it save or not
                if ($row->id) {
                    $message = __('Category data saved successfully.');
                    $this->jump(array('action' => 'index'), $message);
                } else {
                    $message = __('Category data not saved.');
                }
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }
        } else {
            if ($id) {
                $form->setData($values);
                $message = 'You can edit this Category';
            } else {
                $message = 'You can add new Category';
            }
        }
        // Set view
        $this->view()->setTemplate('category_update');
        $this->view()->assign('form', $form);
        $this->view()->assign('title', __('Add a Category'));
        $this->view()->assign('message', $message);
    }
    
    public function deleteAction()
    {

    }	
}
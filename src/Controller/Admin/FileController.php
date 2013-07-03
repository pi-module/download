<?php
/**
 * Download admin file controller
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
use Module\Download\Form\FileForm;
use Module\Download\Form\FileFilter;
use Module\Download\Form\LinkForm;
use Module\Download\Form\LinkFilter;
use Zend\Json\Json;

class FileController extends ActionController
{
    protected $ImagePrefix = 'image_';
    protected $FilePrefix = 'file_';
    protected $fileColumns = array(
        'id' ,'category' ,'title' ,'alias' ,'information' ,'keywords' ,'description' ,'status' ,'create' ,
        'author' ,'view' ,'download' ,'image' ,'path' ,'comments' ,'point' ,'count' ,'source'
    );
    
    protected $sourceColumns = array(
        'id' ,'file' ,'mimetype' ,'location' ,'title' ,'status' ,'create' , 'size' ,
        'type' ,'death' ,'link' ,'source' ,'path' ,'url' ,'ip' ,'author' ,'download'
    );
    
    public function indexAction()
    {
        // Get page
        $page = $this->params('p', 1);
        // Set params
        $params = array(
            'module' => $this->getModule(),
            'controller' => 'file',
            'action' => 'index',
        );
        // Set where
        $where = array();
        //  Get category
        $category = $this->params('category');
        if (!empty($category)) {
            $where['category'] = $category;
            $params['category'] = $category;
            $this->view()->assign('back', 1);
        }
        // Get category list
        $select = $this->getModel('category')->select()->columns(array('id', 'title', 'alias'))->order(array('id DESC'));
        $rowset = $this->getModel('category')->selectWith($select);
        // Make category list
        foreach ($rowset as $row) {
            $categoryList[$row->id] = $row->toArray();
        }
        // Get info
        $columns = array('id', 'category', 'title', 'alias', 'create', 'status','source');
        $offset = (int)($page - 1) * $this->config('admin_perpage');
        $order = array('create DESC', 'id DESC');
        $limit = intval($this->config('admin_perpage'));
        $select = $this->getModel('file')->select()->where($where)->columns($columns)->offset($offset)->order($order)->limit($limit);
        $rowset = $this->getModel('file')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $file[$row->id] = $row->toArray();
            $file[$row->id]['categoryid'] = $categoryList[$row->category]['id'];
            $file[$row->id]['categorytitle'] = $categoryList[$row->category]['title'];
            $file[$row->id]['create'] = date('Y/m/d H:i:s', $file[$row->id]['create']);
        }
        // Set paginator
        $select = $this->getModel('file')->select()->columns(array('count' => new \Zend\Db\Sql\Predicate\Expression('count(*)')))->where($where);
        $count = $this->getModel('file')->selectWith($select)->current()->count;
        $paginator = \Pi\Paginator\Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($this->config('admin_perpage'));
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            // Use router to build URL for each page
            'pageParam' => 'p',
            'totalParam' => 't',
            'router' => $this->getEvent()->getRouter(),
            'route' => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params' => $params,
        ));
        // Set view
        $this->view()->setTemplate('file_index');
        $this->view()->assign('files', $file);
        $this->view()->assign('paginator', $paginator);
    }
    
    public function removeAction()
    {
        // Get id and status
        $id = $this->params('id');
        // set story
        $file = $this->getModel('file')->find($id);
        // Check
        if ($file && !empty($id)) {
            // remove file
            $files = array(
                Pi::path('upload/' . $this->config('image_path') . '/original/' . $category->path . '/' . $category->image),
                Pi::path('upload/' . $this->config('image_path') . '/medium/' . $category->path . '/' . $category->image),
                Pi::path('upload/' . $this->config('image_path') . '/thumb/' . $category->path . '/' . $category->image),
            );
            Pi::service('file')->remove($files);
            // clear DB
            $file->image = '';
            $file->path = '';
            // Save
            if ($file->save()) {
                $message = sprintf(__('Image of %s removed'), $file->title);
                $status = 1;
            } else {
                $message = __('Image not remove');
                $status = 0;
            }
        } else {
            $message = __('Please select file');
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
            $values = $this->getModel('file')->find($id)->toArray();
            // Set story image url
            if ($values['image']) {
                $options['imageurl'] = Pi::url('upload/' . $this->config('image_path') . '/thumb/' . $values['path'] . '/' . $values['image']);
                $options['removeurl'] = $this->url('', array('action' => 'remove', 'id' => $values['id']));
            }
        }
        // Set form
        $form = new FileForm('file', $options);
        $form->setAttribute('enctype', 'multipart/form-data');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $file = $this->request->getFiles();
            $form->setInputFilter(new FileFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                // Tag
                if (!empty($values['tag'])) {
                    $tag = explode(' ', $values['tag']);
                }
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
                // Set just file fields
                foreach (array_keys($values) as $key) {
                    if (!in_array($key, $this->fileColumns)) {
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
                $values['alias'] = $this->alias($alias, $values['id'], $this->getModel('file'));
                // Set if new
                if (empty($values['id'])) {
                    // Set time
                    $values['create'] = time();
                    // Set author
                    $values['author'] = Pi::registry('user')->id;
                }
                // Save values
                if (!empty($values['id'])) {
                    $row = $this->getModel('file')->find($values['id']);
                } else {
                    $row = $this->getModel('file')->createRow();
                }
                $row->assign($values);
                $row->save();
                // Tag
                if (isset($tag) && is_array($tag) && Pi::service('module')->isActive('tag')) {
                    if (empty($values['id'])) {
                        Pi::service('tag')->add($this->getModule(), $row->id, '', $tag);
                    } else {
                        Pi::service('tag')->update($this->getModule(), $row->id, '', $tag);
                    }
                }
                // update Category file count
                Pi::service('api')->download(array('Category', 'FileCount'), $row->category);
                // Check it save or not
                if ($row->id) {
                    $message = __('File data saved successfully.');
                    $url = array('action' => 'attach', 'id' => $row->id);
                    $this->jump($url, $message);
                } else {
                    $message = __('File data not saved.');
                }
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }
        } else {
            if ($id) {
            	 // Get tag list
                if (Pi::service('module')->isActive('tag')) {
                    $tag = Pi::service('tag')->get($this->getModule(), $values['id'], '');
                    if (is_array($tag)) {
                        $values['tag'] = implode(' ', $tag);
                    }
                }
                $form->setData($values);
                $message = 'You can edit this File';
                // Attach link
                $this->view()->assign('attach', $this->url('', array('action' => 'attach', 'id' => $values['id'])));
            } else {
                $message = 'You can add new File';
            }
        }
        // Set view
        $this->view()->setTemplate('file_update');
        $this->view()->assign('form', $form);
        $this->view()->assign('title', __('Add a File'));
        $this->view()->assign('message', $message);
    }
    
    public function attachAction()
    {
        // Get id
        $id = $this->params('id');
        if (empty($id)) {
            $this->jump(array('action' => 'index'), __('You must select file'));
        }
        // Get story
        $file = $this->getModel('file')->find($id)->toArray();
        if (empty($file)) {
            $this->jump(array('action' => 'index'), __('Your selected file not exist'));
        }
        // Get all attach files
        $select = $this->getModel('source')->select()->where(array('file' => $file['id']));
        $rowset = $this->getModel('source')->selectWith($select);
        // Make list
        $contents = array();
        foreach ($rowset as $row) {
            $source[$row->id] = $row->toArray();
            $source[$row->id]['create'] = date('Y/m/d', $source[$row->id]['create']);
            $source[$row->id]['preview'] = $this->filePreview($source[$row->id]['type']);
            if($source[$row->id]['type'] == 'link') {
            	$links[] = $source[$row->id];
            } else {
               $files[] = $source[$row->id];
            }		
        }
        //
        $form = new LinkForm('linkForm');
        $form->setAttribute('action', $this->url('', array('action' => 'link')));
        $form->setData(array('file' => $file['id']));
        // Set view
        $this->view()->setTemplate('file_attach');	
        $this->view()->assign('content', Json::encode($files));
        $this->view()->assign('links', $links);
        $this->view()->assign('file', $file);
        $this->view()->assign('form', $form);
        $this->view()->assign('title', sprintf(__('Attach files to %s'), $file['title']));
    }
    
    public function editAction()
    {
        // deactive log
        Pi::service('log')->active(false);
        if ($this->request->isPost()) {
            $values = $this->request->getPost();
            if (!empty($values['id'])) {
		          // Set just file fields
		          unset($values['submit']);	
		          foreach (array_keys($values) as $key) {
		              if (!in_array($key, $this->sourceColumns)) {
		                  unset($values[$key]);
		              }
		          } 
		          // save 
                $row = $this->getModel('source')->find($values['id']);	
                $row->assign($values);
                $row->save();
            }
        }
        $this->view()->setTemplate(false)->setLayout('layout-content');
    }
    
    public function linkAction()
    {
        // deactive log
        Pi::service('log')->active(false);
        // Set return
        $return = array(
            'status' => 1, 'message' => '', 'id' => '', 'title' => '', 'create' => ''
        );
        // Get id
        $file = $this->params('file');
        if (empty($file)) {
            $return = array(
                'status' => 0,
                'message' => __('You must select file'),
            );
        } else {
        	   // Get file
            $file = $this->getModel('file')->find($file)->toArray();
            if (empty($file)) {
                $return = array(
                    'status' => 0,
                    'message' => __('Your selected file not exist'),
                );
            } else {
                $form = new LinkForm('linkForm');
                if ($this->request->isPost()) {
		              $data = $this->request->getPost();
		              $form->setInputFilter(new LinkFilter);
		              $form->setData($data);
		              if ($form->isValid()) {
		                  $values = $form->getData();
		                  // Set just file fields
                        foreach (array_keys($values) as $key) {
                            if (!in_array($key, $this->sourceColumns)) {
                                unset($values[$key]);
                            }
                        }
                        // set info
                        $values['location'] = 'external';
                        $values['status'] = 1;
                        $values['type'] = 'link';
                        $values['create'] = time();
                        $values['ip'] = getenv('REMOTE_ADDR');
                        $values['author'] = Pi::registry('user')->id;
                        // save info
                        $row = $this->getModel('source')->createRow();
                        $row->assign($values);
                        $row->save();
                        // Set erturn array
                        $return['id'] = $row->id;
                        $return['create'] = date('Y/m/d', $row->create);
                        $return['title'] = $row->title;
                        $return['status'] = $row->status;
                        // Set file Attach count
                        Pi::service('api')->download(array('File', 'SourceCount'), $row->file);
                  } else {
                    $return = array(
                        'status' => 0,
                        'message' => __('Invalid data, please check and re-submit'),
                    );
                  }	
                } else {
                    $return = array(
                        'status' => 0,
                        'message' => __('Please post information'),
                    );
                }
            }
        }
        $this->view()->setTemplate(false)->setLayout('layout-content');
        return Json::encode($return);
    }
 
    public function fileAction()
    {
        // deactive log
        Pi::service('log')->active(false);
        // Set return
        $return = array(
            'status' => 1, 'message' => '', 'id' => '', 'title' => '', 'create' => '',
            'type' => '', 'status' => '', 'hits' => '', 'size' => '', 'preview' => '',
        );
        // Get id
        $id = $this->params('id');
        if (empty($id)) {
            $return = array(
                'status' => 0,
                'message' => __('You must select file'),
            );
        } else {
            // Get file
            $file = $this->getModel('file')->find($id)->toArray();
            if (empty($file)) {
                $return = array(
                    'status' => 0,
                    'message' => __('Your selected file not exist'),
                );
            } else {
                // start upload
                $path = date('Y') . '/' . date('m');
                $uploader = new Upload(array('destination' => $this->config('file_path') . '/file/' . $path, 'rename' => $this->FilePrefix . '%random%'));
                $uploader->setExtension($this->config('file_extension'));
                $uploader->setSize($this->config('file_size'));
                if ($uploader->isValid()) {
                    $uploader->receive();
                    // Set info
                    $source = $uploader->getUploaded('file');
                    // Set save array
                    $values['file'] = $file['id'];
                    $values['type'] = $this->fileType($source);
                    $values['mimetype'] = $this->fileMimeType($path, $source);
                    $values['size'] = $this->fileSize($path, $source);
                    $values['source'] = $source;
                    $values['path'] = $path;
                    $values['location'] = 'internal';
                    $values['status'] = 1;
                    $values['create'] = time();
                    $values['ip'] = getenv('REMOTE_ADDR');
                    $values['url'] = Pi::Url();
                    $values['author'] = Pi::registry('user')->id;
                    // save in DB
                    $row = $this->getModel('source')->createRow();
                    $row->assign($values);
                    $row->save();
                    // Set erturn array
                    $return['id'] = $row->id;
                    $return['create'] = date('Y/m/d', $row->create);
                    $return['type'] = $row->type;
                    $return['title'] = $row->title;
                    $return['status'] = $row->status;
                    $return['size'] = $row->size;
                    $return['preview'] = $this->filePreview($row->type);
                    // Set file Attach count
                    Pi::service('api')->download(array('File', 'SourceCount'), $row->file);
                } else {
                    // Upload error
                    $messages = $uploader->getMessages();
                    $return = array(
                        'status' => 0,
                        'message' => implode('; ', $messages),
                    );
                }
            }
        }
        $this->view()->setTemplate(false)->setLayout('layout-content');
        return Json::encode($return);
    }
    
    protected function filePreview($type)
    {
        $image = 'image/' . $type . '.png';
        $preview = Pi::service('asset')->getModuleAsset($image, $this->getModule());
        return $preview;
    }
    
    protected function fileMimeType($path, $source)
    {
        $source = Pi::Path('upload/' . $this->config('file_path') . '/file/' . $path . '/' . $source);
        $finfo = finfo_open();
        $fileinfo = finfo_file($finfo, $source, FILEINFO_MIME_TYPE);
        finfo_close($finfo);
        return $fileinfo;
    }
    
    protected function fileSize($path, $source)
    {
        $source = Pi::Path('upload/' . $this->config('file_path') . '/file/' . $path . '/' . $source);
        return filesize($source);
    }	
    	
    protected function fileType($source)
    {
        $extension = pathinfo($source, PATHINFO_EXTENSION);
        switch ($extension) {
            case 'zip':
            case 'rar':
            case 'tar':
                $type = 'archive';
                break;

            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                $type = 'image';
                break;

            case 'avi':
            case 'flv':
            case 'mp4':
            case 'webm':
            case 'ogv':
                $type = 'video';
                break;

            case 'mp3':
            case 'ogg':
                $type = 'audio';
                break;

            case 'pdf':
                $type = 'pdf';
                break;

            case 'doc':
            case 'docx':
                $type = 'doc';
                break;

            default:
                $type = 'other';
                break;
        }
        // return
        return $type;
    }
}
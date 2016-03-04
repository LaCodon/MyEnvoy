<?php

use Famework\LaCodon\Param\Paramhandler;
use Famework\Request\Famework_Request;

//use Famework\Registry\Famework_Registry;

class PostController extends Controller {

    const MAX_POST_SIZE = 10000;
    const MAX_COMMENT_SIZE = 5000;

    public function init() {
        parent::init();
        $this->_view->user = Currentuser::auth();
        $this->_paramHandler = new Paramhandler();
    }

    public function addAction() {
        $this->_paramHandler->bindMethods(Paramhandler::POST);

        $content = $this->_paramHandler->getValue('post', TRUE, 1, self::MAX_POST_SIZE);
        $groupID = $this->_paramHandler->getInt('group');

        // validate group
        $possibleGroups = $this->_view->user->getGroupOverview();
        if (!isset($possibleGroups[$groupID])) {
            throw new Exception('Disallowed recipient detected.');
        }

        $content = Security::trim($content);

        Post::insert($this->_view->user, $content, array($groupID));
        Group::setAsDefault($groupID, $this->_view->user);

        Famework_Request::redirect('/' . APPLICATION_LANG . '/dashboard/index');
    }

    public function removeAction() {
        $this->_paramHandler->bindMethods(Paramhandler::GET);

        $postID = $this->_paramHandler->getInt('id');

        $post = Post::getById($postID);

        if (empty($post)) {
            throw new Exception('Post not availabel.');
        }

        if ($post->getOwnerId() !== $this->_view->user->getId()) {
            throw new Exception('Disallowed action.');
        }

        $post->remove();

        Famework_Request::redirect('/' . APPLICATION_LANG . '/dashboard/index');
    }

    private function validatePostForFavAction() {
        $this->_paramHandler->bindMethods(Paramhandler::GET);

        $postID = $this->_paramHandler->getInt('id');
        $post = Post::getById($postID);

        if (empty($post)) {
            throw new Exception('Post not availabel.', Errorcode::POSTCONTROLLER_POST_NOT_AVAILABEL);
        }

        if ($this->_view->user->canSeePost($post->getId()) !== TRUE) {
            throw new Exception('Disallowed action.', Errorcode::POSTCONTROLLER_POST_DISALLOWED_ACTION);
        }

        return $post;
    }

    public function favAction() {
        $this->doFavChange(self::FAV);
    }

    public function defavAction() {
        $this->doFavChange(self::DEFAV);
    }

    const FAV = 1;
    const DEFAV = 2;

    private function doFavChange($action) {
        $this->_paramHandler->bindMethods(Paramhandler::GET);
        $redirectAction = $this->_paramHandler->getValue('redirectlocation', FALSE, 3, 40);

        $post = $this->validatePostForFavAction();
        if ($action === self::FAV) {
            $post->fav($this->_view->user);
        } elseif ($action === self::DEFAV) {
            $post->defav($this->_view->user);
        }

        if ($redirectAction !== NULL) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/user/' . $redirectAction);
        } else {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/dashboard/index');
        }
    }

    public function commentAction() {
        $this->_paramHandler->bindMethods(Paramhandler::GET);
        $redirectAction = $this->_paramHandler->getValue('redirectlocation', FALSE, 3, 40);

        $this->_paramHandler->bindMethods(Paramhandler::POST);
        $content = $this->_paramHandler->getValue('post', TRUE, 1, self::MAX_COMMENT_SIZE);
        $tmpID = $this->_paramHandler->getInt('id');
        $post = Post::getById($tmpID);

        if (empty($post)) {
            throw new Exception('Post not availabel.');
        }

        if ($this->_view->user->canSeePost($post->getId()) !== TRUE || $post->isSubComment() === TRUE) {
            throw new Exception('Disallowed action.');
        }

        $groupIDs = $post->getGroupIds();
        $postID = $post->getId();
        $content = Security::trim($content);

        Post::insert($this->_view->user, $content, $groupIDs, $postID);

        if ($redirectAction !== NULL) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/user/' . $redirectAction);
        } else {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/dashboard/index');
        }
    }

}

<?php

use Famework\LaCodon\Param\Paramhandler;
use Famework\Registry\Famework_Registry;
use Famework\Request\Famework_Request;

class UserController extends Controller {

    public function init() {
        parent::init();
        $this->_view->user = Currentuser::auth();
        $this->_paramHandler = new Paramhandler();
        $this->_view->title($this->_view->user->getName() . '@MyEnvoy');
        $this->_view->addJS(HTTP_ROOT . 'js/jquery-2.1.4.min.js');
        $this->_view->addJS(HTTP_ROOT . 'js/popover.min.js');
        $this->_view->addJS(HTTP_ROOT . 'js/dropdown.js');
        $this->_view->addCSS(HTTP_ROOT . APPLICATION_LANG . '/style/custom');
    }

    public function indexAction() {
        // get username from URL via famework
        $famework = Famework_Registry::get('\famework_sys');
        $username = $famework->getRequestParam('username');
        unset($famework);

        if (preg_match('/^[a-z0-9.]{3,40}$/', $username) !== 1) {
            $this->_view->error = TRUE;
        }

        $this->_view->otheruser = Otheruser::getLocalByName($username, $this->_view->user->getId());

        $this->_view->status = NULL;
        if (empty($this->_view->otheruser)) {
            $this->_view->error = TRUE;
        } else {
            $this->_view->title(sprintf(t('user_index_title'), $this->_view->otheruser->getName()));
            $this->_view->connectionType = $this->_view->user->getConnectionWith($this->_view->otheruser);
            if ($this->_view->connectionType & Currentuser::I_AM_FOLLOWING === Currentuser::I_AM_FOLLOWING) {
                $this->_view->addJS(HTTP_ROOT . 'js/comment.js');
                $this->_view->posts = $this->_view->otheruser->getViewablePosts($this->_view->user);
            } else {
                $this->_view->posts = $this->_view->otheruser->getPublicPosts();
            }
            $this->_view->status = $this->_view->otheruser->getStatus();

            if (empty($this->_view->status)) {
                $this->_view->status = t('user_index_emptystatus');
            }
        }
    }

    public function followAction() {
        $this->_paramHandler->bindMethods(Paramhandler::GET);
        $user_id = $this->_paramHandler->getInt('id');

        $otheruser = new Otheruser($user_id, $this->_view->user->getId());
        $otheruser->follow($this->_view->user);

        Famework_Request::redirect('/' . APPLICATION_LANG . '/user/' . $otheruser->getFullQualifiedName());
    }

    public function unfollowAction() {
        $this->_paramHandler->bindMethods(Paramhandler::GET);
        $user_id = $this->_paramHandler->getInt('id');

        $otheruser = new Otheruser($user_id, $this->_view->user->getId());
        $otheruser->unfollow($this->_view->user);

        Famework_Request::redirect('/' . APPLICATION_LANG . '/user/' . $otheruser->getFullQualifiedName());
    }

}

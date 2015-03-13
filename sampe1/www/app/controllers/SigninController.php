<?php
use Phalcon\Mvc\Controller;
use Facebook\FacebookSession;
session_start();
\Facebook\FacebookSession::enableAppSecretProof(false);

class SigninController extends Controller
{

    public function indexAction()
    {
        $view = $this->view;
        
        // \Facebook\FacebookSession::setDefaultApplication ( $this->di->get ( 'config' )->bind_config->facebook->AppId, $this->di->get ( 'config' )->bind_config->facebook->Secret );
        $helper = new \Facebook\FacebookRedirectLoginHelper($this->url->get('signin/facebook'));
        $loginUrl = $helper->getLoginUrl(array(
            'email',
            'public_profile',
            'user_friends'
        ));
        $view->setVar('fbLoginUrl', $loginUrl);
    }

    public function facebookAction()
    {
        
        // \Facebook\FacebookSession::setDefaultApplication ( $this->di->get ( 'config' )->bind_config->facebook->AppId, $this->di->get ( 'config' )->bind_config->facebook->Secret );
        $helper = new \Facebook\FacebookRedirectLoginHelper($this->url->get('signin/facebook'));
        // try {
        $session = $helper->getSessionFromRedirect();
        if (! $session) {
            $this->flashSession->error('You need try again.');
            return $this->response->redirect('signin/index');
        }
        $session = $session->getLongLivedSession($this->di->get('config')->bind_config->facebook->AppId, $this->di->get('config')->bind_config->facebook->Secret);
        $token = $session->getToken();
        $this->session->set('facebook_token', $token);
        return $this->response->redirect('signin/fbProfile');
        // } catch ( Exception $ex ) {
        // $this->flashSession->error ( 'You need try again.' );
        // return $this->response->redirect ( 'signin/index' );
        // echo $ex->getTraceAsString ();
        // }
    }

    public function fbProfileAction()
    {
        // \Facebook\FacebookSession::setDefaultApplication ( $this->di->get ( 'config' )->bind_config->facebook->AppId, $this->di->get ( 'config' )->bind_config->facebook->Secret );
        // $token = $this->session->get ( 'facebook_token' );
        $token = 'CAAD1uvChYZCYBABdliZA3ZCL4jZAM9zvx1wI7xKLdHqwaMM87YTZCRqHqfcARpeY4AP0x9qZCwyGwnHZAv6ZBuQdfQjQzh59kPHrSTSnglTeSOwWzOot4UdCMAMnhbM8tEJyPNkeqgPMEgVvduXfWqwtVkW8E5z7sbwQE7yG2JP7k49RlBqo4moqoGxhwXrAg3kZD';
        $session = new FacebookSession($token);
        // $session->validate ( $this->di->get ( 'config' )->bind_config->facebook->AppId, $this->di->get ( 'config' )->bind_config->facebook->Secret );
        // $facebook = new \Facebook ( '270183249830902', 'b96c4724cff67ef0ee5c49c8c7673a81', $token );
        echo $token;
        echo '<br>';
        echo $session->getLongLivedSession($this->di->get('config')->bind_config->facebook->AppId, $this->di->get('config')->bind_config->facebook->Secret)
            ->getToken();
    }
}
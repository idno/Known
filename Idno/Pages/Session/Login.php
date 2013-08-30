<?php

/**
 * Defines built-in log in functionality
 */

namespace Idno\Pages\Session {

    /**
     * Default class to serve the homepage
     */
    class Login extends \Idno\Common\Page
    {

        function getContent()
        {
            $t = \Idno\Core\site()->template();
            $t->body = $t->draw('session/login');
            $t->title = 'Sign in';
            $t->drawPage();
        }

        function postContent()
        {
            // TODO: change this to actual basic login, of course
            if ($user = \Idno\Entities\User::getByHandle($this->getInput('email'))) {
            } else if ($user = \Idno\Entities\User::getByEmail($this->getInput('email'))) {
            } else {
                \Idno\Core\site()->triggerEvent('login:failure:nouser', ['handle_or_email' => $this->getInput('email')]); 
                $this->setResponse(401);
                $this->forward('/session/login');
            }

            if ($user instanceof \Idno\Entities\User) {
                if ($user->checkPassword($this->getInput('password'))) {
                    \Idno\Core\site()->triggerEvent('login:success', ['user' => $user]); // Trigger an event for auditing
                    \Idno\Core\site()->session()->logUserOn($user);
                    \Idno\Core\site()->session()->addMessage("You've signed in as {$user->getTitle()}.");
                    $this->forward();
                } else {
                    \Idno\Core\site()->triggerEvent('login:failure:password', ['user' => $user]); 
                }
            }
        }

    }

}
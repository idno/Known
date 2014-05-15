<?php

    /**
     * Session management class
     *
     * @package known
     * @subpackage core
     */

    namespace known\Core {

        use known\Entities\User;

        class Session extends \known\Common\Component
        {

            function init()
            {

                ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30); // Persistent cookies
                //ini_set('session.cookie_httponly', true); // Restrict cookies to HTTP only (help reduce XSS attack profile)

                $sessionHandler = new \Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler(\known\Core\site()->db()->getClient(), [
                    'database'   => 'knownsession',
                    'collection' => 'knownsession'
                ]);
                session_set_save_handler($sessionHandler, true);

                session_name(site()->config->sessionname);
                session_start();
                session_cache_limiter('public');

                // Session login / logout
                site()->addPageHandler('/session/login', '\known\Pages\Session\Login');
                site()->addPageHandler('/session/logout', '\known\Pages\Session\Logout');
                site()->addPageHandler('/currentUser/?', '\known\Pages\Session\CurrentUser');

                // Update the session on save, this is a shim until #46 is fixed properly with #49
                \known\Core\site()->addEventHook('save', function (\known\Core\Event $event) {

                    $object = $event->data()['object'];
                    if ((!empty($object)) && ($object instanceof \known\Entities\User) // Object is a user
                        && ((!empty($_SESSION['user'])) && ($object->getUUID() == $_SESSION['user']->getUUID()))
                    ) // And we're not trying a user change (avoids a possible exploit)
                    {
                        $_SESSION['user'] = $object;
                    }

                });
            }

            /**
             * Get the UUID of the currently logged-in user, or false if
             * we're logged out
             *
             * @return mixed
             */

            function currentUserUUID()
            {
                if ($this->isLoggedOn()) {
                    return $this->currentUser()->getUUID();
                }

                return false;
            }

            /**
             * Wrapper function for isLoggedIn()
             * @see known\Core\Session::isLoggedIn()
             * @return true|false
             */

            function isLoggedOn()
            {
                return $this->isLoggedIn();
            }

            /**
             * Is a user logged into the current session?
             * @return true|false
             */
            function isLoggedIn()
            {
                if (!empty($_SESSION['user']) && $_SESSION['user'] instanceof \known\Entities\User) {
                    return true;
                }

                return false;
            }

            /**
             * Returns the currently logged-in user, if any
             * @return \known\Entities\User
             */

            function currentUser()
            {
                if (!empty($_SESSION['user']))
                    return $_SESSION['user'];

                return false;
            }

            /**
             * Adds a message to the queue to be delivered to the user as soon as is possible
             * @param string $message The text of the message
             * @param string $message_type This type of message; this will be added to the displayed message class, or returned as data
             */

            function addMessage($message, $message_type = 'alert-info')
            {
                if (empty($_SESSION['messages'])) $_SESSION['messages'] = array();
                $_SESSION['messages'][] = array('message' => $message, 'message_type' => $message_type);
            }

            /**
             * Retrieve any messages from the session, remove them from the session, and return them
             * @return array
             */
            function getAndFlushMessages()
            {
                $messages = $this->getMessages();
                $this->flushMessages();

                return $messages;
            }

            /**
             * Retrieve any messages waiting for the user in the session
             * @return array
             */
            function getMessages()
            {
                if (!empty($_SESSION['messages'])) {
                    return $_SESSION['messages'];
                } else {
                    return array();
                }
            }

            /**
             * Remove any messages from the session
             */
            function flushMessages()
            {
                $_SESSION['messages'] = array();
            }

            /**
             * Get access groups the current user is allowed to write to
             * @return array
             */

            function getWriteAccessGroups()
            {
                if ($this->isLoggedOn())
                    return $this->currentUser()->getWriteAccessGroups();

                return array();
            }

            /**
             * Get IDs of the access groups the current user is allowed to write to
             * @return array
             */

            function getWriteAccessGroupIDs()
            {
                if ($this->isLoggedOn())
                    return $this->currentUser()->getWriteAccessGroups();

                return array();
            }

            /**
             * Get access groups the current user (if any) is allowed to read from
             * @return array
             */

            function getReadAccessGroups()
            {
                if ($this->isLoggedOn())
                    return $this->currentUser()->getReadAccessGroups();

                return array('PUBLIC');
            }

            /**
             * Get IDs of the access groups the current user (if any) is allowed to read from
             * @return array
             */

            function getReadAccessGroupIDs()
            {
                $group = array('PUBLIC');
                if ($this->isLoggedOn()) {
                    $group = $this->currentUser()->getReadAccessGroupIDs();
                }

                return $group;
            }

            /**
             * Log the current session user off
             * @return true
             */

            function logUserOff()
            {
                unset($_SESSION['user']);
                session_destroy();

                return true;
            }

            /**
             * Set a piece of session data
             * @param string $name
             * @param mixed $value
             */
            function set($name, $value)
            {
                $_SESSION[$name] = $value;
            }

            /**
             * Retrieve the session data with key $name, if it exists
             * @param string $name
             * @return mixed
             */
            function get($name)
            {
                if (!empty($_SESSION[$name])) {
                    return $_SESSION[$name];
                } else {
                    return false;
                }
            }

            /**
             * Remove data with key $name from the session
             * @param $name
             */
            function remove($name)
            {
                unset($_SESSION[$name]);
            }

            /**
             * Checks HTTP request headers to see if the request has been properly
             * signed for API access, and if so, log the user on and return the user
             *
             * @return \known\Entities\User|false The logged-in user, or false otherwise
             */

            function APIlogin()
            {

                if (!empty($_SERVER['HTTP_X_known_USERNAME']) && !empty($_SERVER['HTTP_X_known_SIGNATURE'])) {
                    if ($user = \known\Entities\User::getByHandle($_SERVER['HTTP_X_known_USERNAME'])) {
                        $key          = $user->getAPIkey();
                        $hmac         = trim($_SERVER['HTTP_X_known_SIGNATURE']);
                        $compare_hmac = base64_encode(hash_hmac('sha256', $_SERVER['REQUEST_URI'], $key, true));
                        if ($hmac == $compare_hmac) {
                            \known\Core\site()->session()->logUserOn($user);
                            \known\Core\site()->session()->setIsAPIRequest(true);

                            return $user;
                        }
                    }
                }

                return false;
            }

            /**
             * Log the specified user on (note that this is NOT the same as taking the user's auth credentials)
             *
             * @param \known\Entities\User $user
             * @return \known\Entities\User
             */

            function logUserOn(\known\Entities\User $user)
            {
                return $this->refreshSessionUser($user);
            }

            /**
             * Refresh the user currently stored in the session
             * @param \known\Entities\User $user
             * @return \known\Entities\User
             */
            function refreshSessionUser(\known\Entities\User $user)
            {
                $user = User::getByUUID($user->getUUID());
                $_SESSION['user'] = $user;
                return $user;
            }

            /**
             * Sets whether this session is an API request or a manual browse
             * @param boolean $is_api_request
             */
            function setIsAPIRequest($is_api_request)
            {
                $is_api_request             = (bool)$is_api_request;
                $_SESSION['is_api_request'] = $is_api_request;
            }

            /**
             * Is this session an API request?
             * @return bool
             */
            function isAPIRequest()
            {
                if (!empty($_SESSION['is_api_request'])) {
                    return true;
                }

                return false;
            }

        }

    }
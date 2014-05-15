<?php

    /**
     * Handles pages in the system (and, by extension, the known API).
     *
     * Developers shoudld extend the getContent, postContent and dataContent
     * methods as follows:
     *
     * getContent: echoes HTML to the page
     *
     * postContent: handles content submitted to the page (assuming that form
     * elements were correctly signed)
     *
     * @package known
     * @subpackage core
     */

    namespace known\Common {

        use known\Entities\User;

        class Page extends \known\Common\Component
        {

            // Property that defines whether this page may forward to
            // other pages. True by default.
            public $forward = true;

            // Property intended to store parsed data from JSON magic input
            // variable
            public $data = array();

            // Stores the response code that we'll be sending back. Can be
            // changed with setResponse
            public $response = 200;

            // Stores arguments given to page handlers, for parsing of regular
            // expression matches
            public $arguments = array();

            // Is this the canonical permalink page for an object? Defaults
            // to no, but you can use $this->setPermalink() to change this
            public $isPermalinkPage = false;

            // Is this an XmlHTTPRequest (AJAX) call?
            public $xhr = false;

            // Who owns this page, anyway?
            public $owner = false;
	    
	    // Page assets that can be registered and set by plugins (javascript, css, etc)
	    public $assets = [];

            function init()
            {
                header('X-Powered-By: http://known.co');
                if ($template = $this->getInput('_t')) {
                    if (\known\Core\site()->template()->templateTypeExists($template)) {
                        \known\Core\site()->template()->setTemplateType($template);
                    }
                }
                \known\Core\site()->setCurrentPage($this);

                // Default exception handler
                set_exception_handler(function ($exception) {
                    $page = \known\Core\site()->currentPage();
                    if (!empty($page))
                        $page->exception($exception);

                });
            }

            /**
             * Internal function used to handle GET requests.
             * Performs some administration functions and hands off to
             * getContent().
             */
            function get()
            {

                if ($this->isAcceptedContentType('application/json')) {
                    \known\Core\site()->template()->setTemplateType('json');
                }

                \known\Core\site()->session()->APIlogin();
                $this->parseJSONPayload();

                $arguments = func_get_args();
                if (!empty($arguments)) $this->arguments = $arguments;

                \known\Core\site()->triggerEvent('page/get', ['page_class' => get_called_class(), 'arguments' => $arguments]);

                $this->getContent();

                if (http_response_code() != 200)
                    http_response_code($this->response);
            }

            /**
             * Internal function used to handle POST requests.
             * Performs some administration functions, checks for the
             * presence of a POST token, and hands off to postContent().
             */
            function post()
            {

                if ($this->isAcceptedContentType('application/json')) {
                    \known\Core\site()->template()->setTemplateType('json');
                }

                \known\Core\site()->session()->APIlogin();

                $arguments = func_get_args();
                if (!empty($arguments)) $this->arguments = $arguments;

                \known\Core\site()->triggerEvent('page/post', ['page_class' => get_called_class(), 'arguments' => $arguments]);

                if (\known\Core\site()->actions()->validateToken('', false)) {
                    $this->parseJSONPayload();
                    $this->postContent();
                } else {

                }
                $this->forward(); // If we haven't forwarded yet, do so (if we can)
                if (http_response_code() != 200)
                    http_response_code($this->response);
            }

            /**
             * Internal function used to handle PUT requests.
             * Performs some administration functions, checks for the
             * presence of a form token, and hands off to postContent().
             */
            function put()
            {

                if ($this->isAcceptedContentType('application/json')) {
                    \known\Core\site()->template()->setTemplateType('json');
                }

                $arguments = func_get_args();
                if (!empty($arguments)) $this->arguments = $arguments;

                \known\Core\site()->triggerEvent('page/put', ['page_class' => get_called_class(), 'arguments' => $arguments]);

                if (\known\Core\site()->actions()->validateToken('', false)) {
                    \known\Core\site()->session()->APIlogin();
                    $this->parseJSONPayload();
                    $this->putContent();
                } else {

                }
                $this->forward(); // If we haven't forwarded yet, do so (if we can)
                if (http_response_code() != 200)
                    http_response_code($this->response);
            }

            /**
             * Internal function used to handle DELETE requests.
             * Performs some administration functions, checks for the
             * presence of a form token, and hands off to postContent().
             */
            function delete()
            {

                if ($this->isAcceptedContentType('application/json')) {
                    \known\Core\site()->template()->setTemplateType('json');
                }

                $arguments = func_get_args();
                if (!empty($arguments)) $this->arguments = $arguments;

                \known\Core\site()->triggerEvent('page/delete', ['page_class' => get_called_class(), 'arguments' => $arguments]);

                if (\known\Core\site()->actions()->validateToken('', false)) {
                    \known\Core\site()->session()->APIlogin();
                    $this->parseJSONPayload();
                    $this->deleteContent();
                } else {

                }
                $this->forward(); // If we haven't forwarded yet, do so (if we can)
                if (http_response_code() != 200)
                    http_response_code($this->response);
            }

            /**
             * Automatically matches JSON/XMLHTTPRequest GET requests.
             * Sets the template to JSON and then calls get().
             */
            function get_xhr()
            {
                if ($this->isAcceptedContentType('application/json')) {
                    \known\Core\site()->template()->setTemplateType('json');
                }
                $arguments = func_get_args();
                if (!empty($arguments)) $this->arguments = $arguments;
                $this->xhr = true;
                $this->get();
            }

            /**
             * Automatically matches JSON/XMLHTTPRequest POST requests.
             * Sets the template to JSON and then calls post().
             */
            function post_xhr()
            {
                if ($this->isAcceptedContentType('application/json')) {
                    \known\Core\site()->template()->setTemplateType('json');
                }
                $arguments = func_get_args();
                if (!empty($arguments)) $this->arguments = $arguments;
                $this->xhr     = true;
                $this->forward = false;
                $this->post();
            }

            /**
             * Automatically matches JSON/XMLHTTPRequest PUT requests.
             * Sets the template to JSON and then calls put().
             */
            function put_xhr()
            {
                if ($this->isAcceptedContentType('application/json')) {
                    \known\Core\site()->template()->setTemplateType('json');
                }
                $arguments = func_get_args();
                if (!empty($arguments)) $this->arguments = $arguments;
                $this->xhr     = true;
                $this->forward = false;
                $this->put();
            }

            /**
             * Automatically matches JSON/XMLHTTPRequest PUT requests.
             * Sets the template to JSON and then calls delete().
             */
            function delete_xhr()
            {
                if ($this->isAcceptedContentType('application/json')) {
                    \known\Core\site()->template()->setTemplateType('json');
                }
                $arguments = func_get_args();
                if (!empty($arguments)) $this->arguments = $arguments;
                $this->xhr     = true;
                $this->forward = false;
                $this->delete();
            }

            /**
             *
             */
            function webmention()
            {
                if ($this->isAcceptedContentType('application/json')) {
                    \known\Core\site()->template()->setTemplateType('json');
                }
                $this->forward = false;
                //$this->webmentionContent();
            }

            /**
             * To be extended by developers
             */
            function getContent()
            {
            }

            /**
             * To be extended by developers
             */
            function postContent()
            {
            }

            /**
             * To be extended by developers
             */
            function putContent()
            {
            }

            /**
             * To be extended by developers
             */
            function deleteContent()
            {
            }

            /**
             * Called when there's been a successful webmention call to the given page.
             * To be extended by developers.
             *
             * @param string $source The source URL (i.e., third-party site URL)
             * @param string $target The target URL (i.e., this page)
             * @param string $source_content The full HTML content of the source URL
             * @param array $source_mf2 The full, parsed Microformats 2 content of the source URL
             * @return bool
             */
            function webmentionContent($source, $target, $source_content, $source_mf2)
            {
                return true;
            }

            /**
             * Page handler for when a resource has disappeared.
             */
            function goneContent()
            {
                $this->setResponse(410);
                http_response_code($this->response);
                $t = \known\Core\site()->template();
                $t->__(['body' => $t->draw('pages/410'), 'title' => 'Gone, baby, gone'])->drawPage();
                exit;
            }

            /**
             * Page handler for when a resource doesn't exist.
             */
            function noContent()
            {
                $this->setResponse(404);
                http_response_code($this->response);
                $t = \known\Core\site()->template();
                $t->__(['body' => $t->draw('pages/404'), 'title' => 'Not found!'])->drawPage();
                exit;
            }
	    
	    /**
	     * You can't see this.
	     */
	    function deniedContent()
            {
                $this->setResponse(403);
                http_response_code($this->response);
                $t = \known\Core\site()->template();
                $t->__(['body' => $t->draw('pages/403'), 'title' => 'Denied!'])->drawPage();
                exit;
            }
	    

            function exception(\Exception $e)
            {
                $this->setResponse(500);
                http_response_code($this->response);
                $t = \known\Core\site()->template();
                $t->__(['body' => $t->__(['exception' => $e])->draw('pages/500'), 'title' => 'Exception'])->drawPage();
                exit;
            }

            /**
             * If this page is allowed to forward, send a header to move
             * the browser on. Otherwise, do nothing
             *
             * @param string $location Location to forward to (eg "/foo/bar")
             */
            function forward($location = '')
            {
                if (empty($location)) {
                    $location = \known\Core\site()->config()->url;
                }
                if (!empty($this->forward)) {
                    if (\known\Core\site()->template()->getTemplateType() != 'default') {
                        $location = \known\Core\site()->template()->getURLWithVar('_t', \known\Core\site()->template()->getTemplateType(), $location);
                    }
                    header('Location: ' . $location);
                    exit;
                }
            }

            /**
             * Placed in pages to ensure that only logged-in users can
             * get at them. Sets response code 401 and tries to forward
             * to the front page.
             */
            function gatekeeper()
            {
                if (!\known\Core\site()->session()->isLoggedIn()) {
                    $this->setResponse(401);
                    $this->forward('/session/login?fwd=' . urlencode($_SERVER['REQUEST_URI']));
                }
            }

            /**
             * Placed in pages to ensure that only logged-out users can
             * get at them. Sets response code 401 and tries to forward
             * to the front page.
             */
            function reverseGatekeeper()
            {
                if (\known\Core\site()->session()->isLoggedIn()) {
                    $this->setResponse(401);
                    $this->forward();
                }
            }

            /**
             * Placed in pages to ensure that only logged-in site administrators can
             * get at them. Sets response code 401 and tries to forward
             * to the front page.
             */
            function adminGatekeeper()
            {
                $ok = false;
                if (\known\Core\site()->session()->isLoggedIn()) {
                    if (\known\Core\site()->session()->currentUser()->isAdmin()) {
                        $ok = true;
                    }
                }
                if (!$ok) {
                    $this->setResponse(401);
                    $this->forward();
                }
            }

            /**
             * Set the response code for the page. Note: this will be overridden
             * if the main system response code is already not 200
             *
             * @param type $code
             */
            function setResponse($code)
            {
                $code           = (int)$code;
                $this->response = $code;
                http_response_code($this->response);
            }

            /**
             * Is this page a permalink for an object? This should be set to 'true'
             * if it is.
             * @param bool $status Is this a permalink? Defaults to 'true'
             */
            function setPermalink($status = true)
            {
                $this->isPermalinkPage = $status;
            }

            /**
             * Is this page a permalink for an object?
             * @return bool
             */
            function isPermalink()
            {
                return $this->isPermalinkPage;
            }

            /**
             * Sets the given user as owner of this page
             * @param $user
             */
            function setOwner($user)
            {
                if ($user instanceof \known\Entities\User) {
                    $this->owner = $user;
                }
            }

            /**
             * Retrieves the effective owner of this page, if one has been set
             * @return bool|User
             */
            function getOwner()
            {
                if (!empty($this->owner)) {
                    if ($this->owner instanceof \known\Entities\User) {
                        return $this->owner;
                    }
                }

                return false;
            }

            /**
             * Has the page been requested over SSL?
             * @return boolean
             */
            static function isSSL()
            {
                if (isset($_SERVER['HTTPS'])) {
                    if ($_SERVER['HTTPS'] == '1')
                        return true;
                    if (strtolower($_SERVER['HTTPS'] == 'on'))
                        return true;
                } else if (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == '443'))
                    return true;

                return false;
            }

            /**
             * Provide access to page data
             * @return array
             */
            function &data()
            {
                return $this->data;
            }

            /**
             * Finds a JSON payload associated with the current page request
             * and parses any variables into $this->data
             */
            function parseJSONPayload()
            {

                // First, let's see if we've been sent anything in form input
                if (!empty($_REQUEST['json'])) {
                    $json = trim($_REQUEST['json']);
                    if ($parsed = @json_decode($json, true)) {
                        $this->data = array_merge($parsed, $this->data());
                    }
                }

                if ($_SERVER['REQUEST_METHOD'] != 'GET') {
                    $body = @file_get_contents('php://input');
                    $body = trim($body);
                    if (!empty($body)) {
                        if ($parsed = @json_decode($body, true)) {
                            $this->data = array_merge($parsed, $this->data());
                        }
                    }
                }

            }

            /**
             * Retrieves input.
             *
             * @param string $name Name of the input variable
             * @param boolean $filter Whether or not to filter the variable for safety (default: false)
             * @return mixed
             */
            function getInput($name, $filter = false)
            {
                if (!empty($name)) {
                    if (!empty($_REQUEST[$name])) {
                        $value = $_REQUEST[$name];
                    } else if (!empty($this->data[$name])) {
                        $value = $this->data[$name];
                    }
                    if (!empty($value)) {
                        if ($filter == true) {
                            // TODO: add some kind of sensible filter
                        }

                        return $value;
                    }
                }

                return false;
            }

            /**
             * Sets an input value that can subsequently be retrieved by getInput.
             * Note that actual input variables (i.e., those supplied by GET or POST
             * variables) will still take precedence.
             *
             * @param string $name
             * @param mixed $value
             */
            function setInput($name, $value)
            {
                if (!empty($name)) {
                    $this->data[$name] = $value;
                }
            }

            /**
             * Detects whether the current web browser accepts the given content type.
             * @param string $contentType The MIME content type.
             * @return bool
             */
            function isAcceptedContentType($contentType)
            {
                if ($headers = getallheaders()) {
                    if (!empty($headers['Accept']))
                        if (substr_count($headers['Accept'], $contentType)) return true;
                }

                return false;
            }
	    
	    /**
	     * Set or add an asset.
	     * @param type $name Name of the asset (e.g. 'known', 'jquery')
	     * @param type $class Class of asset (e.g. 'javascript', 'css')
	     * @param type $value A url or other value
	     */
	    public function setAsset($name, $value, $class) {
		if (!is_array($this->assets)) $this->assets = [];
		if (!is_array($this->assets[$class])) $this->assets[$class] = [];
		
		$this->assets[$class][$name] = $value;
	    }
	    
	    /**
	     * Get assets of a given class.
	     * @param type $class
	     * @return array
	     */
	    public function getAssets($class) {
		return $this->assets[$class];
	    }

        }

    }


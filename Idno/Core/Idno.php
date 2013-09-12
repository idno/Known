<?php

/**
 * Base Idno class
 *
 * @package idno
 * @subpackage core
 */

namespace Idno\Core {

    class Idno extends \Idno\Common\Component
    {

        public $db;
        public $config;
        public $session;
        public $template;
        public $actions;
        public $plugins;
        public $dispatcher;
        public $pagehandlers;
        public static $site;
        public $currentPage;

        function init()
        {
            self::$site = $this;
            $this->dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
            $this->config = new Config();
            $this->db = new DataConcierge();
            $this->config->load();
            $this->session = new Session();
            $this->actions = new Actions();
            $this->template = new Template();
            $this->plugins = new Plugins();
        }

        /**
         * Registers some core Idno page URLs
         */
        function registerpages()
        {

            // Homepage
            $this->addPageHandler('/', '\Idno\Pages\Homepage');
            $this->addPageHandler('/view/([A-Za-z0-9]+)/?', '\Idno\Pages\Entity\View');
            $this->addPageHandler('/edit/([A-Za-z0-9]+)/?', '\Idno\Pages\Entity\Edit');
            $this->addPageHandler('/delete/([A-Za-z0-9]+)/?', '\Idno\Pages\Entity\Delete');
            $this->addPageHandler('/file/([A-Za-z0-9]+)(/.*)?', '\Idno\Pages\File\View');
            $this->addPageHandler('/profile/([A-Za-z0-9]+)/?', '\Idno\Pages\User\View');
            $this->addPageHandler('/profile/([A-Za-z0-9]+)/edit/?', '\Idno\Pages\User\Edit');
            $this->addPageHandler('/search/?', '\Idno\Pages\Entity\Search');

        }

        /**
         * Return the database layer loaded as part of this site
         * @return \Idno\Core\DataConcierge
         */

        function &db()
        {
            return $this->db;
        }

        /**
         * Return the event dispatcher loaded as part of this site
         * @return \Symfony\Component\EventDispatcher\EventDispatcher
         */

        function &events()
        {
            return $this->dispatcher;
        }

        /**
         * Shortcut to trigger an event: supply the event name and
         * (optionally) an array of data, and get a variable back.
         *
         * @param string $eventName The name of the event to trigger
         * @param array $data Data to pass to the event
         * @return mixed
         */

        function triggerEvent($eventName, $data = array())
        {
            $event = new Event($data);
            $this->events()->dispatch($eventName, $event);
            if (!$event->forward()) {
                return $event->response();
            } else {
                header('Location: ' . $event->forward());
                exit;
            }
        }

        /**
         * Helper function that returns the current configuration object
         * for this site (or a configuration setting value)
         *
         * @param The configuration setting value to retrieve (optional)
         *
         * @return \Idno\Core\Config
         */
        function &config($setting = false)
        {
            if ($setting === false)
                return $this->config;
            else
                return $this->config->$setting;
        }

        /**
         * Return the session handler associated with this site
         * @return \Idno\Core\Session
         */

        function &session()
        {
            return $this->session;
        }

        function &plugins()
        {
            return $this->plugins;
        }

        /**
         * Return the template handler associated with this site
         * @return \Idno\Core\Template
         */

        function &template()
        {
            return $this->template;
        }

        /**
         * Return the action helper associated with this site
         * @return \Idno\Core\Actions
         */
        function &actions()
        {
            return $this->actions;
        }

        /**
         * Tells the system that callable $listener wants to be notified when
         * event $event is triggered. $priority is an optional integer
         * that specifies order priority; the higher the number, the earlier
         * in the chain $listener will be notified.
         *
         * @param string $event
         * @param callable $listener
         * @param int $priority
         */

        function addEventHook($event, $listener, $priority = 0)
        {
            if (is_callable($listener))
                $this->dispatcher->addListener($event, $listener, $priority);
        }

        /**
         * Registers a page handler for a given pattern, using Toro
         * page handling syntax
         *
         * @param string $pattern The pattern to match
         * @param callable $handler The handler callable that will serve the page
         */

        function addPageHandler($pattern, $handler)
        {
            if (class_exists($handler))
                $this->pagehandlers[$pattern] = $handler;
        }

        /**
         * Retrieves an instantiated version of the page handler class responsible for
         * a particular page (if any). May also be a whole URL.
         *
         * @param string $path_info The path, including the initial /, or the URL
         * @return bool|\Idno\Common\Page
         */

        function getPageHandler($path_info) {
            if (substr_count($path_info,\Idno\Core\site()->config()->url)) {
                $path_info = '/'. str_replace(\Idno\Core\site()->config()->url,'',$path_info);
            }
            if ($q = strpos($path_info,'?')) {
                $path_info = substr($path_info, 0, $q);
            }
            $tokens = array(
                ':string' => '([a-zA-Z]+)',
                ':number' => '([0-9]+)',
                ':alpha'  => '([a-zA-Z0-9-_]+)'
            );
            $discovered_handler = false;
            $matches = [];
            foreach ($this->pagehandlers as $pattern => $handler_name) {
                $pattern = strtr($pattern, $tokens);
                if (preg_match('#^/?' . $pattern . '/?$#', $path_info, $matches)) {
                    $discovered_handler = $handler_name;
                    $regex_matches = $matches;
                    break;
                }
            }
            if (class_exists($discovered_handler)) {
                $page = new $discovered_handler();
                if ($page instanceof \Idno\Common\Page) {
                    unset($matches[0]);
                    $page->arguments = array_values($matches);
                    return $page;
                }
            }
            return false;
        }

        /**
         * Sets the current page (if any) for access throughout the system
         * @param \Idno\Common\Page $page
         */
        function setCurrentPage($page) {
            $this->currentPage = $page;
        }

        /**
         * Retrieve the current page
         * @return bool|\Idno\Common\Page
         */
        function currentPage() {
            if (!empty($this->currentPage)) return $this->currentPage;
            return false;
        }
        
             
        /**
        * Retrieve the site url from configuration, and allow a simple way of replacing segments of it.
        * 
        * @param array $replacements Accepts arguments in the format of parse_url, allowing you to easily replace parts of the url e.g. 'schema' from http to https
        * @return string
        */
       public function getSiteUrl(array $replacements = null) {
           if (!$replacements)
               return \Idno\Core\site()->config()->url;

           $url = parse_url(\Idno\Core\site()->config()->url);

           // perform any replacements
           foreach ($replacements as $key => $value)
               $url[$key] = $value;

           return $this->build_url($url);
       }
    
        /**
         * Construct a URL from array components (basically an implementation of http_build_url() without PECL.
         * 
         * @todo Move somewhere sensible
         * @param array $url
         * @return string 
         */
        public function build_url(array $url) 
        {
            if (!empty($url['scheme']))
                $page = $url['scheme'] . "://";
            else
                $page = '//';

            // user/pass
            if ((isset($url['user'])) && ($url['user']))
                $page .= $url['user'];
            if ((isset($url['pass'])) && ($url['pass']))
                $page .= ":" . $url['pass'];
            if (($url['user']) || $url['pass'])
                $page .="@";

            if (isset($url['host']))
                $page .= $url['host'];

            if ((isset($url['port'])) && ($url['port']))
                $page .= ":" . $url['port'];

            if (isset($url['path']))
                $page .= $url['path'];

            if ((isset($url['query'])) && ($url['query']))
                $page .= "?" . $url['query'];

            if ((isset($url['fragment'])) && ($url['fragment']))
                $page .= "#" . $url['fragment'];

            return $page;
        }

    }

    /**
     * Helper function that returns the current site object
     * @return \Idno\Core\Idno
     */
    function &site()
    {
        return \Idno\Core\Idno::$site;
    }

}
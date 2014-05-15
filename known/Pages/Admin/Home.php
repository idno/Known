<?php

    /**
     * Administration homepage
     */

    namespace known\Pages\Admin {

        /**
         * Default class to serve the homepage
         */
        class Home extends \known\Common\Page
        {

            function getContent()
            {
                $this->adminGatekeeper(); // Admins only
                $t        = \known\Core\site()->template();
                $t->body  = $t->draw('admin/home');
                $t->title = 'Administration';
                $t->drawPage();

            }

            function postContent()
            {
                $this->adminGatekeeper(); // Admins only
                $title                = $this->getInput('title');
                $description          = $this->getInput('description');
                $url                  = $this->getInput('url');
                $path                 = $this->getInput('path');
                $host                 = $this->getInput('host');
                $hub                  = $this->getInput('hub'); // PuSH hub
                $open_registration    = $this->getInput('open_registration');
                $indieweb_citation    = $this->getInput('indieweb_citation');
                $indieweb_reference   = $this->getInput('indieweb_reference');
                $user_avatar_favicons = $this->getInput('user_avatar_favicons');
                $items_per_page       = (int)$this->getInput('items_per_page');
                if ($open_registration == 'true') {
                    $open_registration = true;
                } else {
                    $open_registration = false;
                }
                if ($indieweb_citation == 'true') {
                    $indieweb_citation = true;
                } else {
                    $indieweb_citation = false;
                }
                if ($indieweb_reference == 'true') {
                    $indieweb_reference = true;
                } else {
                    $indieweb_reference = false;
                }
                if ($user_avatar_favicons == 'true') {
                    $user_avatar_favicons = true;
                } else {
                    $user_avatar_favicons = false;
                }
                if (!empty($title)) \known\Core\site()->config->config['title'] = $title;
                if (!empty($description)) \known\Core\site()->config->config['description'] = $description;
                if (!empty($url)) \known\Core\site()->config->config['url'] = $url;
                if (!empty($path)) \known\Core\site()->config->config['path'] = $path;
                if (!empty($host)) \known\Core\site()->config->config['host'] = $host;
                if (!empty($hub)) \known\Core\site()->config->config['hub'] = $hub;
                if (!empty($items_per_page) && is_int($items_per_page)) \known\Core\site()->config->config['items_per_page'] = $items_per_page;
                \known\Core\site()->config->config['open_registration']    = $open_registration;
                \known\Core\site()->config->config['indieweb_citation']    = $indieweb_citation;
                \known\Core\site()->config->config['indieweb_reference']   = $indieweb_reference;
                \known\Core\site()->config->config['user_avatar_favicons'] = $user_avatar_favicons;
                \known\Core\site()->config()->save();
                $this->forward('/admin/');
            }

        }

    }

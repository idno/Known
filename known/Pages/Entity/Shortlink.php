<?php

    /**
     * Generic shortlink forwarder for entities
     */

    namespace known\Pages\Entity {

        /**
         * Default class to serve the homepage
         */
        class Shortlink extends \known\Common\Page
        {

            // Handle GET requests to the entity

            function getContent()
            {
                if (!empty($this->arguments[0])) {
                    $object = \known\Common\Entity::getByShortURL($this->arguments[0]);
                }
                if (empty($object)) {
                    $this->goneContent();
                }
                header("HTTP/1.1 301 Moved Permanently");
                $this->forward($object->getURL());
            }

            // Get webmention content and handle it

            function webmentionContent($source, $target, $source_content, $source_mf2)
            {
                if (!empty($this->arguments[0])) {
                    $object = \known\Common\Entity::getByShortURL($this->arguments[0]);
                }
                if (empty($object)) return false;

                $return = true;

                if ($object instanceof \known\Common\Entity) {
                    $return = $object->addWebmentions($source, $target, $source_content, $source_mf2);
                }

                return $return;
            }

        }

    }
<?php

    namespace knownPlugins\Like {

        class Like extends \known\Common\Entity {

            function getTitle() {
                return strip_tags($this->body);
            }

            function getDescription() {
                $body = $this->body;
                if (!empty($this->description)) {
                    $body .= ' ' . $this->description;
                }
                return $body;
            }

            /**
             * Like objects have type 'bookmark'
             * @return 'bookmark'
             */
            function getActivityStreamsObjectType() {
                return 'bookmark';
            }

            /**
             * Given a URL, returns the page title.
             * @param $Url
             * @return mixed
             */
            function getTitleFromURL($Url){
                $str = @file_get_contents($Url);
                if(strlen($str)>0){
                    preg_match("/\<title\>(.*)\<\/title\>/i",$str,$title);
                    return $title[1];
                }
                return '';
            }

            /**
             * Saves changes to this object based on user input
             * @return true|false
             */
            function saveDataFromInput() {

                if (empty($this->_id)) {
                    $new = true;
                } else {
                    $new = false;
                }
                $body = \known\Core\site()->currentPage()->getInput('body');
                $description = \known\Core\site()->currentPage()->getInput('description');
                $body = trim($body);
                if(filter_var($body, FILTER_VALIDATE_URL)){
                if (!empty($body)) {
                    $this->body = $body;
                    $this->description = $description;
                    if ($title = $this->getTitleFromURL($body)) {
                        $this->pageTitle = $title;
                    } else {
                        $this->pageTitle = '';
                    }
                    $this->setAccess('PUBLIC');
                    if ($this->save()) {
                        if ($new) {
                            $this->addToFeed();
                        } // Add it to the Activity Streams feed
                        $result = \known\Core\Webmention::pingMentions($this->getURL(), \known\Core\site()->template()->parseURLs($this->body));
                        $result = \known\Core\Webmention::pingMentions($this->getURL(), \known\Core\site()->template()->parseURLs($this->description));
                        \known\Core\site()->session()->addMessage('You starred the page!');
                        return true;
                    }
                } else {
                    \known\Core\site()->session()->addMessage('You can\'t like nothingness. I mean, maybe you can, but it\'s frowned upon.');
                }
                } else {
                    \known\Core\site()->session()->addMessage('That doesn\'t look like a valid URL.');
                }
                return false;

            }

            function deleteData() {
                \known\Core\Webmention::pingMentions($this->getURL(), \known\Core\site()->template()->parseURLs($this->getDescription()));
            }

        }

    }
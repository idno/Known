<?php

    if (!empty($vars['user'])) {
        echo $vars['user']->draw();
    }
    if (!empty($vars['items'])) {

        foreach($vars['items'] as $entry) {
            /* @var \known\Entities\ActivityStreamPost $entry */
            echo $entry->draw();
        }

        echo $this->drawPagination($vars['count']);

    }
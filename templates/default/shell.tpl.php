<?php

    header('Content-type: text/html');
    header('Link: <' . \known\Core\site()->config()->url . 'webmention/>; rel="http://webmention.org/"');
    header('Link: <' . \known\Core\site()->config()->url . 'webmention/>; rel="webmention"');

?>
<?php if (!$_SERVER["HTTP_X_PJAX"]): ?>
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($vars['title']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="initial-scale=1.0" media="(device-height: 568px)"/>
    <meta name="description" content="<?= htmlspecialchars($vars['description']) ?>">
    <meta name="generator" content="Known http://withknown.com">
    <?= $this->draw('shell/favicon'); ?>

    <!-- Le styles -->
    <link href="<?= \known\Core\site()->config()->url . 'external/bootstrap/' ?>assets/css/bootstrap.css"
          rel="stylesheet">
    <link rel="stylesheet" href="<?= \known\Core\site()->config()->url ?>external/font-awesome/css/font-awesome.min.css">
    <style>
        body {
            padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
        }
    </style>
    <link href="<?= \known\Core\site()->config()->url . 'external/bootstrap/' ?>assets/css/bootstrap-responsive.css"
          rel="stylesheet">
    <link href="<?= \known\Core\site()->config()->url ?>css/default.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="<?= \known\Core\site()->config()->url . 'external/bootstrap/' ?>assets/js/html5shiv.js"></script>
    <![endif]-->

    <!-- Default Known JavaScript -->
    <script src="<?= \known\Core\site()->config()->url . 'js/default.js' ?>"></script>

    <!-- To silo is human, to syndicate divine -->
    <link rel="alternate feed" type="application/rss+xml" title="<?= htmlspecialchars($vars['title']) ?>"
          href="<?= $this->getURLWithVar('_t', 'rss'); ?>"/>
    <link rel="alternate feed" type="application/rss+xml" title="<?= htmlspecialchars(\known\Core\site()->config()->title) ?>: all content"
          href="<?= \known\Core\site()->config()->url ?>content/all?_t=rss"/>
    <link rel="feed" type="text/html" title="<?= htmlspecialchars(\known\Core\site()->config()->title) ?>"
          href="<?= \known\Core\site()->config()->url ?>content/all"/>

    <!-- Webmention endpoint -->
    <link href="<?= \known\Core\site()->config()->url ?>webmention/" rel="http://webmention.org/"/>
    <link href="<?= \known\Core\site()->config()->url ?>webmention/" rel="webmention"/>

    <link type="text/plain" rel="author" href="<?= \known\Core\site()->config()->url ?>humans.txt"/>

    <?php
        // Load style assets
        if ($style = \known\Core\site()->currentPage->getAssets('css')) {
            foreach ($style as $css) {
                ?>
                <link href="<?= $css; ?>" rel="stylesheet">
            <?php
            }
        }
    ?>

    <script src="<?=\known\Core\site()->config()->url?>external/fragmention/fragmention.js"></script>
    <?= $this->draw('shell/head', $vars); ?>

</head>

<body>
<?php endif; ?>
<div id="pjax-container">
    <?php
        $currentPage = \known\Core\site()->currentPage();

        if (!empty($currentPage))
            $hidenav = \known\Core\site()->currentPage()->getInput('hidenav');
        if (empty($vars['hidenav']) && empty($hidenav)) {
            ?>
            <div class="navbar navbar-inverse navbar-fixed-top">
                <div class="navbar-inner">
                    <div class="container">
                        <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="brand"
                           href="<?= \known\Core\site()->config()->url ?>"><?= \known\Core\site()->config()->title ?></a>

                        <div class="nav-collapse collapse">
                            <?= $this->draw('shell/toolbar/search') ?>
                            <ul class="nav" role="menu">
                            </ul>
                            <?= $this->draw('shell/toolbar/content') ?>
                            <ul class="nav pull-right" role="menu">
                                <?php

                                    if (\known\Core\site()->session()->isLoggedIn()) {

                                        echo $this->draw('shell/toolbar/logged-in');

                                    } else {

                                        echo $this->draw('shell/toolbar/logged-out');

                                    }

                                ?>
                            </ul>
                        </div>
                        <!--/.nav-collapse -->
                    </div>
                </div>
            </div>

        <?php
        } else {

            ?>
            <div style="height: 1em;"><br/></div>
        <?php

        } // End hidenav test
    ?>

    <div class="container">

        <?php

            if ($messages = \known\Core\site()->session()->getAndFlushMessages()) {
                foreach ($messages as $message) {

                    ?>

                    <div class="alert <?= $message['message_type'] ?>">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?= $message['message'] ?>
                    </div>

                <?php

                }
            }

        ?>
        <?= $this->draw('shell/beforecontent') ?>
        <?= $vars['body'] ?>
        <?= $this->draw('shell/aftercontent') ?>

    </div>
    <!-- /container -->
</div>
<!-- pjax-container -->
<?php if (!$_SERVER["HTTP_X_PJAX"]): ?>
<!-- Le javascript -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="<?= \known\Core\site()->config()->url . 'external/jquery/' ?>jquery.min.js"></script>
<script src="<?= \known\Core\site()->config()->url . 'external/jquery-timeago/' ?>jquery.timeago.js"></script>
<script src="<?= \known\Core\site()->config()->url . 'external/jquery-pjax/' ?>jquery.pjax.js"></script>
<script src="<?= \known\Core\site()->config()->url . 'external/bootstrap/' ?>assets/js/bootstrap.min.js"></script>
<!-- Video shim -->
<script src="<?= \known\Core\site()->config()->url . 'external/fitvids/jquery.fitvids.min.js' ?>"></script>

<?php
    // Load javascript assets
    if ($scripts = \known\Core\site()->currentPage->getAssets('javascript')) {
        foreach ($scripts as $script) {
            ?>
            <script src="<?= $script ?>"></script>
        <?php
        }
    }
?>

<!-- HTML5 form element support for legacy browsers -->
<script src="<?= \known\Core\site()->config()->url . 'external/h5f/h5f.min.js' ?>"></script>

<script>

    //$(document).pjax('a:not([href^=\\.],[href^=file])', '#pjax-container');    // In Known, URLs with extensions are probably files.
    /*$(document).on('pjax:click', function(event) {
     if (event.target.href.match('/edit/')) {
     // For a reason I can't actuallly figure out, /edit pages never render with chrome
     // when PJAXed. I don't understand the rendering pipeline well enough to figure out
     // what's up --jrv 20130705
     return false;
     }
     if (event.target.onclick) { // If there's an onclick handler, we don't want to pjax this
     return false;
     } else {
     return true;
     }
     });*/

    function annotateContent() {
        $(".h-entry").fitVids();
        $("time.dt-published").timeago();
    }

    // Shim so that JS functions can get the current site URL
    function wwwroot() {
        return '<?=\known\Core\site()->config()->wwwroot?>';
    }

    $(document).ready(function () {
        annotateContent();
    })
    $(document).on('pjax:complete', function () {
        annotateContent();
    });


</script>

<?= $this->draw('shell/footer', $vars) ?>

</body>
</html>
<?php endif; ?>

<?php
    $user = \known\Core\site()->session()->currentUser();
?>
<div class="row">

    <div class="span10 offset1">
        <h1>
            Account settings
        </h1>
        <?= $this->draw('account/menu') ?>
        <div class="explanation">
            <p>
                Change your basic account settings here, or <a
                    href="<?= \known\Core\site()->session()->currentUser()->getURL() ?>/edit/">click here to edit your
                    profile</a>.
            </p>
        </div>

        <form action="<?= \known\Core\site()->config()->url ?>account/settings" method="post" class="form-horizontal"
              enctype="multipart/form-data">
            <div class="control-group">
                <label class="control-label" for="inputName">Your name</label>

                <div class="controls">
                    <input type="text" id="inputName" placeholder="Your name" class="span4" name="name"
                           value="<?= htmlspecialchars($user->getTitle()) ?>">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="inputHandle">Your handle</label>

                <div class="controls">
                    <input type="text" id="inputHandle" placeholder="Your handle" class="span4" name="handle"
                           value="<?= htmlspecialchars($user->handle) ?>" disabled>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="inputEmail">Your email address</label>

                <div class="controls">
                    <input type="email" id="inputEmail" placeholder="Your email address" class="span4" name="email"
                           value="<?= htmlspecialchars($user->email) ?>">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="inputPassword">Your password<br/>
                    <small>Leave this blank if you don't want to change it</small>
                </label>

                <div class="controls">
                    <input type="password" id="inputPassword" placeholder="Password" class="span4" name="password">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="inputPassword2">Your password again</label>

                <div class="controls">
                    <input type="password" id="inputPassword2" placeholder="Your password again" class="span4"
                           name="password2">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="apikey">Your API key</label>

                <div class="controls">
                    <input type="text" id="apikey" class="span4" name="apikey"
                           value="<?= htmlspecialchars($user->getAPIkey()) ?>" disabled>
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
            <?= \known\Core\site()->actions()->signForm('/account/settings') ?>

        </form>
    </div>

</div>
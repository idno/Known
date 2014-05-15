<div class="row">
    <div class="span6 offset3 well text-center">

        <h3 class="text-center">
            Sign in
        </h3>

        <form action="<?= \known\Core\site()->config()->url ?>session/login" method="post">
            <div class="control-group">
                <div class="controls">
                    <input type="text" id="inputEmail" name="email" placeholder="Your username or email address"
                           class="span4">
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <input type="password" id="inputPassword" name="password" placeholder="Password" class="span4">
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <button type="submit" class="btn">Sign in</button>
                    <input type="hidden" name="fwd" value="<?php
                        if (!empty($vars['fwd'])) {
                            echo htmlspecialchars($vars['fwd']);
                        } else if (!empty($_SERVER['HTTP_REFERER'])) {
                            echo htmlspecialchars($_SERVER['HTTP_REFERER']);
                        } else {
                            echo \known\Core\site()->config()->url;
                        }?>" />
                </div>
            </div>
            <?= \known\Core\site()->actions()->signForm('/session/login') ?>
        </form>

    </div>
</div>
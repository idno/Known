<div class="row">

    <div class="span10 offset1">
        <h1>Administration</h1>
        <?=$this->draw('admin/menu')?>
        <div class="explanation">
            <p>
                This screen allows you to change basic settings about your site,
                like its name and the number of items of content on each page.
                To add new kinds of content, and new functionality, click
                <a href="<?=\known\Core\site()->config()->url?>admin/plugins/">Plugins</a>.
            </p>
        </div>
    </div>
</div>
<div class="row">
    <div class="span10 offset1">
        <form action="<?=\known\Core\site()->config()->url?>admin/" class="form-horizontal" method="post">

            <div class="control-group">
                <label class="control-label" for="name">Site name<br /><small>This can be anything you want. Except probably Facebook.</small></label>
                <div class="controls">
                    <input type="text" id="name" placeholder="Site name" class="span4" name="title" value="<?=htmlspecialchars(\known\Core\site()->config()->title)?>" >
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="description">Site description<br /><small>A short description of what your site is about.</small></label>
                <div class="controls">
                    <input type="text" id="name" placeholder="Site description" class="span4" name="description" value="<?=htmlspecialchars(\known\Core\site()->config()->description)?>" >
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="open_registration">Open registration<br /><small>Can anyone register for this site? If you're installing Known as a personal or closed-membership site, you'll want to turn this off.</small></label>
                <div class="controls">
                    <select class="span4" name="open_registration">
                        <option value="true" <?php if (\known\Core\site()->config()->open_registration == true) echo 'selected="selected"'; ?>>Yes</option>
                        <option value="false" <?php if (\known\Core\site()->config()->open_registration == false) echo 'selected="selected"'; ?>>No</option>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="url">Website URL<br /><small>The full URL to your Known site. Include a trailing slash.</small></label>
                <div class="controls">
                    <input type="url" id="url" placeholder="Site URL" class="span4" name="url" value="<?=htmlspecialchars(\known\Core\site()->config()->url)?>" >
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="host">Website host<br /><small>Just the hostname of your Known site (no http:// or trailing slash).</small></label>
                <div class="controls">
                    <input type="text" id="host" placeholder="Site hostname" class="span4" name="host" value="<?=htmlspecialchars(\known\Core\site()->config()->host)?>" >
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="path">Full path to installation<br /><small>Make sure you don't include a trailing slash.</small></label>
                <div class="controls">
                    <input type="text" id="path" placeholder="Path to Known installation" class="span4" name="path" value="<?=htmlspecialchars(\known\Core\site()->config()->path)?>" >
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="hub">PubSubHubbub hub<br /><small>The URI of your <a href="https://code.google.com/p/pubsubhubbub/" target="_blank">PubSubHubbub</a> hub.</small></label>
                <div class="controls">
                    <input type="url" id="hub" placeholder="PubSubHubbub hub address" class="span4" name="hub" value="<?=htmlspecialchars(\known\Core\site()->config()->hub)?>" >
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="items_per_page">Items per page<br /><small>The number of items you want displayed on a single page.</small></label>
                <div class="controls">
                    <input type="text" id="items_per_page" placeholder="10" class="span4" name="items_per_page" value="<?=htmlspecialchars(\known\Core\site()->config()->items_per_page)?>" >
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="user_avatar_favicons">Use the user's picture as the website icon on pages they own</small></label>
                <div class="controls">
                    <select class="span4" name="user_avatar_favicons">
                        <option value="true" <?php if (\known\Core\site()->config()->user_avatar_favicons == true) echo 'selected="selected"'; ?>>Yes</option>
                        <option value="false" <?php if (\known\Core\site()->config()->user_avatar_favicons == false) echo 'selected="selected"'; ?>>No</option>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="items_per_page">Include IndieWeb citations<br /><small>Include a unique, citable code at the bottom of every post.</small></label>
                <div class="controls">
                    <select class="span4" name="indieweb_citation">
                        <option value="true" <?php if (\known\Core\site()->config()->indieweb_citation == true) echo 'selected="selected"'; ?>>Yes</option>
                        <option value="false" <?php if (\known\Core\site()->config()->indieweb_citation == false) echo 'selected="selected"'; ?>>No</option>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="items_per_page">Include IndieWeb references<br /><small>Link back to posts here when they are syndicated to external sites.</small></label>
                <div class="controls">
                    <select class="span4" name="indieweb_reference">
                        <option value="true" <?php if (\known\Core\site()->config()->indieweb_reference == true) echo 'selected="selected"'; ?>>Yes</option>
                        <option value="false" <?php if (\known\Core\site()->config()->indieweb_reference == false) echo 'selected="selected"'; ?>>No</option>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
            <?= \known\Core\site()->actions()->signForm('/admin/')?>

        </form>
    </div>

</div>

<div class="navbar">
    <div class="navbar-inner">
        <ul class="nav">
            <li <?php if ($_SERVER['REQUEST_URI'] == '/account/settings/') echo 'class="active"'; ?>><a href="<?=\known\Core\site()->config()->url?>account/settings/" >Account settings</a></li>
            <li <?php if ($_SERVER['REQUEST_URI'] == '/account/settings/homepage/') echo 'class="active"'; ?>><a href="<?=\known\Core\site()->config()->url?>account/settings/homepage/" >Homepage</a></li>
            <?=$this->draw('account/menu/items')?>
        </ul>
    </div>
</div>
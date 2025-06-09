<nav id="sidebar">
    <div class="sidebar-header text-center">
    <?php 
        if (file_exists('data/logo.png')) {
            echo '<a href="'.BASE_URL.'"><img id="logo" src="data/logo.png?'.time().'" alt="logo" class="img-fluid"></a>';
        } elseif (defined('TITLE') && TITLE == 'DocPHT') {
            echo '<a href="'.BASE_URL.'"><h3>'.TITLE.' <i class="fa fa-code" aria-hidden="true"></i></h3></a>';
        } elseif (defined('TITLE') && TITLE != 'DocPHT') {
            echo '<a href="'.BASE_URL.'"><h3>'.TITLE.'</h3></a>';
        }
    ?>
    <hr>
    <?php 
        // Note: The $t variable is automatically available here from the BaseController
        if (isset($_SESSION['Active'])) {
            echo '<small><i class="fa fa-user" aria-hidden="true"></i> '.$t->trans('Welcome&nbsp;').strstr(ucfirst($_SESSION['Username']), '@', true).'</small>';
        }
    ?>    
    </div>

    <ul class="list-inline text-center">
        <?php 
        if (isset($_SESSION['Active'])) {
            echo '<li class="list-inline-item" data-toggle="tooltip" data-placement="top" title="'.$t->trans("Logout").'">
                    <a href="logout" id="sk-logout" class="btn btn-outline-secondary btn-sm" role="button"><i class="fa fa-sign-out" aria-hidden="true"></i></a>
                </li>';
        } else {
            echo '<li class="list-inline-item" data-toggle="tooltip" data-placement="top" title="'.$t->trans("Login").'">
                    <a href="login" id="sk-login" class="btn btn-outline-secondary btn-sm" role="button"><i class="fa fa-sign-in" aria-hidden="true"></i></a>
                </li>';
        }
        if (isset($_SESSION['Active'])) {
            echo '<li class="list-inline-item" data-toggle="tooltip" data-placement="top" title="'.$t->trans("Create new page").'">
            <a href="page/create" id="sk-newPage" class="btn btn-outline-secondary btn-sm" role="button"><i class="fa fa-plus-square" aria-hidden="true"></i></a>
            </li>';
        }
        if (isset($_SESSION['Active'])) {
            echo '<li class="list-inline-item" data-toggle="tooltip" data-placement="top" title="'.$t->trans("Settings").'">
            <a href="admin" id="sk-admin" class="btn btn-outline-secondary btn-sm" role="button"><i class="fa fa-cog" aria-hidden="true"></i></a>
            </li>';
        }
        ?>
    </ul>

    <ul class="nav navbar-nav text-white text-center">
        <li><a href="#search" id="sk-search"><?= $t->trans('Search'); ?> <i class="fa fa-search" aria-hidden="true"></i></a></li>
    </ul>

    <?php $search = ''; ?>
    <div id="search">
        <button type="button" class="close">×</button>
        <form id="form-search" action="page/search" method="post">
            <input type="search" name="search" minlength="5" value="<?php $search; ?>" placeholder="<?= $t->trans('Type the keywords here'); ?>" autocomplete="off" required />
        </form>
    </div>

    <ul class="list-unstyled components">
    <?php
        if (defined('SUBTITLE') && SUBTITLE) {
            echo '<p><b> '.SUBTITLE.' </b></p>';
        }
    ?>
    <?php 
        // *** ERROR IS FIXED HERE ***
        // All data-fetching logic is now REMOVED from the view.
        // We simply loop over the $sidebarData array that the BaseController prepared for us.
        $url = $_SERVER['REQUEST_URI'] ?? '';
        $parse = parse_url($url, PHP_URL_PATH) ?? '';
        $explode = explode('/', $parse);
        $filenameURL = end($explode);
        // Safely get the second to last element for the topic
        $topicURL = (count($explode) > 1) ? $explode[count($explode) - 2] : '';

        if (!empty($sidebarData)) {
            echo '<li>';
            foreach ($sidebarData as $topic => $pages) {
                $topicTitle = str_replace('-', ' ', $topic);
                $active = ($topicURL === $topic) ? 'menu-active' : '';
                $show = ($topicURL === $topic) ? 'show' : '';
                
                echo '<a href="#'.$topic.'-side-navigation" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle '.$active.' ">'. ucfirst($topicTitle) .'</a>';
                echo '<ul class="collapse list-unstyled '.$show.' " id="'.$topic.'-side-navigation">';

                if (!empty($pages) && is_array($pages)) {
                    foreach($pages as $page) {
                        $activePage = '';
                        if (isset($page['filename']) && $filenameURL === $page['filename'] && $topicURL === $topic) {
                            $activePage = 'class="menu-active"';
                        }
                        
                        $filenameTitle = str_replace('-', ' ', $page['filename']);
                        $link = 'page/'.$page['slug'];
                        echo '<li><a href="'.$link.'" '.$activePage.' >'.ucfirst($filenameTitle).'</a></li>';
                    }
                }
                echo '</ul>';
            }
            echo '</li>';
        }
    ?>
    
    </ul>
    
    <?php 
    $cssFile = $_COOKIE["theme"] ?? 'light';
    ?>
    <div class="sidebar-footer-content">
    <?php if($cssFile == 'dark') : ?>
    <div class="d-flex justify-content-center">
        <div class="switch-box">
            <div class="toggle-switch" onclick='window.location.assign("switch-theme")'>
            <input type="checkbox" class="switch-theme">
                <svg class="checkbox" xmlns="http://www.w3.org/2000/svg" style="isolation:isolate" viewBox="0 0 168 80">
                <path class="outer-ring" d="M41.534 9h88.932c17.51 0 31.724 13.658 31.724 30.482 0 16.823-14.215 30.48-31.724 30.48H41.534c-17.51 0-31.724-13.657-31.724-30.48C9.81 22.658 24.025 9 41.534 9z" fill="none" stroke="#233043" stroke-width="3" stroke-linecap="square" stroke-miterlimit="3"/>
                <path class="is_checked" d="M17 39.482c0-12.694 10.306-23 23-23s23 10.306 23 23-10.306 23-23 23-23-10.306-23-23z"/>
                    <path class="is_unchecked" d="M132.77 22.348c7.705 10.695 5.286 25.617-5.417 33.327-2.567 1.85-5.38 3.116-8.288 3.812 7.977 5.03 18.54 5.024 26.668-.83 10.695-7.706 13.122-22.634 5.418-33.33-5.855-8.127-15.88-11.474-25.04-9.23 2.538 1.582 4.806 3.676 6.66 6.25z"/>
                </svg>
            </div>
        </div>
    </div>
    <?php else : ?>
    <div class="d-flex justify-content-center">
        <div class="switch-box">
            <div class="toggle-switch" onclick='window.location.assign("switch-theme")'>
            <input type="checkbox" class="switch-theme" checked>
                <svg class="checkbox" xmlns="http://www.w3.org/2000/svg" style="isolation:isolate" viewBox="0 0 168 80">
                <path class="outer-ring" d="M41.534 9h88.932c17.51 0 31.724 13.658 31.724 30.482 0 16.823-14.215 30.48-31.724 30.48H41.534c-17.51 0-31.724-13.657-31.724-30.48C9.81 22.658 24.025 9 41.534 9z" fill="none" stroke="#233043" stroke-width="3" stroke-linecap="square" stroke-miterlimit="3"/>
                <path class="is_checked" d="M17 39.482c0-12.694 10.306-23 23-23s23 10.306 23 23-10.306 23-23 23-23-10.306-23-23z"/>
                    <path class="is_unchecked" d="M132.77 22.348c7.705 10.695 5.286 25.617-5.417 33.327-2.567 1.85-5.38 3.116-8.288 3.812 7.977 5.03 18.54 5.024 26.668-.83 10.695-7.706 13.122-22.634 5.418-33.33-5.855-8.127-15.88-11.474-25.04-9.23 2.538 1.582 4.806 3.676 6.66 6.25z"/>
                </svg>
            </div>
        </div>
    </div>
    <?php endif ?>
    </div>
</nav>
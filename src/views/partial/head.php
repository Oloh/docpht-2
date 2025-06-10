<!DOCTYPE html>
<html>
<head>
    <base href="<?= BASE_URL ?>">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title><?= ucfirst(str_replace('-',' ',$PageTitle)) ?></title>

    <link rel="stylesheet" href="public/assets/css/bootstrap.min.css" >
    <link rel="stylesheet" href="public/assets/css/jquery-ui.min.css">
    <?php
    $cssFile = $_COOKIE["theme"] ?? 'light';
    echo '<link type="text/css" rel="stylesheet" href="public/assets/css/doc-pht.'.$cssFile.'.css" />';
    ?>
    <link rel="stylesheet" href="public/assets/css/switch.css">
    <link rel="stylesheet" href="public/assets/css/animation.css">
    <link rel="stylesheet" href="public/assets/css/scrollbar.min.css">
    <link rel="stylesheet" href="public/assets/css/prism.css">
    <link rel="stylesheet" href="public/assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="public/assets/css/bootstrap-select.min.css">
    <?php
        if (file_exists('data/favicon.png')) {
            echo '<link id="fav" rel="icon" type="image/png" href="data/favicon.png?'.time().'">';
        }
    ?>
    
</head>

<body>

    <div class="wrapper">

    <div class="progress-container">
        <div class="progress-bar" id="scrollindicator"></div>
    </div>

    <?php 
        // This makes the objects from the View class available to the sidebar
        $pageModel = $this->pageModel;
        $homePageModel = $this->homePageModel;
        $t = new \App\Core\Translations\T;
        include 'sidebar.php'; 
    ?>

    <div id="content">
    <div class="container-fluid">
        
    <?php 
        // This is the fix. It uses the ResponseManager object passed from the View class.
        if (isset($flasherResponseManager)) {
            echo $flasherResponseManager->render();
        }
    ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title><?php echo($data['title']); ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="keywords" content="<?php echo($data['keywords']); ?>">
     <meta name="description" content="<?php echo($data['description']); ?>">
        <script src="/javascript/<?php echo(MY_DIR_GENERIC_NAME); ?>/jQuery/jquery.min.js"></script>
        <script src="/javascript/<?php echo(MY_DIR_GENERIC_NAME); ?>/functions.js"></script>

        <link rel="stylesheet" type="text/css" href="/css/<?php echo(MY_DIR_GENERIC_NAME); ?>/generic.css"/>
        <link rel="stylesheet" type="text/css" href="/css/<?php echo(MY_DIR_MOBILE_NAME); ?>/generic.css"/>
        <link rel="stylesheet" type="text/css" href="/css/<?php echo(MY_DIR_GENERIC_NAME); ?>/catalog.css"/>
        <link rel="stylesheet" type="text/css" href="/css/<?php echo(MY_DIR_MOBILE_NAME); ?>/catalog.css"/>
        <link rel="stylesheet" type="text/css" href="/css/<?php echo(MY_DIR_GENERIC_NAME); ?>/main.css"/>
        <link rel="stylesheet" type="text/css" href="/css/<?php echo(MY_DIR_MOBILE_NAME); ?>/main.css"/>
        <meta name="viewport" content="initial-scale=1.0, user-scalable=no, maximum-scale=1, width=device-width" />
        <link rel="icon" href="/favicon.ico" type="image/x-icon">
    </head>
    <body
        class="action_<?php echo(get_action_name()); ?> controller_<?php echo(get_controller_name()); ?> view_<?php echo($this->get_view_name()); ?>"
        id="layout_<?php echo($this->get_layout_name()); ?>">
        <?php $this->trace_block('_outer_services' . MY_DS . 'yandex_metrics', false, $data); ?>
        <?php $this->trace_block('_outer_services' . MY_DS . 'google_analytics', false, $data); ?>
        <?php $this->trace_block('_models' . MY_DS . 'images' . MY_DS . 'dimension', false); ?>
        <?php $this->trace_block('_models' . MY_DS . 'category_viewer' . MY_DS . 'info', true, $data); ?>
        <?php $this->trace_block('_models/placemark/generic'); ?>
        <?php $this->trace_block('_hats', false); ?>
        <?php $this->trace_block('page_dimentions'); ?>

        <div id="panel_tools"><?php $this->trace_block('panel_tools' . MY_DS . 'panel', false, $data); ?></div>
        <div class="container">
            <div class="padding_after_hat"></div>
            <?php echo($content); ?>
        </div>
        <?php $this->trace_block('_pages' . MY_DS . 'generic' . MY_DS . 'bottom_side', true, $data); ?>
        <?php $this->trace_service_frontend(); ?>
<?php $this->trace_block('_models' . MY_DS . 'page_scrolling', false); ?>
    </body>
</html>
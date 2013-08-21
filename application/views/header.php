<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo (isset($status) ? '(' . $status . ') ' : '') . $applicationName . (isset($pageName) ? ' &ndash; ' . $pageName : ''); ?></title>
    <link rel="stylesheet" type="text/css" href="<?php echo site_url('css/template.css'); ?>">
    <link rel="icon" type="image/png" href="<?php echo site_url('favicon.png')?>">
  </head>
  <body>
    <h1><?php echo anchor(site_url(), $applicationName); ?></h1>


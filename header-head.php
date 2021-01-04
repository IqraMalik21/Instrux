<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php 
    if(!empty($currentPageTitle)){
      echo $currentPageTitle." - InstruX";
    }else{
      echo "InstruX Dashboard";
    }
    ?></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome CSS-->
    <!--<link rel="stylesheet" href="assets/vendor/font-awesome/css/font-awesome.min.css">-->
    <link href="assets/fontawesome-5.7.2/css/all.css" rel="stylesheet"> <!--load all styles -->
    <link href="assets/fontawesome-5.7.2/css/v4-shims.css" rel="stylesheet"> <!--load v4 -->
    <!-- Fontastic Custom icon font-->
    <link rel="stylesheet" href="assets/css/fontastic.css">
    <!-- Google fonts - Roboto -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700">
    <!-- jQuery Circle-->
    <link rel="stylesheet" href="assets/css/grasp_mobile_progress_circle-1.0.0.min.css">
    <!-- Custom Scrollbar-->
    <link rel="stylesheet" href="assets/vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css">
    <!-- theme stylesheet-->
    <link rel="stylesheet" href="assets/css/style.default.css" id="theme-stylesheet">
    <!-- Custom stylesheet - for your changes-->
    <link rel="stylesheet" href="assets/css/custom.css">

    <link rel="stylesheet" type="text/css" href="assets/css/sam.css">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

    <!-- Favicon-->
    <link rel="shortcut icon" href="img/favicon.ico">
    <!-- Tweaks for older IEs--><!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->

    <style type="text/css">
      .google_charts table {
        border-collapse: unset;
      }
      .page {
      position: absolute;
      top: 0;
      right: 0;
      -webkit-transition: width 0.3s linear;
      transition: width 0.3s linear;
      width: calc(100% - 200px);
      background-color: #F7F7F7;
      min-height: 100vh;
      padding-bottom: 50px;
      }
        .loading {
          width: 100%;
          height: 100%;
          top: 0;
          left: 0;
          position: fixed;
          display: block;
          opacity: 0.95;
          background-color: rgb(30, 44, 36);
          z-index: 99;
          text-align: center;
        }

        .loading-chart {
          width: 100%;
          height: 100%;
          top: 0;
          left: 0;
          position: relative;
          display: block;
          opacity: 0.90;
          background-color: rgb(255, 255, 255);
          text-align: center;
        }

      .loading-image {
        position: fixed;
        top: 50%;
        left: 50%;
        z-index: 100;
      }
    </style>
    <script type="text/javascript" src="//www.gstatic.com/charts/loader.js"></script>

    <!-- Bootstrap Multi Select -->
    <script src="assets/css/bootstrap-multiselect.css"></script>

  </head>
  <body>

<div id="loading" class="loading">
    <img class="loading-image" src="assets/img/preloader.svg" alt="Loading..." />
</div>
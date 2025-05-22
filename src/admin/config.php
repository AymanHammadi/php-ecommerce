<?php

// Load database connection
require_once __DIR__ . '/../includes/database.php';

// Base Paths
$basePath = __DIR__;

// Directory Paths
$templates = $basePath . '/includes/templates/';
$components = $basePath . '/includes/templates/components/';
$functions = $basePath . '/includes/functions/';
$assetsPath = $basePath . '/assets/';

// Asset Paths
$css = 'assets/css/';
$js = 'assets/js/';
$images = 'assets/images/';


// Load global functions
require_once $functions . 'functions.php';

// Load language handler
require_once __DIR__ . '/../includes/functions/lang.php';




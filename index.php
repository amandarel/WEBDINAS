<?php
define('ROOT_PATH', __DIR__); 
require_once ROOT_PATH . '/includes/functions.php';
$page_file = 'home.php'; 
$page_id = 'home'; 

$valid_pages = [
    'home'              => 'home.php',      
    'beranda'           => 'home.php',      
    'situs'             => 'situs.php',     
    'tradisi'           => 'tradisi.php',   
    'event'             => 'event.php',     
    'kontak'            => 'kontak.php',    
    'tentang'           => 'tentang.php',   
    'about'             => 'tentang.php',   
    '404'               => '404.php',     
];

if (isset($_GET['page']) && $_GET['page'] !== '') {
    $requested_page = strtolower($_GET['page']);

    if (array_key_exists($requested_page, $valid_pages)) {
        $file_to_load = $valid_pages[$requested_page];

        if (file_exists(ROOT_PATH . '/pages/' . $file_to_load)) {
            $page_file = $file_to_load;
            $page_id = $requested_page;
        } else {
            $page_file = '404.php'; 
        }
    } else {
        $page_file = '404.php';
    }
}

$page_title = $page_title ?? 'Minahasa - Warisan Budaya Sulawesi'; 

require_once ROOT_PATH . '/templates/header.php'; 

require_once ROOT_PATH . '/pages/' . $page_file; 
?>
</main> 

<?php 
require_once ROOT_PATH . '/templates/footer.php'; 
?>

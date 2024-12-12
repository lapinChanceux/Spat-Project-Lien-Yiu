<?php

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
if ($page === 'home') {
    include 'Views/home.phtml';
} else {
    echo "Page not found.";
}

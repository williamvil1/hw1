<?php
include 'includes/auth.php';

if(check_login()) {
    session_destroy();
    header('Location: index.php');
    exit();
} else {
    header('Location: login.php');
    exit();
}

?>
<?php
session_start();
session_unset();
session_destroy();
header("Location: /support-ticket-system/pages/login.php");
exit();
?>
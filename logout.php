<?php
// Simple logout logic. Starting the session, deleting the variables from the sesion and destroying all login info.
session_start();
session_unset();
session_destroy();
header("Location: login.html");
exit();
?>

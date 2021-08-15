<?php
class Helper
{
    public static function redirect($route, $message = "")
    {
        if ($message !== "") {
            $_SESSION['message'] = $message;
        }
        header("location: $route");
        die();
    }
}

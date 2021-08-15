<?php
session_start();
require_once '../autoload.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = json_decode(file_get_contents("php://input"));
    if ($postData->id) {
        DB::delete('tasks', $postData->id);
    }
    $_SESSION['message'] = "Task deleted successfully";
    echo json_encode(array(
        "deleted" => "Task deleted successfully"
    ));
}

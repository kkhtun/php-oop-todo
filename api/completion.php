<?php
require_once '../autoload.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = json_decode(file_get_contents("php://input"));
    $updatedTask = "";
    if ($postData->id && $postData->completion !== null) {
        $updatedTask = DB::update('tasks', [
            'completion' => $postData->completion ? 1 : 0
        ], $postData->id);
    }

    echo json_encode(array(
        "id" => $updatedTask->id,
        "completion" => $updatedTask->completion
    ));
}

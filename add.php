
<?php
session_start();
require_once 'autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    if ($_POST['name'] == "" || $_POST['due'] == null) {
        Helper::redirect('index.php', 'Please fill the task and date');
    }
    // PDO uses prepared statements, no need to use real_escape_string to sanitize?
    $name = $_POST['name'];
    // Need Fix, convert UTC time string from flatpickr to DateTime Obj, then format Unix time into SQL compatible format
    $datetime = new DateTime($_POST['due']);
    $due = $datetime->format('Y-m-d H:i:s');

    $createdTask = DB::create('tasks', [
        "name" =>  $name,
        "due" => $due
    ]);
    if ($createdTask) {
        Helper::redirect('index.php', 'Task Created Successfully');
    }
}
?>
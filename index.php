<?php
session_start();
require_once 'autoload.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

</head>

<body>
    <section class="vh-100" style="background-color: #e2d5de;">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col col-xl-10">
                    <?php if (isset($_SESSION['message'])) {
                        echo "<div class='alert alert-warning py-2 rounded-2' role='alert'>" . $_SESSION['message'] . "</div>";
                        unset($_SESSION['message']);
                    }
                    ?>
                    <div class="card" style="border-radius: 15px;">
                        <div class="card-body p-5">

                            <h5 class="mb-3">Todo List</h5>

                            <form class="d-flex justify-content-center align-items-start mb-4" action="add.php" method="POST">
                                <div class="form-outline flex-fill">
                                    <input type="text" name="name" id="name" class="form-control form-control-lg" required />
                                    <label class="form-label mt-1" for="name">What do you need to do today?</label>
                                </div>
                                <input type="text" name="due" class="py-2 ms-2 mt-1 form-outline border-0 rounded-2 text-center" id="datetime-picker" value="" required>
                                <input type="submit" name="add" class="btn btn-primary btn-lg ms-2" value="Add">
                            </form>

                            <ul class="list-group mb-0">
                                <?php
                                $data = DB::table('tasks')->orderby('due', 'ASC')->paginate(5);
                                foreach ($data['data'] as $task) : ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center border-start-0 border-top-0 border-end-0 border-bottom rounded-0 mb-2">
                                        <div class="d-flex align-items-center">
                                            <input data-id="<?php echo $task->id ?>" class="task-check form-check-input me-2" type="checkbox" value="" <?php echo $task->completion ? 'checked' : '' ?> />
                                            <div class="<?php echo $task->completion ? 'text-decoration-line-through' : '' ?>"><?php echo $task->name ?></div>
                                        </div>
                                        <div class="right">
                                            <span class="d-inline text-muted"><?php echo date("d-M-y h:s A", strtotime($task->due)) ?></span>&nbsp;
                                            <a href="#!" data-mdb-toggle="tooltip" title="Remove item" data-id="<?php echo $task->id ?>" class="task-remove">
                                                <i class="fas fa-times text-primary"></i>
                                            </a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <footer class="mt-2 d-flex justify-content-between">
                                <div class="text-muted">
                                    Page <?php echo $data['current_page_no'] ?> of <?php echo $data['total_pages'] ?>
                                </div>
                                <ul class="pagination justify-content-end">
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo $data['prev_page'] ?>">Previous</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo $data['next_page'] ?>">Next</a>
                                    </li>
                                </ul>
                            </footer>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        $(document).ready(function() {
            $('#datetime-picker').flatpickr({
                enableTime: true,
                dateFormat: "Y-M-d G:i K",
                defaultDate: 'today'
            })

            var flash = document.querySelector(".alert")
            if (flash) {
                setTimeout(function() {
                    flash.remove();
                }, 4000)
            }

            $('.task-check').on('click', function() {
                checkAction = $(this).is(":checked");
                $(this).prop("disabled", true); // Disable to prevent rapid spaming
                $.ajax({
                    url: './api/completion.php',
                    type: 'POST',
                    data: JSON.stringify({
                        id: $(this).data('id'),
                        completion: checkAction
                    }),
                    success: (res) => {
                        $(this).next().toggleClass('text-decoration-line-through')
                        $(this).prop("disabled", false);
                    }
                })
            })

            $('.task-remove').on('click', function(e) {
                e.preventDefault();
                var userConfirm = confirm("Are you sure you want to delete this task?");
                if (userConfirm == true) {
                    $.ajax({
                        url: './api/delete.php',
                        type: 'POST',
                        data: JSON.stringify({
                            id: $(this).data('id'),
                        }),
                        success: (res) => {
                            window.location.reload();
                        }
                    })
                }
            })
        })
    </script>
</body>

</html>
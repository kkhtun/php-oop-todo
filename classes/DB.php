<?php

class DB
{

    private static $dbh, $sql, $res, $data;

    public function __construct()
    {
        self::$dbh = new PDO("mysql:host=localhost;dbname=todo_list", "root", "password");
        self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function query($params = [])
    {
        self::$res = self::$dbh->prepare(self::$sql);
        self::$res->execute($params);
    }

    public function get()
    {
        $this->query();
        self::$data = self::$res->fetchAll(PDO::FETCH_OBJ);
        return self::$data;
    }

    public function count()
    {
        $this->query();
        self::$data = self::$res->rowCount();
        return self::$data;
    }

    public function getOne()
    {
        $this->query();
        self::$data = self::$res->fetch(PDO::FETCH_OBJ);
        return self::$data;
    }

    public function orderBy($col, $order)
    {
        self::$sql .= " order by $col $order";
        return $this;
    }

    public static function table($table) // Cannot use $this in a static method
    {
        self::$sql = "select * from $table";
        $db = new self();
        return $db;
    }

    public function where($col, $operator, $val = "")
    {
        switch (func_num_args()) {
            case 2:
                self::$sql .= " where $col='$operator'";
                break;
            case 3:
                self::$sql .= " where $col $operator '$val'";
                break;
        }
        return $this;
    }

    public function andWhere($col, $operator, $val = "")
    {
        switch (func_num_args()) {
            case 2:
                self::$sql .= " and $col='$operator'";
                break;
            case 3:
                self::$sql .= " and $col $operator '$val'";
                break;
        }
        return $this;
    }

    public function orWhere($col, $operator, $val = "")
    {
        switch (func_num_args()) {
            case 2:
                self::$sql .= " or $col='$operator'";
                break;
            case 3:
                self::$sql .= " or $col $operator '$val'";
                break;
        }
        return $this;
    }

    public static function create($table, $data)
    {
        $cols = implode(',', array_keys($data));
        $values = "";
        $count = 1;
        foreach ($data as $d) {
            $values .= "?";
            if ($count < count($data)) {
                $values .= ",";
            }
            $count++;
        }
        self::$sql = "insert into $table ($cols) values ($values)";
        $db = new self();
        $db->query(array_values($data)); // Pass params to be bound to query method
        $lastInsertId = self::$dbh->lastInsertId(); // PDO has this method of retrieving last inserted ID
        return DB::table($table)->where('id', $lastInsertId)->getOne();
    }

    public static function update($table, $data, $id)
    {
        $count = 1;
        $updateString = "";
        foreach ($data as $key => $val) {
            $updateString .= "$key=?";
            if ($count < count($data)) {
                $updateString .= ",";
            }
            $count++;
        }
        self::$sql = "update $table set $updateString where id='$id'";
        $db = new self();
        $db->query(array_values($data));
        return DB::table($table)->where('id', $id)->getOne();
    }

    public static function delete($table, $id)
    {
        self::$sql = "delete from $table where id='$id'";
        $db = new self();
        $db->query();
        return true;
    }

    public function paginate($records_per_page = 5)
    {
        // Get Total Records before paginate
        $totalRecords = $this->count();

        // Total Page Calculation
        $totalPages = (int) ceil($totalRecords / $records_per_page);
        $totalPages = $totalPages === 0 ? 1 : $totalPages;

        // Check GET params for page, also check out of bounds
        $page_no = isset($_GET['page']) ? $_GET['page'] : 1;
        $page_no = $page_no < 1 ? 1 : $page_no;
        $page_no = $page_no > $totalPages ? $totalPages : $page_no;

        // Paginate Query
        $startIndex = ($page_no - 1) * $records_per_page;
        self::$sql .= " limit $startIndex, $records_per_page";
        $data = $this->get();

        // Also check and add next and prev pages
        $next_no = $page_no == $totalPages ? 1 : $page_no + 1;
        $next_page = "?page=$next_no";
        $prev_no = $page_no == 1 ? $totalPages : $page_no - 1;
        $prev_page = "?page=$prev_no";

        // Build Return Array
        return [
            "data" => $data,
            "current_page_no" => $page_no,
            "total_pages" => $totalPages,
            "totalRecords" => $totalRecords,
            "prev_page" => $prev_page,
            "next_page" => $next_page
        ];
    }
}

// Get All
// $data = DB::table('users')->get();
// echo "<pre>";
// print_r($data);

// Get Count
// $data = DB::table('users')->count();
// echo "<pre>";
// print_r($data);

// Get One with Where
// $data = DB::table('users')->where('id', 1)->getOne();
// echo "<pre>";
// print_r($data);

// Get with Where 2
// $data = DB::table('users')->where('name', 'like', '%m%')->get();
// echo "<pre>";
// print_r($data);

// Get with andWhere 
// $data = DB::table('users')->where('name', 'like', '%b%')->andWhere('id', 10)->get();
// echo "<pre>";
// print_r($data);

// Get with orWhere
// $data = DB::table('users')->where('name', 'like', '%b%')->orWhere('id', 10)->get();
// echo "<pre>";
// print_r($data);

// Order By
// $data = DB::table('users')->orderBy('name', 'ASC')->get();
// echo "<pre>";
// print_r($data);

// Create Record
// $user = DB::create('users', [
//     'name' => 'Emilia',
//     'email' => 'emilia@gmail.com',
//     'age' => mt_rand(20, 25),
//     'location' => 'yangon'
// ]);
// if ($user) {
//     echo "Insertion Success";
//     echo "<pre>";
//     print_r($user);
// }

// Update Record
// $user = DB::update("users", [
//     'name' => 'emilia',
//     'email' => 'emiliaupdated@gmail.com',
//     'age' => mt_rand(20, 30),
//     'location' => 'updated location'
// ], 13);
// echo "<pre>";
// print_r($user);

// Delete Record
// $deleted = DB::delete("users", 11);
// if ($deleted) {
//     echo "Successfully Deleted";
// }

// Paginate
/*
$users = DB::table("users")->orderBy('id', 'DESC')->paginate(3);
?>
<a href="<?php echo $users['prev_page'] ?>">Prev</a>
<a href="<?php echo $users['next_page'] ?>">Next</a>
<p>
    Total : <?php echo $users['totalRecords'] ?>
</p>
<hr>
<?php foreach ($users['data'] as $user)
    echo $user->name . '<br>';
?>
*/
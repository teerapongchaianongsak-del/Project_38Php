<?php

/*
|--------------------------------------------------------------------------
| อ่านค่า Environment (Railway)
|--------------------------------------------------------------------------
| อ่านจาก getenv() ก่อน ถ้าไม่เจอลอง $_SERVER และ $_ENV
*/

function env(string $key, string $default = ""): string
{
    $value = getenv($key);

    if ($value === false || $value === "") {
        $value = $_SERVER[$key] ?? ($_ENV[$key] ?? "");
    }

    return $value !== "" ? (string) $value : $default;
}

$host     = env("PGHOST", "localhost");
$port     = env("PGPORT", "5432");
$user     = env("PGUSER", "postgres");
$password = env("PGPASSWORD", "");
$database = env("PGDATABASE", "postgres");

// ตั้ง APP_DEBUG=0 ใน Railway Variables เมื่อใช้งานจริง เพื่อซ่อนรายละเอียด error
$debug = env("APP_DEBUG", "1") === "1";

// --- DEBUG ชั่วคราว: แสดงใน Deploy Logs เฉพาะความยาว ไม่แสดงรหัสผ่าน ---
// ลบ 4 บรรทัดนี้ออกเมื่อแก้ปัญหาเสร็จ
error_log(
    "DBG host=" . $host .
    " port=" . $port .
    " user_len=" . strlen($user) .
    " PGPASSWORD len=" . strlen($password)
);


/*
|--------------------------------------------------------------------------
| Connect PostgreSQL
|--------------------------------------------------------------------------
*/

try {

    $dsn = "pgsql:host={$host};port={$port};dbname={$database}";

    $conn = new PDO(
        $dsn,
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

} catch (PDOException $e) {

    error_log("DB connection failed: " . $e->getMessage());

    http_response_code(500);

    if ($debug) {
        die("Database connection failed: " . htmlspecialchars($e->getMessage()));
    }

    die("ไม่สามารถเชื่อมต่อฐานข้อมูลได้ กรุณาลองใหม่ภายหลัง");

}


/*
|--------------------------------------------------------------------------
| สร้างตารางอัตโนมัติ (ถ้ายังไม่มี)
|--------------------------------------------------------------------------
*/

try {

    $conn->exec(
        "CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            mobile VARCHAR(50) NOT NULL
        )"
    );

} catch (PDOException $e) {

    error_log("Create table failed: " . $e->getMessage());

}


/*
|--------------------------------------------------------------------------
| ตรวจสอบ Web Server
|--------------------------------------------------------------------------
*/

$serverSoftware = $_SERVER["SERVER_SOFTWARE"] ?? "";

if (stripos($serverSoftware, "Apache") !== false) {

    $serverName = "Apache";

} elseif (stripos($serverSoftware, "nginx") !== false) {

    $serverName = "Nginx";

} else {

    $serverName = "Unknown";

}


/*
|--------------------------------------------------------------------------
| บันทึกข้อมูล
|--------------------------------------------------------------------------
*/

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name   = trim($_POST["name"] ?? "");
    $email  = trim($_POST["email"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");


    if ($name === "" || $email === "" || $mobile === "") {

        $message = "กรุณากรอกข้อมูลให้ครบ";

    } else {

        try {

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, mobile)
                 VALUES (:name, :email, :mobile)"
            );

            $stmt->execute([
                ":name"   => $name,
                ":email"  => $email,
                ":mobile" => $mobile
            ]);

            $message = "บันทึกข้อมูลเรียบร้อยแล้ว";

        } catch (PDOException $e) {

            error_log("Insert failed: " . $e->getMessage());

            $message = $debug
                ? "เกิดข้อผิดพลาด: " . $e->getMessage()
                : "เกิดข้อผิดพลาดในการบันทึกข้อมูล";

        }

    }

}


/*
|--------------------------------------------------------------------------
| ดึงข้อมูลผู้ใช้
|--------------------------------------------------------------------------
*/

try {

    $stmt = $conn->query(
        "SELECT id, name, email, mobile
         FROM users
         ORDER BY id DESC"
    );

    $users = $stmt->fetchAll();

} catch (PDOException $e) {

    error_log("Select failed: " . $e->getMessage());

    $users = [];

    $message = $debug
        ? "ไม่สามารถอ่านข้อมูลได้: " . $e->getMessage()
        : "ไม่สามารถอ่านข้อมูลได้";

}

?>

<!DOCTYPE html>

<html lang="th">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Contact Management</title>

<style>

    body {
        font-family: Arial, sans-serif;
        max-width: 900px;
        margin: 40px auto;
        padding: 20px;
    }

    h1 {
        margin-bottom: 10px;
    }

    .server {
        margin-bottom: 30px;
        padding: 10px 15px;
        background: #f5f5f5;
        border-left: 4px solid #333;
    }

    form {
        border: 1px solid #ddd;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
    }

    input {
        display: block;
        width: 100%;
        box-sizing: border-box;
        padding: 10px;
        margin: 8px 0 15px;
    }

    button {
        padding: 10px 20px;
        cursor: pointer;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 10px;
    }

    th {
        background: #f5f5f5;
    }

    .message {
        padding: 10px;
        background: #eee;
        margin-bottom: 20px;
    }

</style>

</head>

<body>

<h1>Contact Management</h1>

<div class="server">

    Server:
    <strong>
        <?= htmlspecialchars($serverName) ?>
    </strong>

</div>


<?php if ($message): ?>

    <div class="message">

        <?= htmlspecialchars($message) ?>

    </div>

<?php endif; ?>


<form method="POST">

    <label>ชื่อ</label>

    <input
        type="text"
        name="name"
        required
    >


    <label>Email</label>

    <input
        type="email"
        name="email"
        required
    >


    <label>เบอร์โทร</label>

    <input
        type="text"
        name="mobile"
        required
    >


    <button type="submit">
        บันทึกข้อมูล
    </button>

</form>


<h2>ข้อมูลผู้ใช้</h2>


<table>

    <tr>

        <th>ID</th>

        <th>ชื่อ</th>

        <th>Email</th>

        <th>เบอร์โทร</th>

    </tr>


    <?php foreach ($users as $row): ?>

        <tr>

            <td>
                <?= htmlspecialchars((string) $row["id"]) ?>
            </td>

            <td>
                <?= htmlspecialchars($row["name"]) ?>
            </td>

            <td>
                <?= htmlspecialchars($row["email"]) ?>
            </td>

            <td>
                <?= htmlspecialchars($row["mobile"]) ?>
            </td>

        </tr>

    <?php endforeach; ?>

</table>

</body>

</html>

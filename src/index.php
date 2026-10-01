<?php

$host = "mysql";
$user = "phpuser";
$password = "phppass";
$database = "php-app";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
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

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = $_POST["name"] ?? "";
    $email = $_POST["email"] ?? "";
    $mobile = $_POST["mobile"] ?? "";

    $stmt = $conn->prepare(
        "INSERT INTO users (name, email, mobile) VALUES (?, ?, ?)"
    );

    $stmt->bind_param("sss", $name, $email, $mobile);

    if ($stmt->execute()) {
        $message = "บันทึกข้อมูลเรียบร้อยแล้ว";
    } else {
        $message = "เกิดข้อผิดพลาด: " . $stmt->error;
    }

    $stmt->close();
}

$result = $conn->query("SELECT * FROM users ORDER BY id DESC");

?>

<!DOCTYPE html>
<html lang="th">

<head>

    <meta charset="UTF-8">

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
        Server: <strong><?= htmlspecialchars($serverName) ?></strong>
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

        <?php while ($row = $result->fetch_assoc()): ?>

            <tr>

                <td>
                    <?= htmlspecialchars($row["id"]) ?>
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

        <?php endwhile; ?>

    </table>

</body>

</html>

<?php

$conn->close();

?>
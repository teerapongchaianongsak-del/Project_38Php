<?php

$conn = pg_connect(
    "host=" . getenv("PGHOST") .
    " port=" . getenv("PGPORT") .
    " dbname=" . getenv("PGDATABASE") .
    " user=" . getenv("PGUSER") .
    " password=" . getenv("PGPASSWORD")
);

if (!$conn) {
    die("เชื่อมต่อ PostgreSQL ไม่สำเร็จ");
}

$result = pg_query($conn, "SELECT NOW()");

$row = pg_fetch_assoc($result);

echo "เชื่อมต่อ PostgreSQL สำเร็จ<br>";
echo "เวลาในฐานข้อมูล: " . $row['now'];

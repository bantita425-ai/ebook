<?php
session_start();
require_once "connect.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $sname = trim($_POST['surname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $tel = trim($_POST['tel']);

    // ตรวจสอบความถูกต้องแบบง่าย ๆ
    if ($name == "" || $sname == "" || $email == "" || $password == "" || $tel == "") {
        $error = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    } else {
        // เช็คอีเมลซ้ำ
        $sql_check = "SELECT * FROM user WHERE email_user = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check && $res_check->num_rows > 0) {
            $error = "อีเมลนี้ถูกใช้งานแล้ว";
        } else {
            // บันทึกข้อมูล (ถ้าจะใช้ password_hash ให้แก้ได้)
            $sql = "INSERT INTO user (name_user, sname_user, email_user, pass_user, tel_user) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $name, $sname, $email, $password, $tel);
            if ($stmt->execute()) {
                $success = "สมัครสมาชิกสำเร็จ! กำลังไปหน้าล็อกอิน...";
                header("refresh:2; url=login.php");
            } else {
                $error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>สมัครสมาชิก</title>
<style>
    body {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background: #f4f4f4;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }
    .container {
        background: white;
        padding: 2rem;
        border-radius: 6px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        width: 360px;
    }
    h2 {
        margin-bottom: 1.5rem;
        text-align: center;
        color: #333;
    }
    input[type="text"], input[type="email"], input[type="password"], input[type="tel"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 1rem;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }
    label {
        display: flex;
        align-items: center;
        font-size: 0.9rem;
        color: #555;
        margin-bottom: 1rem;
        user-select: none;
    }
    label input[type="checkbox"] {
        margin-right: 8px;
    }
    button {
        width: 100%;
        padding: 10px;
        background: #28a745;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    button:hover {
        background: #1e7e34;
    }
    .login-link {
        margin-top: 1rem;
        text-align: center;
        font-size: 0.9rem;
    }
    .login-link a {
        color: #28a745;
        text-decoration: none;
    }
    .login-link a:hover {
        text-decoration: underline;
    }
    .error-msg {
        color: red;
        margin-bottom: 1rem;
        text-align: center;
    }
    .success-msg {
        color: green;
        margin-bottom: 1rem;
        text-align: center;
    }
</style>
<script>
function togglePassword() {
    var pwd = document.getElementById("password");
    if (pwd.type === "password") {
        pwd.type = "text";
    } else {
        pwd.type = "password";
    }
}
</script>
</head>
<body>
<div class="container">
    <h2>สมัครสมาชิก</h2>
    <?php if ($error): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
        <div class="success-msg"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <input type="text" name="name" placeholder="ชื่อ" required />
        <input type="text" name="surname" placeholder="นามสกุล" required />
        <input type="email" name="email" placeholder="อีเมล" required />
        <input type="password" id="password" name="password" placeholder="รหัสผ่าน" required />
        <label><input type="checkbox" onclick="togglePassword()"> แสดงรหัสผ่าน</label>
        <input type="tel" name="tel" placeholder="เบอร์โทรศัพท์" required pattern="[0-9]{9,10}" title="กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง" />
        <button type="submit">ลงทะเบียน</button>
    </form>
    <div class="login-link">
        มีบัญชีแล้ว? <a href="login.php">เข้าสู่ระบบ</a>
    </div>
</div>
</body>
</html>

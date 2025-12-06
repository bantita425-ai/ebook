<?php
session_start();
require_once "connect.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "กรุณากรอกอีเมลและรหัสผ่านให้ครบถ้วน";
    } else {
        $sql = "SELECT * FROM user WHERE email_user = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // ตรวจสอบรหัสผ่าน (รองรับทั้งแบบ hash และ plain text)
                if (
                    $password === $user['pass_user'] || 
                    password_verify($password, $user['pass_user'])
                ) {
                    $_SESSION['user_id'] = $user['id_user'];
                    $_SESSION['user_name'] = $user['name_user'];
                    header("Location: index.php");  // redirect ไปหน้าแรก
                    exit();
                } else {
                    $error = "รหัสผ่านไม่ถูกต้อง";
                }
            } else {
                $error = "ไม่พบอีเมลนี้ในระบบ";
            }

            $stmt->close();
        } else {
            $error = "เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>เข้าสู่ระบบ</title>
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
        input[type="email"], input[type="password"] {
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
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background: #0056b3;
        }
        .register-link {
            margin-top: 1rem;
            text-align: center;
            font-size: 0.9rem;
        }
        .register-link a {
            color: #007bff;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .error-msg {
            color: red;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
    <script>
        function togglePassword() {
            const pwd = document.getElementById("password");
            pwd.type = pwd.type === "password" ? "text" : "password";
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>เข้าสู่ระบบ</h2>
        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="email" name="email" placeholder="อีเมล" required />
            <input type="password" id="password" name="password" placeholder="รหัสผ่าน" required />
            <label><input type="checkbox" onclick="togglePassword()"> แสดงรหัสผ่าน</label>
            <button type="submit">เข้าสู่ระบบ</button>
        </form>

        <div class="register-link">
            ยังไม่เคยสมัคร? <a href="register.php">ลงทะเบียน</a>
        </div>
    </div>
</body>
</html>

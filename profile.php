<?php
session_start();

$user_name = $_SESSION['user_name'] ?? '';

// ฟังก์ชันส่งอีเมล์รายงานปัญหา
$message_sent = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_message'])) {
    $report = trim($_POST['report_message']);
    if ($report !== '') {
        $to = "bantita425@gmail.com";
        $subject = "รายงานปัญหาจากผู้ใช้: " . ($user_name ?: "ไม่ระบุชื่อ");
        $headers = "From: no-reply@ebookstore.com\r\n";
        $body = "ผู้ใช้: " . ($user_name ?: "ไม่ระบุชื่อ") . "\n\nข้อความรายงาน:\n" . $report;

        if (mail($to, $subject, $body, $headers)) {
            $message_sent = "ส่งรายงานปัญหาสำเร็จ ขอบคุณสำหรับคำติชมของคุณค่ะ";
        } else {
            $message_sent = "ส่งรายงานล้มเหลว โปรดลองอีกครั้ง";
        }
    } else {
        $message_sent = "กรุณากรอกข้อความรายงาน";
    }
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>โปรไฟล์ผู้ใช้</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        .container {
            max-width: 700px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.1);
        }
        h1 {
            color: #5b3e9e;
            margin-bottom: 30px;
            text-align: center;
        }
        .user-name {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
            color: #6a4db8;
        }
        button.report-btn {
            background-color: #7a66c2;
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: block;
            margin: 0 auto 40px;
            transition: background-color 0.3s;
        }
        button.report-btn:hover {
            background-color: #5b3e9e;
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 10;
            left: 0; top: 0;
            width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.4);
            justify-content: center;
            align-items: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: white;
            padding: 30px 25px;
            border-radius: 10px;
            max-width: 480px;
            width: 90%;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        .modal-content h2 {
            margin-top: 0;
            color: #5b3e9e;
            margin-bottom: 20px;
        }
        .modal-content textarea {
            width: 100%;
            height: 120px;
            resize: vertical;
            padding: 12px;
            font-size: 1rem;
            border: 1px solid #aaa;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .modal-content button {
            background-color: #5b3e9e;
            color: white;
            padding: 12px 28px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            margin-right: 10px;
            transition: background-color 0.3s;
        }
        .modal-content button:hover {
            background-color: #48307a;
        }
        .modal-content .cancel-btn {
            background-color: #bbb;
        }
        .modal-content .cancel-btn:hover {
            background-color: #999;
        }
        .message-sent {
            text-align: center;
            font-size: 1.1rem;
            color: green;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<header class="main-header">
    <div class="container header-inner">
        <a href="index.php" class="logo">e‑Book Store</a>
        <form class="search-form" action="product.php" method="get">
            <input type="text" name="q" placeholder="ค้นหาหนังสือ..." />
            <button type="submit">ค้นหา</button>
        </form>
        <nav class="nav-menu">
            <a href="index.php">Home</a>
            <a href="product.php">Product</a>
            <a href="bookshelf.php">ชั้นหนังสือ</a>
            <a href="profile.php">โปรไฟล์</a>
        </nav>
    </div>
</header>

<main class="container">
    <h1>โปรไฟล์ผู้ใช้</h1>
    <div class="user-name">
        <?php echo $user_name ? "สวัสดี, " . htmlspecialchars($user_name) : "ยังไม่ได้เข้าสู่ระบบ"; ?>
    </div>

    <?php if ($message_sent): ?>
        <div class="message-sent"><?php echo htmlspecialchars($message_sent); ?></div>
    <?php endif; ?>

    <button class="report-btn" id="openReport">รายงานปัญหา</button>

    <!-- Modal -->
    <div class="modal" id="reportModal">
        <div class="modal-content">
            <h2>รายงานปัญหา</h2>
            <form method="post" action="profile.php">
                <textarea name="report_message" placeholder="กรุณากรอกรายละเอียดปัญหาของคุณ..." required></textarea>
                <div style="text-align: right;">
                    <button type="submit">ส่งรายงาน</button>
                    <button type="button" class="cancel-btn" id="cancelReport">ยกเลิก</button>
                </div>
            </form>
        </div>
    </div>
</main>

<footer class="main-footer">
    <div class="container">
        &copy; <?php echo date("Y"); ?> e‑Book Store. All rights reserved.
    </div>
</footer>

<script>
    const openBtn = document.getElementById('openReport');
    const modal = document.getElementById('reportModal');
    const cancelBtn = document.getElementById('cancelReport');

    openBtn.addEventListener('click', () => {
        modal.classList.add('active');
    });

    cancelBtn.addEventListener('click', () => {
        modal.classList.remove('active');
    });

    // ปิด modal เมื่อคลิกรอบนอก
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });
</script>

</body>
</html>

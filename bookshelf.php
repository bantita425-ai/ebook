<?php
require_once "connect.php";

session_start();

// สมมุติ user login จาก session หรือข้อมูล user_name ที่มากับ POST (ถ้าไม่มีระบบล็อกอินจริง ๆ)
// ในที่นี้จะใช้ user_name เก็บไว้ session อย่างง่าย
if (isset($_POST['user_name'])) {
    $_SESSION['user_name'] = trim($_POST['user_name']);
}
$user_name = $_SESSION['user_name'] ?? '';

$message = '';
$added_books = [];

// ดึงหนังสือที่ user เพิ่มแล้วจากตาราง order
if ($user_name) {
    $stmt = $conn->prepare("SELECT b.* FROM `order` o JOIN book b ON o.id_book = b.id_book WHERE o.name_user = ?");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $added_books[] = $row;
    }
    $stmt->close();
}

// ฟังก์ชันตรวจสอบและเพิ่มหนังสือจากรหัสที่กรอก
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pass_book_input'])) {
    $input_code = trim($_POST['pass_book_input']);

    if (!$user_name) {
        $message = "กรุณากรอกชื่อ user ก่อนเพิ่มรหัสหนังสือ";
    } elseif ($input_code === '') {
        $message = "กรุณากรอกรหัสหนังสือ";
    } else {
        // ตรวจสอบรหัสหนังสือในฐานข้อมูล
        $stmt = $conn->prepare("SELECT id_book FROM book WHERE pass_book = ?");
        $stmt->bind_param("s", $input_code);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows == 0) {
            $message = "คุณยังไม่ได้สั่งหนังสือเล่มนี้ (ไม่พบรหัสหนังสือ)";
        } else {
            $book_data = $res->fetch_assoc();
            $id_book = $book_data['id_book'];

            // ตรวจสอบว่า user มีหนังสือนี้ใน order หรือยัง
            $stmt2 = $conn->prepare("SELECT * FROM `order` WHERE id_book = ? AND name_user = ?");
            $stmt2->bind_param("is", $id_book, $user_name);
            $stmt2->execute();
            $res2 = $stmt2->get_result();

            if ($res2->num_rows == 0) {
                $message = "คุณยังไม่ได้สั่งหนังสือเล่มนี้ (ไม่มีคำสั่งซื้อในระบบ)";
            } else {
                // ตรวจสอบว่าซ้ำหรือไม่ในชั้นหนังสือ (ในที่นี้ชั้นหนังสือคือ order ที่ user มีอยู่)
                $exists = false;
                foreach ($added_books as $ab) {
                    if ($ab['id_book'] == $id_book) {
                        $exists = true;
                        break;
                    }
                }
                if ($exists) {
                    $message = "คุณเพิ่มหนังสือเล่มนี้ในชั้นหนังสือแล้ว";
                } else {
                    // เพิ่มเข้าในชั้นหนังสือ จริง ๆ order คือคำสั่งซื้อแล้ว
                    // ไม่ต้องเพิ่มอะไรเพิ่มเพราะมันเชื่อมกับ order แล้ว แค่รีเฟรช list ใหม่
                    $message = "เพิ่มหนังสือเรียบร้อยแล้ว";
                }
            }
            $stmt2->close();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ชั้นหนังสือของฉัน</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        form {
            margin-bottom: 30px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        input[type="text"] {
            flex: 1 1 250px;
            padding: 12px;
            font-size: 1rem;
            border: 1px solid #aaa;
            border-radius: 6px;
        }
        button {
            padding: 12px 28px;
            background-color: #9c8de1;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #7a66c2;
        }
        .message {
            margin-bottom: 30px;
            font-size: 1.1rem;
            color: #b55353;
        }
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px,1fr));
            gap: 20px;
        }
        .book-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgb(0 0 0 / 0.1);
            text-align: center;
            padding: 15px;
            cursor: pointer;
            transition: box-shadow 0.3s;
        }
        .book-card:hover {
            box-shadow: 0 6px 20px rgb(0 0 0 / 0.15);
        }
        .book-card img {
            max-width: 100%;
            border-radius: 6px;
            margin-bottom: 8px;
        }
        .book-card .title {
            font-weight: 600;
            color: #5b3e9e;
            font-size: 1rem;
            margin-bottom: 6px;
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
    <h1>ชั้นหนังสือของฉัน</h1>

    <form method="post" action="bookshelf.php">
        <input type="text" name="user_name" placeholder="กรอกชื่อ user" value="<?php echo htmlspecialchars($user_name); ?>" required />
        <input type="text" name="pass_book_input" placeholder="กรอกรหัสหนังสือ" required />
        <button type="submit">เพิ่ม</button>
    </form>

    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if (empty($added_books)): ?>
        <p>ยังไม่มีหนังสือในชั้นหนังสือของคุณ</p>
    <?php else: ?>
        <div class="book-grid">
            <?php foreach ($added_books as $book): ?>
                <div class="book-card" onclick="window.location='detail.php?id=<?php echo $book['id_book']; ?>'">
                    <img src="img/<?php echo htmlspecialchars($book['img_book']); ?>" alt="<?php echo htmlspecialchars($book['name_book']); ?>" />
                    <div class="title"><?php echo htmlspecialchars($book['name_book']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<footer class="main-footer">
    <div class="container">
        &copy; <?php echo date("Y"); ?> e‑Book Store. All rights reserved.
    </div>
</footer>
</body>
</html>

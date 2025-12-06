<?php
session_start(); // เริ่ม session
require_once "connect.php";

// ดึงข้อมูลหนังสือแนะนำ 3 เล่ม
$sql = "SELECT id_book, name_book, detail_book, img_book FROM book LIMIT 3";
$res = $conn->query($sql);
$recommend = [];
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $recommend[] = $row;
    }
}

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>หน้าแรก</title>
    <link rel="stylesheet" href="css/style.css" />
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
                <!-- ลิงก์โปรไฟล์ขึ้นกับสถานะล็อกอิน -->
                <a href="<?php echo $is_logged_in ? 'profile.php' : 'login.php'; ?>">โปรไฟล์</a>
            </nav>
        </div>
    </header>

    <main>
        <!-- ส่วนภาพเคลื่อนไหว / banner -->
        <section class="hero">
            <div class="overlay"></div>
            <div class="hero-content">
                <h1>ยินดีต้อนรับสู่ร้าน e‑Book</h1>
                <a href="product.php" class="btn-primary">เลือกสินค้า</a>
            </div>
        </section>

        <!-- ส่วนหนังสือแนะนำ -->
        <section class="recommend-section container">
            <h2>หนังสือแนะนำ</h2>
            <div class="recommend-list">
                <?php foreach ($recommend as $book): ?>
                    <div class="book-item">
                        <a href="detail.php?id=<?php echo $book['id_book']; ?>">
                            <img src="images/<?php echo htmlspecialchars($book['img_book']); ?>" alt="<?php echo htmlspecialchars($book['name_book']); ?>" />
                            <p class="book-title"><?php echo htmlspecialchars($book['name_book']); ?></p>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer class="main-footer">
        <div class="container">
            &copy; <?php echo date("Y"); ?> e‑Book Store. All rights reserved.
        </div>
    </footer>
</body>
</html>

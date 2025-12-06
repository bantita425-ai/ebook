<?php
require_once "connect.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: product.php");
    exit;
}

$id = (int)$_GET['id'];

// ดึงข้อมูลหนังสือจากฐานข้อมูล
$sql = "SELECT * FROM book WHERE id_book = $id LIMIT 1";
$res = $conn->query($sql);

if (!$res || $res->num_rows == 0) {
    echo "ไม่พบหนังสือที่คุณต้องการ";
    exit;
}

$book = $res->fetch_assoc();

// สมมุติราคา (เพิ่มเป็นฟิลด์ถ้ามี) หรือกำหนดเอง
$price = 199; // ตัวอย่างราคาหนังสือ 199 บาท
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>รายละเอียดหนังสือ | <?php echo htmlspecialchars($book['name_book']); ?></title>
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
                <a href="profile.php">โปรไฟล์</a>
            </nav>
        </div>
    </header>

    <main class="container" style="padding: 40px 0;">
        <div class="detail-wrapper">
            <div class="book-img">
                <img src="images/<?php echo htmlspecialchars($book['img_book']); ?>" alt="<?php echo htmlspecialchars($book['name_book']); ?>" />
            </div>
            <div class="book-info">
                <h2><?php echo htmlspecialchars($book['name_book']); ?></h2>
                <p class="detail-text"><?php echo nl2br(htmlspecialchars($book['detail_book'])); ?></p>
                <p class="price">ราคา: <strong><?php echo number_format($price); ?> บาท</strong></p>
                <a href="order.php?id=<?php echo $book['id_book']; ?>" class="btn-primary">สั่งซื้อที่นี่</a>
            </div>
        </div>
    </main>

    <footer class="main-footer">
        <div class="container">
            &copy; <?php echo date("Y"); ?> e‑Book Store. All rights reserved.
        </div>
    </footer>

</body>
</html>

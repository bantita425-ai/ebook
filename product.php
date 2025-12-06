<?php
require_once "connect.php";

// รับค่าค้นหา (ถ้ามี)
$search = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : "";

// ดึงข้อมูลหนังสือทั้งหมด หรือที่ค้นหา
$sql = "SELECT id_book, name_book, img_book FROM book";
if ($search !== "") {
    $sql .= " WHERE name_book LIKE '%$search%'";
}
$sql .= " ORDER BY id_book ASC";
$res = $conn->query($sql);

$books = [];
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $books[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>สินค้าทั้งหมด | ร้าน e‑Book</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <header class="main-header">
        <div class="container header-inner">
            <a href="index.php" class="logo">e‑Book Store</a>
            <form class="search-form" action="product.php" method="get">
                <input type="text" name="q" placeholder="ค้นหาหนังสือ..." value="<?php echo htmlspecialchars($search); ?>" />
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
        <h2 style="color:#5b3e9e; text-align:center; margin-bottom:30px;">สินค้าทั้งหมด</h2>

        <div class="product-grid">
            <?php if (count($books) === 0): ?>
                <p style="text-align:center; color:#777;">ไม่พบสินค้าตามที่ค้นหา</p>
            <?php else: ?>
                <?php
                // จัดเป็น 3 แถวๆ ละ 3 เล่ม รวม 9 เล่ม (ถ้ามี)
                $max_items = 9;
                $count = 0;
                foreach ($books as $book):
                    if ($count >= $max_items) break;
                ?>
                <div class="book-item">
                    <a href="detail.php?id=<?php echo $book['id_book']; ?>">
                        <img src="images/<?php echo htmlspecialchars($book['img_book']); ?>" alt="<?php echo htmlspecialchars($book['name_book']); ?>" />
                        <p class="book-title"><?php echo htmlspecialchars($book['name_book']); ?></p>
                    </a>
                    <a href="detail.php?id=<?php echo $book['id_book']; ?>" class="btn-primary" style="margin-top: 8px; display: inline-block;">สั่งสินค้า</a>
                </div>
                <?php
                    $count++;
                endforeach;
                ?>
            <?php endif; ?>
        </div>
    </main>

    <footer class="main-footer">
        <div class="container">

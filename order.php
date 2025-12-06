<?php
require_once "connect.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: product.php");
    exit;
}

$id_book = (int)$_GET['id'];

// ดึงข้อมูลหนังสือ
$sql = "SELECT * FROM book WHERE id_book = $id_book LIMIT 1";
$res = $conn->query($sql);

if (!$res || $res->num_rows == 0) {
    echo "ไม่พบหนังสือที่คุณต้องการสั่งซื้อ";
    exit;
}

$book = $res->fetch_assoc();

// สมมุติราคา (ถ้าไม่มีในฐานข้อมูล)
$price = 199;

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>สั่งซื้อ | <?php echo htmlspecialchars($book['name_book']); ?></title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        .steps {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 40px;
        }
        .step-box {
            flex: 1;
            padding: 15px 20px;
            text-align: center;
            border-radius: 8px;
            font-weight: 600;
            color: white;
        }
        .step1 { background-color: #8b7ddb; }
        .step2, .step3 { background-color: #d0cfe2; color: #555; }
        .order-info {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.1);
        }
        .order-info h3 {
            color: #5b3e9e;
            margin-bottom: 20px;
        }
        .order-summary {
            background: #f6f7fb;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 30px;
            font-size: 1rem;
            color: #444;
        }
        .btn-primary {
            background-color: #5b3e9e;
            color: white;
            padding: 12px 28px;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary:hover {
            background-color: #48307a;
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

    <main class="container" style="padding: 40px 0;">
        <div class="steps">
            <div class="step-box step1">1. รายการสินค้า</div>
            <div class="step-box step2">2. ชำระเงิน</div>
            <div class="step-box step3">3. ชำระเงินสำเร็จ</div>
        </div>

        <div class="order-info">
            <h3>รายการสินค้า</h3>
            <div>
                <p><strong>ชื่อหนังสือ:</strong> <?php echo htmlspecialchars($book['name_book']); ?></p>
                <p><strong>ราคา:</strong> <?php echo number_format($price); ?> บาท</p>
            </div>
            <div class="order-summary">
                <strong>สรุปรายการคำสั่งซื้อทั้งหมด:</strong><br />
                1 x <?php echo htmlspecialchars($book['name_book']); ?> — <?php echo number_format($price); ?> บาท
            </div>
            <a href="payment.php?id=<?php echo $book['id_book']; ?>" class="btn-primary">ดำเนินการสั่งซื้อ</a>
        </div>
    </main>

    <footer class="main-footer">
        <div class="container">
            &copy; <?php echo date("Y"); ?> e‑Book Store. All rights reserved.
        </div>
    </footer>

</body>
</html>

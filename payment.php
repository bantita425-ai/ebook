<?php
// เปิดแสดง error สำหรับ debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "connect.php";

// ตรวจสอบว่ามี id หนังสือใน URL และเป็นตัวเลข
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: product.php");
    exit;
}

$id_book = (int)$_GET['id'];

// ดึงข้อมูลหนังสือ
$sql = "SELECT * FROM book WHERE id_book = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_book);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows == 0) {
    echo "ไม่พบหนังสือที่คุณต้องการสั่งซื้อ";
    exit;
}

$book = $res->fetch_assoc();
$price = 199; // สมมุติราคาหนังสือ

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจากฟอร์ม
    $user_name = trim($_POST['user_name'] ?? '');
    $upload_ok = false;

    // ตรวจสอบ user_name
    if ($user_name === '') {
        $errors[] = "กรุณากรอกชื่อ user";
    }

    // ตรวจสอบไฟล์ภาพอัปโหลด
    if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] != 0) {
        $errors[] = "กรุณาอัปโหลดภาพหลักฐานการชำระเงิน";
    } else {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        // เช็ค mime type จากไฟล์จริงเพื่อความปลอดภัย
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['payment_proof']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_types)) {
            $errors[] = "ไฟล์ที่อัปโหลดต้องเป็นภาพ JPG, PNG หรือ GIF เท่านั้น";
        } else {
            $upload_ok = true;
        }
    }

    if (empty($errors) && $upload_ok) {
        // โฟลเดอร์เก็บไฟล์อัปโหลด
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $ext = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
        $new_filename = 'payment_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        $target_path = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $target_path)) {
            // บันทึกข้อมูลลงฐานข้อมูล orders (แก้ชื่อ table เป็น orders)
            $stmt2 = $conn->prepare("INSERT INTO orders (id_book, name_user, status_payment, payment_proof) VALUES (?, ?, ?, ?)");
            $status_payment = 'pending'; // สถานะรอการตรวจสอบ

            $stmt2->bind_param("isss", $id_book, $user_name, $status_payment, $new_filename);

            if ($stmt2->execute()) {
                $success = true;
                $stmt2->close();

                // redirect ไปหน้า success.php พร้อมส่ง user_name และ id_book
                header("Location: success.php?user=" . urlencode($user_name) . "&id=" . $id_book);
                exit;
            } else {
                $errors[] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล โปรดลองใหม่อีกครั้ง";
                // ลบไฟล์ที่อัปโหลดถ้าบันทึกไม่สำเร็จ
                if (file_exists($target_path)) unlink($target_path);
                $stmt2->close();
            }
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ชำระเงิน | <?php echo htmlspecialchars($book['name_book']); ?></title>
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
        .step1, .step3 { background-color: #d0cfe2; color: #555; }
        .step2 { background-color: #8b7ddb; }

        .payment-info {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.1);
            color: #333;
        }
        .payment-info h3 {
            color: #5b3e9e;
            margin-bottom: 20px;
        }
        .bank-info {
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        .bank-info p {
            margin: 5px 0;
        }
        form label {
            display: block;
            margin: 15px 0 8px;
            font-weight: 600;
            color: #5b3e9e;
        }
        form input[type="text"], form input[type="file"] {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #aaa;
            border-radius: 6px;
        }
        form button {
            margin-top: 25px;
            background-color: #5b3e9e;
            color: white;
            border: none;
            padding: 14px 32px;
            font-size: 1.2rem;
            border-radius: 8px;
            cursor: pointer;
        }
        form button:hover {
            background-color: #48307a;
        }
        .error-list {
            background: #ffdddd;
            color: #a33;
            padding: 10px 15px;
            border-radius: 6px;
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

    <main class="container" style="padding: 40px 0;">
        <div class="steps">
            <div class="step-box step1">1. รายการสินค้า</div>
            <div class="step-box step2">2. ชำระเงิน</div>
            <div class="step-box step3">3. ชำระเงินสำเร็จ</div>
        </div>

        <div class="payment-info">
            <h3>ชำระเงินสำหรับ: <?php echo htmlspecialchars($book['name_book']); ?></h3>
            <div class="bank-info">
                <p><strong>เลขบัญชี:</strong> 0-000-0000-0</p>
                <p><strong>ชื่อธนาคาร:</strong> กรุงเทพ</p>
                <p><strong>ราคาสุทธิ:</strong> <?php echo number_format($price); ?> บาท</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="error-list">
                    <ul>
                        <?php foreach($errors as $e): ?>
                            <li><?php echo htmlspecialchars($e); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="payment.php?id=<?php echo $book['id_book']; ?>" method="post" enctype="multipart/form-data" novalidate>
                <label for="payment_proof">อัปโหลดภาพหลักฐานการชำระเงิน</label>
                <input type="file" name="payment_proof" id="payment_proof" accept="image/*" required />

                <label for="user_name">กรอกชื่อ user</label>
                <input type="text" name="user_name" id="user_name" value="<?php echo htmlspecialchars($_POST['user_name'] ?? ''); ?>" required />

                <button type="submit">ชำระเงินสำเร็จ</button>
            </form>
        </div>
    </main>

    <footer class="main-footer">
        <div class="container">
            &copy; <?php echo date("Y"); ?> e‑Book Store. All rights reserved.
        </div>
    </footer>

</body>
</html>

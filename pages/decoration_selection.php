<?php
session_start();
require_once "../config/db.php"; // change if needed

if (!isset($_SESSION['booking_id'])) {
    die("No booking selected. Please start a booking first.");
}

$booking_id = (int)$_SESSION['booking_id'];
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

if ($category_id <= 0) die("Invalid category.");

$catStmt = $conn->prepare("SELECT category_name FROM decoration_categories WHERE category_id = ?");
$catStmt->bind_param("i", $category_id);
$catStmt->execute();
$catRow = $catStmt->get_result()->fetch_assoc();
if (!$catRow) die("Category not found.");

$category_name = $catRow['category_name'];

$itemStmt = $conn->prepare("
    SELECT item_id, item_name, description, image_path, price
    FROM decoration_items
    WHERE category_id = ? AND is_active = 1
    ORDER BY item_id DESC
");
$itemStmt->bind_param("i", $category_id);
$itemStmt->execute();
$items = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$qtyAllowed = in_array($category_name, ["Tables", "Chairs"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= htmlspecialchars($category_name) ?> - Items</title>
    <style>
        :root { --primary:#6b2fb8; --bg:#f5f5f7; }
        body{ margin:0; font-family: Arial, sans-serif; background: var(--bg); }
        .wrap{ max-width: 1100px; margin: 20px auto; padding: 0 14px; }

        .topbar{
            display:flex; align-items:center; justify-content:space-between;
            gap:12px; margin-bottom: 14px;
        }
        .title h2{ margin:0; }
        .title p{ margin:6px 0 0; color:#666; font-size:13px; }
        a.back{ text-decoration:none; color: var(--primary); font-weight: 800; }

        form{ margin-top: 12px; }
        .grid{
            display:grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap:14px;
        }
        .card{
            background:#fff;
            border-radius: 16px;
            border: 1px solid #eee;
            box-shadow: 0 10px 25px rgba(0,0,0,.06);
            overflow:hidden;
        }
        .img{
            height: 150px;
            background: #eee;
            display:flex; align-items:center; justify-content:center;
            color:#888; font-size: 12px;
        }
        .img img{ width:100%; height:100%; object-fit:cover; display:block; }

        .body{ padding: 14px; }
        .row1{ display:flex; justify-content:space-between; gap:10px; }
        .name{ font-weight:900; color:#222; }
        .price{ font-weight:900; color: var(--primary); }

        .desc{ margin-top:8px; color:#666; font-size: 13px; min-height: 34px; }

        .controls{
            margin-top: 12px;
            display:flex; align-items:center; justify-content:space-between;
            gap: 12px;
            padding-top: 12px;
            border-top: 1px dashed #eee;
        }
        label.select{
            display:flex; align-items:center; gap: 8px;
            font-weight:800; color:#333;
        }
        input[type="number"]{
            width:80px; padding:8px;
            border-radius:10px; border:1px solid #ddd;
            outline:none;
        }
        .saveBar{
            margin-top: 16px;
            display:flex;
            justify-content:flex-end;
        }
        .btn{
            border:none; cursor:pointer;
            background: var(--primary); color:#fff;
            padding: 10px 16px;
            border-radius: 12px;
            font-weight: 900;
        }
        .hint{ color:#666; font-size: 12px; }
    </style>
</head>
<body>
<div class="wrap">

    <div class="topbar">
        <div class="title">
            <h2><?= htmlspecialchars($category_name) ?></h2>
            <p><?= $qtyAllowed ? "Quantity enabled (Tables/Chairs)." : "No quantity (always 1)." ?></p>
            <a class="back" href="decoration_by_category.php">← Back to categories</a>
        </div>
    </div>

    <form method="POST" action="../actions/decoration_action.php">
        <input type="hidden" name="category_id" value="<?= $category_id ?>"/>

        <div class="grid">
            <?php foreach ($items as $it): 
                $id = (int)$it['item_id'];
                $price = (float)$it['price'];
                $img = $it['image_path'];
            ?>
            <div class="card">
                <div class="img">
                    <?php if (!empty($img)): ?>
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($it['item_name']) ?>">
                    <?php else: ?>
                        No image
                    <?php endif; ?>
                </div>

                <div class="body">
                    <div class="row1">
                        <div class="name"><?= htmlspecialchars($it['item_name']) ?></div>
                        <div class="price">$<?= number_format($price, 2) ?></div>
                    </div>
                    <div class="desc"><?= htmlspecialchars($it['description'] ?? '') ?></div>

                    <div class="controls">
                        <label class="select">
                            <input type="checkbox" name="selected_items[]" value="<?= $id ?>">
                            Select
                        </label>

                        <?php if ($qtyAllowed): ?>
                            <div>
                                <span class="hint">Qty</span><br/>
                                <input type="number" name="qty[<?= $id ?>]" value="1" min="1">
                            </div>
                        <?php else: ?>
                            <div class="hint">Qty = 1</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="saveBar">
            <button class="btn" type="submit">Save Selection</button>
        </div>
    </form>

</div>
</body>
</html>
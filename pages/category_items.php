<?php
session_start();
include("../config/db.php");
cancelExpiredBookings($conn);

if (!isset($_SESSION['user_id'])) {
  echo '<script>alert("Session expired. Please log in again."); window.location.href = "login.php";</script>';
  exit();
}
if (!isset($_SESSION['booking_id'])) {
  echo '<script>alert("Please complete the booking form first."); window.location.href = "booking_form.php";</script>';
  exit();
}
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

if ($category_id <= 0) {
    header("Location: decoration_by_category.php");
    exit();
}

// Get category name
$cstmt = $conn->prepare("SELECT category_name FROM decoration_categories WHERE category_id=?");
$cstmt->bind_param("i", $category_id);
$cstmt->execute();
$cat = $cstmt->get_result()->fetch_assoc();

if (!$cat) {
    header("Location: decoration_by_category.php");
    exit();
}

// Get items of this category
$istmt = $conn->prepare("SELECT item_id, item_name, description, price, image_path
                         FROM decoration_items
                         WHERE category_id=? AND is_active=1
                         ORDER BY item_name");
$istmt->bind_param("i", $category_id);
$istmt->execute();
$items = $istmt->get_result();

// Get previously selected item for THIS category (if user already chose before)
$prev = null;
$pstmt = $conn->prepare("
    SELECT bd.item_id, bd.quantity
    FROM booking_decorations bd
    JOIN decoration_items di ON di.item_id = bd.item_id
    WHERE bd.booking_id=? AND di.category_id=?
    LIMIT 1
");
$pstmt->bind_param("ii", $booking_id, $category_id);
$pstmt->execute();
$prev = $pstmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Select Item</title>

<style>
body{ margin:0; font-family:Arial,sans-serif; background:#f7f5f2; padding:22px; }
.wrap{ max-width:520px; margin:0 auto; }
.top{ display:flex; align-items:center; gap:12px; margin-bottom:16px; }
.back{
  width:44px;height:44px;border-radius:14px;
  display:flex;align-items:center;justify-content:center;
  background:#111;color:#fff;text-decoration:none;
}
h1{ margin:0; font-size:18px; color:#111; }
.list{ display:flex; flex-direction:column; gap:12px; }
.item{
  background:#fff;
  border-radius:16px;
  padding:14px;
  box-shadow:0 10px 22px rgba(0,0,0,0.07);
  border:1px solid rgba(0,0,0,0.06);
}
.row1{ display:flex; justify-content:space-between; align-items:flex-start; gap:10px; }
.name{ font-weight:800; font-size:14px; color:#111; }
.desc{ color:#777; font-size:12px; margin-top:6px; line-height:1.4; }
.price{ font-weight:800; color:#5b2c83; }
.controls{ margin-top:12px; display:flex; justify-content:space-between; align-items:center; gap:10px; }
.qty{ width:100px; padding:10px; border-radius:12px; border:1px solid #ddd; }
.btn{
  width:100%;
  margin-top:16px;
  padding:12px;
  border:none;
  border-radius:14px;
  background:#5b2c83;
  color:#fff;
  font-weight:800;
  cursor:pointer;
}
.note{ margin-top:12px; color:#666; font-size:12px; text-align:center; }
.item-img {
    width: 490px;        /* fill card width */
    height: 250px;       /* let height scale automatically */
    border-radius: 12px;
    margin-bottom: 12px;
    display: block;
}

</style>
</head>

<body>
<div class="wrap">
  <div class="top">
    <a class="back" href="decoration_by_category.php">←</a>
    <h1><?= htmlspecialchars($cat['category_name']) ?> — Choose One</h1>
  </div>

  <form action="../actions/save_category_selection.php" method="POST">
    <input type="hidden" name="category_id" value="<?= $category_id ?>">

    <div class="list">
      <?php while($it = $items->fetch_assoc()): ?>
        <div class="item">
          <?php if (!empty($it['image_path'])): ?>
    <img
      src="../<?= htmlspecialchars($it['image_path']) ?>"
      alt="<?= htmlspecialchars($it['item_name']) ?>"
      class="item-img"
      loading="lazy"
    >
  <?php endif; ?>
          <div class="row1">
            <div>
              <div class="name">
                <label>
                  <input
                    type="radio"
                    name="item_id"
                    value="<?= (int)$it['item_id'] ?>"
                    <?= ($prev && (int)$prev['item_id']==(int)$it['item_id']) ? "checked" : "" ?>
                    required
                  >
                  <?= htmlspecialchars($it['item_name']) ?>
                </label>
              </div>
              <?php if(!empty($it['description'])): ?>
                <div class="desc"><?= htmlspecialchars($it['description']) ?></div>
              <?php endif; ?>
            </div>
            <div class="price">$<?= number_format((float)$it['price'], 2) ?></div>
          </div>

          <div class="controls">
            <div style="color:#666;font-size:12px;">Quantity</div>
            <input class="qty" type="number" name="qty" min="1"
              value="<?= $prev ? (int)$prev['quantity'] : 1 ?>">
          </div>
        </div>
      <?php endwhile; ?>
    </div>

    <button class="btn" type="submit">Save Selection</button>
    <div class="note">You can return and change your choice anytime before payment.</div>
  </form>
</div>
</body>
</html>
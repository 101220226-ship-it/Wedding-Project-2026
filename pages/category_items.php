<?php
session_start();
include("../config/db.php");
cancelExpiredBookings($conn);

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
if (!isset($_SESSION['booking_id'])) {
  header("Location: booking_form.php");
  exit();
}

$booking_id = (int)$_SESSION['booking_id'];
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

// Get previously selected item for THIS category
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

// Store items in array for reuse
$itemsArray = [];
while($row = $items->fetch_assoc()) {
    $itemsArray[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Select Item - <?= htmlspecialchars($cat['category_name']) ?></title>
<style>
body { margin:0; font-family:Arial,sans-serif; background:#f7f5f2; padding:22px; }
.wrap { max-width:1200px; margin:0 auto; }
.top { display:flex; align-items:center; gap:12px; margin-bottom:16px; }
.back {
  width:44px; height:44px; border-radius:14px;
  display:flex; align-items:center; justify-content:center;
  background:#111; color:#fff; text-decoration:none; font-size:20px;
}
h1 { margin:0; font-size:18px; color:#111; }
.list { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:20px; }
.item {
  background:#fff;
  border-radius:16px;
  padding:14px;
  box-shadow:0 10px 22px rgba(0,0,0,0.07);
  border:2px solid transparent;
  cursor:pointer;
  transition: border-color 0.2s;
}
.item:hover { border-color:#ddd; }
.item.selected { border-color:#5b2c83; background:#faf8fc; }
.item-img {
  width:100%; height:180px; object-fit:cover;
  border-radius:12px; margin-bottom:12px;
}
.row1 { display:flex; justify-content:space-between; align-items:flex-start; gap:10px; }
.name { font-weight:800; font-size:14px; color:#111; display:flex; align-items:center; gap:8px; }
.desc { color:#777; font-size:12px; margin-top:6px; line-height:1.4; }
.price { font-weight:800; color:#5b2c83; font-size:16px; }
.controls { margin-top:12px; display:flex; justify-content:space-between; align-items:center; }
.qty { width:80px; padding:10px; border-radius:12px; border:1px solid #ddd; font-size:14px; }
.btn {
  display:block; width:100%; margin-top:20px; padding:16px;
  border:none; border-radius:14px; background:#5b2c83;
  color:#fff; font-weight:800; font-size:16px; cursor:pointer;
}
.btn:hover { background:#6f36a0; }
.note { margin-top:12px; color:#666; font-size:12px; text-align:center; }
</style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <a class="back" href="decoration_by_category.php">←</a>
    <h1><?= htmlspecialchars($cat['category_name']) ?> — Choose One</h1>
  </div>

  <form id="selectionForm" method="POST" action="../actions/save_category_selection.php">
    <input type="hidden" name="category_id" value="<?= $category_id ?>">
    <input type="hidden" name="item_id" id="selected_item_id" value="<?= $prev ? (int)$prev['item_id'] : '' ?>">
    <input type="hidden" name="qty" id="selected_qty" value="<?= $prev ? (int)$prev['quantity'] : 1 ?>">

    <div class="list">
      <?php foreach($itemsArray as $it): 
        $isSelected = ($prev && (int)$prev['item_id'] == (int)$it['item_id']);
        $qtyValue = $isSelected ? (int)$prev['quantity'] : 1;
      ?>
        <div class="item <?= $isSelected ? 'selected' : '' ?>" 
             data-item-id="<?= (int)$it['item_id'] ?>"
             onclick="selectItem(this, <?= (int)$it['item_id'] ?>)">
          
          <?php if (!empty($it['image_path'])): ?>
            <img src="../<?= htmlspecialchars($it['image_path']) ?>" 
                 alt="<?= htmlspecialchars($it['item_name']) ?>" 
                 class="item-img" loading="lazy">
          <?php endif; ?>
          
          <div class="row1">
            <div>
              <div class="name">
                <span class="checkmark"><?= $isSelected ? '✓' : '○' ?></span>
                <?= htmlspecialchars($it['item_name']) ?>
              </div>
              <?php if(!empty($it['description'])): ?>
                <div class="desc"><?= htmlspecialchars($it['description']) ?></div>
              <?php endif; ?>
            </div>
            <div class="price">$<?= number_format((float)$it['price'], 2) ?></div>
          </div>

          <div class="controls">
            <div style="color:#666;font-size:12px;">Quantity:</div>
            <input class="qty" type="number" min="1" value="<?= $qtyValue ?>"
                   data-item-id="<?= (int)$it['item_id'] ?>"
                   onclick="event.stopPropagation();"
                   onchange="updateQty(<?= (int)$it['item_id'] ?>, this.value)">
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <button type="submit" class="btn">Save Selection</button>
    <p class="note">You can return and change your choice anytime before payment.</p>
  </form>
</div>

<script>
function selectItem(element, itemId) {
    // Remove selected from all
    document.querySelectorAll('.item').forEach(function(item) {
        item.classList.remove('selected');
        item.querySelector('.checkmark').textContent = '○';
    });
    
    // Add selected to clicked
    element.classList.add('selected');
    element.querySelector('.checkmark').textContent = '✓';
    
    // Update hidden field
    document.getElementById('selected_item_id').value = itemId;
    
    // Update qty from this item's input
    var qtyInput = element.querySelector('.qty');
    document.getElementById('selected_qty').value = qtyInput.value;
}

function updateQty(itemId, value) {
    // If this item is selected, update the hidden field too
    var selectedId = document.getElementById('selected_item_id').value;
    if (selectedId == itemId) {
        document.getElementById('selected_qty').value = value;
    }
}

// Form validation
document.getElementById('selectionForm').addEventListener('submit', function(e) {
    var itemId = document.getElementById('selected_item_id').value;
    if (!itemId) {
        e.preventDefault();
        alert('Please select an item first by clicking on it!');
        return false;
    }
});
</script>
</body>
</html>

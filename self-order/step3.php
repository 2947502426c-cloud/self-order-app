<?php
require_once("database.php");
date_default_timezone_set('Asia/Hong_Kong');

// 驗證點餐token
if (!isset($_GET["tk"]) || empty($_GET["tk"])) {
    die("<h1 style='text-align:center; margin-top:100px; color:#dc3545;'>無效的點餐鏈接！</h1>");
}
$token = $_GET["tk"];

// 查詢用餐信息（桌號、結束時間）
try {
    $sql = "SELECT tableNo, timeEnd FROM newClient WHERE token = ?";
    $stmt = $con->prepare($sql);
    $stmt->execute([$token]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$client) {
        die("<h1 style='text-align:center; margin-top:100px; color:#dc3545;'>點餐鏈接已過期！</h1>");
    }
    $tableNo = $client["tableNo"];
    $timeEnd = $client["timeEnd"];
} catch (PDOException $e) {
    die("<h1 style='text-align:center; margin-top:100px;'>系統錯誤：" . $e->getMessage() . "</h1>");
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
/* 點餐頁核心樣式 */
body { padding-bottom: 180px; background: #f5f5f5; margin: 0; padding: 0; }
.navbar { background-color: #0d6efd !important; color: white; padding: 12px 20px; }
.container { max-width: 1200px; margin: 20px auto; padding: 0 15px; }
h2 { text-align: center; color: #0d6efd; margin-bottom: 30px; }

/* 菜品卡片樣式 */
.row { display: flex; flex-wrap: wrap; margin: 0 -10px; }
.col-6, .col-md-4, .col-lg-3 { padding: 0 10px; margin-bottom: 20px; }
.dish-card { 
    background: white; border-radius: 8px; overflow: hidden; 
    box-shadow: 0 2px 8px rgba(0,0,0,0.1); cursor: pointer;
    transition: transform 0.2s;
}
.dish-card:hover { transform: scale(1.03); }
.dish-img { width: 100%; height: 160px; object-fit: cover; }
.card-body { padding: 15px; }
.card-title { font-size: 1.1rem; margin: 0 0 8px; color: #333; }
.card-text { font-size: 1.2rem; color: #dc3545; font-weight: bold; margin: 0; }

/* 購物車底部面板 */
.cart-panel { 
    position: fixed; bottom: 0; left: 0; right: 0; 
    background: white; border-top: 3px solid #0d6efd;
    padding: 15px 20px; box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
}
.cart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.cart-header h4 { margin: 0; color: #333; }
.cart-items { max-height: 120px; overflow-y: auto; margin-bottom: 15px; }
.cart-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px dashed #eee; }
.cart-item span { font-size: 1rem; }
.cart-total { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
.cart-total span { font-size: 1.3rem; font-weight: bold; color: #333; }
.btn-submit { background: #0d6efd; color: white; border: none; padding: 12px 30px; border-radius: 4px; font-size: 1.1rem; cursor: pointer; }
.btn-submit:hover { background: #0b5ed7; }

/* 響應式調整 */
@media (max-width: 768px) {
    .col-md-4 { width: 50%; }
}
@media (max-width: 576px) {
    .col-6, .col-md-4, .col-lg-3 { width: 100%; }
    .cart-total span { font-size: 1.1rem; }
    .btn-submit { width: 100%; }
}
</style>
<title>好好食漢堡包 - 點餐頁</title>
</head>
<body>
<!-- 頂部導航欄（顯示桌號和倒計時） -->
<nav class="navbar">
    <div class="container-fluid">
        <span class="navbar-text fs-5">枱號：<?php echo $tableNo; ?></span>
        <span class="navbar-text fs-5">剩餘時間：<span id="countdown" class="text-warning">加載中...</span></span>
    </div>
</nav>

<!-- 菜品列表容器 -->
<div class="container">
    <h2>菜單</h2>
    <div class="row" id="dishList">
        <!-- 菜品將通過JS動態加載 -->
        <div class="col-12 text-center fs-4 py-5 text-secondary">加載菜品中...</div>
    </div>
</div>

<!-- 底部購物車面板 -->
<div class="cart-panel">
    <div class="cart-header">
        <h4>購物車 (<span id="cartCount">0</span>)</h4>
        <button class="btn btn-sm btn-danger" onclick="clearCart()">清空</button>
    </div>
    <div class="cart-items" id="cartItems">
        <div class="text-center text-secondary">購物車是空的</div>
    </div>
    <div class="cart-total">
        <span>總金額：¥<span id="totalPrice">0.00</span></span>
        <button class="btn-submit" onclick="submitOrder()">提交訂單</button>
    </div>
</div>

<!-- 引入依賴 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// 1. 倒計時功能（避免超時點餐）
const endTime = new Date("<?php echo $timeEnd; ?>");
function updateCountdown() {
    const now = new Date();
    const diff = endTime - now;
    
    if (diff <= 0) {
        document.getElementById("countdown").textContent = "00:00:00";
        alert("用餐時間已結束，無法繼續點餐！");
        return;
    }
    
    // 計算時分秒
    const hours = Math.floor(diff / 3600000).toString().padStart(2, '0');
    const minutes = Math.floor((diff % 3600000) / 60000).toString().padStart(2, '0');
    const seconds = Math.floor((diff % 60000) / 1000).toString().padStart(2, '0');
    document.getElementById("countdown").textContent = `${hours}:${minutes}:${seconds}`;
}
updateCountdown();
setInterval(updateCountdown, 1000);

// 2. 菜品數據和購物車狀態
let dishes = [];
let cart = [];

// 3. 加載菜品（調用get_dishes.php接口）
$.get("get_dishes.php", function(res) {
    try {
        const data = JSON.parse(res);
        if (data.status === "success" && Object.keys(data.dishes).length > 0) {
            dishes = data.dishes;
            renderDishes(); // 渲染菜品到頁面
        } else {
            document.getElementById("dishList").innerHTML = "<div class='col-12 text-center fs-4 py-5 text-secondary'>暫無菜品可點</div>";
        }
    } catch (err) {
        document.getElementById("dishList").innerHTML = "<div class='col-12 text-center fs-4 py-5 text-danger'>菜品加載失敗，請刷新頁面</div>";
        console.error("菜品加載錯誤：", err);
    }
});

// 4. 渲染菜品列表
function renderDishes() {
    let html = "";
    // 按分類循環渲染菜品（如漢堡、小吃、飲品）
    for (const category in dishes) {
        html += `<div class="col-12"><h3 class="text-center my-3 text-dark">${category}</h3></div>`;
        dishes[category].forEach(dish => {
            html += `
            <div class="col-6 col-md-4 col-lg-3">
                <div class="dish-card" onclick="addToCart(${dish.id})">
                    <img src="${dish.image}" class="dish-img" alt="${dish.name}">
                    <div class="card-body">
                        <h5 class="card-title">${dish.name}</h5>
                        <p class="card-text">¥${dish.price.toFixed(2)}</p>
                    </div>
                </div>
            </div>
            `;
        });
    }
    document.getElementById("dishList").innerHTML = html;
}

// 5. 加入購物車
function addToCart(dishId) {
    // 從所有菜品中找到對應ID的菜品
    const allDishes = Object.values(dishes).flat();
    const dish = allDishes.find(item => item.id === dishId);
    if (!dish) return;

    // 檢查購物車中是否已有該菜品，有則數量+1，無則新增
    const cartIndex = cart.findIndex(item => item.id === dishId);
    if (cartIndex > -1) {
        cart[cartIndex].quantity++;
    } else {
        cart.push({ ...dish, quantity: 1 });
    }

    updateCart(); // 更新購物車顯示
}

// 6. 更新購物車顯示
function updateCart() {
    const cartItemsEl = document.getElementById("cartItems");
    const cartCountEl = document.getElementById("cartCount");
    const totalPriceEl = document.getElementById("totalPrice");
    
    if (cart.length === 0) {
        cartItemsEl.innerHTML = "<div class='text-center text-secondary'>購物車是空的</div>";
        cartCountEl.textContent = "0";
        totalPriceEl.textContent = "0.00";
        return;
    }

    // 渲染購物車項目
    let cartHtml = "";
    let totalPrice = 0;
    cart.forEach(item => {
        const subtotal = item.price * item.quantity;
        totalPrice += subtotal;
        cartHtml += `
        <div class="cart-item">
            <span>${item.name} x${item.quantity}</span>
            <span>¥${subtotal.toFixed(2)}</span>
            <button class="btn btn-sm btn-danger" onclick="removeFromCart(${item.id})">刪除</button>
        </div>
        `;
    });

    cartItemsEl.innerHTML = cartHtml;
    cartCountEl.textContent = cart.length;
    totalPriceEl.textContent = totalPrice.toFixed(2);
}

// 7. 從購物車刪除菜品
function removeFromCart(dishId) {
    cart = cart.filter(item => item.id !== dishId);
    updateCart();
}

// 8. 清空購物車
function clearCart() {
    if (confirm("確定要清空購物車嗎？")) {
        cart = [];
        updateCart();
    }
}

// 9. 提交訂單（調用submit_order.php接口）
function submitOrder() {
    if (cart.length === 0) {
        alert("購物車是空的，無法提交訂單！");
        return;
    }

    if (!confirm("確定要提交訂單嗎？提交後無法修改！")) {
        return;
    }

    // 構建訂單數據
    const orderData = {
        tableNo: "<?php echo $tableNo; ?>",
        token: "<?php echo $token; ?>",
        cart: cart.map(item => ({
            dishId: item.id,
            dishName: item.name,
            price: item.price,
            quantity: item.quantity
        })),
        totalPrice: parseFloat(document.getElementById("totalPrice").textContent)
    };

    // 發送訂單數據到後端
    $.post("submit_order.php", JSON.stringify(orderData), function(res) {
        try {
            const data = JSON.parse(res);
            if (data.status === "success") {
                alert("訂單提交成功！廚房已收到，請耐心等待～");
                cart = [];
                updateCart();
            } else {
                alert("訂單提交失敗：" + data.msg);
            }
        } catch (err) {
            alert("訂單提交失敗，請重試！");
            console.error("訂單提交錯誤：", err);
        }
    }, "json");
}
</script>
</body>
</html>
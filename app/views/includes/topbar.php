<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SESSION['user']['role'] !==$role) {
    $_SESSION['error']='You must be logged in as '.$role;
    header('Location:/educhat/login');
    exit;
}
if (!isset($_SESSION['user'])) {
    $_SESSION['error']='You must be logged in';
    header('Location:/educhat/login');
    exit;
}
$appName= $_SESSION['user']['name'] ?? 'School Name';?>

<div class="topbar">
    <div class="app-name"><?php echo $appName;?></div>
    <form action="/educhat/logout" method="post" >
        <button type="submit" class="logout-btn" id="confirmBtn">Logout</button>
    </form>
</div>
<div class="main-content">
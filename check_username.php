<?php
require_once('./includes/db_link.php');

if (isset($_POST['username']) && !empty($_POST['username'])) {
    $username = mysqli_real_escape_string($link, $_POST['username']);
    $query = "SELECT username FROM users WHERE username = '$username'";
    $result = mysqli_query($link, $query);

    if (mysqli_num_rows($result) > 0) {
        echo "<span style='color:red'>نام کاربری وارد شده قبلاً در سیستم ثبت شده است.</span>";
    } else {
        echo "<span style='color:green'>نام کاربری وارد شده قابل استفاده است.</span>";
    }
} else {
    echo "<span style='color:orange'>لطفاً نام کاربری را وارد کنید.</span>";
}

mysqli_close($link);
?>

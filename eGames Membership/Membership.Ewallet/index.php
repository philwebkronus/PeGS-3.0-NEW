<?php
if(isset($_SESSION["MemberInfo"])){
    header("Location: profile.php");
} else {
    header("Location: login.php");
}
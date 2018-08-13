<a href="index.php">Home</a>
<div class="dropdown">
    <button class="dropbtn">Special Tools 
        <i class="fa fa-caret-down"></i>
    </button>
    <div class="dropdown-content">
<!--        <a href="changesiteterminalpassword.php">Change Site Terminal Password</a>
        <a href="habaneroaccountcreation.php">Habanero Terminal Account Creation</a>-->
        <a href="habanerouserbasedaccountcreation.php">Habanero Userbased Account Creation</a>
    </div>
</div> 
<div class="dropdown">
    <button class="dropbtn">View Full Logs
        <i class="fa fa-caret-down"></i>
    </button>
    <div class="dropdown-content">
        <!-- <a target="_balnk" href="log/ChangePassword/">Change Site Terminal Password Logs</a>
        <a target="_balnk" href="log/HabaneroAccountCreation/">Habanero Terminal Account Creation Logs</a>-->
        <a target="_balnk" href="log/HabaneroUserbasedAccountCreation/">Habanero Userbased Account Creation Logs</a>
    </div>
</div> 
<div style="float:right">
    <a href="logout.php" title="Logout"><span>LOGOUT AS <?php echo strtoupper($_SESSION['loggedin']); ?></span></a>
</div>



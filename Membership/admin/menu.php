<?php

/*
 * @author : owliber
 * @date : 2013-05-20
 */

include('sessionmanager.php');
?>
<div align="right" id="menu_container">
    <ul class="dropdown">
        <?php foreach($usermenu as $menu)
        {?>
          <li><a href="<?php echo $menu['Link']; ?>"><?php echo $menu['Name']; ?></a>
          <?php 
          //Check if current menu has submenus
          
          $menuid = $menu['MenuID'];
          $submenus = $accessrights->getSubMenus($menuid, $accounttypeid);
          
          if(is_array($submenus) && count($submenus) > 0)
          {?>
          <ul class="sub_menu">
              <?php foreach($submenus as $submenu)
              {?>
              <li><a href="<?php echo $submenu['Link']; ?>"><?php echo $submenu['Name']; ?></a></li>
              <?php
              }?>
          </ul>
          <?php 
          }?>
        <?php
        }?>
          </li>
          <li><a href="logout.php">Logout</a></li>
    </ul>
</div>
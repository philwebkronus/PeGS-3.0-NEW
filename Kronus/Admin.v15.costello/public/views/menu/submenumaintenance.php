<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>


<script>
    
    //Page Loader -- this is to send Menu ID via $_POST (not $_GET)
    function loadsubpage(x,g, m)
    {
        document.getElementById('submenuform').action = x;
        document.getElementById('group').value = g;
        jQuery('.mid').val(m);
//        document.getElementById('mid').value = m;
        document.getElementById('submenuform').submit();
    }

</script>
                 <?php
                
                    if (!empty($menuid))
                    {
                        $rightid = $_SESSION['acctype'];    
                        $dom = new DOMDocument;
                        $dom->load('../../xml/newXMLDocument.xml');
                        $xpath = new DOMXPath($dom);
                        $menu2 = $xpath->query('//right[@id='.$rightid.']/menu');
                        
                        $submenu = $xpath->query('//right[@id='.$rightid.']/menu[@id='.$menuid .']/submenu');

                        if($submenu->length)
                        {
                            echo "<ul id='menu1' class='menu collapsible expand'>";
                            
                            $tmpgroup = "";
                            foreach ($submenu as $submenu) 
                            {
                                
                                $group = $submenu->getAttribute('group');
                                $path = $submenu->getAttribute('path');
                                $name = $submenu->getAttribute('name');
                                if (($menugroup == $group) || (empty($group)) || $group==" ")
                                {
                                    $selected = "current_page_item";
                                }
                                else
                                {
                                    $selected = "";
                                }

//                                if (!empty($group))
//                                {
   
                                    if ($tmpgroup == "")
                                    {
                                        echo "<li>";
                                        echo "<a href='#' onclick=\"loadsubpage('menublank.php','".$group."','".$submenu->getAttribute('pagename')."');\">$group</a>";
                                        echo "<ul class='$selected'>";
                                    }
                                    elseif (($tmpgroup != $group) && (!empty($group)))
                                    {
                                        if($group == " ")
                                        {
                                            echo "
                                                </ul>
                                                </li>
                                            <li style=\"background-color: #CCCCCC\">
                                                ";
                                        }
                                        else
                                        {
                                            echo "
                                                </ul>
                                                </li>
                                            <li>
                                            <a href='#' onclick=\"loadsubpage('menublank.php','".$group."','".$submenu->getAttribute('pagename')."');\" >$group</a>
                                                <ul class='$selected'>";
                                        }

                                    }
              
//                                }
      
                                echo "<li><a style=\"background-color: #CCCCCC; cursor: pointer; color: #000000\" onclick=\"loadsubpage('".$path."','".$group."','".$submenu->getAttribute('pagename')."');\" >".$name."</a></li>"; 
                                
                                if (!empty($group))
                                    $tmpgroup = $group;
                                else
                                    $tmpgroup = "999";
                            }
                            
                            echo "  </ul> </li> </ul>";
                        }
                        else 
                        {
                          echo "";
    //                      die('xml not found or is empty');
                        }   
                    }
                 ?>

<form name="submenuform" id="submenuform" method="POST">
    <input type="hidden" id="group" name="group" />
    <input type="hidden" name="mid" class="mid" />
</form>
<?php

/**
 * Date Created 12 12, 11 2:43:52 PM <pre />
 * Description of TerminalPanel
 * @author Bryan Salazar
 */
class TerminalPanel extends MI_Widget {
    public function run($param) {
        $this->render('terminal_panel',$param);
    }
}


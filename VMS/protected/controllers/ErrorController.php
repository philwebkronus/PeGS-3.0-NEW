<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ErrorController
 *
 * @author elperez
 */
class ErrorController extends Controller{
    public function actionError()
    {
        $app = Yii::app();
        if( $error = $app->errorHandler->error->code )
        {
            if( $app->request->isAjaxRequest )
                echo $error['message'];

            $this->render( 'error' . ( $app->getViewFile( 'error' . $error ) ? $error : '' ), $error );
        }
    }
}

?>

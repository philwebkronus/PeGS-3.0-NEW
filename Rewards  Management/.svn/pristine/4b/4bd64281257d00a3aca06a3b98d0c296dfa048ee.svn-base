<?php

class ManagePartnersController extends Controller {

    public $showDialog = false;
    public $dialogMsg;

    /**
     * Get Partner Data from database and encode to json for jqgrid.
     */
    public function actionAddPartner() {
        $model = new ManagePartnersForm;
        $model->attributes = $_POST['ManagePartnersForm'];
        if ($model->addPartner()) {
            $model->addPartnerDetails();
        }
        $this->redirect(array('index', 'model' => $model));
    }

    public function actionEdit($id) {
//        $this->redirect(array('edit','model'=>$model));
        $model = new ManagePartnersForm;
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            
        }
        $this->render('index', array('model' => $model));
    }

    /**
     * Lists all models.
     */
    public function actionIndex() {
        $model = new ManagePartnersForm;
        $data = $model->getPartnerDetails();
        $updateUrl = $this->createUrl('update', array('id' => ''));
        $ctr = 0;
        $countData = count($data);
        if (is_array($data) && sizeof($data) > 0) {
            if (!array_key_exists('errcode', $data)) {
                do {
                    $arrayNewList['PartnerID'] = $data[$ctr]['PartnerID'];
                    $arrayNewList['PartnerName'] = urldecode($data[$ctr]['PartnerName']);
                    if ($data[$ctr]['Status'] == 1) {
                        $arrayNewList['Status'] = 'Active';
                    } else {
                        $arrayNewList['Status'] = 'Inactive';
                    }
                    $arrayNewList['NumberOfRewardOffers'] = urldecode($data[$ctr]['NumberOfRewardOffers']);
                    $arrayNewList['ContactPerson'] = urldecode($data[$ctr]['ContactPerson']);
                    $arrayNewList['ContactPersonEmail'] = urldecode($data[$ctr]['ContactPersonEmail']);
                    $arrayNewList['EditLink'] = "<div title='Edit Details'><form id='editlinkform' action='edit' method='post'><input type='hidden' id='editlinkid' value='$arrayNewList[PartnerID]'><a href='index?id=$arrayNewList[PartnerID]' onclick='editDialog();return false;'></form><span class='ui-icon ui-icon-gear'></span></div>";
                    $arrayData[] = $arrayNewList;
                    $ctr = $ctr + 1;
                } while ($ctr < $countData);
            }
        }
        if (Yii::app()->request->isAjaxRequest) {
            echo jqGrid::generateJSON(10, $arrayData, 'PartnerID');
            Yii::app()->end();
        }
        $this->render('index', array('model' => $model));
    }

    /**
     * Performs the AJAX validation.
     * @param ManagePartners $model the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'manage-partners-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

}

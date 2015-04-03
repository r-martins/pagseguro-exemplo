<?php
class RicardoMartins_PagSeguro_NotificationController extends Mage_Core_Controller_Front_Action
{
    /**
     * Recebe e processa as notificações do pagseguro quando há alguma notificação.
     * Não esqueça de configurar a url de retorno como http://sualoja.com.br/pagseguro/notification
     */
    public function indexAction()
    {
        $helper = Mage::helper('ricardomartins_pagseguro');
        if($helper->isSandbox()){
            $this->getResponse()->setHeader('access-control-allow-origin','https://sandbox.pagseguro.uol.com.br');
        }
        /** @var RicardoMartins_PagSeguro_Model_Abstract $model */
        Mage::helper('ricardomartins_pagseguro')->writeLog('Notificação recebida do pagseguro com os parametros:'. var_export($this->getRequest()->getParams(),true));
        $model =  Mage::getModel('ricardomartins_pagseguro/abstract');
        $response = $model->getNotificationStatus($this->getRequest()->getPost('notificationCode'));
        $model->proccessNotificatonResult($response);
    }
}
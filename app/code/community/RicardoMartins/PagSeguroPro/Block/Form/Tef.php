<?php
class RicardoMartins_PagSeguroPro_Block_Form_Tef extends Mage_Payment_Block_Form
{
    /**
     * Set block template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ricardomartins_pagseguropro/form/tef.phtml');
    }
}
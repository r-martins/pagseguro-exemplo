<?php
/**
* Key license type
*
* @author    Ricardo Martins <ricardo@ricardomartins.net.br>
*/
class RicardoMartins_PagSeguroPro_Model_Source_Keytype
{
    public function toOptionArray()
    {
    $options = array();
    $options[] = array('value'=>'','label'=>'Assinatura');
    $options[] = array('value'=>'app','label'=>'Aplicação');

    return $options;
    }
}
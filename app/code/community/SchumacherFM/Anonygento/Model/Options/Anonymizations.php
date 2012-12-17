<?php
/**
 * @category    SchumacherFM_Anonygento
 * @package     Block
 * @author      Cyrill at Schumacher dot fm
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @bugs        https://github.com/SchumacherFM/Anonygento/issues
 */

class SchumacherFM_Anonygento_Model_Options_Anonymizations extends Varien_Object
{
    /**
     * @var array
     */
    protected $_options = array(

        // internalKey => modelname
        'customer'             => 'customer/customer',
        'customerAddress'      => 'customer/address',
        'order'                => 'sales/order',
        'orderAddress'         => 'sales/order_address',
        'orderGrid'            => 'sales/order_grid_collection',
        'orderPayment'         => 'sales/order_payment',
        'quote'                => 'sales/quote',
        'quoteAddress'         => 'sales/quote_address',
        'quotePayment'         => 'sales/quote_payment',
        'creditmemoGrid'       => 'sales/order_creditmemo_grid_collection',
        'invoiceGrid'          => 'sales/order_invoice_grid_collection',
        'shipmentGrid'         => 'sales/order_shipment_grid_collection',
        'newsletterSubscriber' => 'newsletter/subscriber',
        'giftmessageMessage'   => 'giftmessage/message',
    );

    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        $return = array();
        foreach ($this->_options as $opt => $modelName) {
            $return[] = array(
                'label' => Mage::helper('schumacherfm_anonygento')->__($opt),
                'value' => $opt,
                'model' => $modelName
            );
        }

        return $return;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = array();
        foreach ($this->getAllOptions() as $option => $modelName) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }

    /**
     * @var Varien_Data_Collection
     */
    protected $_collection = null;

    /**
     * @return Varien_Data_Collection
     */
    public function getCollection()
    {

        if ($this->_hasAdminCollection()) {
            return $this->_getAdminCollection();
        }

        if ($this->_collection !== null) {
            return $this->_collection;
        }

        $this->_collection = new Varien_Data_Collection();

        $rowCountModel = Mage::getModel('schumacherfm_anonygento/counter');

        foreach ($this->getAllOptions() as $option) {

            $optObj = $this->_array2VO($option);
            $optObj
                ->setStatus(Mage::helper('schumacherfm_anonygento')->getAnonymizations($option['value']))
                ->setUnanonymized($rowCountModel->unAnonymized($option['model']))
                ->setAnonymized($rowCountModel->anonymized($option['model']));

            $this->_collection->addItem($optObj);
        }
        $this->_setAdminCollection();
        return $this->_collection;

    }

    protected function _setAdminCollection()
    {
        Mage::getSingleton('admin/session')->setAnonymizationsCollection($this->_collection->toArray());
    }

    /**
     * @return Varien_Data_Collection
     */
    protected function _getAdminCollection()
    {
        $return = Mage::getSingleton('admin/session')->getAnonymizationsCollection();

        if (!isset($return['items'])) {
            throw new Exception('items key is empty for getAnonymizationsCollection');
        }

        $collection = new Varien_Data_Collection();

        foreach ($return['items'] as $item) {
            $collection->addItem(
                $this->_array2VO($item)->setRowcountcached('yes')
            );
        }
        return $collection;
    }

    /**
     * @return bool
     */
    protected function _hasAdminCollection()
    {
        return (boolean)Mage::getSingleton('admin/session')->hasAnonymizationsCollection();
    }

    /**
     * @param array $array
     *
     * @return Varien_Object
     */
    protected function _array2VO($array)
    {
        $obj = new Varien_Object();
        $obj->addData($array);
        return $obj;
    }
}
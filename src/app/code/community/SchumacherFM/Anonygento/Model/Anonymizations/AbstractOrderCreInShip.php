<?php
/**
 * @category    SchumacherFM_Anonygento
 * @package     Model
 * @author      Cyrill at Schumacher dot fm
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @bugs        https://github.com/SchumacherFM/Anonygento/issues
 */
abstract class SchumacherFM_Anonygento_Model_Anonymizations_AbstractOrderCreInShip
    extends SchumacherFM_Anonygento_Model_Anonymizations_Abstract
{

    /**
     * normally this won't run
     */
    protected function _run()
    {
        parent::run($this->_getCollection(), '_anonymizeModel');
    }

    /**
     * @param Mage_Sales_Model_Abstract $model
     */
    protected function _anonymizeModel(Mage_Sales_Model_Abstract $model)
    {
        $model->setAnonymized(1);
        $this->_anonymizeModelComments($model);
        $model->getResource()->save($model);
        $model->getResource()->updateGridRecords($model->getOrderId());
        $model = null;
    }

    /**
     * @param Mage_Sales_Model_Abstract        $model
     */
    protected function _anonymizeModelComments(Mage_Sales_Model_Abstract $model)
    {
        $emptyCopy = new Varien_Object();
        $emptyCopy->setAnonymized(1);

        $commentsCollection = $model->getCommentsCollection();
        foreach ($commentsCollection as $comment) {
            $this->_copyObjectData($emptyCopy, $comment);
            $comment->getResource()->save($comment);
        }
        $commentsCollection = null;
    }

    /**
     * @param Mage_Sales_Model_Order       $order
     */
    public function anonymizeByOrder(Mage_Sales_Model_Order $order)
    {
        $collection = $order->{'get' . $this->getCollectionName() . 'Collection'}();
        foreach ($collection as $collectionModel) {
            $this->_anonymizeModel($collectionModel, $this->getModelName());
        }
        $collection = null;

    }

    /**
     * @return Varien_Data_CollectionDb
     */
    protected function _getCollection()
    {
        return parent::_getCollection('sales/order_' . $this->getModelName());
    }

}
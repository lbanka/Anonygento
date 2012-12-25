<?php
/**
 * @category    SchumacherFM_Anonygento
 * @package     Model
 * @author      Cyrill at Schumacher dot fm
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @bugs        https://github.com/SchumacherFM/Anonygento/issues
 */
class SchumacherFM_Anonygento_Model_Anonymizations_GiftmessageMessage extends SchumacherFM_Anonygento_Model_Anonymizations_Abstract
{

    public function run()
    {
        $messageCollection = $this->_getCollection();

        $i = 0;
        foreach ($messageCollection as $message) {
            $this->_anonymizeGiftMessage($message);
            $this->getProgressBar()->update($i);
            $i++;
        }
        $this->getProgressBar()->finish();
    }

    /**
     * @param Mage_GiftMessage_Model_Message $subscriber
     */
    protected function _anonymizeGiftMessage(Mage_GiftMessage_Model_Message $message)
    {

        $customer = $this->_getRandomCustomer()->getCustomer();

        $this->_copyObjectData($customer, $message, $this->_getMappings('GiftMessage'));

        $message->setMessage($this->_getInstance('schumacherfm_anonygento/random_loremIpsum')->getLoremIpsum(mt_rand(20, 40), 'txt'));
        $message->setRecipient($this->_getRandomCustomer()->getEmailWeird());

        $message->save();

    }

    /**
     * @return Mage_GiftMessage_Model_Resource_Message_Collection
     */
    protected function _getCollection()
    {
        $collection = Mage::getModel('giftmessage/message')
            ->getCollection()
            ->addFieldToSelect($this->_getMappings('GiftMessage')->getEntityAttributes());
        /* @var $collection Mage_GiftMessage_Model_Resource_Message_Collection */

        $this->_collectionAddStaticAnonymized($collection, 0);

        return $collection;
    }

}
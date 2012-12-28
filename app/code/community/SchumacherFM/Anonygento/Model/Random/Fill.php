<?php
/**
 * @category    SchumacherFM_Anonygento
 * @package     Model
 * @author      Cyrill at Schumacher dot fm
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @bugs        https://github.com/SchumacherFM/Anonygento/issues
 */
class SchumacherFM_Anonygento_Model_Random_Fill extends Varien_Object
{

    /**
     * @return boolean
     */
    public function fill()
    {
        $fill  = $this->getMappings()->getFill();
        $toObj = $this->getToObj();
        /* @var $toObj Varien_Object */

        if (!is_array($fill) || !is_object($toObj)) {
            return FALSE;
        }

        foreach ($fill as $attribute => $method) {
            $newData  = $this->_handleMappingMethod($method);
            $origData = $toObj->getData($attribute);

            if (!empty($origData)) {
                $toObj->setData($attribute, $newData);
            }
        }
        return TRUE;
    }

    /**
     * @param array $methodOptions
     *
     * @return mixed
     * @throws Exception
     */
    protected function _handleMappingMethod($methodOptions)
    {
        $model = NULL;
        if (isset($methodOptions['model']) && !empty($methodOptions['model'])) {
            $model = Mage::getModel($methodOptions['model']);
        } elseif (isset($methodOptions['helper']) && !empty($methodOptions['helper'])) {
            $model = Mage::helper($methodOptions['helper']);
        }

        $method = isset($methodOptions['method']) ? $methodOptions['method'] : '';
        $args   = isset($methodOptions['args']) ? $methodOptions['args'] : array();

        if (is_object($model) && method_exists($model, $method)) {
            return call_user_func_array(array($model, $method), $args);
        } else {
            return call_user_func_array($method, $args);
        }

    }

}
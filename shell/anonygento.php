<?php
/**
 * @category    SchumacherFM_Anonygento
 * @package     Model
 * @author      Cyrill at Schumacher dot fm
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @bugs        https://github.com/SchumacherFM/Anonygento/issues
 */

require_once 'abstract.php';

class Mage_Shell_Anonygento extends Mage_Shell_Abstract
{

    private $_devMode = FALSE;

    /**
     * @var SchumacherFM_Anonygento_Model_Console_Console
     */
    private $_console = null;

    /**
     * @var SchumacherFM\Anonygento\Model\Zend\Console\Adapter\Posix
     */
    private $_consoleInstance = null;

    protected function _construct()
    {
        /**
         * e.g. setting from .bash_profile
         */
        $this->_devMode = (isset($_SERVER['ANONYGENTO_DEV']) && $_SERVER['ANONYGENTO_DEV'] === 'true');

        // register Zend Framework 2 autoloader
        Mage::getModel('schumacherfm_anonygento/autoload_zf2')->register();
        $this->_console         = Mage::getModel('schumacherfm_anonygento/console_console');
        $this->_consoleInstance = $this->_console->getInstance();
    }

    public function __destruct()
    {
        Varien_Profiler::stop('Anonygento');
        $duration = Varien_Profiler::fetch('Anonygento', 'sum');

        $this->_consoleInstance->writeLine('Runs for ' . sprintf('%.2f', $duration) .
            ' secs or ' . sprintf('%.2f', $duration / 60) . ' min ');

    }

    /**
     * Run script
     *
     */
    public function run()
    {
        $userResult = $isAdminUser = FALSE;

        if ($this->_devMode !== TRUE) {
            $prompt = $this->_console->getModelZf2('console_prompt_confirm');
            $prompt->setPromptText('Anonymize this Magento installation? [y/n]');
            $userResult = $prompt->show();
            $isAdminUser = $userResult ? $this->_console->isAdminUser() : FALSE;
        }

        if (($userResult && $isAdminUser) || $this->_devMode === TRUE) {
            $this->_runAnonymization();
        } else {
            $this->_consoleInstance->writeLine('Nothing done! ' . $username . PHP_EOL . $password,
                SchumacherFM_Anonygento_Model_Console_Color::GREEN);
        }
    }

    private function _runAnonymization()
    {
        Varien_Profiler::enable();
        Varien_Profiler::start('Anonygento');

        $_execCollection = $this->_console->getAnonymizationCollection();

        foreach ($_execCollection as $anonExec) {
            $anonModel = $this->_console->getModel($anonExec->getValue());

            $reCalc = $this->_console->reCalcUnAnonymized($anonExec->getModel());

            if ($anonModel) {
                $this->_consoleInstance->writeLine('Running ' . $anonExec->getLabel() . ', work load: ' .
                        $anonExec->getUnanonymized() . '/' . $reCalc . ' rows',
                    SchumacherFM_Anonygento_Model_Console_Color::MAGENTA);

                if ($reCalc > 0 || $this->_devMode === TRUE) {
                    $progessBar = $this->_console->getProgressBar($reCalc);
                    $anonModel->setProgressBar($progessBar);
                    $anonModel->run();
                }
            } else {
                $this->_consoleInstance->writeLine('Model ' . $anonExec->getValue() . ' not found or not necessary!',
                    SchumacherFM_Anonygento_Model_Console_Color::LIGHT_RED);
            }

        }

    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return 'Usage:  php -f anonygento.php' . PHP_EOL . PHP_EOL;
    }
}

$shell = new Mage_Shell_Anonygento($argv);
$shell->run();
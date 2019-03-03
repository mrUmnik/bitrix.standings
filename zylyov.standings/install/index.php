<?php

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;

class zylyov_standings extends CModule
{
    public $MODULE_ID = "zylyov.standings";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_PATH;

    public function __construct()
    {
        $this->MODULE_NAME = Loc::getMessage("ZS.MODULE_NAME");
        $this->MODULE_DESCRIPTION = "";
        $this->MODULE_PATH = $this->getModulePath();

        $arModuleVersion = array();
        include $this->MODULE_PATH . "/install/version.php";

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
    }

    protected function getModulePath()
    {
        $modulePath = explode(DIRECTORY_SEPARATOR, __FILE__);
        $modulePath = array_slice($modulePath, 0, array_search($this->MODULE_ID, $modulePath) + 1);

        return join(DIRECTORY_SEPARATOR, $modulePath);
    }

    public function doInstall()
    {
        if (!\Bitrix\Main\Loader::includeModule('ui')) {
            ShowError(Loc::getMessage("ZS.MODULE_UI_NOT_INSTALLED"));
            //@todo проверка что версия модуля ui больше или равна 18.5.1
        } else {
            ModuleManager::registerModule($this->MODULE_ID);
            $this->installDB();
            $this->installEvents();
            $this->installFiles();
        }
    }

    public function doUninstall()
    {
        $this->unInstallDB();
        $this->unInstallEvents();
        $this->unInstallFiles();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }


    public function installEvents()
    {
        RegisterModuleDependences("main", "OnBuildGlobalMenu", $this->MODULE_ID, "\\Zylyov\\Standings\\Menu", "OnBuildGlobalMenu");
        return true;
    }

    public function unInstallEvents()
    {
        UnRegisterModuleDependences("main", "OnBuildGlobalMenu", $this->MODULE_ID, "\\Zylyov\\Standings\\Menu", "OnBuildGlobalMenu");
        return true;
    }

    public function installDB()
    {
        global $DB;
        if (!($DB->Query("SHOW TABLES LIKE 'zylyov_standings_team'")->Fetch())) {
            $DB->runSQLBatch($this->getModulePath() . '/install/db/install.sql');
        }

        return true;
    }

    public function unInstallDB()
    {
        return true;
    }

    public function installFiles()
    {
        $docRoot = $_SERVER['DOCUMENT_ROOT'];
        copyDirFiles(
            $this->getModulePath() . '/install/components',
            $docRoot . '/bitrix/components/zylyov',
            true, true
        );
        copyDirFiles(
            $this->getModulePath() . '/install/admin',
            $docRoot . '/bitrix/admin',
            true, true
        );
        copyDirFiles(
            $this->getModulePath() . '/install/js',
            $docRoot . '/bitrix/js/zylyov',
            true, true
        );
        copyDirFiles(
            $this->getModulePath() . '/install/public',
            $docRoot,
            true, true
        );
        return true;
    }

    public function unInstallFiles()
    {
        return true;
    }
}

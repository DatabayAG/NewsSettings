<?php

declare(strict_types=1);

use ILIAS\DI\Container;
use ILIAS\Plugin\NewsSettings\GUI\Administration\Settings;

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

class ilNewsSettingsPlugin extends ilEventHookPlugin
{
    private const CTYPE = 'Services';
    private const CNAME = 'EventHandling';
    private const SLOT_ID = 'evhk';
    private const PNAME = 'NewsSettings';

    /** @var self */
    private static $instance = null;
    /** @var bool */
    protected static $initialized = false;
    /** @var int[] */
    protected static $createdObjIds = [];
    /** @var \ILIAS\DI\Container */
    protected $dic;

    public function __construct()
    {
        global $DIC;

        $this->dic = $DIC;
        parent::__construct();
    }

    protected function init() : void
    {
        parent::init();
        $this->registerAutoloader();

        if (!self::$initialized) {
            self::$initialized = true;

            $this->dic['plugin.newssettings.settings'] = function (Container $c) : Settings {
                return new Settings(
                    new ilSetting($this->getId())
                );
            };
        }
    }

    public function registerAutoloader() : void
    {
        require_once __DIR__ . '/../vendor/autoload.php';
    }

    public static function getInstance() : self
    {
        if (null === self::$instance) {
            /** @var self $instance */
            $instance = ilPluginAdmin::getPluginObject(
                self::CTYPE,
                self::CNAME,
                self::SLOT_ID,
                self::PNAME
            );

            self::$instance = $instance;
        }

        return self::$instance;
    }

    public function handleEvent($a_component, $a_event, $a_parameter) : void
    {
        if (
            'Services/Object' === $a_component &&
            'create' === $a_event &&
            isset($a_parameter['obj_id'])
        ) {
            self::$createdObjIds[] = (int) $a_parameter['obj_id'];
            return;
        }

        if (
            'Services/Object' === $a_component &&
            'putObjectInTree' === $a_event &&
            isset($a_parameter['object']) &&
            (
                isset($a_parameter['obj_id']) &&
                in_array((int) $a_parameter['obj_id'], self::$createdObjIds, true)
            )
        ) {
            /** @var ilObject $object */
            $object = $a_parameter['object'];
            /** @var Settings $pluginSettings */
            $pluginSettings = $this->dic['plugin.newssettings.settings'];

            // TODO: Does not work for grp and crs
            if (
                in_array($object->getType(), $this->getValidObjectTypes(), true) &&
                $pluginSettings->isNewsEnabledFor($object->getType())
            ) {
                $object->setUseNews(true);

                if ($pluginSettings->isNewsBlockEnabledFor($object->getType())) {
                    $object->setNewsBlockActivated(true);
                }

                if (basename($_SERVER['PHP_SELF']) !== 'server.php') {
                    $object->update();
                }
            }
        }
    }

    /**
     * @return string[]
     */
    public function getValidObjectTypes() : array
    {
        return [
            'cat',
            'crs',
            'grp',
        ];
    }

    public function getPluginName() : string
    {
        return self::PNAME;
    }
}

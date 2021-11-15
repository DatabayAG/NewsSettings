<?php

declare(strict_types=1);
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

    protected function init() : void
    {
        parent::init();
        $this->registerAutoloader();

        if (!self::$initialized) {
            self::$initialized = true;
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
            (
                isset($a_parameter['obj_type'])
            ) &&
            isset($a_parameter['obj_id'])
        ) {
            self::$createdObjIds[] = $a_parameter['obj_id'];
            return;
        }
    }

    public function getPluginName() : string
    {
        return self::PNAME;
    }
}

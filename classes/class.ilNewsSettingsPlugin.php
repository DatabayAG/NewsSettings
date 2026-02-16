<?php

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
 *********************************************************************/

declare(strict_types=1);

use ILIAS\DI\Container;
use ILIAS\Plugin\NewsSettings\GUI\Administration\Settings;

class ilNewsSettingsPlugin extends ilEventHookPlugin
{
    private const ID = 'objnewsefaultset';

    private static ?self $instance = null;
    private static bool $initialized = false;
    /** @var list<int> */
    private static array $createdObjIds = [];

    private Container $dic;

    public function __construct(
        ilDBInterface $db,
        ilComponentRepositoryWrite $component_repository,
        string $id
    ) {
        global $DIC;

        $this->dic = $DIC;

        parent::__construct($db, $component_repository, $id);
    }

    protected function init(): void
    {
        parent::init();

        if (!self::$initialized) {
            self::$initialized = true;

            $this->dic['plugin.newssettings.settings'] = function (Container $c): Settings {
                return new Settings(
                    new ilSetting($this->getId())
                );
            };
        }
    }

    public static function getInstance(): self
    {
        global $DIC;

        if (self::$instance instanceof self) {
            return self::$instance;
        }

        $componentFactory = $DIC["component.factory"];
        self::$instance = $componentFactory->getPlugin(self::ID);
        return self::$instance;
    }

    public function handleEvent($a_component, $a_event, $a_parameter): void
    {
        if ('Services/Object' === $a_component &&
            'create' === $a_event &&
            isset($a_parameter['obj_id'])) {
            self::$createdObjIds[] = (int) $a_parameter['obj_id'];
            return;
        }

        if (isset($a_parameter['object'], $a_parameter['obj_id']) &&
            'Services/Object' === $a_component &&
            'putObjectInTree' === $a_event &&
            in_array((int) $a_parameter['obj_id'], self::$createdObjIds, true)) {
            /** @var ilObject|ilContainer $object */
            $object = $a_parameter['object'];
            /** @var Settings $pluginSettings */
            $pluginSettings = $this->dic['plugin.newssettings.settings'];

            if (in_array($object->getType(), $this->getValidObjectTypes(), true) &&
                $pluginSettings->isNewsEnabledFor($object->getType())) {
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
     * @return list<string>
     */
    public function getValidObjectTypes(): array
    {
        return [
            'cat',
            'crs',
            'grp',
        ];
    }
}

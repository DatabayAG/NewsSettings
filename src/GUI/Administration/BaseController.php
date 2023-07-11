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

namespace ILIAS\Plugin\NewsSettings\GUI\Administration;

use ilCtrl;
use ilGlobalTemplateInterface;
use ilLanguage;
use ilNewsSettingsApplyConfigGUI;
use ilNewsSettingsConfigGUI;
use ilNewsSettingsPlugin;
use ilObjComponentSettingsGUI;
use ilPluginConfigGUI;
use ilSetting;
use ilTabsGUI;
use ilUtil;
use ilCtrlInterface;

abstract class BaseController extends ilPluginConfigGUI
{
    protected ilCtrlInterface $ctrl;
    protected ilTabsGUI $tabs;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $pageTemplate;
    protected ilSetting $settings;
    protected \ILIAS\UI\Factory $uiFactory;
    protected \ILIAS\UI\Renderer $uiRenderer;
    protected \ILIAS\DI\Container $dic;
    protected \ILIAS\HTTP\GlobalHttpState $http;
    protected Settings $pluginSettings;

    public function __construct(ilNewsSettingsPlugin $plugin = null)
    {
        global $DIC;

        $this->dic = $DIC;
        $this->tabs = $DIC->tabs();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->pageTemplate = $DIC->ui()->mainTemplate();
        $this->settings = $DIC->settings();
        $this->uiFactory = $DIC->ui()->factory();
        $this->uiRenderer = $DIC->ui()->renderer();
        $this->http = $DIC->http();

        $this->plugin_object = $plugin;
        if (!$this->plugin_object instanceof ilNewsSettingsPlugin) {
            $this->plugin_object = ilNewsSettingsPlugin::getInstance();
        }
    }

    private function setCtrlParameterFromQuery(string $class, string $parameter): void
    {
        if (
            isset($this->http->request()->getQueryParams()[$parameter]) &&
            is_string($this->http->request()->getQueryParams()[$parameter])
        ) {
            $this->ctrl->setParameterByClass(
                $class,
                $parameter,
                ilUtil::stripSlashes($this->http->request()->getQueryParams()[$parameter])
            );
        }
    }

    private function setCtrlParameterFromBody(string $class, string $parameter): void
    {
        if (
            isset($this->http->request()->getParsedBody()[$parameter]) &&
            is_string($this->http->request()->getParsedBody()[$parameter])
        ) {
            $this->ctrl->setParameterByClass(
                $class,
                $parameter,
                ilUtil::stripSlashes($this->http->request()->getParsedBody()[$parameter])
            );
        }
    }

    public function executeCommand(): void
    {
        foreach (['ctype', 'cname', 'slot_id', 'plugin_id', 'pname'] as $parameter) {
            $this->setCtrlParameterFromQuery(static::class, $parameter);
        }

        $this->pageTemplate->setTitle(
            $this->lng->txt('cmps_plugin') . ': ' . ilUtil::stripSlashes($this->http->request()->getQueryParams()['pname'])
        );
        $this->pageTemplate->setDescription('');

        $this->performCommand($this->ctrl->getCmd());
        $this->showTabs();
    }

    protected function showTabs(): void
    {
        $this->tabs->clearTargets();

        foreach ([ilObjComponentSettingsGUI::class, ilNewsSettingsConfigGUI::class] as $controllerClass) {
            foreach (['ctype', 'cname', 'slot_id', 'plugin_id', 'pname'] as $parameter) {
                $this->setCtrlParameterFromQuery($controllerClass, $parameter);
            }
        }

        $this->showBackTargetTab();

        $this->tabs->addTab(
            'configuration_presets',
            $this->plugin_object->txt('configuration_presets'),
            $this->ctrl->getLinkTargetByClass(ilNewsSettingsConfigGUI::class)
        );

        $this->tabs->addTab(
            'modify_settings',
            $this->plugin_object->txt('modify_settings'),
            $this->ctrl->getLinkTargetByClass(ilNewsSettingsApplyConfigGUI::class)
        );
    }

    protected function showBackTargetTab(): void
    {
        if (isset($this->http->request()->getQueryParams()['plugin_id'])) {
            $this->tabs->setBackTarget(
                $this->lng->txt('cmps_plugin'),
                $this->ctrl->getLinkTargetByClass(ilObjComponentSettingsGUI::class, 'showPlugin')
            );
        } else {
            $this->tabs->setBackTarget(
                $this->lng->txt('cmps_plugins'),
                $this->ctrl->getLinkTargetByClass(ilObjComponentSettingsGUI::class, 'listPlugins')
            );
        }
    }

    public function performCommand(string $cmd): void
    {
        $this->pluginSettings = $this->dic['plugin.newssettings.settings'];

        if (true === method_exists($this, $cmd)) {
            $this->{$cmd}();
        } else {
            $this->{$this->getDefaultCommand()}();
        }
    }

    abstract protected function getDefaultCommand(): string;
}

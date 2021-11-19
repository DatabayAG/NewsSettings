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

abstract class BaseController extends ilPluginConfigGUI
{
    /** @var ilCtrl */
    protected $ctrl;
    /** @var ilTabsGUI */
    protected $tabs;
    /** @var ilLanguage */
    protected $lng;
    /** @var ilGlobalTemplateInterface */
    protected $pageTemplate;
    /** @var ilSetting */
    public $settings;
    /** @var ilNewsSettingsPlugin */
    protected $plugin_object;
    /** @var \ILIAS\UI\Factory */
    protected $uiFactory;
    /** @var \ILIAS\UI\Renderer */
    protected $uiRenderer;
    /** @var \ILIAS\DI\Container */
    protected $dic;
    /** @var \ILIAS\HTTP\GlobalHttpState */
    protected $http;
    /** @var Settings */
    protected $pluginSettings;

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

    private function setCtrlParameterFromQuery(string $class, string $parameter) : void
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

    private function setCtrlParameterFromBody(string $class, string $parameter) : void
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

    public function executeCommand() : void
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

    protected function showTabs() : void
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

    protected function showBackTargetTab() : void
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

    /**
     * @param string $cmd
     */
    public function performCommand($cmd) : void
    {
        $this->pluginSettings = $this->dic['plugin.newssettings.settings'];

        if (true === method_exists($this, $cmd)) {
            $this->{$cmd}();
        } else {
            $this->{$this->getDefaultCommand()}();
        }
    }

    abstract protected function getDefaultCommand() : string;
}

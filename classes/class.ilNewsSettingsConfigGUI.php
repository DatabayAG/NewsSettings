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

use ILIAS\Plugin\NewsSettings\GUI\Administration\BaseController;

class ilNewsSettingsConfigGUI extends BaseController
{
    protected function getDefaultCommand() : string
    {
        return 'showPresetConfiguration';
    }

    public function executeCommand() : void
    {
        $nextClass = $this->ctrl->getNextClass();
        switch (strtolower($nextClass)) {
            default:
                parent::executeCommand();
                $this->tabs->activateTab('configuration_presets');
                break;
        }
    }

    protected function getPresetForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'savePresetConfiguration'));
        $form->setTitle($this->plugin_object->txt('configuration_presets'));

        $form->addCommandButton('savePresetConfiguration', $this->lng->txt('save'));

        return $form;
    }

    protected function showPresetConfiguration(ilPropertyFormGUI $form = null) : void
    {
        if (null === $form) {
            $form = $this->getPresetForm();
        }

        $this->populateValues($form);
        $this->pageTemplate->setContent($form->getHTML());
    }

    protected function savePresetConfiguration() : void
    {
        $form = $this->getPresetForm();
        if ($form->checkInput()) {
            ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
            $this->ctrl->redirect($this);
        }

        $form->setValuesByPost();

        $this->pageTemplate->setContent($form->getHTML());
    }

    protected function populateValues(ilPropertyFormGUI $form) : void
    {
        $form->setValuesByArray([
        ]);
    }
}

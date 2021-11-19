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

class ilNewsSettingsApplyConfigGUI extends BaseController
{
    protected function getDefaultCommand() : string
    {
        return 'showConfiguration';
    }

    public function executeCommand() : void
    {
        $nextClass = $this->ctrl->getNextClass();
        switch (strtolower($nextClass)) {
            default:
                parent::executeCommand();
                $this->tabs->activateTab('modify_settings');
                break;
        }
    }

    protected function getForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'confirmApplyConfiguration'));
        $form->setTitle($this->plugin_object->txt('modify_settings'));
        $form->addCommandButton('confirmApplyConfiguration', $this->plugin_object->txt('btn_label_migrate_objs'));

        $enabledServices = new ilCheckboxInputGUI(
            $this->plugin_object->txt('enabled_news_and_block'),
            'enabled_news_and_block'
        );
        $enabledServices->setValue('1');

        $this->lng->loadLanguageModule('trac');
        $objectTypes = new ilMultiSelectInputGUI(
            $this->lng->txt('obj_types'),
            'obj_types'
        );
        $objectTypes->setRequired(true);
        $options = [];
        foreach ($this->plugin_object->getValidObjectTypes() as $objectType) {
            $options[$objectType] = $this->lng->txt('obj_' . $objectType);
        }
        $objectTypes->setOptions($options);
        $enabledServices->addSubItem($objectTypes);

        $form->addItem($enabledServices);

        return $form;
    }

    protected function showConfiguration(ilPropertyFormGUI $form = null) : void
    {
        if (null === $form) {
            $form = $this->getForm();
        }

        $this->pageTemplate->setContent($form->getHTML());
    }

    protected function confirmApplyConfiguration() : void
    {
        $form = $this->getForm();
        if ($form->checkInput()) {
            $confirmation = new ilConfirmationGUI();

            $confirmation->setFormAction($this->ctrl->getFormAction($this, 'applyConfiguration'));
            $confirmation->setConfirm($this->lng->txt('confirm'), 'applyConfiguration');
            $confirmation->setCancel($this->lng->txt('cancel'), $this->getDefaultCommand());

            $objTypes = [];
            foreach ($form->getInput('obj_types') as $objType) {
                $confirmation->addHiddenItem('obj_types[]', $objType);
                $objTypes[] = $this->lng->txt('obj_' . $objType);
            }

            $confirmation->setHeaderText(sprintf(
                $this->plugin_object->txt('sure_adopt_preset_x'),
                implode(', ', $objTypes)
            ));

            $this->pageTemplate->setContent($confirmation->getHTML());
            return;
        }

        $form->setValuesByPost();

        $this->pageTemplate->setContent($form->getHTML());
    }

    protected function applyConfiguration() : void
    {
        $objTypes = array_intersect(
            (array) ($this->http->request()->getParsedBody()['obj_types'] ?? []),
            $this->plugin_object->getValidObjectTypes()
        );

        $newsSettings = [
            ilObjectServiceSettingsGUI::USE_NEWS,
            ilObjectServiceSettingsGUI::NEWS_VISIBILITY,
        ];

        foreach ($newsSettings as $newsSetting) {
            $objTypesIn = 'AND ' . $this->dic->database()->in('od.type', $objTypes, false, 'text');
            $this->dic->database()->manipulateF(
                'UPDATE container_settings
            INNER JOIN object_data od ON od.obj_id = container_settings.id  
            SET container_settings.value = %s
            WHERE container_settings.keyword = %s ' . $objTypesIn,
                ['text', 'text'],
                ['1', $newsSetting]
            );

            $this->dic->database()->manipulateF(
                'INSERT INTO container_settings
            (id, keyword, value)  
            (
                SELECT od.obj_id, %s, %s
                FROM object_data od
                LEFT JOIN container_settings
                    ON container_settings.id = od.obj_id AND container_settings.keyword = %s
                WHERE container_settings.id IS NULL ' . $objTypesIn . '
            )',
                ['text', 'text', 'text'],
                [$newsSetting, '1', $newsSetting]
            );
        }

        ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this);
    }
}

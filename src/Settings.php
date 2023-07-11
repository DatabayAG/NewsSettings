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

use ilSetting;

class Settings
{
    /** @var ilSetting */
    private $settings;

    private $newsByObjType = [];

    public function __construct(ilSetting $settings)
    {
        $this->settings = $settings;
        $this->read();
    }

    private function read(): void
    {
        $newsByObjType = $this->settings->get('news_by_obj_type', null);
        if ($newsByObjType !== null && $newsByObjType !== '') {
            $newsByObjType = json_decode($newsByObjType, true, 512, JSON_THROW_ON_ERROR);
        }

        if (!is_array($newsByObjType)) {
            $newsByObjType  = [];
        }

        $this->newsByObjType = $newsByObjType;
    }

    public function setNewsStatusFor(string $objType, bool $status): void
    {
        $this->newsByObjType[$objType]['news'] = $status;
    }

    public function isNewsEnabledFor(string $objType): bool
    {
        return $this->newsByObjType[$objType]['news'] ?? false;
    }

    public function setNewsBlockStatusFor(string $objType, bool $status): void
    {
        $this->newsByObjType[$objType]['news_block'] = $status;
    }

    public function isNewsBlockEnabledFor(string $objType): bool
    {
        return $this->newsByObjType[$objType]['news_block'] ?? false;
    }

    public function save(): void
    {
        $this->settings->set('news_by_obj_type', json_encode($this->newsByObjType, JSON_THROW_ON_ERROR));
    }
}

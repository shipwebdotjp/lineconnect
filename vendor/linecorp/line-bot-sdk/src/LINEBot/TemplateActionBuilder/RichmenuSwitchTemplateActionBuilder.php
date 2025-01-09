<?php
/**
 * Copyright 2018 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

 // made by shipweb 2025

namespace LINE\LINEBot\TemplateActionBuilder;

use LINE\LINEBot\Constant\ActionType;
use LINE\LINEBot\TemplateActionBuilder;

/**
 * A builder class for richmenu switch action.
 *
 * @package LINE\LINEBot\TemplateActionBuilder
 */
class RichmenuSwitchTemplateActionBuilder implements TemplateActionBuilder
{
    /** @var string */
	private $label;
	/** @var string */
	private $richMenuAliasId;
	/** @var string */
    private $data;

    /**
     * RichmenuSwitchAction constructor.
     * This action can be configured only with quick reply buttons.
     *
     * @param string $richMenuAliasId richMenuAliasId.
	 * param string $data data.
     */
    public function __construct($richMenuAliasId, $data, $label = null)
	{
		$this->richMenuAliasId = $richMenuAliasId;
		$this->data = $data;
		$this->label = $label;
	}

    /**
     * Builds richmenu switch action structure.
     *
     * @return array Built richmenu switch action structure.
     */
    public function buildTemplateAction()
    {
        return [
            'type' => ActionType::RICHMENU_SWITCH,
			'label' => $this->label,
            'richMenuAliasId' => $this->richMenuAliasId,
			'data' => $this->data,
        ];
    }
}

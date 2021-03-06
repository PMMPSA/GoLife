<?php

declare(strict_types = 1);

namespace Kit\libs;

use pocketmine\plugin\PluginBase;

class FormAPI extends PluginBase{


    /**
     * @deprecated
     *
     * @param callable|null $function
     * @return SimpleForm
     */
    public function createSimpleForm(?callable $function = null) : SimpleForm {
        return new SimpleForm($function);
    }
	/**
     * @deprecated
     *
     * @param callable|null $function
     * @return ModalForm
     */
    public function createModalForm(callable $function = null) : ModalForm {
        return new ModalForm($function);
    }

}

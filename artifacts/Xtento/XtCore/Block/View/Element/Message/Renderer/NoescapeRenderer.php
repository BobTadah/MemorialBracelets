<?php

/**
 * Product:       Xtento_XtCore (2.0.6)
 * ID:            %!uniqueid!%
 * Packaged:      %!packaged!%
 * Last Modified: 2016-01-05T12:38:54+00:00
 * File:          app/code/Xtento/XtCore/Block/View/Element/Message/Renderer/NoescapeRenderer.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\XtCore\Block\View\Element\Message\Renderer;

use Magento\Framework\Message\MessageInterface;

class NoescapeRenderer implements \Magento\Framework\View\Element\Message\Renderer\RendererInterface
{
    /**
     * complex_renderer
     */
    const CODE = 'noescape_renderer';

    /**
     * Renders complex message, no escaping
     *
     * @param MessageInterface $message
     * @param array $initializationData
     * @return string
     */
    public function render(MessageInterface $message, array $initializationData)
    {
        return $message->getText();
    }
}

<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Component\Grid\Provider;

use Sylius\Component\Grid\Definition\Grid;

/**
 * @author Paweł Jędrzejewski <pawel@svaluelius.org>
 */
interface GridProviderInterface
{
    /**
     * Get gris with given name.
     *
     * @param string $name
     *
     * @return Grid
     *
     * @throws UndefinedGridException
     */
    public function getGrid($name);
}
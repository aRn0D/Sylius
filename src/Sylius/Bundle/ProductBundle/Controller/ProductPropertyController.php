<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\ProductBundle\Controller;

use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductPropertyController extends ResourceController
{
    public function createAction(Request $request)
    {
        $property = $this->getProperty($request->get('id'));

        /** @var \Sylius\Bundle\ProductBundle\Model\ProductProperty $productProperty */
        $productProperty = $this->createNew();
        $productProperty->setProperty($property);

        $form = $this->createForm('sylius_product_property', $productProperty);
        $template = $this->getConfiguration()->getTemplate('render.html');

        return $this->render($template, array(
            'form' => $form->createView()
        ));
    }

    public function getProperty($id)
    {
        $property = $this->get('sylius.repository.property')->find($id);

        if (null === $property) {
            throw new NotFoundHttpException('Requested property does not exist');
        }

        return $property;
    }
}

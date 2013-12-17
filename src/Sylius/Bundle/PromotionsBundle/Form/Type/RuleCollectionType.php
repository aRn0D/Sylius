<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\PromotionsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RuleCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $prototypes = array();

        foreach ($options['registry']->getCheckers() as $type => $checker) {

            $form = $builder->create($options['prototype_name'], $options['type'], array_replace(array(
                'label' => $options['prototype_name'].'label__',
                'rule_type' => $type
            ), $options['options']));

            $prototypes[$type] = $form->getForm();
        }

        $builder->setAttribute('prototypes', $prototypes);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['prototypes'] = array();

        foreach ($form->getConfig()->getAttribute('prototypes') as $type => $prototype) {
            $view->vars['prototypes'][$type] = $prototype->createView($view);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array(
            'registry',
            'rule_type'
        ));

        $resolver
            ->setDefaults(array(
                'type'             => 'sylius_promotion_rule',
                'allow_add'        => true,
                'allow_delete'     => true,
                'by_reference'     => false,
                'label'            => 'sylius.form.promotion.rules',
                'button_add_label' => 'sylius.promotion.add_rule',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'collection';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sylius_rule_collection';
    }
}

<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\FixturesBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Bundle\FixturesBundle\DataFixtures\DataFixture;
use Sylius\Component\User\Model\GroupInterface;

/**
 * Group fixtures.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 */
class LoadGroupsData extends DataFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $manager->persist($this->createGroup('Administrators', array('ROLE_SYLIUS_ADMIN')));
        $manager->persist($this->createGroup('Wholesale Customers'));
        $manager->persist($this->createGroup('Retail Customers'));
        $manager->persist($this->createGroup('Sales'));
        $manager->persist($this->createGroup('Suppliers'));
        $manager->persist($this->createGroup('API', array('ROLE_API')));

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }

    /**
     * @param string $name
     * @param array  $roles
     *
     * @return GroupInterface
     */
    protected function createGroup($name, array $roles = array())
    {
        /* @var $group GroupInterface */
        $group = $this->getGroupRepository()->createNew();
        $group->setName($name);

        $this->setReference('Sylius.Group.'.$name, $group);

        return $group;
    }
}

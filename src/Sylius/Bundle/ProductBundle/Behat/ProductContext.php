<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\ProductBundle\Behat;

use Behat\Gherkin\Node\TableNode;
use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Bundle\ResourceBundle\Behat\DefaultContext;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Product\Model\ProductInterface;

class ProductContext extends DefaultContext
{
    /**
     * @Given /^there are products:$/
     * @Given /^there are following products:$/
     * @Given /^the following products exist:$/
     */
    public function thereAreProducts(TableNode $table)
    {
        $manager = $this->getEntityManager();

        foreach ($table->getHash() as $data) {
            $this->thereIsProduct($data, false);
        }

        $manager->flush();
    }

    /**
     * @Given /^there is product:$/
     */
    public function thereIsProduct($data, $flush = true)
    {
        $productBuilder = $this->getService('sylius.product_builder')
            ->create(trim($data['name']))
            ->setCurrentLocale($this->getContainer()->getParameter('sylius.locale'))
            ->setDescription('...')
            ->getMasterVariant()->setPrice($data['price'] * 100);

        if (!empty($data['options'])) {
            foreach (explode(',', $data['options']) as $option) {
                $option = $this->findOneByName('product_option', trim($option));
                $productBuilder->addOption($option);
            }
        }

        if (!empty($data['attributes'])) {
            $attribute = explode(':', $data['attributes']);

            $productBuilder->addAttribute($attribute[0], $attribute[1]);
        }

        if (isset($data['sku'])) {
            $productBuilder->setSku($data['sku']);
        }

        if (isset($data['description'])) {
            $productBuilder->setDescription($data['description']);
        }

        if (isset($data['quantity'])) {
            $productBuilder->getMasterVariant()->setOnHand($data['quantity']);
        }

        if (isset($data['variants selection']) && !empty($data['variants selection'])) {
            $productBuilder->setVariantSelectionMethod($data['variants selection']);
        }

        if (isset($data['tax category'])) {
            $productBuilder->setTaxCategory($this->findOneByName('tax_category', trim($data['tax category'])));
        }

        if (isset($data['taxons'])) {
            $taxons = new ArrayCollection();

            foreach (explode(',', $data['taxons']) as $taxonName) {
                $taxons->add($this->findOneByName('taxon', trim($taxonName)));
            }

            $productBuilder->setTaxons($taxons);
        }

        if (isset($data['deleted']) && 'yes' === $data['deleted']) {
            $productBuilder->setDeletedAt(new \DateTime());
        }

        return $productBuilder->save($flush);
    }

    /**
     * @Given /^there is prototype "([^""]*)" with following configuration:$/
     */
    public function thereIsPrototypeWithFollowingConfiguration($name, TableNode $table)
    {
        $manager = $this->getEntityManager();
        $repository = $this->getRepository('product_prototype');

        $prototype = $repository->createNew();
        $prototype->setName($name);

        $data = $table->getRowsHash();

        foreach (explode(',', $data['options']) as $optionName) {
            $prototype->addOption($this->findOneByName('product_option', trim($optionName)));
        }

        foreach (explode(',', $data['attributes']) as $attributeName) {
            $prototype->addAttribute($this->findOneByName('product_attribute', trim($attributeName)));
        }

        $manager->persist($prototype);
        $manager->flush();
    }

    /**
     * @Given /^there are following options:$/
     * @Given /^the following options exist:$/
     */
    public function thereAreOptions(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->thereIsOption($data['name'], $data['values'], $data['presentation'], false);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @Given /^I created option "([^""]*)" with values "([^""]*)"$/
     */
    public function thereIsOption($name, $values, $presentation = null, $flush = true)
    {
        $optionValueRepository = $this->getRepository('product_option_value');

        $option = $this->getRepository('product_option')->createNew();
        $option->setName($name);
        $option->setPresentation($presentation ?: $name);

        foreach (explode(',', $values) as $value) {
            $optionValue = $optionValueRepository->createNew();
            $optionValue->setValue(trim($value));

            $option->addValue($optionValue);
        }

        $manager = $this->getEntityManager();
        $manager->persist($option);

        if ($flush) {
            $manager->flush();
        }

        return $option;
    }

    /**
     * @Given /^there are following attributes:$/
     * @Given /^the following attributes exist:$/
     */
    public function thereAreAttributes(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $choices = isset($data['choices']) && $data['choices'] ? explode(',', $data['choices']) : array();
            $additionalData = array(
                'type'         => isset($data['type']) ? $data['type'] : 'text',
                'presentation' => isset($data['presentation']) ? $data['presentation'] : $data['name']
            );
            if ($choices) {
                $additionalData['configuration'] = array('choices' => $choices);
            }
            $this->thereIsAttribute($data['name'], $additionalData);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @Given /^There is attribute "([^""]*)"$/
     * @Given /^I created attribute "([^""]*)"$/
     */
    public function thereIsAttribute($name, $additionalData = array(), $flush = true)
    {
        $additionalData = array_merge(array(
            'presentation' => $name,
            'type' => 'text'
        ), $additionalData);

        $attribute = $this->getRepository('product_attribute')->createNew();
        $attribute->setName($name);

        foreach ($additionalData as $key => $value) {
            $attribute->{'set'.\ucfirst($key)}($value);
        }

        $manager = $this->getEntityManager();
        $manager->persist($attribute);
        if ($flush) {
            $manager->flush();
        }

        return $attribute;
    }

    /**
     * @Given /^the following product translations exist:$/
     */
    public function theFollowingProductTranslationsExist(TableNode $table)
    {
        $manager = $this->getEntityManager();

        foreach ($table->getHash() as $data) {
            $productTranslation = $this->findOneByName('product_translation', $data['product']);
            /** @var Product $product */
            $product = $productTranslation->getTranslatable();
            $product->setCurrentLocale($data['locale']);
            $product
                ->setName($data['name'])
                ->setDescription('...');
        }

        $manager->flush();
    }

    /**
     * @Then :locale translation for product :productName should exist
     */
    public function translationForProductExist($locale, $productName)
    {
        $product = $this->findOneByName('product_translation', $productName);

        if (!$product->getLocale() === $locale) {
            throw new \Exception('There is no translation for product'. $productName . ' in '.$locale . 'locale');
        }
    }

    /**
     * @Given the following attribute translations exist
     */
    public function theFollowingAttributeTranslationsExist(TableNode $table)
    {
        $manager = $this->getEntityManager();

        foreach ($table->getHash() as $data) {
            $attribute = $this->findOneByName('product_attribute', $data['attribute']);
            $attribute
                ->setCurrentLocale($data['locale'])
                ->setPresentation($data['presentation']);
        }

        $manager->flush();
    }

    /**
     * @Given the following option translations exist
     */
    public function theFollowingOptionTranslationsExist(TableNode $table)
    {
        $manager = $this->getEntityManager();

        foreach ($table->getHash() as $data) {
            $option = $this->findOneByName('product_option', $data['option']);
            $option
                ->setCurrentLocale($data['locale'])
                ->setPresentation($data['presentation']);
        }

        $manager->flush();
    }
}

<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\Generic\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => inline_value_objects([
                // source: https://github.com/Sylius/Sylius/blob/master/UPGRADE-1.0.md#upgrade-from-100-beta3-to-100
                new MethodCallRename(
                    'Sylius\Component\Core\Repository\OrderRepositoryInterface',
                    'count',
                    'countPlacedOrders'
                ),
                new MethodCallRename(
                    'Sylius\Component\Core\Repository\OrderRepositoryInterface',
                    'countByChannel',
                    'countFulfilledByChannel'
                ),
                new MethodCallRename(
                    'Sylius\Component\Product\Repository\ProductVariantRepositoryInterface',
                    'findByCodeAndProductCode',
                    'findByCodesAndProductCode'
                ),
                new MethodCallRename(
                    'Sylius\Component\Core\Model\OrderInterface',
                    'getLastNewPayment',
                    'getLastPayment'
                ),
                new MethodCallRename(
                    'Sylius\Component\Taxonomy\Model\TaxonInterface',
                    'getParents ',
                    'getAncestors'
                ),
            ]),
        ]]);

    $services->set(AddParamTypeDeclarationRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::TYPEHINT_FOR_PARAMETER_BY_METHOD_BY_CLASS => [
                'Sylius\Bundle\CoreBundle\Context\SessionAndChannelBasedCartContext' => [
                    '__construct' => ['Sylius\Component\Core\Storage\CartStorageInterface'],
                ],
            ],
        ]]);

    $services->set(ArgumentAdderRector::class)
        ->call('configure', [[
            ArgumentAdderRector::POSITION_WITH_DEFAULT_VALUE_BY_METHOD_NAMES_BY_CLASS_TYPES => [
                'Sylius\Component\Mailer\Sender\SenderInterface' => [
                    'send' => [[
                        'name' => 'code',
                        'default_value' => '[]',
                    ]],
                ],
                'Sylius\Component\Core\Repository\ProductRepositoryInterface' => [
                    'findOneBySlug' => [
                        2 => [
                            'name' => '__unknown__',
                            'default_value' => false,
                        ],
                    ],
                ],
            ],
        ]]);

    $services->set(AddReturnTypeDeclarationRector::class)
        ->call('configure', [[
            AddReturnTypeDeclarationRector::TYPEHINT_FOR_METHOD_BY_CLASS => [
                'Sylius\Component\Order\Model\OrderInterface' => [
                    'getAdjustmentsRecursively' => [
                        'array' => 'Doctrine\Common\Collections\Collection',
                    ],
                ],
                'Sylius\Component\Order\Model\OrderItemInterface' => [
                    'getAdjustmentsRecursively' => [
                        'array' => 'Doctrine\Common\Collections\Collection',
                    ],
                ],
                'Sylius\Component\Registry\PrioritizedServiceRegistryInterface' => [
                    'all' => [
                        'Zend\Stdlib\PriorityQueue' => 'iterable',
                    ],
                ],
            ],
        ]]);

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'Sylius\Bundle\CoreBundle\Context\SessionAndChannelBasedCartContext' => 'Sylius\Component\Core\Storage\CartStorageInterface',
                'Sylius\Bundle\CoreBundle\EmailManager\ShipmentEmailManager' => 'Sylius\Bundle\AdminBundle\EmailManager\ShipmentEmailManager',
                'Sylius\Bundle\CoreBundle\EmailManager\ShipmentEmailManagerInterface' => 'Sylius\Bundle\AdminBundle\EmailManager\ShipmentEmailManagerInterface',
                'Sylius\Bundle\CoreBundle\EmailManager\ContactEmailManager' => 'Sylius\Bundle\ShopBundle\EmailManager\ContactEmailManager',
                'Sylius\Bundle\CoreBundle\EmailManager\ContactEmailManagerInterface' => 'Sylius\Bundle\ShopBundle\EmailManager\ContactEmailManagerInterface',
                'Sylius\Bundle\CoreBundle\EmailManager\OrderEmailManager' => 'Sylius\Bundle\ShopBundle\EmailManager\OrderEmailManager',
                'Sylius\Bundle\CoreBundle\EmailManager\OrderEmailManagerInterface' => 'Sylius\Bundle\ShopBundle\EmailManager\OrderEmailManagerInterface',
                'Sylius\Bundle\CoreBundle\EventListener\UserMailerListener' => 'Sylius\Bundle\ShopBundle\EventListener\UserMailerListener',
                'Sylius\Bundle\CoreBundle\Form\Type\ProductTaxonChoiceType' => 'Sylius\Bundle\CoreBundle\Form\Type\Taxon\ProductTaxonAutocompleteChoiceType',
                'Sylius\Component\Order\Factory\AddToCartCommandFactoryInterface' => 'Sylius\Bundle\OrderBundle\Factory\AddToCartCommandFactoryInterface',
                'Sylius\Bundle\ResourceBundle\Model\ResourceLogEntry' => 'Sylius\Component\Resource\Model\ResourceLogEntry',
            ],
        ]]);
};

<?php

declare(strict_types=1);

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\State;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchInterface;

class CreateTestSimpleProduct implements DataPatchInterface
{

    protected ProductInterfaceFactory $productInterfaceFactory;
    protected ProductRepositoryInterface $productRepository;
    protected State $appState;
    protected EavSetup $eavSetup;
    protected CategoryLinkManagementInterface $categoryLink;

    public function __construct(
        ProductInterfaceFactory         $productInterfaceFactory,
        ProductRepositoryInterface      $productRepository,
        State                           $appState,
        EavSetup                        $eavSetup,
        CategoryLinkManagementInterface $categoryLink
    )
    {
        $this->appState = $appState;
        $this->productInterfaceFactory = $productInterfaceFactory;
        $this->productRepository = $productRepository;
        $this->eavSetup = $eavSetup;
        $this->categoryLink = $categoryLink;
    }

    /**
     * Add new product
     * @throws \Exception
     */
    public function apply(): PatchInterface
    {
        $this->appState->emulateAreaCode('adminhtml', [$this, 'execute']);
        return $this;
    }

    /**
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws ValidationException
     */
    public function execute(): void
    {
        try {
            // if element is found nothing happens
            // if it is not found, throws exception and adds product
            // wasn't able to find a better way to do it
            $this->productRepository->get('simple-test-product');
        } catch (NoSuchEntityException) {
            $product = $this->productInterfaceFactory->create();
            $attributeSetId = $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default');
            $product->setTypeId(Type::TYPE_SIMPLE)
                ->setAttributeSetId($attributeSetId)
                ->setName('Simple Test Product')
                ->setSku('simple-test-product')
                ->setPrice(12.11)
                ->setVisibility(Visibility::VISIBILITY_BOTH)
                ->setStatus(Status::STATUS_ENABLED);

            $product = $this->productRepository->save($product);

            $this->categoryLink->assignProductToCategories($product->getSku(), [2]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getAliases(): array
    {
        return [];
    }
}

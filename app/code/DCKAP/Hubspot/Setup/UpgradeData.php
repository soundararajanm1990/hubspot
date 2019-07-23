<?php
namespace DCKAP\Hubspot\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Config;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class UpgradeData implements UpgradeDataInterface
{

    private $customerSetupFactory;

    private $attributeSetFactory;
    
    private $eavSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    )
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }


    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();        
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
           
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();
            $attributeSet = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

            $customerSetup->addAttribute(
                Customer::ENTITY,
                'vid',
                [
                    'type' => 'int',
                    'label' => 'Contact ID',
                    'input' => 'text',
                    'required' => false,
                    'default' => '0',
                    'visible' => true,
                    'user_defined' => false,
                    'sort_order' => 250,
                    'position' => 250,
                    'system' => false,
                ]
            );

            $customAttribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'vid');
            $customAttribute->addData(
                [
                    'attribute_set_id' => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms' => ['adminhtml_customer']
                ]
            );
            $customAttribute->save();
            $setup->endSetup();
        }       
        
        $setup->endSetup();

    }

}

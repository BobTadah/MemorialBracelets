<?php

namespace MemorialBracelets\ContactFormFix\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $installer->getConnection()
            ->modifyColumn(
                'webforms_fieldsets',
                'name',
                [
                    'type' => 'text'
                ]
            );

        $installer->endSetup();
    }
}

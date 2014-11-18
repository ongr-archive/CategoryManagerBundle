<?php

/*
 *************************************************************************
 * NFQ eXtremes CONFIDENTIAL
 * [2013] - [2014] NFQ eXtremes UAB
 * All Rights Reserved.
 *************************************************************************
 * NOTICE:
 * All information contained herein is, and remains the property of NFQ eXtremes UAB.
 * Dissemination of this information or reproduction of this material is strictly forbidden
 * unless prior written permission is obtained from NFQ eXtremes UAB.
 *************************************************************************
 */

namespace Fox\CategoryManagerBundle\Tests\Functional\DependencyInjection\Compiler;

use Fox\CategoryManagerBundle\DependencyInjection\Compiler\ProviderCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ProviderCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for process()
     *
     * @return array
     */
    public function getProcessData()
    {
        $out = [];

        // case #0 tagged provider service is available
        $definition = $this->getMockBuilder('Symfony\\Component\\DependencyInjection\\Definition')
            ->disableOriginalConstructor()
            ->getMock();
        $definition->expects($this->once())
            ->method('addMethodCall')
            ->with('addProvider', [new Reference('test.provider.name'), 'test_provider_id']);

        $container = $this->getMock('Symfony\\Component\\DependencyInjection\\ContainerBuilder');
        $container->expects($this->once())
            ->method('getDefinition')
            ->with('fox_category_manager.transfer_manager')
            ->willReturn($definition);
        $container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('fox_category_manager.provider')
            ->willReturn([
                'test.provider.name' => [
                    0 => [
                        'id' => 'test_provider_id',
                    ],
                ],
            ]);

        $out[] = [$container];

        // case #1 tagged provider service is not available
        $definition = $this->getMockBuilder('Symfony\\Component\\DependencyInjection\\Definition')
            ->disableOriginalConstructor()
            ->getMock();
        $definition->expects($this->never())
            ->method('addMethodCall');

        $container = $this->getMock('Symfony\\Component\\DependencyInjection\\ContainerBuilder');
        $container->expects($this->once())
            ->method('getDefinition')
            ->with('fox_category_manager.transfer_manager')
            ->willReturn($definition);
        $container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('fox_category_manager.provider')
            ->willReturn([]);

        return $out;
    }

    /**
     * Test for process()
     *
     * @param \PHPUnit_Framework_MockObject_MockObject|ContainerBuilder $container
     *
     * @dataProvider getProcessData
     */
    public function testProcess($container)
    {
        $compilerPass = new ProviderCompilerPass();
        $compilerPass->process($container);
    }

    /**
     * Test for process() with tagged provider without an id attribute
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage fox_category_manager.provider
     */
    public function testProcessMissingId()
    {
        $container = $this->getMock('Symfony\\Component\\DependencyInjection\\ContainerBuilder');

        $container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('fox_category_manager.provider')
            ->willReturn([
                'test.provider.name' => [
                    0 => [],
                ],
            ]);

        $compilerPass = new ProviderCompilerPass();
        $compilerPass->process($container);
    }
}

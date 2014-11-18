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

use Fox\CategoryManagerBundle\DependencyInjection\Compiler\WriterCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class WriterCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for process()
     *
     * @return array
     */
    public function getProcessData()
    {
        $out = [];

        // case #0 tagged writer service is available
        $definition = $this->getMockBuilder('Symfony\\Component\\DependencyInjection\\Definition')
            ->disableOriginalConstructor()
            ->getMock();
        $definition->expects($this->once())
            ->method('addMethodCall')
            ->with('addWriter', [new Reference('test.writer.name'), 'test_writer_id']);

        $container = $this->getMock('Symfony\\Component\\DependencyInjection\\ContainerBuilder');
        $container->expects($this->once())
            ->method('getDefinition')
            ->with('fox_category_manager.transfer_manager')
            ->willReturn($definition);
        $container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('fox_category_manager.writer')
            ->willReturn([
                'test.writer.name' => [
                    0 => [
                        'id' => 'test_writer_id',
                    ],
                ],
            ]);

        $out[] = [$container];

        // case #1 tagged writer service is not available
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
            ->with('fox_category_manager.writer')
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
        $compilerPass = new WriterCompilerPass();
        $compilerPass->process($container);
    }

    /**
     * Test for process() with tagged writer without an id attribute
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage fox_category_manager.writer
     */
    public function testProcessMissingId()
    {
        $container = $this->getMock('Symfony\\Component\\DependencyInjection\\ContainerBuilder');

        $container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('fox_category_manager.writer')
            ->willReturn([
                'test.writer.name' => [
                    0 => [],
                ],
            ]);

        $compilerPass = new WriterCompilerPass();
        $compilerPass->process($container);
    }
}

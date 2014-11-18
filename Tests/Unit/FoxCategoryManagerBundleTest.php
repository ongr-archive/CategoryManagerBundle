<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\Tests\Unit;

use ONGR\CategoryManagerBundle\ONGRCategoryManagerBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

class ONGRCategoryBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * List of passes, which should not be added to compiler
     *
     * @var array
     */
    protected $passesBlacklist = array();

    /**
     * Check whether all Passes in DependencyInjection/Compiler/ are added to container
     */
    public function testPassesRegistered()
    {
        $container = new ContainerBuilder();
        $bundle = new ONGRCategoryManagerBundle();
        $bundle->build($container);

        /** @var array $loadedPasses Array of class names of loaded passes*/
        $loadedPasses = array();
        /** @var PassConfig $passConfig */
        $passConfig = $container->getCompiler()->getPassConfig();
        foreach ($passConfig->getPasses() as $pass) {
            $classPath = explode("\\", get_class($pass));
            $loadedPasses[] = end($classPath);
        }

        $finder = new Finder();
        $finder->files()->in(__DIR__ . "/../../DependencyInjection/Compiler/");

        /** @var $file SplFileInfo */
        foreach ($finder as $file) {
            $passName = str_replace(".php", "", $file->getFilename());
            // check whether pass is not blacklisted and not added by bundle
            if (!in_array($passName, $this->passesBlacklist)) {
                $this->assertContains(
                    $passName,
                    $loadedPasses,
                    sprintf(
                        "Compiler pass '%s' is not added to container or not blacklisted in test.",
                        $passName
                    )
                );
            }
        }
    }
}

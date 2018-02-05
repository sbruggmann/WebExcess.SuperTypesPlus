<?php
namespace WebExcess\SuperTypesPlus\Aspects;

use Neos\Flow\Annotations as Flow;
use WebExcess\SuperTypesPlus\Traits\NodeTypeConfigurationEnrichmentTrait;

/**
 * @Flow\Aspect
 */
class NodeTypeManagerAspect
{
    use NodeTypeConfigurationEnrichmentTrait;

    /**
     * @Flow\Around("method(Neos\ContentRepository\Domain\Service\NodeTypeManager->evaluateSuperTypesConfiguration())")
     */
    public function mixinEvaluateSuperTypesConfiguration(\Neos\Flow\AOP\JoinPointInterface $joinPoint)
    {
        $superTypesConfiguration = $joinPoint->getMethodArgument('superTypesConfiguration');
        $completeNodeTypeConfiguration = $joinPoint->getMethodArgument('completeNodeTypeConfiguration');

        $superTypes = [];
        foreach ($superTypesConfiguration as $superTypeName => $enabled) {
            if (is_array($enabled)) {
                $superTypeModification = $enabled;
                $superTypeConfiguration = $completeNodeTypeConfiguration[$superTypeName];

                // set absolute i18n translations path and id..
                $this->addLabelsToNodeTypeConfiguration($superTypeName, $superTypeConfiguration);

                reset($superTypeModification);
                if (is_numeric(key($superTypeModification))) {
                    foreach ($superTypeModification as $superTypeModificationItem) {
                        if ($superTypeModificationItem === true || (array_key_exists('*', $superTypeModificationItem) && $superTypeModificationItem['*'] === true)) {
                            /**
                             * superTypes:
                             *   'Vendor.Package:SuperType':
                             *     -
                             *       true                       <--
                             *     -
                             *       '*': true                  <--
                             *     -
                             *       properties:
                             *         'fromName': 'toName'
                             */
                            $superTypes[$superTypeName] = $joinPoint->getProxy()->publicEvaluateSuperTypeConfiguration($superTypeName, true, $completeNodeTypeConfiguration);

                        } else {
                            /**
                             * superTypes:
                             *   'Vendor.Package:SuperType':
                             *     -
                             *       true
                             *     -
                             *       properties:
                             *         'fromName': 'toName'     <--
                             */
                            $joinPoint->getProxy()->generateNodeType($superTypeName, $superTypeModificationItem, $superTypeConfiguration, $completeNodeTypeConfiguration, $superTypes);
                        }
                    }
                } else {
                    /**
                     * superTypes:
                     *   'Vendor.Package:SuperType': [...]      <--
                     */
                    $joinPoint->getProxy()->generateNodeType($superTypeName, $superTypeModification, $superTypeConfiguration, $completeNodeTypeConfiguration, $superTypes);
                }
            } else {
                /**
                 * superTypes:
                 *   'Vendor.Package:SuperType': true           <--
                 */
                $superTypes[$superTypeName] = $joinPoint->getProxy()->publicEvaluateSuperTypeConfiguration($superTypeName, $enabled, $completeNodeTypeConfiguration);
            }
        }

        return $superTypes;
    }

}

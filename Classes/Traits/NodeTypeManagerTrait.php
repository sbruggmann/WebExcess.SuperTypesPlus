<?php
namespace WebExcess\SuperTypesPlus\Traits;

trait NodeTypeManagerTrait
{

    /**
     * @param $superTypeName
     * @param $enabled
     * @param $completeNodeTypeConfiguration
     * @return mixed
     */
    public function publicEvaluateSuperTypeConfiguration($superTypeName, $enabled, &$completeNodeTypeConfiguration)
    {
        if (!is_bool($enabled)) {
            $enabled = true;
        }
        return $this->evaluateSuperTypeConfiguration($superTypeName, $enabled, $completeNodeTypeConfiguration);
    }

    /**
     * @param $originalSuperTypeName
     * @param $nodeTypeConfiguration
     * @return string
     */
    public function getGeneratedNodeTypeName($originalSuperTypeName, $nodeTypeConfiguration)
    {
        if (array_key_exists('_name', $nodeTypeConfiguration)) {
            return $nodeTypeConfiguration['_name'];
        } else {
            return $originalSuperTypeName . '-' . sha1(serialize($nodeTypeConfiguration));
        }
    }

    /**
     * @param $superTypeName
     * @param $superTypeModification
     * @param $superTypeConfiguration
     * @param $completeNodeTypeConfiguration
     * @param $superTypes
     */
    public function generateNodeType($superTypeName, $superTypeModification, $superTypeConfiguration, &$completeNodeTypeConfiguration, &$superTypes)
    {
        $mappingWalk = function ($superTypeModification, $superTypeConfiguration) use (&$mappingWalk) {
            foreach ($superTypeModification as $key => $value) {
                /**
                 * superTypes:
                 *   'Vendor.Package:SuperType': false  <--
                 */
                if ($value === false && array_key_exists($key, $superTypeConfiguration)) {
                    unset($superTypeConfiguration[$key]);

                /**
                 * superTypes:
                 *   'Vendor.Package:SuperType':
                 *     'fromName': 'toName'             <--
                 */
                } elseif (is_string($value) && array_key_exists($key, $superTypeConfiguration)) {
                    $superTypeConfiguration[$value] = $superTypeConfiguration[$key];
                    unset($superTypeConfiguration[$key]);

                /**
                 * superTypes:
                 *   'Vendor.Package:SuperType':
                 *     properties:                      <--
                 *       'fromName': 'toName'
                 */
                } elseif (is_array($value) && !is_numeric($key) && array_key_exists($key, $superTypeConfiguration)) {
                    list($superTypeModification[$key], $superTypeConfiguration[$key]) = $mappingWalk($value, $superTypeConfiguration[$key]);

                /**
                 * superTypes:
                 *   'Vendor.Package:SuperType':
                 *     properties:
                 *       '*': 'to*'                     <--
                 */
                } elseif ($key == '*') {
                    foreach ($superTypeConfiguration as $propertyName => $propertyConfiguration) {
                        $asteriskPosition = strpos($value, '*');
                        if ($asteriskPosition >= 0) {
                            $newPropertyName = str_replace('*', ($asteriskPosition == 0 ? lcfirst($propertyName) : ucfirst($propertyName)), $value);
                            $superTypeConfiguration[$newPropertyName] = $propertyConfiguration;
                            if ($value != '*') {
                                unset($superTypeConfiguration[$propertyName]);
                            }
                        }
                    }
                }
            }

            return array($superTypeModification, $superTypeConfiguration);
        };

        list($superTypeModification, $superTypeConfiguration) = $mappingWalk($superTypeModification, $superTypeConfiguration);
        $generatedSuperTypeName = $this->getGeneratedNodeTypeName($superTypeName, $superTypeModification);

        $completeNodeTypeConfiguration[$generatedSuperTypeName] = $superTypeConfiguration;
        $superTypes[$generatedSuperTypeName] = $this->publicEvaluateSuperTypeConfiguration($generatedSuperTypeName, true, $completeNodeTypeConfiguration);
    }

}

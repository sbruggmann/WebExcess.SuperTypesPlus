<?php

namespace WebExcess\SuperTypesPlus\Aspects;

use Neos\Flow\Annotations as Flow;

/**
 * An aspect for demonstrating trait introduction
 *
 * Introduces Example\MyPackage\SomeTrait to the class Example\MyPackage\MyClass:
 *
 * @Flow\Introduce("class(Neos\ContentRepository\Domain\Service\NodeTypeManager)", traitName="WebExcess\SuperTypesPlus\Traits\NodeTypeManagerTrait")
 * @Flow\Aspect
 */
class NodeTypeManagerTraitAspect
{
}

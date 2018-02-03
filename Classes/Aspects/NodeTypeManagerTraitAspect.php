<?php

namespace WebExcess\SuperTypesPlus\Aspects;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Introduce("class(Neos\ContentRepository\Domain\Service\NodeTypeManager)", traitName="WebExcess\SuperTypesPlus\Traits\NodeTypeManagerTrait")
 * @Flow\Aspect
 */
class NodeTypeManagerTraitAspect
{
}

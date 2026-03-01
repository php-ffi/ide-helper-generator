<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser\Builder;

use FFI\Generator\Node\RecordFieldNode;
use FFI\Generator\Node\Type\RecordTypeNode;

/**
 * @template T of RecordTypeNode
 *
 * @template-implements BuilderInterface<T>
 */
abstract readonly class RecordTypeNodeTypeBuilder extends TypeBuilder
{
    abstract public function create(\DOMElement $el): RecordTypeNode;

    /**
     * @param T $node
     */
    protected function fillRecordFields(RecordTypeNode $node, \DOMElement $el): void
    {
        $members = $el->getAttribute('members');

        if ($members === '') {
            return;
        }

        foreach ($this->document->getElementsByIdListString($members) as $member) {
            if ($member->nodeName !== 'Field') {
                continue;
            }

            $node->fields[] = $this->createRecordFieldNode($member);
        }
    }

    private function createRecordFieldNode(\DOMElement $el): RecordFieldNode
    {
        $name = $el->getAttribute('name');

        return new RecordFieldNode(
            name: $name === '' ? null : $name,
            type: $this->getTypeReference($el, 'type'),
        );
    }
}
